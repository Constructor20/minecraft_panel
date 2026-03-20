<?php

namespace App;

/**
 * Router simple - Gère les routes de l'application
 * 
 * Exercice : Ajouter les routes pour l'API JSON
 */
class Router {
    private $routes = [];

    /**
     * Ajouter une route GET
     */
    public function get($path, $callback) {
        $this->routes['GET'][$path] = $callback;
    }

    /**
     * Ajouter une route POST
     */
    public function post($path, $callback) {
        $this->routes['POST'][$path] = $callback;
    }

    /**
     * Dispatcher la requête
     */
    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Supprimer le slash final
        $uri = rtrim($uri, '/');

        // Vérifier si une route correspond
        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $route => $callback) {
                // Convertir {id} en expression régulière
                $pattern = preg_replace('/\{(\w+)\}/', '(\d+)', $route);
                $pattern = '#^' . $pattern . '$#';

                if (preg_match($pattern, $uri, $matches)) {
                    array_shift($matches); // Supprimer le match complet

                    if (is_callable($callback)) {
                        return call_user_func_array($callback, $matches);
                    }

                    if (is_string($callback)) {
                        return $this->callController($callback, $matches);
                    }
                }
            }
        }

        // Route non trouvée
        http_response_code(404);
        echo "Page non trouvée";
    }

    /**
     * Appeler un contrôleur
     */
    private function callController($callback, $params) {
        list($controller, $action) = explode('@', $callback);

        $controllerClass = "App\\Http\\Controllers\\{$controller}";

        if (!class_exists($controllerClass)) {
            throw new \Exception("Contrôleur non trouvé : {$controllerClass}");
        }

        $controllerFile = ROOT_PATH . "/app/Http/Controllers/{$controller}.php";
        require_once $controllerFile;

        $db = \App\Database\Database::getInstance();
        $userModel = new \App\Models\User($db);
        $serverModel = new \App\Models\Server($db);

        $controllerInstance = new $controllerClass($db, $userModel, $serverModel);

        if (!method_exists($controllerInstance, $action)) {
            throw new \Exception("Méthode non trouvée : {$action}");
        }

        return call_user_func_array([$controllerInstance, $action], $params);
    }
}
