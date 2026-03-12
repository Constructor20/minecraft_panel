<?php
require_once BASE_PATH . '/includes/lib/woltour.php';
require_once BASE_PATH . '/includes/lib/sshtour.php';

class ServerService {
    private $db;
    private $apiKey = '6CeuzFgZu7WJko0x3i1KcIH82PJsaNzYvFPQcPto+F8=';
    private $pcIp = '192.168.1.22';

    public function __construct(Database $db) {
        $this->db = $db;
    }

    private function checkPcOnline($ip) {
        $ch = @fsockopen($ip, 80, $errno, $errstr, 1);
        if ($ch) { 
            fclose($ch); 
            return true;
        }
        $ch = @fsockopen($ip, 22, $errno, $errstr, 1);
        if ($ch) { 
            fclose($ch); 
            return true;
        }
        return false;
    }

    public function getServer($serverId) {
        return $this->db->fetch("SELECT * FROM servers WHERE id = ?", [$serverId]);
    }

    public function getServerWithPermissions($serverId, $userId) {
        if ($userId == 1) {
            $server = $this->getServer($serverId);
            return [
                'server' => $server,
                'permissions' => [
                    'can_view' => 1,
                    'can_start' => 1,
                    'can_stop' => 1,
                    'can_console' => 1,
                    'can_files' => 1
                ]
            ];
        }

        $server = $this->getServer($serverId);
        $perm = $this->db->fetch("SELECT * FROM permissions WHERE user_id = ? AND server_id = ?", [$userId, $serverId]);

        return [
            'server' => $server,
            'permissions' => $perm ?: [
                'can_view' => 0,
                'can_start' => 0,
                'can_stop' => 0,
                'can_console' => 0,
                'can_files' => 0
            ]
        ];
    }

