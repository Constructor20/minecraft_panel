<?php
function checkPcOnline($pc_ip) {
    $ch = @fsockopen($pc_ip, 80, $errno, $errstr, 1);
    if ($ch) { 
        fclose($ch); 
        return true;
    }
    $ch = @fsockopen($pc_ip, 22, $errno, $errstr, 1);
    if ($ch) { 
        fclose($ch); 
        return true;
    }
    return false;
}

function checkApiRunning($pc_ip) {
    if (!checkPcOnline($pc_ip)) {
        return false;
    }
    $ch = curl_init("http://$pc_ip:8080/health");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($code === 200);
}

function ensureApiRunning($server_id, $wait_for_api = true) {
    $db_host = 'mysql-db';
    $db_name = 'minecraft_panel';
    $db_user = 'root';
    $db_pass = 'nouveaumotdepasse123';
    
    try {
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
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Erreur BDD: ' . $e->getMessage()];
    }
    
    if (!$server) {
        return ['success' => false, 'message' => 'Aucun serveur trouvé dans la base de données'];
    }
    
    $pc_ip = !empty($server['pc_ip']) ? $server['pc_ip'] : '192.168.1.22';
    $pc_mac = !empty($server['pc_mac']) ? $server['pc_mac'] : '2c:f0:5d:7f:e3:2b';
    
    $pcOn = checkPcOnline($pc_ip);
    
    if (!$pcOn) {
        require_once __DIR__ . '/woltour.php';
        send_wol($pc_mac, '192.168.1.255');
        
        for ($i = 0; $i < 45; $i++) {
            usleep(2000000);
            if (checkPcOnline($pc_ip)) {
                break;
            }
        }
        
        usleep(3000000);
    }
    
    $pcOn = checkPcOnline($pc_ip);
    if (!$pcOn) {
        return ['success' => false, 'message' => 'PC non joignable après WOL'];
    }
    
    $apiRunning = checkApiRunning($pc_ip);
    
    if (!$apiRunning) {
        require_once __DIR__ . '/sshtour.php';
        
        ssh_kill_process($pc_ip, 'python');
        usleep(1500000);
        
        ssh_start_api($pc_ip);
    }
    
    if ($wait_for_api) {
        for ($i = 0; $i < 20; $i++) {
            usleep(1000000);
            if (checkApiRunning($pc_ip)) {
                return ['success' => true, 'message' => 'API démarrée', 'pc_online' => true, 'api_running' => true];
            }
        }
        return ['success' => 'starting', 'message' => 'API en cours de démarrage...', 'pc_online' => true, 'api_running' => false];
    }
    
    return ['success' => true, 'message' => 'Commandes envoyées', 'pc_online' => true, 'api_running' => $apiRunning];
}

function waitForApi($server_id, $timeout = 30) {
    $db_host = 'mysql-db';
    $db_name = 'minecraft_panel';
    $db_user = 'root';
    $db_pass = 'nouveaumotdepasse123';
    
    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
        
        $stmt = $pdo->prepare("SELECT pc_ip FROM servers WHERE id = :id");
        $stmt->execute(['id' => $server_id]);
        $server = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$server) {
            $stmt = $pdo->query("SELECT pc_ip FROM servers LIMIT 1");
            $server = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        if (!$server) {
            return false;
        }
        
        $pc_ip = $server['pc_ip'] ?? '192.168.1.22';
        
    } catch (Exception $e) {
        return false;
    }
    
    for ($i = 0; $i < $timeout; $i++) {
        if (checkApiRunning($pc_ip)) {
            return true;
        }
        usleep(1000000);
    }
    
    return false;
}

function getServerStatusFromApi($pc_ip, $server_id) {
    if (!checkApiRunning($pc_ip)) {
        return null;
    }
    
    $ch = curl_init("http://$pc_ip:8080/status/$server_id");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($code === 200) {
        return json_decode($resp, true);
    }
    return null;
}

function getSystemStatus($pc_ip = '192.168.1.22') {
    $pcOn = checkPcOnline($pc_ip);
    $apiRunning = false;
    
    if ($pcOn) {
        $apiRunning = checkApiRunning($pc_ip);
    }
    
    $db_host = 'mysql-db';
    $db_name = 'minecraft_panel';
    $db_user = 'root';
    $db_pass = 'nouveaumotdepasse123';
    
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $stmt = $pdo->query("SELECT id FROM servers");
    $servers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $server_statuses = [];
    $any_running = false;
    $any_starting = false;
    $any_stopping = false;
    
    foreach ($servers as $srv) {
        $sid = $srv['id'];
        if ($apiRunning) {
            $status = getServerStatusFromApi($pc_ip, $sid);
            if ($status) {
                $running = $status['running'] ?? false;
                $starting = $status['starting'] ?? false;
                $stopping = $status['stopping'] ?? false;
                
                if ($running) {
                    $server_statuses[$sid] = 'running';
                    $any_running = true;
                } elseif ($starting) {
                    $server_statuses[$sid] = 'starting';
                    $any_starting = true;
                } elseif ($stopping) {
                    $server_statuses[$sid] = 'stopping';
                    $any_stopping = true;
                } else {
                    $server_statuses[$sid] = 'stopped';
                }
            } else {
                $server_statuses[$sid] = 'unknown';
            }
        } else {
            $server_statuses[$sid] = 'stopped';
        }
    }
    
    return [
        'pc_online' => $pcOn,
        'api_running' => $apiRunning,
        'any_server_running' => $any_running,
        'any_server_starting' => $any_starting,
        'any_server_stopping' => $any_stopping,
        'server_statuses' => $server_statuses
    ];
}

function getAllMcServersStopped($pc_ip = '192.168.1.22') {
    $status = getSystemStatus($pc_ip);
    
    if (!$status['pc_online'] || !$status['api_running']) {
        return true;
    }
    
    foreach ($status['server_statuses'] as $sid => $srv_status) {
        if ($srv_status === 'running' || $srv_status === 'starting' || $srv_status === 'stopping') {
            return false;
        }
    }
    
    return true;
}

function stopApi($pc_ip = '192.168.1.22') {
    require_once __DIR__ . '/sshtour.php';
    ssh_kill_process($pc_ip, 'python');
    return ['success' => true, 'message' => 'API arrêtée'];
}

function shutdownPC($pc_ip = '192.168.1.22') {
    require_once __DIR__ . '/sshtour.php';
    
    ssh_kill_process($pc_ip, 'python');
    usleep(2000000);
    
    $cmd = "ssh -o StrictHostKeyChecking=no -i /var/www/id_ed25519 aleix@$pc_ip 'shutdown /s /t 30 /c \"Shutdown scheduled by remote\"'";
    exec($cmd . " > /dev/null 2>&1 &");
    
    return ['success' => true, 'message' => 'PC va s\'éteindre dans 30 secondes'];
}
