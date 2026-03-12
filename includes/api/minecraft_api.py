import os
import sys
import subprocess
import logging
import time
import socket
from flask import Flask, request, jsonify
from functools import wraps

app = Flask(__name__)

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

API_KEY = "6CeuzFgZu7WJko0x3i1KcIH82PJsaNzYvFPQcPto+F8="

CREATE_NO_WINDOW = 0x08000000

# Database connection - use host.docker.internal for Windows to reach Docker MySQL
DB_CONFIG = {
    'host': '192.168.1.59',
    'port': 8005,
    'user': 'root',
    'password': 'nouveaumotdepasse123',
    'database': 'minecraft_panel'
}

# Keep DB connection alive
_db_conn = None

def get_db_connection():
    global _db_conn
    try:
        if _db_conn and _db_conn.open:
            return _db_conn
        import pymysql
        _db_conn = pymysql.connect(
            host=DB_CONFIG['host'],
            port=DB_CONFIG.get('port', 3306),
            user=DB_CONFIG['user'],
            password=DB_CONFIG['password'],
            database=DB_CONFIG['database'],
            charset='utf8mb4',
            cursorclass=pymysql.cursors.DictCursor,
            autocommit=True
        )
        return _db_conn
    except Exception as e:
        logger.error(f"DB connection error: {e}")
        return None
        return None

def require_apikey(f):
    @wraps(f)
    def decorated_function(*args, **kwargs):
        api_key = request.headers.get('X-API-Key')
        logger.info(f"API Key received: {api_key}, Expected: {API_KEY}")
        if api_key != API_KEY:
            return jsonify({"error": "Unauthorized"}), 401
        return f(*args, **kwargs)
    return decorated_function

_cache = {}
CACHE_TTL = 10
_recent_commands = {}

def get_cached(key):
    now = time.time()
    if key in _cache:
        val, ts = _cache[key]
        if now - ts < CACHE_TTL:
            return val
    return None

def set_cached(key, val):
    _cache[key] = (val, time.time())

def get_server_config(server_id):
    cached = get_cached(f"config_{server_id}")
    if cached is not None:
        return cached
    
    conn = get_db_connection()
    if not conn:
        return None
    try:
        with conn.cursor() as cursor:
            cursor.execute("SELECT * FROM servers WHERE id = %s", (server_id,))
            result = cursor.fetchone()
            if result:
                set_cached(f"config_{server_id}", result)
            return result
    finally:
        conn.close()

def get_all_servers():
    conn = get_db_connection()
    if not conn:
        return []
    try:
        with conn.cursor() as cursor:
            cursor.execute("SELECT id FROM servers")
            return [row["id"] for row in cursor.fetchall()]
    finally:
        conn.close()

def is_server_running(server_id):
    """Returns (running: bool, pid: int or None)"""
    config = get_server_config(server_id)
    if not config:
        return False, None
    
    server_path = config.get('path', '')
    if not server_path:
        return False, None
    
    # Get directory from JAR path
    if server_path.lower().endswith('.jar'):
        search_dir = os.path.dirname(server_path)
    else:
        search_dir = server_path
    
    search_dir_lower = search_dir.lower().replace('/', '\\')
    # Also extract just the jar filename for matching
    jar_filename = os.path.basename(server_path).lower() if server_path.lower().endswith('.jar') else ''
    
    logger.info("is_server_running: checking for server at path: {} (jar: {})".format(search_dir_lower, jar_filename))
    
    # Try PowerShell to get command line
    try:
        import subprocess
        ps_script = "Get-Process java | ForEach-Object { $cmd = (Get-CimInstance Win32_Process -Filter \"ProcessId=$($_.Id)\").CommandLine; Write-Output \"$($_.Id)|$cmd\" }"
        result = subprocess.run(
            ['powershell', '-Command', ps_script],
            capture_output=True,
            text=True,
            timeout=10
        )
        if result.stdout:
            for line in result.stdout.strip().split('\n'):
                if '|' in line:
                    parts = line.split('|', 1)
                    if len(parts) == 2:
                        pid_str, cmdline = parts
                        cmdline = cmdline.lower() if cmdline else ''
                        logger.info("PowerShell check - PID: {}, cmdline: {}".format(pid_str, cmdline[:100] if cmdline else 'none'))
                        # Check by full path or by jar filename
                        if search_dir_lower in cmdline.replace('/', '\\') or (jar_filename and jar_filename in cmdline):
                            logger.info("is_server_running: MATCH FOUND! PID={}".format(pid_str))
                            return True, int(pid_str) if pid_str and pid_str.isdigit() else None
    except Exception as e:
        logger.error("PowerShell error: {}".format(e))
    
    # Use psutil as fallback - but this time be more careful about matching
    try:
        import psutil
        for proc in psutil.process_iter():
            try:
                name = proc.name()
                if name and 'java' in name.lower():
                    cmdline = proc.cmdline()
                    if cmdline:
                        cmdline_str = ' '.join(cmdline).lower()
                        logger.info("psutil check - PID: {}, cmdline: {}".format(proc.pid, cmdline_str[:100]))
                        # Check by jar filename since cwd is not in cmdline
                        if jar_filename and jar_filename in cmdline_str:
                            logger.info("is_server_running: MATCH FOUND via psutil (jar match)! PID={}".format(proc.pid))
                            return True, proc.pid
                        # Also check if search_dir is anywhere in the command (unlikely but possible)
                        if search_dir_lower in cmdline_str.replace('/', '\\'):
                            logger.info("is_server_running: MATCH FOUND via psutil (path match)! PID={}".format(proc.pid))
                            return True, proc.pid
            except:
                continue
    except Exception as e:
        logger.error("psutil error: {}".format(e))
    
    return False, None

