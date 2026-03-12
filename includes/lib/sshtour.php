<?php

function ssh_start_api($ip = null) {
    $ip = $ip ?: "192.168.1.22";
    $user = "aleix";
    $key = "/var/www/id_ed25519";

    $cmd = "ssh -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -i " . escapeshellarg($key) . " " . escapeshellarg("$user@$ip") . " 'schtasks /run /tn MinecraftAPI'";
    
    exec($cmd . " > /dev/null 2>&1 &");
    
    return ["success" => true, "exitCode" => 0, "stdout" => "", "stderr" => ""];
}

function ssh_kill_process($ip, $processName) {
    $ip = $ip ?: "192.168.1.22";
    $user = "aleix";
    $key = "/var/www/id_ed25519";
    
    $cmd = "ssh -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -i " . escapeshellarg($key) . " " . escapeshellarg("$user@$ip") . " 'taskkill /F /IM $processName.exe 2>nul'";
    exec($cmd, $output, $exit);
    return ["success" => $exit === 0, "exitCode" => $exit, "stdout" => implode("\n", $output), "stderr" => ""];
}

function wait_for_ssh($ip, $port = 22, $timeout = 60) {
    $elapsed = 0;
    while ($elapsed < $timeout) {
        $conn = @fsockopen($ip, $port, $errno, $errstr, 1);
        if ($conn) {
            fclose($conn);
            return true;
        }
        usleep(500000);
        $elapsed += 0.5;
    }
    return false;
}

function ssh_exec($cmd, $ip = null) {
    $ip = $ip ?: "192.168.1.22";
    $user = "aleix";
    $key = "/var/www/id_ed25519";
    
    $full_cmd = "ssh -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -i " . escapeshellarg($key) . " " . escapeshellarg("$user@$ip") . " " . escapeshellarg($cmd);
    
    exec($full_cmd, $output, $exit);
    
    return [
        "success"  => $exit === 0,
        "exitCode" => $exit,
        "stdout"   => implode("\n", $output),
        "stderr"   => ""
    ];
}
