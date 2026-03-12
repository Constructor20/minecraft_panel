<?php
/**
 * Configuration du Minecraft Panel
 * 
 * Ce fichier contient toutes les constantes de configuration
 * pour faciliter les modifications
 */

// ==================== PC TOUR (Windows) ====================
define('PC_IP', '192.168.1.22');
define('PC_MAC', '2c:f0:5d:7f:e3:2b');
define('SSH_USER', 'aleix');

// ==================== API PYTHON ====================
define('API_KEY', '6CeuzFgZu7WJko0x3i1KcIH82PJsaNzYvFPQcPto+F8=');
define('API_PORT', 8080);
define('API_URL', 'http://' . PC_IP . ':' . API_PORT);

// ==================== BASE DE DONNÉES ====================
define('DB_HOST', '192.168.1.59');
define('DB_PORT', 8005);
define('DB_NAME', 'minecraft_panel');
define('DB_USER', 'root');
define('DB_PASS', 'nouveaumotdepasse123');

// ==================== CHEMINS ====================
define('SSH_KEY_PATH', '/var/www/id_ed25519');

// ==================== TIMEOUTS ====================
define('WOL_WAIT_TIME', 60);      // secondes
define('API_WAIT_TIME', 30);      // secondes
define('SERVER_START_TIME', 60);    // secondes
