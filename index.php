<?php
ob_start();
session_start();

define('BASE_PATH', __DIR__);
define('BASE_URL', '');

require_once BASE_PATH . '/core/Database.php';
require_once BASE_PATH . '/core/Auth.php';
require_once BASE_PATH . '/core/Router.php';
require_once BASE_PATH . '/core/CSRF.php';

$db = new Database();
$auth = new Auth($db);
$router = new Router();

require_once BASE_PATH . '/controllers/AuthController.php';
require_once BASE_PATH . '/controllers/ServerController.php';
require_once BASE_PATH . '/controllers/ProfileController.php';
require_once BASE_PATH . '/controllers/AdminController.php';

$authController = new AuthController($db, $auth);
$serverController = new ServerController($db, $auth);
$profileController = new ProfileController($db, $auth);
$adminController = new AdminController($db, $auth);

$router->get('/', function() use ($auth) {
    if ($auth->isLoggedIn()) {
        header('Location: ' . BASE_URL . '/profile');
        exit;
    }
    header('Location: ' . BASE_URL . '/login');
    exit;
});

$router->get('/login', [$authController, 'showLogin']);
$router->post('/login', [$authController, 'login']);

$router->get('/register', [$authController, 'showRegister']);
$router->post('/register', [$authController, 'register']);

$router->get('/logout', [$authController, 'logout']);

$router->get('/forgot', [$authController, 'showForgot']);
$router->post('/forgot', [$authController, 'forgot']);

$router->get('/reset', [$authController, 'showReset']);
$router->post('/reset', [$authController, 'reset']);

$router->get('/profile', [$profileController, 'index'], [$auth, 'requireLogin']);
$router->get('/profile/edit', [$profileController, 'edit'], [$auth, 'requireLogin']);
$router->post('/profile/edit', [$profileController, 'update'], [$auth, 'requireLogin']);
$router->get('/profile/password', [$profileController, 'showPassword'], [$auth, 'requireLogin']);
$router->post('/profile/password', [$profileController, 'updatePassword'], [$auth, 'requireLogin']);

$router->get('/servers', [$serverController, 'index'], [$auth, 'requireLogin']);
$router->get('/servers/console', [$serverController, 'console'], [$auth, 'requireLogin']);
$router->get('/servers/files', [$serverController, 'files'], [$auth, 'requireLogin']);
$router->post('/api/console/command', [$serverController, 'sendCommand'], [$auth, 'requireLogin']);
$router->post('/api/console/action', [$serverController, 'sendAction'], [$auth, 'requireLogin']);
$router->get('/api/console/status', [$serverController, 'getStatus'], [$auth, 'requireLogin']);
$router->get('/api/system/status', function() use ($auth) {
    $auth->requireLogin();
    require_once BASE_PATH . '/includes/lib/api_helper.php';
    echo json_encode(getSystemStatus());
}, [$auth, 'requireLogin']);
$router->post('/api/system/stop-api', function() use ($auth) {
    $auth->requireLogin();
    require_once BASE_PATH . '/includes/lib/api_helper.php';
    echo json_encode(stopApi());
}, [$auth, 'requireLogin']);
$router->post('/api/system/shutdown-pc', function() use ($auth) {
    $auth->requireLogin();
    require_once BASE_PATH . '/includes/lib/api_helper.php';
    echo json_encode(shutdownPC());
}, [$auth, 'requireLogin']);
$router->get('/api/files', function() use ($auth) {
    $auth->requireLogin();
    include BASE_PATH . '/includes/api_files.php';
}, [$auth, 'requireLogin']);
$router->post('/api/files', function() use ($auth) {
    $auth->requireLogin();
    include BASE_PATH . '/includes/api_files.php';
}, [$auth, 'requireLogin']);
$router->get('/api/files/ensure', function() use ($auth) {
    header('Content-Type: application/json');
    
    try {
        $auth->requireLogin();
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Non autorisé']);
        exit;
    }
    
    require_once BASE_PATH . '/includes/lib/api_helper.php';
    
    $server_id = intval($_GET['server_id'] ?? 0);
    
    // Get PC IP
    $pc_ip = '192.168.1.22';
    try {
        $db_host = 'mysql-db';
        $db_name = 'minecraft_panel';
        $db_user = 'root';
        $db_pass = 'nouveaumotdepasse123';
        
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
        
        if ($server_id) {
            $stmt = $pdo->prepare("SELECT pc_ip FROM servers WHERE id = ?");
            $stmt->execute([$server_id]);
        } else {
            $stmt = $pdo->query("SELECT pc_ip FROM servers LIMIT 1");
        }
        $srv = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($srv && !empty($srv['pc_ip'])) {
            $pc_ip = $srv['pc_ip'];
        }
    } catch (Exception $e) {}
    
    // Just check if API is running, don't start it
    $apiRunning = checkApiRunning($pc_ip);
    error_log("[API FILES] PC: $pc_ip, API Running: " . ($apiRunning ? 'yes' : 'no'));
    
    if ($apiRunning) {
        error_log("[API FILES] API is running, returning ok");
        echo json_encode(['status' => 'ok', 'message' => 'API running']);
    } else {
        // Check if PC is on
        $pcOn = checkPcOnline($pc_ip);
        error_log("[API FILES] PC online: " . ($pcOn ? 'yes' : 'no'));
        
        if (!$pcOn) {
            error_log("[API FILES] PC is off, returning error");
            echo json_encode(['status' => 'error', 'message' => 'PC is off']);
        } else {
            error_log("[API FILES] PC is on but API not running, returning error");
            echo json_encode(['status' => 'error', 'message' => 'API not running']);
        }
    }
}, [$auth, 'requireLogin']);