def send_rcon_command(host, port, password, command):
    try:
        import socket, struct
        
        sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        sock.settimeout(10)
        sock.connect((host, int(port)))
        
        request_id = 1
        
        def send_packet(req_id, packet_type, payload):
            payload_bytes = payload.encode('utf-8') + b'\x00\x00'
            data = struct.pack('<ii', req_id, packet_type) + payload_bytes
            return struct.pack('<i', len(data)) + data
        
        auth_packet = send_packet(request_id, 3, password)
        sock.send(auth_packet)
        
        auth_resp = sock.recv(4096)
        
        exec_packet = send_packet(request_id, 2, command)
        sock.send(exec_packet)
        
        response = sock.recv(4096)
        sock.close()
        
        if response and len(response) > 12:
            resp_data = response[12:]
            null_idx = resp_data.find(b'\x00')
            if null_idx > 0:
                resp_data = resp_data[:null_idx]
            return resp_data.decode('utf-8', errors='ignore')
        
        return ""
    except Exception as e:
        logger.error("RCON error: {}".format(e))
        return None

def send_screen_command(server_id, command):
    try:
        config = get_server_config(server_id)
        if not config:
            return False
        
        server_path = config.get('path', '')
        if not server_path:
            return False
        
        if server_path.lower().endswith('.jar'):
            server_path = os.path.dirname(server_path)
        
        props_file = os.path.join(server_path, 'server.properties')
        rcon_port = 25575
        rcon_password = ""
        rcon_enabled = False
        
        if os.path.exists(props_file):
            with open(props_file, 'r') as f:
                for line in f:
                    line = line.strip()
                    if line.startswith('enable-rcon='):
                        rcon_enabled = line.split('=')[1].lower() == 'true'
                    elif line.startswith('rcon.port='):
                        rcon_port = int(line.split('=')[1])
                    elif line.startswith('rcon.password='):
                        rcon_password = line.split('=')[1]
        
        if not rcon_enabled or not rcon_password:
            logger.warning("RCON not enabled for server {}".format(server_id))
            return False
        
        result = send_rcon_command('127.0.0.1', rcon_port, rcon_password, command)
        logger.info("RCON command sent to server {}: {}".format(server_id, command))
        return result
    except Exception as e:
        logger.error("Error sending command: {}".format(e))
        return False

def get_java_process_info(server_path):
    try:
        import psutil
        search_dir = server_path
        if server_path.lower().endswith('.jar'):
            search_dir = os.path.dirname(server_path)
        search_dir_lower = search_dir.lower().replace('/', '\\')
        
        jar_filename = os.path.basename(server_path).lower() if server_path.lower().endswith('.jar') else ''
        
        java_procs_data = []
        
        for proc in psutil.process_iter():
            try:
                name = proc.name()
                if name and 'java' in name.lower():
                    cmdline = proc.cmdline()
                    if cmdline:
                        cmdline_str = ' '.join(cmdline).lower()
                        if search_dir_lower in cmdline_str.replace('/', '\\') or (jar_filename and jar_filename in cmdline_str):
                            java_procs_data.append({
                                'proc': proc,
                                'cmdline': cmdline_str
                            })
            except:
                continue
        
        if java_procs_data:
            for pd in java_procs_data:
                pd['proc'].cpu_percent()
            
            import time
            time.sleep(0.3)
            
            for pd in java_procs_data:
                try:
                    proc = pd['proc']
                    cpu = proc.cpu_percent()
                    ram = proc.memory_info().rss / (1024 * 1024)
                    return {
                        'cpu': round(cpu, 1),
                        'ram': round(ram, 1),
                        'pid': proc.pid
                    }
                except:
                    continue
        
    except Exception as e:
        logger.error("Error getting java process info: {}".format(e))
    
    return {'cpu': 0, 'ram': 0, 'pid': None}

