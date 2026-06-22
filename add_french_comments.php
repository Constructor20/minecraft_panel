<?php
// Replace boring comments with casual French ones
// Target: ServerController.php
$path = '/var/www/html/app/Http/Controllers/ServerController.php';
$ctrl = file_get_contents($path);

$ctrl = str_replace(
    '    public function index()',
    '    // Pages d\'accueil : tous les serveurs en cards
    public function index()',
    $ctrl
);

$ctrl = str_replace(
    '    public function console(Request $request)',
    '    // Console : soit l\'overview avec toutes les cards, soit le terminal si ?id=X
    public function console(Request $request)',
    $ctrl
);

$ctrl = str_replace(
    '    public function files(Request $request)',
    '    // Pareil pour les fichiers : overview cards ou explorateur dédié
    public function files(Request $request)',
    $ctrl
);

file_put_contents($path, $ctrl);
echo "ServerController done\n";

// Same for AdminController
$path2 = '/var/www/html/app/Http/Controllers/AdminController.php';
$admin = file_get_contents($path2);

$admin = str_replace(
    '    public function index()',
    '    // Admin : tout en un — serveurs, utilisateurs, permissions
    public function index()',
    $admin
);

$admin = str_replace(
    '    public function storeServer(Request $request)',
    '    // Créer un serveur (depuis le modal)
    public function storeServer(Request $request)',
    $admin
);

$admin = str_replace(
    '    public function updateServer(Request $request, Server $server)',
    '    // Modifier un serveur
    public function updateServer(Request $request, Server $server)',
    $admin
);

$admin = str_replace(
    '    public function destroyServer(Server $server)',
    '    // Supprimer + nettoyer les permissions liées
    public function destroyServer(Server $server)',
    $admin
);

$admin = str_replace(
    '    public function storePermission(Request $request)',
    '    // Ajouter ou mettre à jour une permission (updateOrCreate)
    public function storePermission(Request $request)',
    $admin
);

$admin = str_replace(
    '    public function updatePermission(Request $request, Permission $permission)',
    '    // Modifier les droits d\'une permission existante
    public function updatePermission(Request $request, Permission $permission)',
    $admin
);

$admin = str_replace(
    '    public function destroyPermission(Permission $permission)',
    '    // Supprimer une permission
    public function destroyPermission(Permission $permission)',
    $admin
);

$admin = str_replace(
    '    public function destroyUser(User $user)',
    '    // Supprimer un utilisateur + ses permissions
    public function destroyUser(User $user)',
    $admin
);

file_put_contents($path2, $admin);
echo "AdminController done\n";

// DashboardController
$path3 = '/var/www/html/app/Http/Controllers/DashboardController.php';
$dash = file_get_contents($path3);

$dash = str_replace(
    '    public function index()',
    '    // Dashboard : stats + liens rapides
    public function index()',
    $dash
);

file_put_contents($path3, $dash);
echo "DashboardController done\n";
