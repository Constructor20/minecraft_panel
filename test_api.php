<?php
/**
 * API de test pour le Minecraft Panel
 * 
 * Endpoints sans authentification pour les tests
 * 
 * Usage:
 * - test_api.php?action=wol           -> Allumer le PC
 * - test_api.php?action=ping          -> Vérifier PC et API
 * - test_api.php?action=start-api    -> Démarrer l'API Python
 * - test_api.php?action=stop-api     -> Arrêter l'API Python
 * - test_api.php?action=shutdown-pc  -> Éteindre le PC
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/lib/woltour.php';
require_once __DIR__ . '/includes/lib/sshtour.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$server_id = intval($_GET['server_id'] ?? 7);

/**
 * Vérifie si le PC est joignable (SSH)
 */
function checkPcOnline($ip) {
    $ch = @fsockopen($ip, 22, $errno, $errstr, 1);
    if ($ch) { fclose($ch); return true; }
    return false;
}

/**
 * Vérifie si l'API Python est joignable
 */
function checkApiRunning($ip) {
    if (!checkPcOnline($ip)) return false;
    $ch = curl_init("http://$ip:8080/health");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($code === 200);
}

// Exécution des actions
$result = [
    'action' => $action,
    'server_id' => $server_id,
    'pc_ip' => PC_IP
];

switch ($action) {
    case 'wol':
        $wol = send_wol(PC_MAC, '192.168.1.255');
        $result['wol_sent'] = $wol;
        $result['message'] = $wol ? 'WOL envoyé' : 'WOL échoué';
        break;
        
    case 'ping':
    case 'debug':
        $pcOn = checkPcOnline(PC_IP);
        $result['pc_online'] = $pcOn;
        $result['api_running'] = checkApiRunning(PC_IP);
        break;
        
    case 'start-api':
        ssh_kill_process(PC_IP, 'python');
        usleep(1500000);
        ssh_start_api(PC_IP);
        $result['message'] = 'API start command sent';
        break;
        
    case 'stop-api':
        ssh_kill_process(PC_IP, 'python');
        $result['message'] = 'API stopped';
        break;
        
    case 'shutdown-pc':
        ssh_kill_process(PC_IP, 'python');
        usleep(2000000);
        $cmd = "ssh -o StrictHostKeyChecking=no -i " . SSH_KEY_PATH . " " . SSH_USER . "@" . PC_IP . " 'shutdown /s /t 30 /c \"Shutdown\"'";
        exec($cmd . " > /dev/null 2>&1 &");
        $result['message'] = 'PC will shutdown in 30s';
        break;
        
    default:
        $result['error'] = 'Action inconnue. Actions: wol, ping, start-api, stop-api, shutdown-pc';
        $result['available_actions'] = ['wol', 'ping', 'start-api', 'stop-api', 'shutdown-pc'];
}

echo json_encode($result);