@app.route('/status/<int:server_id>', methods=['GET'])
@require_apikey
def get_status(server_id):
    # Use cached status if available (1 second TTL)
    cache_key = f"status_{server_id}"
    cached = get_cached(cache_key)
    if cached is not None:
        return jsonify(cached)
    
    config = get_server_config(server_id)
    if not config:
        return jsonify({"error": "Server not found"}), 404

    running, server_pid = is_server_running(server_id)
    cpu = 0
    ram = 0
    tps = 20
    current_players = 0
    disk_usage = 0
    
    # Check starting/stopping state
    is_starting, status = is_server_starting_or_stopping(server_id)
    starting = is_starting
    stopping = status == 'stopping'
    
    if running:
        server_path = config.get('path', '')
        if server_path:
            info = get_java_process_info(server_path)
            cpu = info['cpu']
            ram = info['ram']
            
            # Get disk usage
            try:
                if os.path.exists(server_path):
                    total = 0
                    used = 0
                    for dirpath, dirnames, filenames in os.walk(server_path):
                        for f in filenames:
                            fp = os.path.join(dirpath, f)
                            try:
                                used += os.path.getsize(fp)
                            except:
                                pass
                    disk_usage = round(used / (1024 * 1024 * 1024), 2)  # GB
            except:
                pass
        
        tps = get_tps(server_id)
        current_players = get_player_count(server_id)

    max_players = config.get('max_players', 20)
    
    result = {
        "running": running,
        "online": running,
        "starting": starting,
        "stopping": stopping,
        "current_players": current_players,
        "max_players": max_players,
        "cpu": round(cpu, 1),
        "ram": round(ram, 1),
        "disk": disk_usage,
        "tps": tps
    }
    
    set_cached(cache_key, result)
    return jsonify(result)

def get_tps(server_id):
    cached = get_cached(f"tps_{server_id}")
    if cached is not None:
        return cached
    
    try:
        config = get_server_config(server_id)
        if not config:
            return 20
        
        server_path = config.get('path', '')
        if not server_path:
            return 20
        
        log_file, is_gzipped = find_latest_log_for_path(server_path)
        if log_file:
            if is_gzipped:
                import gzip
                with gzip.open(log_file, 'rt', encoding='utf-8', errors='ignore') as f:
                    lines = f.readlines()
                    for line in reversed(lines[-20:]):
                        if 'TPS' in line or 'tps' in line:
                            import re
                            match = re.search(r'(\d+\.?\d*)\s*TPS', line, re.IGNORECASE)
                            if match:
                                tps = float(match.group(1))
                                set_cached(f"tps_{server_id}", tps)
                                return tps
            else:
                with open(log_file, 'r', encoding='utf-8', errors='ignore') as f:
                    lines = f.readlines()
                    for line in reversed(lines[-20:]):
                        if 'TPS' in line or 'tps' in line:
                            import re
                            match = re.search(r'(\d+\.?\d*)\s*TPS', line, re.IGNORECASE)
                            if match:
                                tps = float(match.group(1))
                                set_cached(f"tps_{server_id}", tps)
                                return tps
    except Exception as e:
        logger.error("Error getting TPS: {}".format(e))
    return 20

