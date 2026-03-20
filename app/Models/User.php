<?php

namespace App\Models;

/**
 * Modèle User - Gère les utilisateurs
 * 
 * Exercice : Ajouter une méthode pour changer le mot de passe
 */
class User {
    private $db;
    private $table = 'users';

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Trouver un utilisateur par son ID
     * 
     * @param int $id
     * @return array|false
     */
    public function find($id) {
        return $this->db->fetch(
            "SELECT id, username, email, role, created_at FROM {$this->table} WHERE id = ?",
            [$id]
        );
    }

    /**
     * Trouver un utilisateur par son pseudo ou email
     * 
     * @param string $username
     * @return array|false
     */
    public function findByUsernameOrEmail($username) {
        return $this->db->fetch(
            "SELECT * FROM {$this->table} WHERE username = ? OR email = ?",
            [$username, $username]
        );
    }

    /**
     * Vérifier si un utilisateur existe
     * 
     * @param string $username
     * @param string $email
     * @return bool
     */
    public function exists($username, $email) {
        $result = $this->db->fetch(
            "SELECT id FROM {$this->table} WHERE username = ? OR email = ?",
            [$username, $email]
        );
        return $result !== false;
    }

    /**
     * Créer un nouvel utilisateur
     * 
     * @param string $username
     * @param string $email
     * @param string $password (hashée)
     * @param string $role (par défaut : 'user')
     * @return int ID du nouvel utilisateur
     */
    public function create($username, $email, $password, $role = 'user') {
        $this->db->query(
            "INSERT INTO {$this->table} (username, email, password, role) VALUES (?, ?, ?, ?)",
            [$username, $email, $password, $role]
        );
        return $this->db->lastInsertId();
    }

    /**
     * Vérifier le mot de passe
     * 
     * @param string $password Mot de passe en clair
     * @param string $hash Hash stocké en base
     * @return bool
     */
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Hasher un mot de passe
     * 
     * @param string $password
     * @return string
     */
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Récupérer tous les utilisateurs
     * 
     * @return array
     */
    public function all() {
        return $this->db->fetchAll(
            "SELECT id, username, email, role, created_at FROM {$this->table}"
        );
    }
}
