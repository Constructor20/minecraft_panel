<?php
class Router {
    private $routes = [];
    private $middlewares = [];

    public function get($path, $handler, $middleware = null) {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post($path, $handler, $middleware = null) {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function put($path, $handler, $middleware = null) {
        $this->addRoute('PUT', $path, $handler, $middleware);
    }

    public function delete($path, $handler, $middleware = null) {
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    private function addRoute($method, $path, $handler, $middleware) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middleware' => $middleware
        ];
    }

    public function run() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        $uri = str_replace(BASE_URL, '', $uri);
        $uri = trim($uri, '/');
        
        if (empty($uri) || $uri === '') {
            $uri = '/';
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $routePath = ltrim($route['path'], '/');
            $pattern = $this->convertToRegex($routePath);

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);

                if ($route['middleware']) {
                    call_user_func($route['middleware']);
                }

                if (is_callable($route['handler'])) {
                    call_user_func_array($route['handler'], $matches);
                } elseif (is_array($route['handler']) && count($route['handler']) === 2) {
                    [$controller, $action] = $route['handler'];
                    call_user_func_array([$controller, $action], $matches);
                }
                return;
            }
        }

        http_response_code(404);
        echo json_encode(['error' => 'Page non trouvée']);
        exit;
    }

    private function convertToRegex($path) {
        if ($path === '/' || $path === '') {
            return '#^/?$#';
        }
        if (strpos($path, '/') === 0) {
            $path = ltrim($path, '/');
        }
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';
        return $pattern;
    }
}