def get_player_count(server_id):
    cached = get_cached(f"players_{server_id}")
    if cached is not None:
        return cached
    
    try:
        config = get_server_config(server_id)
        if not config:
            return 0
        
        server_path = config.get('path', '')
        
        if server_path:
            log_file, is_gzipped = find_latest_log_for_path(server_path)
            if log_file:
                if is_gzipped:
                    import gzip
                    with gzip.open(log_file, 'rt', encoding='utf-8', errors='ignore') as f:
                        lines = f.readlines()
                        for line in reversed(lines[-15:]):
                            if 'players online' in line.lower() or 'there are' in line.lower():
                                import re
                                match = re.search(r'(\d+)\s*/\s*(\d+)', line)
                                if match:
                                    players = int(match.group(1))
                                    set_cached(f"players_{server_id}", players)
                                    return players
                else:
                    with open(log_file, 'r', encoding='utf-8', errors='ignore') as f:
                        lines = f.readlines()
                        for line in reversed(lines[-15:]):
                            if 'players online' in line.lower() or 'there are' in line.lower():
                                import re
                                match = re.search(r'(\d+)\s*/\s*(\d+)', line)
                                if match:
                                    players = int(match.group(1))
                                    set_cached(f"players_{server_id}", players)
                                    return players
    except Exception as e:
        logger.error("Error getting player count: {}".format(e))
    return 0

def find_latest_log():
    server_ids = get_all_servers()
    for sid in server_ids:
        config = get_server_config(sid)
        if config:
            server_path = config.get('path', '')
            if server_path:
                logs_dir = os.path.join(server_path, 'logs')
                if os.path.exists(logs_dir):
                    log_files = [f for f in os.listdir(logs_dir) if f.endswith('.log')]
                    if log_files:
                        latest = sorted(log_files)[-1]
                        return os.path.join(logs_dir, latest)
                latest_log = os.path.join(server_path, 'logs', 'latest.log')
                if os.path.exists(latest_log):
                    return latest_log
    return None

@app.route('/start', methods=['POST'])
@require_apikey
def start_server():
    data = request.get_json()
    server_id = data.get('server_id')

    config = get_server_config(server_id)
    if not config:
        return jsonify({"error": "Server not found"}), 404

    running, _ = is_server_running(server_id)
    if running:
        return jsonify({"status": "already_running", "message": "Server already running"})

    server_path = config.get('path', '')
    if not server_path:
        return jsonify({"error": "Server path not configured"}), 400
    
    logger.info("Starting server {} with path: {}".format(server_id, server_path))
    
    # Get the directory (parent of JAR file if path ends with .jar)
    if server_path.lower().endswith('.jar'):
        work_dir = os.path.dirname(server_path)
        start_cmd = 'java -Xmx4G -Xms2G -jar "{}" nogui'.format(os.path.basename(server_path))
    else:
        work_dir = server_path
        start_cmd = 'java -Xmx4G -Xms2G -jar server.jar nogui'
    
    logger.info("Work dir: {}, Command: {}".format(work_dir, start_cmd))
    
    # Check if directory exists
    if not os.path.exists(work_dir):
        logger.error("Directory does not exist: {}".format(work_dir))
        return jsonify({"error": "Directory does not exist: " + work_dir}), 400
    
    try:
        proc = subprocess.Popen(
            start_cmd,
            cwd=work_dir,
            shell=True,
            stdout=subprocess.DEVNULL,
            stderr=subprocess.DEVNULL,
            creationflags=CREATE_NO_WINDOW
        )
        logger.info("Process started with PID: {}".format(proc.pid))
        return jsonify({"status": "success", "message": "Server start command sent", "pid": proc.pid, "work_dir": work_dir, "cmd": start_cmd})
    except Exception as e:
        logger.error("Error starting server: {}".format(e))
        return jsonify({"status": "error", "message": str(e)}), 500

