<?php
header('Content-Type: application/json');

require_once BASE_PATH . '/includes/lib/api_helper.php';

$server_id = $_POST['server_id'] ?? $_GET['server_id'] ?? null;
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$path = $_POST['path'] ?? $_GET['path'] ?? '';
$content = $_POST['content'] ?? '';

if (!$server_id) {
    // Try to get first server
    try {
        $db_host = 'mysql-db';
        $db_name = 'minecraft_panel';
        $db_user = 'root';
        $db_pass = 'nouveaumotdepasse123';
        
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
        $stmt = $pdo->query("SELECT id FROM servers LIMIT 1");
        $srv = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($srv) {
            $server_id = $srv['id'];
        }
    } catch (Exception $e) {}
}

if (!$server_id) {
    echo json_encode(['status' => 'error', 'message' => 'Server ID required']);
    exit;
}

$server_id = intval($server_id);

$db_host = 'mysql-db';
$db_name = 'minecraft_panel';
$db_user = 'root';
$db_pass = 'nouveaumotdepasse123';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $stmt = $pdo->prepare("SELECT path, pc_ip FROM servers WHERE id = ?");
    $stmt->execute([$server_id]);
    $server = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$server) {
        // Try to get first server
        $stmt = $pdo->query("SELECT path, pc_ip FROM servers LIMIT 1");
        $server = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    if (!$server) {
        echo json_encode(['status' => 'error', 'message' => 'Serveur non trouvé']);
        exit;
    }
    
    $pc_ip = $server['pc_ip'] ?: '192.168.1.22';
    $api_key = '6CeuzFgZu7WJko0x3i1KcIH82PJsaNzYvFPQcPto+F8=';
    
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erreur BDD']);
    exit;
}

function callApi($endpoint, $data, $pc_ip, $api_key) {
    $ch = curl_init("http://$pc_ip:8080$endpoint");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', "X-API-Key: $api_key"]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $output = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['output' => $output, 'code' => $code];
}

$valid_actions = ['list', 'read', 'write', 'mkdir', 'delete', 'rename'];
if (!in_array($action, $valid_actions) && !isset($_FILES["file"])) {
    echo json_encode(['status' => 'error', 'message' => 'Action non reconnue']);
    exit;
}

// Quick check if API is running - don't start it
if (!checkApiRunning($pc_ip)) {
    echo json_encode(['status' => 'error', 'message' => 'API non démarrée']);
    exit;
}

if ($action === 'list') {
    $result = callApi('/files', ['server_id' => $server_id, 'path' => $path], $pc_ip, $api_key);
    
    if ($result['code'] !== 200) {
        echo json_encode(['status' => 'error', 'message' => 'Erreur liste']);
        exit;
    }
    
    $response = json_decode($result['output'], true);
    if ($response && isset($response['files'])) {
        foreach ($response['files'] as &$item) {
            if (!isset($item['is_dir'])) {
                $item['is_dir'] = (strpos($item['name'], '.') === false);
            }
        }
        echo json_encode(['status' => 'ok', 'items' => $response['files'], 'path' => $path]);
        exit;
    }
    echo json_encode(['status' => 'error', 'message' => 'Erreur']);
    exit;
}

if ($action === 'read') {
    $result = callApi('/files/read', ['server_id' => $server_id, 'path' => $path], $pc_ip, $api_key);
    echo $result['code'] === 200 ? $result['output'] : json_encode(['status' => 'error']);
    exit;
}

if ($action === 'write') {
    $result = callApi('/files/write', ['server_id' => $server_id, 'path' => $path, 'content' => base64_encode($content)], $pc_ip, $api_key);
    echo $result['code'] === 200 ? $result['output'] : json_encode(['status' => 'error']);
    exit;
}

if ($action === 'mkdir') {
    $result = callApi('/files/mkdir', ['server_id' => $server_id, 'path' => $path], $pc_ip, $api_key);
    echo $result['code'] === 200 ? $result['output'] : json_encode(['status' => 'error']);
    exit;
}

if ($action === 'delete') {
    $result = callApi('/files/delete', ['server_id' => $server_id, 'path' => $path], $pc_ip, $api_key);
    echo $result['code'] === 200 ? $result['output'] : json_encode(['status' => 'error']);
    exit;
}

if ($action === 'rename') {
    $new_path = $_POST['new_path'] ?? '';
    if (!$new_path) {
        echo json_encode(['status' => 'error', 'message' => 'Nouveau chemin requis']);
        exit;
    }
    $result = callApi('/files/rename', ['server_id' => $server_id, 'path' => $path, 'new_path' => $new_path], $pc_ip, $api_key);
    echo $result['code'] === 200 ? $result['output'] : json_encode(['status' => 'error']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['status' => 'error', 'message' => 'Erreur upload: ' . $file['error']]);
        exit;
    }
    
    $maxSize = 500 * 1024 * 1024; // 500MB
    if ($file['size'] > $maxSize) {
        echo json_encode(['status' => 'error', 'message' => 'Fichier trop volumineux (max 500MB)']);
        exit;
    }
    
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'zip', 'jar', 'txt', 'yml', 'yaml', 'json', 'xml', 'cfg', 'conf', 'properties', 'md', 'html', 'css', 'js', 'sql', 'sh', 'bat', 'ps1'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowedExtensions)) {
        echo json_encode(['status' => 'error', 'message' => 'Extension non autorisée: ' . $ext]);
        exit;
    }
    
    $fileContent = base64_encode(file_get_contents($file['tmp_name']));
    $uploadPath = $path ? $path . '/' . $file['name'] : '/' . $file['name'];
    
    $result = callApi('/files/write', ['server_id' => $server_id, 'path' => $uploadPath, 'content' => $fileContent], $pc_ip, $api_key);
    echo $result['code'] === 200 ? $result['output'] : json_encode(['status' => 'error', 'message' => 'Erreur lors de l\'upload']);
    exit;
}
