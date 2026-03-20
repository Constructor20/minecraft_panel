<?php

namespace App\Database;

/**
 * Classe Database - Connexion à MySQL
 * 
 * Utilise PDO pour se connecter à la base de données.
 * 
 * Exercice : Ajouter une méthode pour les transactions
 */
class Database {
    private $pdo;
    private static $instance = null;

    private $host;
    private $dbname;
    private $username;
    private $password;

    /**
     * Constructeur - Lit la config et établit la connexion
     */
    public function __construct() {
        $config = require ROOT_PATH . '/config/database.php';

        $this->host = $config['host'];
        $this->dbname = $config['database'];
        $this->username = $config['username'];
        $this->password = $config['password'];

        $this->connect();
    }

    /**
     * Connexion à la base de données
     */
    private function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";
            $this->pdo = new \PDO($dsn, $this->username, $this->password, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (\PDOException $e) {
            error_log("Erreur de connexion : " . $e->getMessage());
            throw new \Exception("Impossible de se connecter à la base de données");
        }
    }

    /**
     * Singleton - Une seule instance de Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Exécuter une requête SQL
     * 
     * @param string $sql
     * @param array $params
     * @return \PDOStatement
     */
    public function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Récupérer toutes les lignes
     * 
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Récupérer une seule ligne
     * 
     * @param string $sql
     * @param array $params
     * @return array|false
     */
    public function fetch($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    /**
     * Dernier ID inséré
     * 
     * @return string
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    /**
     * Obtenir la connexion PDO
     */
    public function getConnection() {
        return $this->pdo;
    }
}
