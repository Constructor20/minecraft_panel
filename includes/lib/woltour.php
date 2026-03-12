<?php
function send_wol($mac, $broadcast = "192.168.1.255", $port = 9) {
    // Try using the host script first (more reliable for WOL)
    $script_path = __DIR__ . '/wol.sh';
    if (file_exists($script_path)) {
        exec("bash " . escapeshellarg($script_path) . " 2>&1", $output, $ret);
        if ($ret === 0) {
            return true;
        }
    }
    
    // Fallback to wakeonlan command
    $output = [];
    exec("wakeonlan -i " . escapeshellarg($broadcast) . " " . escapeshellarg($mac) . " 2>&1", $output, $ret);
    
    if ($ret === 0) {
        return true;
    }
    
    return false;
}

function ping_pc($ip, $timeout = 2) {
    if (stripos(PHP_OS, 'WIN') === 0) {
        $output = shell_exec("ping -n 1 -w " . ($timeout*1000) . " " . escapeshellarg($ip));
        return (strpos($output, "TTL=") !== false);
    } else {
        $output = shell_exec("ping -c 1 -W $timeout " . escapeshellarg($ip));
        return (strpos($output, "ttl=") !== false);
    }
}