    private function callApi($url, $postData = null) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-API-Key: ' . $this->apiKey,
            'Content-Type: application/json'
        ]);
        
        if ($postData) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        }
        
        $output = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        return ['output' => $output, 'code' => $code, 'error' => $error];
    }

    public function getServerStatus($serverId) {
        $server = $this->getServer($serverId);
        
        if (!$server) {
            return ['status' => 'error', 'message' => 'Serveur non trouvé dans la base de données'];
        }

        $pcIp = $server['pc_ip'] ?? $this->pcIp;
        
        // Check if PC is reachable first
        $pcOnline = $this->checkPcOnline($pcIp);
        
        if (!$pcOnline) {
            return [
                'status' => 'ok',
                'pc_online' => false,
                'api_running' => false,
                'running' => false,
                'online' => false,
                'starting' => false,
                'stopping' => false,
                'current_players' => 0,
                'max_players' => $server['max_players'] ?? 20,
                'cpu' => 0,
                'ram' => 0,
                'tps' => 20,
                'logs' => [],
                'message' => 'PC distant (' . $pcIp . ') injoignable. Vérifiez la connexion réseau.'
            ];
        }

        // Check if API is running first
        $healthCheck = curl_init("http://$pcIp:8080/health");
        curl_setopt($healthCheck, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($healthCheck, CURLOPT_TIMEOUT, 2);
        curl_setopt($healthCheck, CURLOPT_CONNECTTIMEOUT, 2);
        curl_exec($healthCheck);
        $healthCode = curl_getinfo($healthCheck, CURLINFO_HTTP_CODE);
        curl_close($healthCheck);
        
        if ($healthCode !== 200) {
            return [
                'status' => 'ok',
                'pc_online' => true,
                'api_running' => false,
                'running' => false,
                'online' => false,
                'starting' => false,
                'stopping' => false,
                'current_players' => 0,
                'max_players' => $server['max_players'] ?? 20,
                'cpu' => 0,
                'ram' => 0,
                'tps' => 20,
                'logs' => [],
                'message' => "API non démarrée sur le PC distant. Démarrez l'API Python."
            ];
        }
        
        $result = $this->callApi("http://$pcIp:8080/status/$serverId");
        
        if ($result['code'] !== 200) {
            return [
                'status' => 'error',
                'message' => 'Erreur API: ' . ($result['error'] ?? 'Code ' . $result['code']),
                'api_running' => true,
                'running' => false,
                'online' => false,
                'starting' => false,
                'stopping' => false,
                'current_players' => 0,
                'max_players' => $server['max_players'] ?? 20,
                'cpu' => 0,
                'ram' => 0,
                'tps' => 20,
                'logs' => []
            ];
        }

        $data = json_decode($result['output'], true);
        
        // Fetch logs separately if needed
        $logs = [];
        $logsResult = $this->callApi("http://$pcIp:8080/logs/$serverId");
        if ($logsResult['code'] === 200) {
            $logsData = json_decode($logsResult['output'], true);
            $logs = $logsData['logs'] ?? [];
        }
        
        return [
            'status' => 'ok',
            'api_running' => true,
            'running' => $data['running'] ?? false,
            'online' => $data['running'] ?? false,
            'starting' => $data['starting'] ?? false,
            'stopping' => $data['stopping'] ?? false,
            'current_players' => $data['current_players'] ?? 0,
            'max_players' => $server['max_players'] ?? 20,
            'cpu' => $data['cpu'] ?? 0,
            'ram' => $data['ram'] ?? 0,
            'tps' => $data['tps'] ?? 20,
            'logs' => $logs
        ];
    }

    public function sendCommand($serverId, $command) {
        $server = $this->getServer($serverId);
        
        if (!$server) {
            return ['success' => false, 'message' => 'Serveur introuvable'];
        }

        $pcIp = $server['pc_ip'] ?? $this->pcIp;
        
        $result = $this->callApi("http://$pcIp:8080/command", [
            'server_id' => $serverId,
            'command' => $command
        ]);

        return $result['code'] === 200 
            ? ['success' => true, 'message' => 'Commande envoyée']
            : ['success' => false, 'message' => 'Erreur: ' . $result['error']];
    }

    public function sendAction($serverId, $action) {
        $server = $this->getServer($serverId);
        
        if (!$server) {
            return ['success' => false, 'message' => 'Serveur introuvable'];
        }

        $pcIp = $server['pc_ip'] ?? $this->pcIp;
        
        // Quick check if API is already running - just check, don't start
        require_once __DIR__ . '/../includes/lib/api_helper.php';
        
        if (!checkApiRunning($pcIp)) {
            // API not running, try to start it
            $check = ensureApiRunning($serverId, false); // Don't wait
            
            if (!$check['success'] && $check['success'] !== 'starting') {
                return ['success' => false, 'message' => $check['message'] ?? 'Erreur API'];
            }
            
            // Quick wait for API (max 10s)
            for ($i = 0; $i < 10; $i++) {
                usleep(1000000);
                if (checkApiRunning($pcIp)) {
                    break;
                }
            }
            
            if (!checkApiRunning($pcIp)) {
                return ['success' => false, 'message' => 'API non joignable'];
            }
        }

        // Send start/stop command
        $endpoint = ($action === 'start') ? '/start' : '/stop';
        $result = $this->callApi("http://$pcIp:8080$endpoint", ['server_id' => $serverId]);

        if ($result['code'] === 200) {
            return ['success' => true, 'message' => "Serveur $action"];
        } else {
            $responseData = json_decode($result['output'], true);
            return ['success' => false, 'message' => $responseData['error'] ?? 'Erreur HTTP ' . $result['code']];
        }
    }

    public function getSystemStatus() {
        $pcIp = $this->pcIp;
        $result = $this->callApi("http://$pcIp:8080/system/status");

        if ($result['code'] === 200 && $result['output']) {
            $data = json_decode($result['output'], true);
            return [
                'status' => 'ok',
                'any_server_running' => $data['any_server_running'] ?? false,
                'api_running' => $data['api_running'] ?? true
            ];
        }

        return ['status' => 'ok', 'any_server_running' => false, 'api_running' => false];
    }

    public function stopServices() {
        $pcIp = $this->pcIp;
        
        $result = $this->callApi("http://$pcIp:8080/system/stop-services", ['confirm_key' => 'STOP_SERVICES_CONFIRM']);

        return $result['code'] === 200 ? json_decode($result['output'], true) : ['success' => false, 'error' => $result['error']];
    }

    public function getShutdownToken() {
        $pcIp = $this->pcIp;
        $result = $this->callApi("http://$pcIp:8080/system/shutdown-token");

        if ($result['code'] === 200 && $result['output']) {
            $data = json_decode($result['output'], true);
            return $data['token'] ?? null;
        }
        return null;
    }

    public function requestShutdown($token) {
        $pcIp = $this->pcIp;
        $result = $this->callApi("http://$pcIp:8080/system/shutdown-request", ['token' => $token]);

        return $result['code'] === 200 ? json_decode($result['output'], true) : ['success' => false];
    }

    public function cancelShutdown() {
        $pcIp = $this->pcIp;
        $result = $this->callApi("http://$pcIp:8080/system/shutdown-cancel", []);

        return $result['code'] === 200 ? json_decode($result['output'], true) : ['success' => false];
    }

    public function updateServerConfig($serverId, $config) {
        $allowedFields = ['name', 'path', 'java_args', 'port', 'max_players', 'type', 'auto_start', 'auto_restart', 'restart_time', 'backup_enabled', 'backup_interval', 'notify_stop', 'notify_message'];
        
        $updates = [];
        $values = [];
        
        foreach ($config as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $updates[] = "`$key` = ?";
                $values[] = $value;
            }
        }
        
        if (empty($updates)) {
            return ['success' => false, 'message' => 'Aucun champ valide'];
        }
        
        $values[] = $serverId;
        
        $sql = "UPDATE servers SET " . implode(', ', $updates) . " WHERE id = ?";
        
        try {
            $this->db->query($sql, $values);
            return ['success' => true, 'message' => 'Configuration mise à jour'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erreur: ' . $e->getMessage()];
        }
    }
}