$router->get('/test/system', function() use ($auth) {
    $auth->requireLogin();
    if ($auth->getUserId() != 1) {
        http_response_code(403);
        echo json_encode(['error' => 'Access denied']);
        exit;
    }
    
    header('Content-Type: application/json');
    
    $action = $_GET['action'] ?? 'status';
    $server_id = intval($_GET['server_id'] ?? 1);
    $pc_ip = '192.168.1.22';
    $pc_mac = '2c:f0:5d:7f:e3:2b';
    
    try {
        $db_host = 'mysql-db';
        $db_name = 'minecraft_panel';
        $db_user = 'root';
        $db_pass = 'nouveaumotdepasse123';
        
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Try to get server by id, or get first available
        $stmt = $pdo->prepare("SELECT pc_ip, pc_mac FROM servers WHERE id = :id");
        $stmt->execute(['id' => $server_id]);
        $server = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // If not found, get first server
        if (!$server) {
            $stmt = $pdo->query("SELECT pc_ip, pc_mac FROM servers LIMIT 1");
            $server = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($server) {
                $server_id = 1; // Update to use this server
            }
        }
        
        if ($server) {
            $pc_ip = !empty($server['pc_ip']) ? $server['pc_ip'] : '192.168.1.22';
            $pc_mac = !empty($server['pc_mac']) ? $server['pc_mac'] : '2c:f0:5d:7f:e3:2b';
        }
    } catch (Exception $e) {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
    
    require_once BASE_PATH . '/includes/lib/api_helper.php';
    
    switch ($action) {
        case 'status':
            $status = getSystemStatus($pc_ip);
            echo json_encode($status);
            break;
            
        case 'check-pc':
            $pcOn = checkPcOnline($pc_ip);
            echo json_encode(['pc_ip' => $pc_ip, 'pc_online' => $pcOn]);
            break;
            
        case 'check-api':
            $apiRunning = checkApiRunning($pc_ip);
            echo json_encode(['pc_ip' => $pc_ip, 'api_running' => $apiRunning]);
            break;
            
        case 'all-stopped':
            $stopped = getAllMcServersStopped($pc_ip);
            echo json_encode(['all_stopped' => $stopped]);
            break;
            
        case 'ensure-api':
            // Use fallback to find server
            $result = ensureApiRunning($server_id);
            echo json_encode($result);
            break;
            
        case 'wol':
            require_once BASE_PATH . '/includes/lib/woltour.php';
            
            $result = send_wol($pc_mac, '192.168.1.255');
            
            echo json_encode([
                'action' => 'wol',
                'pc_ip' => $pc_ip,
                'pc_mac' => $pc_mac,
                'wol_sent' => $result,
                'message' => $result ? 'WOL envoyé' : 'Échec WOL'
            ]);
            break;
            
        case 'debug':
            echo json_encode([
                'server_id' => $server_id,
                'pc_ip' => $pc_ip,
                'pc_mac' => $pc_mac,
                'pc_online' => checkPcOnline($pc_ip),
                'api_running' => checkApiRunning($pc_ip)
            ]);
            break;
            
        case 'start-api-direct':
            require_once BASE_PATH . '/includes/lib/sshtour.php';
            
            // First check what's on the PC
            $checkCmd = "ssh -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -i /var/www/id_ed25519 aleix@$pc_ip 'tasklist | findstr python'";
            exec($checkCmd . " 2>&1", $checkOutput, $checkExit);
            
            $killResult = ssh_kill_process($pc_ip, 'python');
            usleep(2000000);
            
            $startResult = ssh_start_api($pc_ip);
            
            usleep(3000000);
            
            // Check again if Python is running
            exec($checkCmd . " 2>&1", $checkOutput2, $checkExit2);
            
            echo json_encode([
                'action' => 'start-api-direct',
                'pc_ip' => $pc_ip,
                'before_kill' => implode("\n", $checkOutput),
                'kill_result' => $killResult,
                'start_result' => $startResult,
                'after_start' => implode("\n", $checkOutput2),
                'api_running_now' => checkApiRunning($pc_ip)
            ]);
            break;
            
        case 'stop-api':
            $result = stopApi($pc_ip);
            echo json_encode($result);
            break;
            
        case 'shutdown-pc':
            $result = shutdownPC($pc_ip);
            echo json_encode($result);
            break;
            
        default:
            echo json_encode(['error' => 'Unknown action', 'available' => ['status', 'check-pc', 'check-api', 'all-stopped', 'ensure-api', 'wol', 'debug', 'stop-api', 'shutdown-pc']]);
    }
});

$router->get('/test/panel', function() use ($auth) {
    $auth->requireLogin();
    if ($auth->getUserId() != 1) {
        http_response_code(403);
        echo 'Access denied';
        exit;
    }
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Test Panel - Minecraft Panel</title>
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #0f172a; color: #e2e8f0; padding: 20px; }
            .container { max-width: 1200px; margin: 0 auto; }
            h1 { color: #60a5fa; border-bottom: 1px solid #334155; padding-bottom: 10px; }
            .test-section { background: #1e293b; border-radius: 8px; padding: 20px; margin-bottom: 20px; }
            .test-section h2 { color: #38bdf8; margin-top: 0; }
            button { background: #3b82f6; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; margin: 5px; font-size: 14px; }
            button:hover { background: #2563eb; }
            button.danger { background: #ef4444; }
            button.danger:hover { background: #dc2626; }
            button.warning { background: #f59e0b; }
            button.warning:hover { background: #d97706; }
            button.success { background: #22c55e; }
            button.success:hover { background: #16a34a; }
            button:disabled { opacity: 0.5; cursor: not-allowed; }
            .result { background: #0f172a; padding: 15px; border-radius: 6px; margin-top: 15px; font-family: monospace; white-space: pre-wrap; word-wrap: break-word; max-height: 300px; overflow-y: auto; }
            .status-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 15px 0; }
            .status-card { background: #334155; padding: 15px; border-radius: 6px; text-align: center; }
            .status-card .label { font-size: 12px; color: #94a3b8; text-transform: uppercase; }
            .status-card .value { font-size: 24px; font-weight: bold; margin-top: 5px; }
            .status-card.online .value { color: #22c55e; }
            .status-card.offline .value { color: #ef4444; }
            .status-card.warning .value { color: #f59e0b; }
            .test-case { background: #0f172a; padding: 10px; margin: 5px 0; border-radius: 4px; border-left: 3px solid #3b82f6; }
            .test-case.pass { border-left-color: #22c55e; }
            .test-case.fail { border-left-color: #ef4444; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🧪 Test Panel - Minecraft Panel</h1>
            
            <div class="test-section">
                <h2>📊 État du Système</h2>
                <div class="status-grid" id="system-status">
                    <div class="status-card offline">
                        <div class="label">PC</div>
                        <div class="value" id="status-pc">...</div>
                    </div>
                    <div class="status-card offline">
                        <div class="label">API</div>
                        <div class="value" id="status-api">...</div>
                    </div>
                    <div class="status-card offline">
                        <div class="label">Serveurs MC</div>
                        <div class="value" id="status-mc">...</div>
                    </div>
                    <div class="status-card offline">
                        <div class="label">API PC Arrêt</div>
                        <div class="value" id="status-can-stop">...</div>
                    </div>
                </div>
                <button onclick="refreshStatus()" class="success">🔄 Actualiser</button>
            </div>
            
            <div class="test-section">
                <h2>🖥️ Contrôle PC/API</h2>
                <button onclick="testAction('debug')" class="warning">🔍 Debug Info</button>
                <button onclick="testAction('check-pc')" class="warning">Vérifier PC</button>
                <button onclick="testAction('check-api')" class="warning">Vérifier API</button>
                <button onclick="testAction('wol')" class="success">📡 Envoyer WOL</button>
                <button onclick="testAction('start-api-direct')" class="success">🐍 Lancer API Direct</button>
                <button onclick="testAction('ensure-api')" class="success">Démarrer API</button>
                <button onclick="testAction('stop-api')" class="danger">Arrêter API</button>
                <button onclick="testAction('shutdown-pc')" class="danger">Éteindre PC</button>
                <button onclick="testAction('status')" class="warning">État Complet</button>
            </div>
            
            <div class="test-section">
                <h2>🎮 Contrôle Serveur MC</h2>
                <div id="server-controls">
                    <button onclick="runQuickStart()" class="success" id="btn-start" style="font-size: 16px; padding: 12px 24px;">🚀 ALLUMER TOUT</button>
                </div>
            </div>
            
            <div class="test-section">
                <h2>📋 Scénario Complet</h2>
                <button onclick="runFullTest()" class="success">🔄 Test: Allumer → MC → Arrêter</button>
            </div>
            
            <div class="test-section">
                <h2>📋 Résultats</h2>
                <div class="result" id="result">Cliquez sur un bouton pour tester...</div>
            </div>
        </div>
        
        <script>
        const BASE_URL = '';
        let currentStatus = {};
        
        async function refreshStatus() {
            try {
                const resp = await fetch(BASE_URL + '/test/system?action=status');
                currentStatus = await resp.json();
                
                document.getElementById('status-pc').textContent = currentStatus.pc_online ? 'ON' : 'OFF';
                document.getElementById('status-pc').parentElement.className = currentStatus.pc_online ? 'status-card online' : 'status-card offline';
                
                document.getElementById('status-api').textContent = currentStatus.api_running ? 'ON' : 'OFF';
                document.getElementById('status-api').parentElement.className = currentStatus.api_running ? 'status-card online' : 'status-card offline';
                
                const anyRunning = currentStatus.any_server_running;
                const anyStarting = currentStatus.any_server_starting;
                const anyStopping = currentStatus.any_server_stopping;
                
                let mcStatus = 'OFF';
                if (anyRunning) mcStatus = 'ON';
                else if (anyStarting) mcStatus = 'Démarrage';
                else if (anyStopping) mcStatus = 'Arrêt';
                
                document.getElementById('status-mc').textContent = mcStatus;
                document.getElementById('status-mc').parentElement.className = anyRunning ? 'status-card online' : (anyStarting || anyStopping ? 'status-card warning' : 'status-card offline');
                
                const canStop = !anyRunning && !anyStarting && !anyStopping;
                document.getElementById('status-can-stop').textContent = canStop ? 'OUI' : 'NON';
                document.getElementById('status-can-stop').parentElement.className = canStop ? 'status-card online' : 'status-card offline';
                
            } catch(e) {
                console.error(e);
            }
        }
        
        async function testAction(action) {
            const resultEl = document.getElementById('result');
            resultEl.textContent = 'Chargement...';
            try {
                const resp = await fetch(BASE_URL + '/test/system?action=' + action);
                const text = await resp.text();
                try {
                    const data = JSON.parse(text);
                    resultEl.textContent = JSON.stringify(data, null, 2);
                } catch(e) {
                    resultEl.textContent = 'Erreur JSON: ' + e.message + '\nRéponse: ' + text.substring(0, 500);
                }
                refreshStatus();
            } catch(e) {
                resultEl.textContent = 'Erreur fetch: ' + e.message;
            }
        }
        
        // ============================================
        // FONCTIONS DE BASE (Réutilisables)
        // ============================================
        
        // Vérifie l'état du système
        async function getStatus() {
            const resp = await fetch(BASE_URL + '/test/system?action=status');
            return await resp.json();
        }
        
        // Envoie le signal WOL pour allumer le PC
        async function wakePc() {
            await fetch(BASE_URL + '/test/system?action=wol');
        }
        
        // Démarre l'API Python
        async function startApi() {
            console.log('[API] Démarrage de l\'API sur PC Tour...');
            try {
                const resp = await fetch(BASE_URL + '/test/system?action=start-api-direct');
                const data = await resp.json();
                console.log('[API] Réponse:', data);
                return data;
            } catch(e) {
                console.error('[API] Erreur:', e);
                throw e;
            }
        }
        
        // Arrête l'API Python
        async function stopApi() {
            await fetch(BASE_URL + '/test/system?action=stop-api');
        }
        
        // Démarre le serveur MC
        async function startMc(id = 1) {
            // Add debug info
            console.log('Starting MC server ' + id);
            const resp = await fetch(BASE_URL + '/includes/api_proxy.php?server_id=' + id + '&action=start');
            const data = await resp.json();
            console.log('MC start response:', data);
            return { json: () => data };
        }
        
        // Arrête le serveur MC
        async function stopMc(id = 1) {
            return await fetch(BASE_URL + '/includes/api_proxy.php?server_id=' + id + '&action=stop');
        }
        
        // Récupère le statut du serveur MC
        async function getMcStatus(id = 1) {
            return await fetch(BASE_URL + '/includes/api_proxy.php?server_id=' + id);
        }
        
        // ============================================
        // FONCTIONS DE VÉRIFICATION (Si OFF → Start)
        // ============================================
        
        // Vérifie et allume le PC si besoin
        async function ensurePcOn(log = true, maxRetries = 12) {
            const resultEl = document.getElementById('result');
            
            // Vérifier si PC déjà ON
            for (let i = 0; i < 2; i++) {
                try {
                    const status = await getStatus();
                    if (status.pc_online === true) {
                        if (log) resultEl.textContent += '✅ PC déjà ON\n';
                        return true;
                    }
                } catch(e) {}
                await new Promise(r => setTimeout(r, 500));
            }
            
            // PC OFF - Envoyer WOL
            if (log) resultEl.textContent += '🖥️ PC OFF - Envoi WOL...\n';
            await wakePc();
            
            // Boucle de vérification continue (plus rapide)
            for (let i = 0; i < maxRetries; i++) {
                try {
                    const status = await getStatus();
                    
                    if (status.pc_online === true) {
                        if (log) resultEl.textContent += `✅ PC allumé (${i+1}s)\n`;
                        return true;
                    }
                    
                    if (log) resultEl.textContent += `⏳ Attente PC... (${i+1}s)\n`;
                    
                } catch(e) {
                    if (log) resultEl.textContent += `⏳ Connexion PC... (${i+1}s)\n`;
                }
                
                await new Promise(r => setTimeout(r, 1000));
            }
            
            if (log) resultEl.textContent += '❌ PC non joignable\n';
            return false;
        }
        
        // Vérifie et démarre l'API si besoin
        async function ensureApiOn(log = true, maxRetries = 20) {
            const resultEl = document.getElementById('result');
            console.log('[API] Vérification statut API...');
            
            // D'abord, vérifier si on peut joindre l'API
            for (let i = 0; i < 3; i++) {
                try {
                    const status = await getStatus();
                    console.log('[API] Status:', status);
                    if (status.api_running === true) {
                        if (log) resultEl.textContent += '✅ API déjà ON\n';
                        console.log('[API] API déjà démarrée');
                        return true;
                    }
                    if (status.pc_online === false) {
                        if (log) resultEl.textContent += '❌ PC OFF\n';
                        console.log('[API] PC OFF - tentative WOL...');
                        return false;
                    }
                    break;
                } catch(e) {}
                await new Promise(r => setTimeout(r, 500));
            }
            
            // Si on arrive ici, l'API n'est pas active - on la démarre
            if (log) resultEl.textContent += '🐍 API OFF - Lancement...\n';
            console.log('[API] API OFF - Lancement en cours...');
            await startApi();
            
            // Boucle de vérification continue
            for (let i = 0; i < maxRetries; i++) {
                try {
                    const status = await getStatus();
                    
                    if (status.api_running === true) {
                        if (log) resultEl.textContent += `✅ API démarrée (${i+1}s)\n`;
                        return true;
                    }
                    
                    if (status.pc_online === false) {
                        if (log) resultEl.textContent += '❌ PC OFF\n';
                        return false;
                    }
                    
                    if (log) resultEl.textContent += `⏳ Attente API... (${i+1}s)\n`;
                    
                } catch(e) {
                    if (log) resultEl.textContent += `⏳ Connexion API... (${i+1}s)\n`;
                }
                
                await new Promise(r => setTimeout(r, 1000));
            }
            
            if (log) resultEl.textContent += '❌ API non joignable\n';
            return false;
        }
        
        // Vérifie et démarre le serveur MC si besoin
        async function ensureMcOn(id = 1, log = true, maxRetries = 20) {
            const resultEl = document.getElementById('result');
            
            // Vérifier si MC déjà ON
            for (let i = 0; i < 2; i++) {
                try {
                    const mc = await getMcStatus(id);
                    const data = await mc.json();
                    console.log('MC status check:', data);
                    
                    if (data.running && data.online) {
                        if (log) resultEl.textContent += '✅ MC déjà ON\n';
                        return true;
                    }
                } catch(e) {
                    console.log('MC status error:', e);
                }
                await new Promise(r => setTimeout(r, 500));
            }
            
            // MC OFF - Démarrer
            if (log) resultEl.textContent += '🎮 MC OFF - Démarrage...\n';
            
            try {
                const mc = await startMc(id);
                const data = await mc.json();
                console.log('MC start response:', data);
                
                if (!data.success && data.status !== 'already_running') {
                    if (log) resultEl.textContent += '❌ Erreur: ' + (data.message || 'Inconnu') + '\n';
                    return false;
                }
            } catch(e) {
                if (log) resultEl.textContent += '❌ Erreur: ' + e.message + '\n';
                return false;
            }
            
            // Boucle de vérification continue
            for (let i = 0; i < maxRetries; i++) {
                try {
                    const mc = await getMcStatus(id);
                    const data = await mc.json();
                    console.log('MC status after start:', data);
                    
                    if (data.running && data.online) {
                        if (log) resultEl.textContent += `✅ MC EN LIGNE! (${i+1}s)\n`;
                        return true;
                    }
                    
                    if (data.starting) {
                        if (log) resultEl.textContent += `⏳ Démarrage MC... (${i+1}s)\n`;
                    }
                    
                } catch(e) {
                    if (log) resultEl.textContent += `⏳ Connexion MC... (${i+1}s)\n`;
                }
                
                await new Promise(r => setTimeout(r, 1000));
            }
            
            if (log) resultEl.textContent += '❌ MC pas démarré\n';
            return false;
        }
        
        // ============================================
        // BOUTON PRINCIPAL: ALLUMER TOUT
        // ============================================
        
        async function runQuickStart() {
            const resultEl = document.getElementById('result');
            const btn = document.getElementById('btn-start');
            
            if (btn) { btn.disabled = true; btn.textContent = '⏳ Démarrage...'; }
            resultEl.textContent = '🚀 Vérification...\n';
            
            // Étape 1: PC
            if (!await ensurePcOn()) {
                if (btn) { btn.disabled = false; btn.textContent = '🚀 ALLUMER TOUT'; }
                refreshStatus();
                return;
            }
            
            // Étape 2: API
            if (!await ensureApiOn()) {
                if (btn) { btn.disabled = false; btn.textContent = '🚀 ALLUMER TOUT'; }
                refreshStatus();
                return;
            }
            
            // Étape 3: MC
            if (!await ensureMcOn(1)) {
                if (btn) { btn.disabled = false; btn.textContent = '🚀 ALLUMER TOUT'; }
                refreshStatus();
                return;
            }
            
            resultEl.textContent += '\n🏁 Tout est allumé! ✅\n';
            
            if (btn) { btn.disabled = false; btn.textContent = '🚀 ALLUMER TOUT'; }
            refreshStatus();
        }
        
        // ============================================
        // TEST COMPLET
        // ============================================
        
        async function runFullTest() {
            const resultEl = document.getElementById('result');
            resultEl.textContent = '🔄 Test complet...\n';
            
            // Allumer
            if (!await ensurePcOn()) { refreshStatus(); return; }
            if (!await ensureApiOn()) { refreshStatus(); return; }
            if (!await ensureMcOn(1)) { refreshStatus(); return; }
            
            // Attendre
            resultEl.textContent += '\n⏳ Attente 3s...\n';
            await new Promise(r => setTimeout(r, 3000));
            
            // Arrêter MC
            resultEl.textContent += '\n⏹️ Arrêt MC...\n';
            await stopMc(1);
            
            for (let i = 0; i < 10; i++) {
                await new Promise(r => setTimeout(r, 2000));
                const m = await getMcStatus(1);
                const d = await m.json();
                if (!d.running) {
                    resultEl.textContent += `✅ MC arrêté (${(i+1)*2}s)\n`;
                    break;
                }
                resultEl.textContent += `⏳ Arrêt... (${(i+1)*2}s)\n`;
            }
            
            resultEl.textContent += '\n🏁 Test terminé!\n';
            refreshStatus();
        }
        
        async function testAction(action) {
            const resultEl = document.getElementById('result');
            resultEl.textContent = 'Chargement...';
            try {
                const resp = await fetch(BASE_URL + '/test/system?action=' + action);
                const text = await resp.text();
                try {
                    const data = JSON.parse(text);
                    resultEl.textContent = JSON.stringify(data, null, 2);
                } catch(e) {
                    resultEl.textContent = 'Erreur JSON: ' + e.message + '\nRéponse: ' + text.substring(0, 500);
                }
                refreshStatus();
            } catch(e) {
                resultEl.textContent = 'Erreur fetch: ' + e.message;
            }
        }
        
        async function runAllTests() {
            const results = document.getElementById('test-results');
            results.innerHTML = '';
            
            const tests = [
                { name: 'Vérifier PC', action: 'check-pc', expected: null },
                { name: 'Vérifier API', action: 'check-api', expected: null },
                { name: 'État Complet', action: 'status', expected: null },
                { name: 'Tous serveurs arrêté?', action: 'all-stopped', expected: null }
            ];
            
            for (const test of tests) {
                const div = document.createElement('div');
                div.className = 'test-case';
                div.textContent = '⏳ Test: ' + test.name;
                results.appendChild(div);
                
                try {
                    const resp = await fetch(BASE_URL + '/test/system?action=' + test.action);
                    const data = await resp.json();
                    div.className += ' pass';
                    div.textContent = '✅ Test: ' + test.name + ' - ' + JSON.stringify(data);
                } catch(e) {
                    div.className += ' fail';
                    div.textContent = '❌ Test: ' + test.name + ' - Erreur: ' + e.message;
                }
            }
            
            refreshStatus();
        }
        
        // Test complet: Allumer PC → API → MC → Arrêter
        async function runFullTest() {
            const resultEl = document.getElementById('result');
            resultEl.textContent = '🚀 Début du test complet...\n';
            
            // Étape 1: Vérifier état initial
            resultEl.textContent += '\n📋 Étape 1: Vérification état initial\n';
            let status = await getStatus();
            resultEl.textContent += `PC: ${status.pc_online ? 'ON' : 'OFF'} | API: ${status.api_running ? 'ON' : 'OFF'}\n`;
            
            // Étape 2: Allumer PC si besoin
            if (!status.pc_online) {
                resultEl.textContent += '\n📡 Étape 2: Envoi WOL\n';
                await fetch(BASE_URL + '/test/system?action=wol');
                
                for (let i = 0; i < 15; i++) {
                    await new Promise(r => setTimeout(r, 5000));
                    status = await getStatus();
                    if (status.pc_online) {
                        resultEl.textContent += `✅ PC allumé (${(i+1)*5}s)\n`;
                        break;
                    }
                    resultEl.textContent += `⏳ Attente PC... (${(i+1)*5}s)\n`;
                }
            } else {
                resultEl.textContent += '\n✅ Étape 2: PC déjà allumé\n';
            }
            
            // Revérifier PC avant de lancer API
            status = await getStatus();
            if (!status.pc_online) {
                resultEl.textContent += '\n❌ Étape 3: PC pas allumé, impossible de lancer API\n';
                refreshStatus();
                return;
            }
            
            // Étape 3: Démarrer API
            if (!status.api_running) {
                resultEl.textContent += '\n🐍 Étape 3: Lancement API\n';
                const apiResp = await fetch(BASE_URL + '/test/system?action=start-api-direct');
                const apiData = await apiResp.json();
                resultEl.textContent += 'API Response: ' + JSON.stringify(apiData) + '\n';
                
                for (let i = 0; i < 10; i++) {
                    await new Promise(r => setTimeout(r, 2000));
                    status = await getStatus();
                    if (status.api_running) {
                        resultEl.textContent += `✅ API démarrée (${(i+1)*2}s)\n`;
                        break;
                    }
                    resultEl.textContent += `⏳ Attente API... (${(i+1)*2}s)\n`;
                }
            } else {
                resultEl.textContent += '\n✅ Étape 3: API déjà démarrée\n';
            }
            
            // Étape 4: Démarrer MC
            resultEl.textContent += '\n🎮 Étape 4: Démarrage Serveur MC\n';
            await fetch(BASE_URL + '/includes/api_proxy.php?server_id=1&action=start');
            
            for (let i = 0; i < 15; i++) {
                await new Promise(r => setTimeout(r, 2000));
                const mcResp = await fetch(BASE_URL + '/includes/api_proxy.php?server_id=1');
                const mcData = await mcResp.json();
                if (mcData.running && mcData.online) {
                    resultEl.textContent += `✅ Serveur MC EN LIGNE! (${(i+1)*2}s)\n`;
                    break;
                }
                resultEl.textContent += `⏳ Démarrage MC... (${(i+1)*2}s)\n`;
            }
            
            // Étape 5: Arrêter MC
            resultEl.textContent += '\n⏹️ Étape 5: Arrêt Serveur MC\n';
            try {
                const stopResp = await fetch(BASE_URL + '/includes/api_proxy.php?server_id=1&action=stop');
                if (!stopResp || stopResp.status === 0) {
                    resultEl.textContent += '❌ Erreur: Impossible de joindre le serveur\n';
                } else {
                    resultEl.textContent += '✅ Commande d\'arrêt envoyée\n';
                }
            } catch(e) {
                resultEl.textContent += '❌ Erreur: ' + e.message + '\n';
            }
            
            for (let i = 0; i < 10; i++) {
                await new Promise(r => setTimeout(r, 2000));
                const mcResp = await fetch(BASE_URL + '/includes/api_proxy.php?server_id=1');
                const mcData = await mcResp.json();
                if (!mcData.running) {
                    resultEl.textContent += `✅ Serveur MC ARRÊTÉ (${(i+1)*2}s)\n`;
                    break;
                }
                resultEl.textContent += `⏹️ Arrêt MC... (${(i+1)*2}s)\n`;
            }
            
            resultEl.textContent += '\n🏁 Test complet terminé!\n';
            refreshStatus();
        }
        
        // Quick Start: PC → API → MC (vérifie chaque étape)
        async function runQuickStart() {
            const resultEl = document.getElementById('result');
            resultEl.textContent = '⚡ Quick Start...\n';
            
            // Étape 1: Vérifier PC
            resultEl.textContent += '🔍 Vérification PC...\n';
            let status = await getStatus();
            
            if (!status.pc_online) {
                resultEl.textContent += '🖥️ PC OFF - Envoi WOL...\n';
                await fetch(BASE_URL + '/test/system?action=wol');
                
                for (let i = 0; i < 15; i++) {
                    await new Promise(r => setTimeout(r, 5000));
                    status = await getStatus();
                    if (status.pc_online) {
                        resultEl.textContent += '✅ PC allumé\n';
                        break;
                    }
                    resultEl.textContent += `⏳ Attente PC... (${(i+1)*5}s)\n`;
                }
                
                if (!status.pc_online) {
                    resultEl.textContent += '❌ PC non joignable\n';
                    refreshStatus();
                    return;
                }
            } else {
                resultEl.textContent += '✅ PC ON\n';
            }
            
            // Étape 2: Vérifier API
            resultEl.textContent += '🔍 Vérification API...\n';
            status = await getStatus();
            
            if (!status.api_running) {
                resultEl.textContent += '🐍 API OFF - Lancement...\n';
                await fetch(BASE_URL + '/test/system?action=start-api-direct');
                
                for (let i = 0; i < 10; i++) {
                    await new Promise(r => setTimeout(r, 2000));
                    status = await getStatus();
                    if (status.api_running) {
                        resultEl.textContent += '✅ API démarrée\n';
                        break;
                    }
                    resultEl.textContent += `⏳ Attente API... (${(i+1)*2}s)\n`;
                }
                
                if (!status.api_running) {
                    resultEl.textContent += '❌ API non démarrée\n';
                    refreshStatus();
                    return;
                }
            } else {
                resultEl.textContent += '✅ API ON\n';
            }
            
            // Étape 3: Vérifier MC
            resultEl.textContent += '🔍 Vérification Serveur MC...\n';
            const mcResp = await fetch(BASE_URL + '/includes/api_proxy.php?server_id=1');
            let mcData = await mcResp.json();
            
            if (mcData.running && mcData.online) {
                resultEl.textContent += '✅ Serveur MC déjà ON\n';
            } else {
                resultEl.textContent += '🎮 MC OFF - Démarrage...\n';
                await fetch(BASE_URL + '/includes/api_proxy.php?server_id=1&action=start');
                
                for (let i = 0; i < 15; i++) {
                    await new Promise(r => setTimeout(r, 2000));
                    const checkResp = await fetch(BASE_URL + '/includes/api_proxy.php?server_id=1');
                    mcData = await checkResp.json();
                    
                    if (mcData.running && mcData.online) {
                        resultEl.textContent += `✅ Serveur MC EN LIGNE! (${(i+1)*2}s)\n`;
                        break;
                    }
                    resultEl.textContent += `⏳ Démarrage MC... (${(i+1)*2}s)\n`;
                }
            }
            
            resultEl.textContent += '\n🏁 Tout est ON!\n';
            refreshStatus();
        }
        
        refreshStatus();
        </script>
    </body>
    </html>
    <?php
});

$router->get('/admin', [$adminController, 'index'], [$auth, 'requireAdmin']);
$router->post('/admin/servers/add', [$adminController, 'addServer'], [$auth, 'requireAdmin']);
$router->post('/admin/servers/update', [$adminController, 'updateServer'], [$auth, 'requireAdmin']);
$router->post('/admin/servers/delete', [$adminController, 'deleteServer'], [$auth, 'requireAdmin']);
$router->post('/admin/permissions/update', [$adminController, 'updatePermissions'], [$auth, 'requireAdmin']);

$router->run();
ob_end_flush();
