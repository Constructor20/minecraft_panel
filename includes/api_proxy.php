<?php
header('Content-Type: application/json');

if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__ . '/..');
}

require_once BASE_PATH . '/core/Database.php';
require_once BASE_PATH . '/core/Auth.php';

session_start();

$db = new Database();
$auth = new Auth($db);

if (!$auth->isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$userId = $auth->getUserId();

$server_id = $_GET['server_id'] ?? $_POST['server_id'] ?? null;
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$command = $_GET['command'] ?? $_POST['command'] ?? '';

if (!$server_id) {
    echo json_encode(['status' => 'error', 'message' => 'Server ID required']);
    exit;
}

$server_id = intval($server_id);

$server = $db->fetch("SELECT * FROM servers WHERE id = ?", [$server_id]);
if (!$server) {
    echo json_encode(['status' => 'error', 'message' => 'Server not found']);
    exit;
}

if ($userId != 1) {
    $perms = $db->fetch("SELECT * FROM permissions WHERE user_id = ? AND server_id = ?", [$userId, $server_id]);
    if (!$perms || !$perms['can_view']) {
        echo json_encode(['status' => 'error', 'message' => 'Access denied']);
        exit;
    }
}

require_once BASE_PATH . '/core/ServerService.php';
$serverService = new ServerService($db);

if ($action === 'start' || $action === 'stop') {
    if ($userId != 1) {
        $perms = $db->fetch("SELECT * FROM permissions WHERE user_id = ? AND server_id = ?", [$userId, $server_id]);
        if ($action === 'start' && (!$perms || !$perms['can_start'])) {
            echo json_encode(['status' => 'error', 'message' => 'Permission denied']);
            exit;
        }
        if ($action === 'stop' && (!$perms || !$perms['can_stop'])) {
            echo json_encode(['status' => 'error', 'message' => 'Permission denied']);
            exit;
        }
    }
    $result = $serverService->sendAction($server_id, $action);
    echo json_encode($result);
    exit;
}

if (!empty($command)) {
    if ($userId != 1) {
        $perms = $db->fetch("SELECT * FROM permissions WHERE user_id = ? AND server_id = ?", [$userId, $server_id]);
        if (!$perms || !$perms['can_console']) {
            echo json_encode(['status' => 'error', 'message' => 'Permission denied']);
            exit;
        }
    }
    $result = $serverService->sendCommand($server_id, $command);
    echo json_encode($result);
    exit;
}

if ($action === 'update_config') {
    if ($userId != 1) {
        echo json_encode(['status' => 'error', 'message' => 'Admin only']);
        exit;
    }
    $config = $_POST['config'] ?? json_decode(file_get_contents('php://input'), true)['config'] ?? [];
    $result = $serverService->updateServerConfig($server_id, $config);
    echo json_encode($result);
    exit;
}

if ($action === 'get_config') {
    if ($userId != 1) {
        echo json_encode(['status' => 'error', 'message' => 'Admin only']);
        exit;
    }
    $server = $serverService->getServer($server_id);
    echo json_encode(['status' => 'ok', 'config' => $server]);
    exit;
}

$result = $serverService->getServerStatus($server_id);
echo json_encode($result);
