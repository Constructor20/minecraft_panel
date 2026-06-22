<?php

namespace App\Http\Controllers;

use App\Models\Server;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // Dashboard : stats + liens rapides
    // Dashboard : stats + liens rapides
    public function index()
    {
        $serverCount = Server::count();
        return view('dashboard', compact('serverCount'));
    }
}