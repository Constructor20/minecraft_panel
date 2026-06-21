<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Server;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $servers = [
            [
                'name' => 'Create 2', 'type' => 'forge', 'port' => 25565,
                'path' => 'F:\\all_serv\\create2', 'ram' => 8, 'ram_unit' => 'G',
                'jar_file' => 'server.jar', 'max_players' => 20,
                'java_args' => '-Xmx6G -Xms4G',
                'pc_ip' => '192.168.1.22',
                'auto_start' => true,
            ],
            [
                'name' => 'Survie Vanilla', 'type' => 'vanilla', 'port' => 25566,
                'path' => '/home/minecraft/server1', 'ram' => 4, 'ram_unit' => 'G',
                'jar_file' => 'server.jar', 'max_players' => 10,
                'java_args' => '-Xmx3G -Xms1G',
                'pc_ip' => '192.168.1.22',
                'auto_start' => false,
            ],
            [
                'name' => 'Mini-Jeux', 'type' => 'paper', 'port' => 25567,
                'path' => 'F:\\all_serv\\minigames', 'ram' => 6, 'ram_unit' => 'G',
                'jar_file' => 'paper.jar', 'max_players' => 30,
                'java_args' => '-Xmx4G -Xms2G',
                'pc_ip' => '192.168.1.22',
                'auto_start' => false,
            ],
            [
                'name' => 'Skyblock', 'type' => 'purpur', 'port' => 25568,
                'path' => 'F:\\all_serv\\skyblock', 'ram' => 3, 'ram_unit' => 'G',
                'jar_file' => 'purpur.jar', 'max_players' => 15,
                'java_args' => '-Xmx2G -Xms1G',
                'pc_ip' => '192.168.1.22',
                'auto_start' => false,
            ],
            [
                'name' => 'Ancien Serveur', 'type' => 'spigot', 'port' => 25569,
                'path' => 'F:\\all_serv\\old', 'ram' => 2, 'ram_unit' => 'G',
                'jar_file' => 'spigot.jar', 'max_players' => 5,
                'java_args' => '-Xmx1G -Xms512M',
                'pc_ip' => '192.168.1.22',
                'auto_start' => false,
            ],
        ];

        foreach ($servers as $data) {
            Server::create($data);
        }

        $count = Server::count();
        echo "Seeded: $count servers\n";
    }
}