<?php
class CSRF {
    private static $tokenKey = 'csrf_token';

    public static function generate() {
        if (!isset($_SESSION[self::$tokenKey])) {
            $_SESSION[self::$tokenKey] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::$tokenKey];
    }

    public static function get() {
        return self::generate();
    }

    public static function verify($token) {
        if (!isset($_SESSION[self::$tokenKey])) {
            return false;
        }
        return hash_equals($_SESSION[self::$tokenKey], $token);
    }

    public static function tokenField() {
        return '<input type="hidden" name="csrf_token" value="' . self::generate() . '">';
    }

    public static function validateRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        if ($method === 'GET') {
            return true;
        }

        $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        
        if (empty($token)) {
            return false;
        }

        return self::verify($token);
    }
}