@app.route('/stop', methods=['POST'])
@require_apikey
def stop_server():
    data = request.get_json()
    server_id = data.get('server_id')

    config = get_server_config(server_id)
    if not config:
        return jsonify({"error": "Server not found"}), 404

    running, server_pid = is_server_running(server_id)
    if not running:
        return jsonify({"status": "already_stopped", "message": "Server not running"})

    send_screen_command(server_id, "stop")
    logger.info("Stopping server {}".format(server_id))

    import time
    for i in range(10):
        time.sleep(2)
        if not is_server_running(server_id):
            return jsonify({"status": "success", "message": "Server stopped"})
    
    if server_pid:
        try:
            subprocess.run(['taskkill', '/F', '/PID', str(server_pid)], 
                         stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
            time.sleep(3)
        except Exception as e:
            logger.error("Error forcing stop: {}".format(e))

    import psutil
    java_still_running = False
    for proc in psutil.process_iter():
        try:
            if proc.name().lower() == 'java.exe':
                java_still_running = True
                break
        except:
            pass
    
    if not java_still_running:
        return jsonify({"status": "success", "message": "Server stopped"})
    
    return jsonify({"status": "error", "message": "Server failed to stop"})

@app.route('/command', methods=['POST'])
@require_apikey
def send_command():
    data = request.get_json()
    server_id = data.get('server_id')
    command = data.get('command')

    if not server_id or not command:
        return jsonify({"error": "Missing server_id or command"}), 400

    config = get_server_config(server_id)
    if not config:
        return jsonify({"error": "Server not found"}), 404

    running, _ = is_server_running(server_id)
    if not running:
        return jsonify({"error": "Server not running"}), 400

    response = send_screen_command(server_id, command)
    
    if response is not None:
        import datetime
        timestamp = datetime.datetime.now().strftime("%H:%M:%S")
        
        response = response.strip()
        
        if command == 'help' and '/' in response:
            response = response.replace('/', '\n/')
        
        if server_id not in _recent_commands:
            _recent_commands[server_id] = []
        
        log_entry = "[{}] [Server thread/INFO]: <Console> {}".format(timestamp, command)
        _recent_commands[server_id].append(log_entry)
        
        if response:
            for line in response.split('\n'):
                line = line.strip()
                if line:
                    resp_entry = "[{}] [Server thread/INFO]: {}".format(timestamp, line)
                    _recent_commands[server_id].append(resp_entry)
        
        if len(_recent_commands[server_id]) > 30:
            _recent_commands[server_id] = _recent_commands[server_id][-30:]
        
        logger.info("Command sent to server {}: {}".format(server_id, command))
        return jsonify({"status": "success", "message": "Command sent", "response": response})

    return jsonify({"error": "Failed to send command"}), 500

@app.route('/health', methods=['GET'])
def health():
    return jsonify({"status": "ok", "timestamp": int(time.time())})

@app.route('/logs/<int:server_id>', methods=['GET'])
@require_apikey
def get_logs(server_id):
    config = get_server_config(server_id)
    if not config:
        return jsonify({"error": "Server not found"}), 404
    
    server_path = config.get('path', '')
    logger.info("get_logs: server_path from config = {}".format(server_path))
    if not server_path:
        return jsonify({"logs": []})
    
    try:
        log_file, is_gzipped = find_latest_log_for_path(server_path)
        logger.info("get_logs: log_file={}, is_gzipped={}".format(log_file, is_gzipped))
        
        if not log_file:
            return jsonify({"logs": []})
        
        if is_gzipped:
            import gzip
            with gzip.open(log_file, 'rt', encoding='utf-8', errors='ignore') as f:
                lines = f.readlines()
                last_lines = lines[-1000:] if len(lines) > 1000 else lines
        else:
            with open(log_file, 'r', encoding='utf-8', errors='ignore') as f:
                lines = f.readlines()
                last_lines = lines[-1000:] if len(lines) > 1000 else lines
        
        logs = [line.strip() for line in last_lines if line.strip()]
        
        if server_id in _recent_commands:
            logs.extend(_recent_commands[server_id])
        
        return jsonify({"logs": logs})
    except Exception as e:
        logger.error("Error reading logs: {}".format(e))
        return jsonify({"logs": [], "error": str(e)})

def find_latest_log_for_path(server_path):
    # Handle case where path is a JAR file, not a directory
    if server_path.lower().endswith('.jar'):
        server_path = os.path.dirname(server_path)
    
    # FIRST: Check latest.log for real-time console (highest priority)
    latest_log = os.path.join(server_path, 'logs', 'latest.log')
    if os.path.exists(latest_log) and os.path.getsize(latest_log) > 0:
        return latest_log, False
    
    # SECOND: Check archived .log.gz files
    logs_dir = os.path.join(server_path, 'logs')
    if os.path.exists(logs_dir):
        # Get all .log and .log.gz files except latest.log
        log_files = [f for f in os.listdir(logs_dir) if (f.endswith('.log') or f.endswith('.log.gz')) and f != 'latest.log']
        if log_files:
            # Sort and get the latest by modification time
            log_files_with_time = [(f, os.path.getmtime(os.path.join(logs_dir, f))) for f in log_files]
            log_files_with_time.sort(key=lambda x: x[1], reverse=True)
            latest = log_files_with_time[0][0]
            return os.path.join(logs_dir, latest), latest.endswith('.gz')
    
    return None, False

@app.route('/files/list', methods=['POST'])
@app.route('/files', methods=['POST'])
@app.route('/file/list', methods=['POST'])
@require_apikey
def list_files():
    data = request.get_json()
    server_id = data.get('server_id')
    relative_path = data.get('path', '')

    server_path = get_server_directory(server_id)
    if not server_path:
        return jsonify({"error": "Server not found or path not configured"}), 404

    target_path = os.path.join(server_path, relative_path)
    target_path = os.path.normpath(target_path)
    server_path = os.path.normpath(server_path)

    if not target_path.startswith(server_path):
        return jsonify({"error": "Access denied: path outside server directory"}), 403

    if not os.path.exists(target_path):
        return jsonify({"error": "Path not found"}), 404

    if not os.path.isdir(target_path):
        return jsonify({"error": "Not a directory"}), 400

    try:
        items = []
        for item in os.listdir(target_path):
            item_path = os.path.join(target_path, item)
            is_directory = os.path.isdir(item_path)
            items.append({
                "name": item,
                "is_dir": is_directory,
                "size": os.path.getsize(item_path) if os.path.isfile(item_path) else 0
            })
        return jsonify({"files": items, "path": relative_path})
    except Exception as e:
        return jsonify({"error": str(e)}), 500

def get_server_directory(server_id):
    config = get_server_config(server_id)
    if not config:
        return None
    
    server_path = config.get('path', '')
    if not server_path:
        return None
    
    # If path is a file (ends with .jar), use its parent directory
    if server_path.lower().endswith('.jar'):
        return os.path.dirname(server_path)
    
    return server_path

@app.route('/files/read', methods=['POST'])
@app.route('/file/read', methods=['POST'])
@require_apikey
def read_file():
    data = request.get_json()
    server_id = data.get('server_id')
    relative_path = data.get('path', '')

    server_path = get_server_directory(server_id)
    if not server_path:
        return jsonify({"error": "Server not found or path not configured"}), 404

    target_path = os.path.join(server_path, relative_path)
    target_path = os.path.normpath(target_path)
    server_path = os.path.normpath(server_path)

    if not target_path.startswith(server_path):
        return jsonify({"error": "Access denied"}), 403

    if not os.path.isfile(target_path):
        return jsonify({"error": "Not a file"}), 400

    try:
        with open(target_path, 'r', encoding='utf-8', errors='ignore') as f:
            content = f.read()
        return jsonify({"content": content, "path": relative_path})
    except Exception as e:
        return jsonify({"error": str(e)}), 500

@app.route('/files/write', methods=['POST'])
@app.route('/file/write', methods=['POST'])
@require_apikey
def write_file():
    data = request.get_json()
    server_id = data.get('server_id')
    relative_path = data.get('path', '')
    content = data.get('content', '')
    
    try:
        import base64
        content = base64.b64decode(content).decode('utf-8')
    except:
        pass

    server_path = get_server_directory(server_id)
    if not server_path:
        return jsonify({"error": "Server not found or path not configured"}), 404

    target_path = os.path.join(server_path, relative_path)
    target_path = os.path.normpath(target_path)
    server_path = os.path.normpath(server_path)

    if not target_path.startswith(server_path):
        return jsonify({"error": "Access denied"}), 403

    try:
        os.makedirs(os.path.dirname(target_path), exist_ok=True)
        with open(target_path, 'w', encoding='utf-8') as f:
            f.write(content)
        return jsonify({"status": "success", "message": "File saved"})
    except Exception as e:
        return jsonify({"error": str(e)}), 500

@app.route('/files/mkdir', methods=['POST'])
@app.route('/file/mkdir', methods=['POST'])
@require_apikey
def make_directory():
    data = request.get_json()
    server_id = data.get('server_id')
    relative_path = data.get('path', '')

    server_path = get_server_directory(server_id)
    if not server_path:
        return jsonify({"error": "Server not found or path not configured"}), 404

    target_path = os.path.join(server_path, relative_path)
    target_path = os.path.normpath(target_path)
    server_path = os.path.normpath(server_path)

    if not target_path.startswith(server_path):
        return jsonify({"error": "Access denied"}), 403

    try:
        os.makedirs(target_path, exist_ok=True)
        return jsonify({"status": "success", "message": "Directory created"})
    except Exception as e:
        return jsonify({"error": str(e)}), 500

@app.route('/files/delete', methods=['POST'])
@app.route('/file/delete', methods=['POST'])
@require_apikey
def delete_item():
    data = request.get_json()
    server_id = data.get('server_id')
    relative_path = data.get('path', '')

    server_path = get_server_directory(server_id)
    if not server_path:
        return jsonify({"error": "Server not found or path not configured"}), 404

    target_path = os.path.join(server_path, relative_path)
    target_path = os.path.normpath(target_path)
    server_path = os.path.normpath(server_path)

    if not target_path.startswith(server_path):
        return jsonify({"error": "Access denied"}), 403

    if not os.path.exists(target_path):
        return jsonify({"error": "Path not found"}), 404

    try:
        if os.path.isdir(target_path):
            import shutil
            shutil.rmtree(target_path)
        else:
            os.remove(target_path)
        return jsonify({"status": "success", "message": "Deleted"})
    except Exception as e:
        return jsonify({"error": str(e)}), 500

@app.route('/files/rename', methods=['POST'])
@require_apikey
def rename_item():
    data = request.get_json()
    server_id = data.get('server_id')
    relative_path = data.get('path', '')
    new_relative_path = data.get('new_path', '')

    if not relative_path or not new_relative_path:
        return jsonify({"error": "Missing path or new_path"}), 400

    server_path = get_server_directory(server_id)
    if not server_path:
        return jsonify({"error": "Server not found or path not configured"}), 404

    old_path = os.path.join(server_path, relative_path)
    old_path = os.path.normpath(old_path)
    new_path = os.path.join(server_path, new_relative_path)
    new_path = os.path.normpath(new_path)
    server_path = os.path.normpath(server_path)

    if not old_path.startswith(server_path) or not new_path.startswith(server_path):
        return jsonify({"error": "Access denied"}), 403

    if not os.path.exists(old_path):
        return jsonify({"error": "Source path not found"}), 404

    if os.path.exists(new_path):
        return jsonify({"error": "Destination path already exists"}), 400

    try:
        os.rename(old_path, new_path)
        return jsonify({"status": "success", "message": "Renamed"})
    except Exception as e:
        return jsonify({"error": str(e)}), 500

# Aliases for old endpoints
@app.route('/files', methods=['POST'])
@app.route('/file/list', methods=['POST'])
@require_apikey
def list_files_alias():
    return list_files()

@app.route('/file/read', methods=['POST'])
@require_apikey
def read_file_alias():
    return read_file()

@app.route('/file/write', methods=['POST'])
@require_apikey
def write_file_alias():
    return write_file()

@app.route('/file/mkdir', methods=['POST'])
@require_apikey
def make_directory_alias():
    return make_directory()

@app.route('/file/delete', methods=['POST'])
@require_apikey
def delete_item_alias():
    return delete_item()

def check_all_servers_status():
    all_server_ids = get_all_servers()
    any_running = False
    starting_count = 0
    stopping_count = 0
    
    server_statuses = {}
    
    for sid in all_server_ids:
        running, _ = is_server_running(sid)
        if running:
            any_running = True
            server_statuses[sid] = 'running'
        else:
            server_statuses[sid] = 'stopped'
    
    return {
        'any_running': any_running,
        'starting_count': starting_count,
        'stopping_count': stopping_count,
        'server_statuses': server_statuses
    }

def is_server_starting_or_stopping(server_id):
    config = get_server_config(server_id)
    if not config:
        return False, 'stopped'
    
    server_path = config.get('path', '')
    if not server_path:
        return False, 'stopped'
    
    log_file = os.path.join(server_path, 'logs', 'latest.log')
    if not os.path.exists(log_file):
        return False, 'stopped'
    
    try:
        with open(log_file, 'r', encoding='utf-8', errors='ignore') as f:
            lines = f.readlines()[-100:]
            for line in reversed(lines):
                if 'Stopping server' in line or 'Saving chunks' in line:
                    return True, 'stopping'
                if 'Done' in line and 'Starting' not in line:
                    return False, 'running'
    except:
        pass
    
    return False, 'stopped'

@app.route('/system/status', methods=['GET'])
@require_apikey
def get_system_status():
    # Use cached system status
    cached = get_cached("system_status")
    if cached is not None:
        return jsonify(cached)
    
    status_info = check_all_servers_status()
    
    all_server_ids = get_all_servers()
    for sid in all_server_ids:
        is_starting_stop, _ = is_server_starting_or_stopping(sid)
        if is_starting_stop:
            status_info['any_running'] = True
            break
    
    result = {
        "any_server_running": status_info['any_running'],
        "starting": status_info.get('starting_count', 0) > 0,
        "stopping": status_info.get('stopping_count', 0) > 0,
        "api_running": True,
        "server_statuses": status_info.get('server_statuses', {})
    }
    
    set_cached("system_status", result)
    return jsonify(result)

@app.route('/system/stop-services', methods=['POST'])
@require_apikey
def stop_services():
    data = request.get_json()
    confirm_key = data.get('confirm_key', '')
    
    if confirm_key != "STOP_SERVICES_CONFIRM":
        return jsonify({"error": "Invalid confirmation key"}), 403
    
    status_info = check_all_servers_status()
    for sid in status_info.get('server_statuses', {}).values():
        if sid == 'running':
            return jsonify({"error": "Cannot stop services: at least one server is running"}), 400
    
    for sid in get_all_servers():
        is_starting_stop, _ = is_server_starting_or_stopping(sid)
        if is_starting_stop:
            return jsonify({"error": "Cannot stop services: a server is starting/stopping"}), 400
    
    try:
        import os
        import signal
        import subprocess
        
        script_dir = os.path.dirname(os.path.abspath(__file__))
        
        if os.name == 'nt':
            subprocess.Popen(['taskkill', '/F', '/IM', 'java.exe'], 
                           stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
            subprocess.Popen(['taskkill', '/F', '/IM', 'LibreHardwareMonitor.exe'], 
                           stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
        else:
            os.kill(os.getpid(), signal.SIGTERM)
        
        return jsonify({"status": "success", "message": "Minecraft servers stopped"})
    except Exception as e:
        return jsonify({"error": str(e)}), 500

SHUTDOWN_TOKEN = "SHUTDOWN_PC_2024_SECURE_TOKEN_xyz123"
SHUTDOWN_TOKEN_FILE = os.path.join(os.path.dirname(os.path.abspath(__file__)), '.shutdown_token')

def generate_shutdown_token():
    import secrets
    token = secrets.token_hex(32)
    with open(SHUTDOWN_TOKEN_FILE, 'w') as f:
        f.write(token)
    return token

def get_shutdown_token():
    if os.path.exists(SHUTDOWN_TOKEN_FILE):
        with open(SHUTDOWN_TOKEN_FILE, 'r') as f:
            return f.read().strip()
    return generate_shutdown_token()

@app.route('/system/shutdown-request', methods=['POST'])
@require_apikey
def request_shutdown():
    data = request.get_json()
    token = data.get('token', '')
    
    expected_token = get_shutdown_token()
    
    if token != expected_token:
        logger.warning("Invalid shutdown token attempted from {}".format(request.remote_addr))
        return jsonify({"error": "Invalid token"}), 403
    
    status_info = check_all_servers_status()
    for sid in status_info.get('server_statuses', {}).values():
        if sid == 'running':
            return jsonify({"error": "Cannot shutdown: at least one server is running"}), 400
    
    for sid in get_all_servers():
        is_starting_stop, _ = is_server_starting_or_stopping(sid)
        if is_starting_stop:
            return jsonify({"error": "Cannot shutdown: a server is starting/stopping"}), 400
    
    try:
        logger.info("Shutdown requested and confirmed - shutting down PC")
        script_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'shutdown_elevated2.ps1')
        subprocess.Popen(['powershell', '-ExecutionPolicy', 'Bypass', '-File', script_path], 
                        stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL, creationflags=CREATE_NO_WINDOW)
        return jsonify({"status": "success", "message": "PC will shutdown in 30 seconds"})
    except Exception as e:
        return jsonify({"error": str(e)}), 500

@app.route('/system/shutdown-token', methods=['GET'])
@require_apikey
def get_shutdown_token_endpoint():
    return jsonify({"token": get_shutdown_token()})

@app.route('/system/shutdown-cancel', methods=['POST'])
@require_apikey
def cancel_shutdown():
    try:
        subprocess.Popen(['cmd', '/c', 'shutdown /a'], 
                        stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
        return jsonify({"status": "success", "message": "Shutdown cancelled"})
    except Exception as e:
        return jsonify({"error": str(e)}), 500

@app.route('/test', methods=['GET', 'POST'])
def test_endpoint():
    return jsonify({"status": "ok", "message": "API is working", "method": request.method})

if __name__ == '__main__':
    logger.info("Starting Minecraft API on http://0.0.0.0:8080")
    app.run(host='0.0.0.0', port=8080, debug=False)
