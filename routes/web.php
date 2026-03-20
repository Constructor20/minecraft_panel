<?php

use App\Router;
use App\Database\Database;
use App\Models\User;
use App\Models\Server;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ServerController;

/**
 * Routes web
 */

$router = new Router();
$db = Database::getInstance();
$userModel = new User($db);
$serverModel = new Server($db);

// Page d'accueil - redirection vers login ou dashboard
$router->get('/', function() {
    if (isset($_SESSION['user_id'])) {
        header('Location: /dashboard');
    } else {
        header('Location: /login');
    }
    exit;
});

// Auth
$router->get('/login', function() use ($db, $userModel) {
    $auth = new AuthController($db, $userModel);
    $auth->showLogin();
});

$router->post('/login', function() use ($db, $userModel) {
    $auth = new AuthController($db, $userModel);
    $auth->login();
});

$router->get('/logout', function() use ($db, $userModel) {
    $auth = new AuthController($db, $userModel);
    $auth->logout();
});

$router->get('/register', function() {
    require ROOT_PATH . '/resources/views/auth/register.php';
});

$router->post('/register', function() use ($db, $userModel) {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        $_SESSION['error'] = 'Tous les champs sont requis';
        header('Location: /register');
        exit;
    }

    if ($userModel->exists($username, $email)) {
        $_SESSION['error'] = 'Utilisateur ou email déjà existant';
        header('Location: /register');
        exit;
    }

    $hash = $userModel->hashPassword($password);
    $userModel->create($username, $email, $hash);

    $_SESSION['success'] = 'Compte créé, vous pouvez vous connecter';
    header('Location: /login');
    exit;
});

// Dashboard
$router->get('/dashboard', function() {
    $auth = new AuthController(Database::getInstance(), new User(Database::getInstance()));
    $auth->requireLogin();
    require ROOT_PATH . '/resources/views/dashboard.php';
});

// Servers
$router->get('/servers', function() use ($db, $serverModel) {
    $auth = new AuthController($db, new User($db));
    $auth->requireLogin();
    $controller = new ServerController($db, $serverModel);
    $controller->index();
});

$router->get('/servers/create', function() use ($db, $serverModel) {
    $auth = new AuthController($db, new User($db));
    $auth->requireLogin();
    $controller = new ServerController($db, $serverModel);
    $controller->create();
});

$router->post('/servers', function() use ($db, $serverModel) {
    $auth = new AuthController($db, new User($db));
    $auth->requireLogin();
    $controller = new ServerController($db, $serverModel);
    $controller->store();
});

$router->get('/servers/{id}', function($id) use ($db, $serverModel) {
    $auth = new AuthController($db, new User($db));
    $auth->requireLogin();
    $controller = new ServerController($db, $serverModel);
    $controller->show($id);
});

$router->get('/servers/{id}/edit', function($id) use ($db, $serverModel) {
    $auth = new AuthController($db, new User($db));
    $auth->requireLogin();
    $controller = new ServerController($db, $serverModel);
    $controller->edit($id);
});

$router->post('/servers/{id}/update', function($id) use ($db, $serverModel) {
    $auth = new AuthController($db, new User($db));
    $auth->requireLogin();
    $controller = new ServerController($db, $serverModel);
    $controller->update($id);
});

$router->get('/servers/{id}/delete', function($id) use ($db, $serverModel) {
    $auth = new AuthController($db, new User($db));
    $auth->requireLogin();
    $controller = new ServerController($db, $serverModel);
    $controller->delete($id);
});

$router->get('/servers/{id}/start', function($id) use ($db, $serverModel) {
    $auth = new AuthController($db, new User($db));
    $auth->requireLogin();
    $controller = new ServerController($db, $serverModel);
    $controller->start($id);
});

$router->get('/servers/{id}/stop', function($id) use ($db, $serverModel) {
    $auth = new AuthController($db, new User($db));
    $auth->requireLogin();
    $controller = new ServerController($db, $serverModel);
    $controller->stop($id);
});

// Profile
$router->get('/profile', function() use ($db, $userModel) {
    $auth = new AuthController($db, $userModel);
    $auth->requireLogin();
    require ROOT_PATH . '/resources/views/profile.php';
});

// Dispatch
$router->dispatch();
