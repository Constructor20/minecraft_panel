<?php
class Auth {
    private $db;
    private $sessionKey = 'user_id';

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function isLoggedIn() {
        return isset($_SESSION[$this->sessionKey]);
    }

    public function getUserId() {
        return $_SESSION[$this->sessionKey] ?? null;
    }

    public function getUsername() {
        return $_SESSION['username'] ?? null;
    }

    public function getEmail() {
        return $_SESSION['email'] ?? null;
    }

    public function isAdmin() {
        return $this->getUserId() == 1;
    }

    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    public function requireAdmin() {
        $this->requireLogin();
        if (!$this->isAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Accès refusé']);
            exit;
        }
    }

    public function login($userId, $username, $email) {
        session_regenerate_id(true);
        $_SESSION[$this->sessionKey] = $userId;
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
    }

    public function logout() {
        $_SESSION = [];
        session_destroy();
        header('Location: ' . BASE_URL . '/login');
        exit;
    }

    public function getUser($userId) {
        return $this->db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
    }

    public function verifyUser($username, $password) {
        $user = $this->db->fetch(
            "SELECT * FROM users WHERE username = ? OR email = ?",
            [$username, $username]
        );

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return null;
    }

    public function createUser($username, $email, $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $this->db->query(
            "INSERT INTO users (username, email, password) VALUES (?, ?, ?)",
            [$username, $email, $hash]
        );
        return $this->db->lastInsertId();
    }

    public function userExists($username, $email) {
        return $this->db->fetch(
            "SELECT id FROM users WHERE username = ? OR email = ?",
            [$username, $email]
        ) !== false;
    }

    public function updatePassword($userId, $newPassword) {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->db->query(
            "UPDATE users SET password = ? WHERE id = ?",
            [$hash, $userId]
        );
    }

    public function updateProfile($userId, $username, $email) {
        $this->db->query(
            "UPDATE users SET username = ?, email = ? WHERE id = ?",
            [$username, $email, $userId]
        );
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
    }
}
