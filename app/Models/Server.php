<?php

namespace App\Models;

/**
 * Modèle Server - Gère les serveurs Minecraft
 * 
 * Exercice : Ajouter des méthodes pour les statistiques
 */
class Server {
    private $db;
    private $table = 'servers';

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Récupérer tous les serveurs
     * 
     * @return array
     */
    public function all() {
        return $this->db->fetchAll("SELECT * FROM {$this->table}");
    }

    /**
     * Trouver un serveur par son ID
     * 
     * @param int $id
     * @return array|false
     */
    public function find($id) {
        return $this->db->fetch(
            "SELECT * FROM {$this->table} WHERE id = ?",
            [$id]
        );
    }

    /**
     * Trouver un serveur par son nom
     * 
     * @param string $name
     * @return array|false
     */
    public function findByName($name) {
        return $this->db->fetch(
            "SELECT * FROM {$this->table} WHERE name = ?",
            [$name]
        );
    }

    /**
     * Créer un nouveau serveur
     * 
     * @param array $data
     * @return int ID du nouveau serveur
     */
    public function create($data) {
        $this->db->query(
            "INSERT INTO {$this->table} (name, description, port, memory, status) VALUES (?, ?, ?, ?, ?)",
            [$data['name'], $data['description'], $data['port'], $data['memory'], 'stopped']
        );
        return $this->db->lastInsertId();
    }

    /**
     * Mettre à jour un serveur
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        $fields = [];
        $values = [];

        foreach ($data as $key => $value) {
            $fields[] = "{$key} = ?";
            $values[] = $value;
        }

        $values[] = $id;
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?";

        $this->db->query($sql, $values);
        return true;
    }

    /**
     * Supprimer un serveur
     * 
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $this->db->query("DELETE FROM {$this->table} WHERE id = ?", [$id]);
        return true;
    }

    /**
     * Mettre à jour le statut d'un serveur
     * 
     * @param int $id
     * @param string $status (started, stopped, starting, stopping)
     * @return bool
     */
    public function updateStatus($id, $status) {
        $this->db->query(
            "UPDATE {$this->table} SET status = ? WHERE id = ?",
            [$status, $id]
        );
        return true;
    }

    /**
     * Récupérer les serveurs actifs
     * 
     * @return array
     */
    public function getRunning() {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE status = 'started'"
        );
    }
}
