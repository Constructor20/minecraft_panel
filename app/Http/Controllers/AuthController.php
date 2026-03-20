<?php

namespace App\Http\Controllers;

/**
 * Controller Auth - Gère la connexion/déconnexion
 * 
 * Exercice : Ajouter l'inscription et la déconnexion
 */
class AuthController {
    private $userModel;
    private $db;

    public function __construct($db, $userModel) {
        $this->db = $db;
        $this->userModel = $userModel;
        $this->startSession();
    }

    private function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Afficher le formulaire de connexion
     */
    public function showLogin() {
        if ($this->isLoggedIn()) {
            header('Location: /dashboard');
            exit;
        }
        require ROOT_PATH . '/resources/views/auth/login.php';
    }

    /**
     * Traiter la connexion
     */
    public function login() {
        $this->startSession();

        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $_SESSION['error'] = 'Tous les champs sont requis';
            header('Location: /login');
            exit;
        }

        $user = $this->userModel->findByUsernameOrEmail($username);

        if ($user && $this->userModel->verifyPassword($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            header('Location: /dashboard');
            exit;
        }

        $_SESSION['error'] = 'Pseudo ou mot de passe incorrect';
        header('Location: /login');
        exit;
    }

    /**
     * Déconnexion
     */
    public function logout() {
        $this->startSession();

        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();

        header('Location: /login');
        exit;
    }

    /**
     * Vérifier si l'utilisateur est connecté
     */
    public function isLoggedIn() {
        $this->startSession();
        return isset($_SESSION['user_id']);
    }

    /**
     * Vérifier si l'utilisateur est connecté, sinon rediriger
     */
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: /login');
            exit;
        }
    }

    /**
     * Obtenir l'utilisateur connecté
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }

        return $this->userModel->find($_SESSION['user_id']);
    }

    /**
     * Vérifier si l'utilisateur est admin
     */
    public function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
}
