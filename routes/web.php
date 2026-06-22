<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\AdminController;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/profile', function () { return redirect('/dashboard'); })->name('profile');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/servers', [ServerController::class, 'index'])->name('servers.index');
    Route::get('/servers/console', [ServerController::class, 'console'])->name('servers.console');
    Route::get('/servers/files', [ServerController::class, 'files'])->name('servers.files');
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::post('/admin/servers', [AdminController::class, 'storeServer'])->name('admin.servers.store');
    Route::put('/admin/servers/{server}', [AdminController::class, 'updateServer'])->name('admin.servers.update');
    Route::delete('/admin/servers/{server}', [AdminController::class, 'destroyServer'])->name('admin.servers.destroy');
    Route::post('/admin/permissions', [AdminController::class, 'storePermission'])->name('admin.permissions.store');
    Route::put('/admin/permissions/{permission}', [AdminController::class, 'updatePermission'])->name('admin.permissions.update');
    Route::delete('/admin/permissions/{permission}', [AdminController::class, 'destroyPermission'])->name('admin.permissions.destroy');
    Route::delete('/admin/users/{user}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');
});