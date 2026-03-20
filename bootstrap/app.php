/**
 * Bootstrap - Initialisation de l'application
 */

// Définir le chemin racine
define('ROOT_PATH', dirname(__DIR__));

// Activer les erreurs pour le développement
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Charger les dépendances (si on utilise Composer, sinon classes PHP simples)
spl_autoload_register(function ($class) {
    $prefixes = [
        'App\\' => ROOT_PATH . '/app/',
        'Database\\' => ROOT_PATH . '/database/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) === 0) {
            $relativeClass = substr($class, $len);
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) {
                require $file;
                return;
            }
        }
    }
});
