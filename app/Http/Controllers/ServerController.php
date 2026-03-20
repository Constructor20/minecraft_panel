<?php

namespace App\Http\Controllers;

/**
 * Controller Server - Gère les serveurs Minecraft
 * 
 * Exercice : Ajouter les actions WOL et contrôle API
 */
class ServerController {
    private $serverModel;
    private $db;

    public function __construct($db, $serverModel) {
        $this->db = $db;
        $this->serverModel = $serverModel;
    }

    /**
     * Liste tous les serveurs
     */
    public function index() {
        $servers = $this->serverModel->all();
        require ROOT_PATH . '/resources/views/servers/index.php';
    }

    /**
     * Affiche un serveur
     */
    public function show($id) {
        $server = $this->serverModel->find($id);

        if (!$server) {
            http_response_code(404);
            require ROOT_PATH . '/resources/views/errors/404.php';
            return;
        }

        require ROOT_PATH . '/resources/views/servers/show.php';
    }

    /**
     * Formulaire de création
     */
    public function create() {
        require ROOT_PATH . '/resources/views/servers/create.php';
    }

    /**
     * Enregistrer un nouveau serveur
     */
    public function store() {
        $data = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'port' => (int) ($_POST['port'] ?? 0),
            'memory' => (int) ($_POST['memory'] ?? 2048)
        ];

        if (empty($data['name']) || empty($data['port'])) {
            $_SESSION['error'] = 'Nom et port sont requis';
            header('Location: /servers/create');
            exit;
        }

        $this->serverModel->create($data);

        $_SESSION['success'] = 'Serveur créé avec succès';
        header('Location: /servers');
        exit;
    }

    /**
     * Formulaire d'édition
     */
    public function edit($id) {
        $server = $this->serverModel->find($id);

        if (!$server) {
            http_response_code(404);
            require ROOT_PATH . '/resources/views/errors/404.php';
            return;
        }

        require ROOT_PATH . '/resources/views/servers/edit.php';
    }

    /**
     * Mettre à jour un serveur
     */
    public function update($id) {
        $data = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'port' => (int) ($_POST['port'] ?? 0),
            'memory' => (int) ($_POST['memory'] ?? 2048)
        ];

        $this->serverModel->update($id, $data);

        $_SESSION['success'] = 'Serveur mis à jour';
        header('Location: /servers');
        exit;
    }

    /**
     * Supprimer un serveur
     */
    public function delete($id) {
        $this->serverModel->delete($id);

        $_SESSION['success'] = 'Serveur supprimé';
        header('Location: /servers');
        exit;
    }

    /**
     * Démarrer un serveur
     * 
     * Exercice : Implémenter la logique WOL et API
     */
    public function start($id) {
        $server = $this->serverModel->find($id);

        if (!$server) {
            $_SESSION['error'] = 'Serveur non trouvé';
            header('Location: /servers');
            exit;
        }

        // TODO: Implémenter le démarrage via SSH/API
        // 1. Envoyer WOL si le PC est éteint
        // 2. Attendre que le PC démarre
        // 3. Envoyer la commande de démarrage au serveur

        $this->serverModel->updateStatus($id, 'starting');

        $_SESSION['success'] = "Serveur {$server['name']} en cours de démarrage...";
        header('Location: /servers');
        exit;
    }

    /**
     * Arrêter un serveur
     * 
     * Exercice : Implémenter l'arrêt via SSH/API
     */
    public function stop($id) {
        $server = $this->serverModel->find($id);

        if (!$server) {
            $_SESSION['error'] = 'Serveur non trouvé';
            header('Location: /servers');
            exit;
        }

        // TODO: Implémenter l'arrêt via SSH/API

        $this->serverModel->updateStatus($id, 'stopped');

        $_SESSION['success'] = "Serveur {$server['name']} arrêté";
        header('Location: /servers');
        exit;
    }
}
