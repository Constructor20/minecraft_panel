<?php

/**
 * Point d'entrée pour exécuter les seeders
 * 
 * Usage : php database/seed.php
 */

define('ROOT_PATH', dirname(__DIR__));

require_once ROOT_PATH . '/bootstrap/app.php';

use App\Database\Database;
use Database\Seeders\UserSeeder;
use Database\Seeders\ServerSeeder;

echo "=== Seeders Minecraft Panel ===\n\n";

$db = Database::getInstance();

echo "--- Utilisateurs ---\n";
$userSeeder = new UserSeeder($db);
$userSeeder->run();

echo "\n--- Serveurs ---\n";
$serverSeeder = new ServerSeeder($db);
$serverSeeder->run();

echo "\n=== Terminé ===\n";
