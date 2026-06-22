<?php

namespace App\Http\Controllers;

use App\Models\Server;
use Illuminate\Http\Request;

class ServerController extends Controller
{
    // Pages d'accueil : tous les serveurs en cards
    // Pages d'accueil : tous les serveurs en cards
    public function index()
    {
        $servers = Server::with('permissions')->get();
        return view('servers.index', compact('servers'));
    }

    // Console : soit l'overview avec toutes les cards, soit le terminal si ?id=X
    // Console : soit l'overview avec toutes les cards, soit le terminal si ?id=X
    public function console(Request $request)
    {
        $servers = Server::all();
        $server = $request->id ? Server::find($request->id) : null;
        return view('servers.console', compact('server', 'servers'));
    }

    // Pareil pour les fichiers : overview cards ou explorateur dédié
    // Pareil pour les fichiers : overview cards ou explorateur dédié
    public function files(Request $request)
    {
        $servers = Server::all();
        $server = $request->id ? Server::find($request->id) : null;
        return view('servers.files', compact('server', 'servers'));
    }
}