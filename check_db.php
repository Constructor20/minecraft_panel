<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== SERVERS ===\n";
foreach (App\Models\Server::all() as $s) {
    echo "{$s->id}: {$s->name} ({$s->type})\n";
}
echo "\n=== PERMISSIONS ===\n";
foreach (App\Models\Permission::all() as $p) {
    echo "{$p->id}: user={$p->user_id} server={$p->server_id} view={$p->can_view} console={$p->can_console} files={$p->can_files}\n";
}
echo "\n=== USERS ===\n";
foreach (App\Models\User::all() as $u) {
    echo "{$u->id}: {$u->username} ({$u->email})\n";
}
