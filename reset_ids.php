<?php
// Reset IDs and create default permissions
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Server;
use App\Models\Permission;
use App\Models\User;

echo "=== Resetting Server IDs ===\n";

// Disable FK checks
DB::statement('SET FOREIGN_KEY_CHECKS=0');

// Get servers ordered by current ID, reassign sequential IDs
$servers = Server::orderBy('id')->get();
$i = 1;
foreach ($servers as $s) {
    DB::table('servers')->where('id', $s->id)->update(['id' => $i]);
    echo "  {$s->name}: {$s->id} -> {$i}\n";
    $i++;
}

// Reset auto-increment
DB::statement('ALTER TABLE servers AUTO_INCREMENT = ' . ($i + 1));

echo "=== Resetting User IDs ===\n";
$users = User::orderBy('id')->get();
$j = 1;
foreach ($users as $u) {
    DB::table('users')->where('id', $u->id)->update(['id' => $j]);
    echo "  {$u->username}: {$u->id} -> {$j}\n";
    $j++;
}
DB::statement('ALTER TABLE users AUTO_INCREMENT = ' . ($j + 1));

echo "=== Creating sample permissions ===\n";

// User 1 (Chris) gets full access to all servers
foreach (Server::all() as $s) {
    Permission::updateOrCreate(
        ['user_id' => 1, 'server_id' => $s->id],
        ['can_view' => true, 'can_start' => true, 'can_stop' => true, 'can_console' => true, 'can_files' => true]
    );
    echo "  Chris -> {$s->name}: all permissions\n";
}

// User 2 (testuser) gets view + console only
foreach (Server::all() as $s) {
    Permission::updateOrCreate(
        ['user_id' => 2, 'server_id' => $s->id],
        ['can_view' => true, 'can_start' => false, 'can_stop' => false, 'can_console' => true, 'can_files' => false]
    );
    echo "  testuser -> {$s->name}: view + console\n";
}

DB::statement('SET FOREIGN_KEY_CHECKS=1');

echo "\n=== Final state ===\n";
echo "Servers: " . Server::count() . "\n";
echo "Users: " . User::count() . "\n";
echo "Permissions: " . Permission::count() . "\n";
echo "Done.\n";
