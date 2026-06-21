<?php

namespace App\Http\Controllers;

use App\Models\Server;
use Illuminate\Http\Request;

class ServerController extends Controller
{
    public function index()
    {
        $servers = Server::with('permissions')->get();
        return view('servers.index', compact('servers'));
    }

    public function console(Request $request)
    {
        $servers = Server::all();
        $server = $request->id ? Server::find($request->id) : $servers->first();
        return view('servers.console', compact('server', 'servers'));
    }

    public function files(Request $request)
    {
        $servers = Server::all();
        $server = $request->id ? Server::find($request->id) : $servers->first();
        return view('servers.files', compact('server', 'servers'));
    }
}