<?php

namespace Database\Seeders;

require_once ROOT_PATH . '/app/Models/Server.php';

/**
 * Seeder Server - Crée des serveurs de test
 * 
 * Exercice : Ajouter vos propres serveurs
 */
class ServerSeeder {
    private $db;
    private $serverModel;

    public function __construct($db) {
        $this->db = $db;
        $this->serverModel = new \App\Models\Server($db);
    }

    /**
     * Exécuter le seeder
     */
    public function run() {
        echo "Création des serveurs...\n";

        $servers = [
            [
                'name' => 'Survival',
                'description' => 'Serveur survival classique',
                'port' => 25565,
                'memory' => 4096
            ],
            [
                'name' => 'Creative',
                'description' => 'Serveur creative avec plots',
                'port' => 25566,
                'memory' => 2048
            ],
            [
                'name' => 'Skyblock',
                'description' => 'Serveur skyblock',
                'port' => 25567,
                'memory' => 2048
            ]
        ];

        foreach ($servers as $server) {
            if (!$this->serverModel->findByName($server['name'])) {
                $this->serverModel->create($server);
                echo "- {$server['name']} créé\n";
            } else {
                echo "- {$server['name']} existe déjà\n";
            }
        }

        echo "Terminé !\n";
    }
}
