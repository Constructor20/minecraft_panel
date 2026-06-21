<?php

namespace App\Http\Controllers;

use App\Models\Server;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        $users = User::all();
        $servers = Server::all();
        $permissions = Permission::with('user', 'server')->get();
        return view('admin.index', compact('users', 'servers', 'permissions'));
    }

    // === SERVERS CRUD ===
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

    public function destroyServer(Server $server)
    {
        $server->permissions()->delete();
        $server->delete();
        return redirect()->route('admin.index')->with('success', 'Serveur supprimé');
    }

    // === PERMISSIONS CRUD ===
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

    public function destroyPermission(Permission $permission)
    {
        $permission->delete();
        return redirect()->route('admin.index')->with('success', 'Permission supprimée');
    }
}