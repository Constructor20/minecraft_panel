<?php

namespace Database\Seeders;

require_once ROOT_PATH . '/app/Models/User.php';

/**
 * Seeder User - Crée des utilisateurs de test
 * 
 * Exercice : Ajouter d'autres utilisateurs
 */
class UserSeeder {
    private $db;
    private $userModel;

    public function __construct($db) {
        $this->db = $db;
        $this->userModel = new \App\Models\User($db);
    }

    /**
     * Exécuter le seeder
     */
    public function run() {
        echo "Création des utilisateurs...\n";

        $users = [
            [
                'username' => 'admin',
                'email' => 'admin@panel.local',
                'password' => 'admin123',
                'role' => 'admin'
            ],
            [
                'username' => 'chris',
                'email' => 'chris@panel.local',
                'password' => 'chris123',
                'role' => 'user'
            ],
            [
                'username' => 'aleix',
                'email' => 'aleix@panel.local',
                'password' => 'aleix123',
                'role' => 'user'
            ]
        ];

        foreach ($users as $user) {
            if (!$this->userModel->exists($user['username'], $user['email'])) {
                $hash = $this->userModel->hashPassword($user['password']);
                $this->userModel->create($user['username'], $user['email'], $hash, $user['role']);
                echo "- {$user['username']} créé\n";
            } else {
                echo "- {$user['username']} existe déjà\n";
            }
        }

        echo "Terminé !\n";
    }
}
