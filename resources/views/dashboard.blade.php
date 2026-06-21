@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-bold text-white">Dashboard</h1>
        <p class="text-slate-400 mt-1">Bienvenue, <span class="text-green-400">{{ auth()->user()->username }}</span></p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <div class="glass-card rounded-xl p-5 slide-in" style="animation-delay: 0.05s">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-slate-400 text-sm font-medium">Serveurs</p>
                    <p class="text-3xl font-bold text-white mt-1">{{ $serverCount }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-400/20 to-emerald-600/20 flex items-center justify-center border border-green-500/20">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
                </div>
            </div>
            <a href="{{ route('servers.index') }}" class="mt-4 inline-flex items-center gap-1 text-sm text-green-400 hover:text-green-300 transition-colors">
                Voir les serveurs
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <div class="glass-card rounded-xl p-5 slide-in" style="animation-delay: 0.1s">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-slate-400 text-sm font-medium">Console</p>
                    <p class="text-3xl font-bold text-white mt-1">Terminal</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-400/20 to-blue-600/20 flex items-center justify-center border border-blue-500/20">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <a href="{{ route('servers.console') }}" class="mt-4 inline-flex items-center gap-1 text-sm text-blue-400 hover:text-blue-300 transition-colors">
                Accéder à la console
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <div class="glass-card rounded-xl p-5 slide-in" style="animation-delay: 0.15s">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-slate-400 text-sm font-medium">Fichiers</p>
                    <p class="text-3xl font-bold text-white mt-1">Explorateur</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-400/20 to-amber-600/20 flex items-center justify-center border border-amber-500/20">
                    <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                </div>
            </div>
            <a href="{{ route('servers.files') }}" class="mt-4 inline-flex items-center gap-1 text-sm text-amber-400 hover:text-amber-300 transition-colors">
                Gérer les fichiers
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <div class="glass-card rounded-xl p-5 slide-in" style="animation-delay: 0.2s">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-slate-400 text-sm font-medium">Administration</p>
                    <p class="text-3xl font-bold text-white mt-1">Panel</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-400/20 to-purple-600/20 flex items-center justify-center border border-purple-500/20">
                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
            </div>
            <a href="{{ route('admin.index') }}" class="mt-4 inline-flex items-center gap-1 text-sm text-purple-400 hover:text-purple-300 transition-colors">
                Administration
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>

    <div class="glass rounded-xl p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Accès rapides</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <a href="{{ route('servers.console') }}" class="flex items-center gap-4 p-4 rounded-lg bg-slate-800/30 hover:bg-slate-800/50 transition-colors group">
                <div class="w-10 h-10 rounded-lg bg-green-500/10 flex items-center justify-center text-lg group-hover:bg-green-500/20 transition-colors">💻</div>
                <div>
                    <p class="text-sm font-medium text-slate-200 group-hover:text-white transition-colors">Console interactive</p>
                    <p class="text-xs text-slate-500">Exécutez des commandes sur vos serveurs</p>
                </div>
            </a>
            <a href="{{ route('servers.files') }}" class="flex items-center gap-4 p-4 rounded-lg bg-slate-800/30 hover:bg-slate-800/50 transition-colors group">
                <div class="w-10 h-10 rounded-lg bg-green-500/10 flex items-center justify-center text-lg group-hover:bg-green-500/20 transition-colors">📁</div>
                <div>
                    <p class="text-sm font-medium text-slate-200 group-hover:text-white transition-colors">Gestionnaire de fichiers</p>
                    <p class="text-xs text-slate-500">Parcourez et modifiez les fichiers</p>
                </div>
            </a>
        </div>
    </div>
@endsection