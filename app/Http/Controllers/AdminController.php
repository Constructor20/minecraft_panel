<?php

namespace App\Http\Controllers;

use App\Models\Server;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // Admin : tout en un — serveurs, utilisateurs, permissions
    // Admin : tout en un — serveurs, utilisateurs, permissions
    public function index()
    {
        $users = User::all();
        $servers = Server::all();
        $permissions = Permission::with('user', 'server')->get();
        return view('admin.index', compact('users', 'servers', 'permissions'));
    }

    // === SERVERS CRUD ===
    // Créer un serveur (depuis le modal)
    // Créer un serveur (depuis le modal)
    public function storeServer(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'port' => 'required|integer|min:1024|max:65535|unique:servers,port',
            'ram' => 'required|integer|min:1',
            'ram_unit' => 'required|in:G,M',
            'max_players' => 'required|integer|min:1',
            'path' => 'nullable|string',
            'jar_file' => 'nullable|string',
            'java_args' => 'nullable|string',
            'pc_ip' => 'nullable|string',
        ]);

        Server::create($data);
        return redirect()->route('admin.index')->with('success', 'Serveur créé');
    }

    // Modifier un serveur
    // Modifier un serveur
    public function updateServer(Request $request, Server $server)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'port' => 'required|integer|min:1024|max:65535|unique:servers,port,' . $server->id,
            'ram' => 'required|integer|min:1',
            'ram_unit' => 'required|in:G,M',
            'max_players' => 'required|integer|min:1',
            'path' => 'nullable|string',
            'jar_file' => 'nullable|string',
            'java_args' => 'nullable|string',
            'pc_ip' => 'nullable|string',
            'auto_start' => 'boolean',
        ]);

        $data['auto_start'] = $request->boolean('auto_start');
        $server->update($data);

        return redirect()->route('admin.index')->with('success', 'Serveur mis à jour');
    }

    // Supprimer + nettoyer les permissions liées
    // Supprimer + nettoyer les permissions liées
    public function destroyServer(Server $server)
    {
        $server->permissions()->delete();
        $server->delete();
        return redirect()->route('admin.index')->with('success', 'Serveur supprimé');
    }

    // === PERMISSIONS CRUD ===
    // Ajouter ou mettre à jour une permission (updateOrCreate)
    // Ajouter ou mettre à jour une permission (updateOrCreate)
    public function storePermission(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'server_id' => 'required|exists:servers,id',
            'can_view' => 'boolean',
            'can_start' => 'boolean',
            'can_stop' => 'boolean',
            'can_console' => 'boolean',
            'can_files' => 'boolean',
        ]);

        Permission::updateOrCreate(
            ['user_id' => $data['user_id'], 'server_id' => $data['server_id']],
            [
                'can_view' => $request->boolean('can_view'),
                'can_start' => $request->boolean('can_start'),
                'can_stop' => $request->boolean('can_stop'),
                'can_console' => $request->boolean('can_console'),
                'can_files' => $request->boolean('can_files'),
            ]
        );

        return redirect()->route('admin.index')->with('success', 'Permission mise à jour');
    }

        // Modifier les droits d'une permission existante
    // Modifier les droits d'une permission existante
    public function updatePermission(Request $request, Permission $permission)
    {
        $data = $request->validate([
            'can_view' => 'boolean',
            'can_start' => 'boolean',
            'can_stop' => 'boolean',
            'can_console' => 'boolean',
            'can_files' => 'boolean',
        ]);

        $permission->update([
            'can_view' => $request->boolean('can_view'),
            'can_start' => $request->boolean('can_start'),
            'can_stop' => $request->boolean('can_stop'),
            'can_console' => $request->boolean('can_console'),
            'can_files' => $request->boolean('can_files'),
        ]);

        return redirect()->route('admin.index')->with('success', 'Permission mise à jour');
    }

    // Supprimer un utilisateur + ses permissions
    // Supprimer un utilisateur + ses permissions
    public function destroyUser(User $user)
    {
        $user->permissions()->delete();
        $user->delete();
        return redirect()->route('admin.index')->with('success', 'Utilisateur supprimé');
    }
    // Supprimer une permission
    // Supprimer une permission
    public function destroyPermission(Permission $permission)
    {
        $permission->delete();
        return redirect()->route('admin.index')->with('success', 'Permission supprimée');
    }
}