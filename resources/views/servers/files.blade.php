@extends('layouts.app')
@section('title', 'Fichiers')

@section('content')
    @if(isset($server) && $server)
        {{-- File browser for a specific server --}}
        <div class="mb-6">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <a href="{{ route('servers.files') }}" class="text-sm text-slate-500 hover:text-amber-400 transition-colors">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            Tous les serveurs
                        </a>
                        <span class="text-slate-600">/</span>
                        <span class="text-sm text-amber-400 font-medium">{{ $server->name }}</span>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-bold text-white">Gestionnaire de fichiers</h1>
                    <p class="text-slate-400 mt-1">{{ $server->name }} — Parcourez et modifiez les fichiers</p>
                </div>
                <div class="flex items-center gap-3">
                    @if(isset($servers))
                    <select onchange="if(this.value) window.location=this.value" class="bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2 text-sm text-slate-200 focus:outline-none focus:border-amber-400/50">
                        @foreach($servers as $s)
                            <option value="{{ route('servers.files', ['id' => $s->id]) }}" {{ $server->id === $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                    @endif
                </div>
            </div>
        </div>

        <div class="mb-4 flex items-center gap-2 text-sm text-slate-400 flex-wrap">
            <a href="{{ route('servers.files') }}" class="hover:text-amber-400 transition-colors">📁 racine</a>
            <span class="text-slate-600">/</span>
            <span class="text-slate-300">📁 {{ $server->name }}</span>
            <span class="text-slate-600">/</span>
            <span class="text-slate-300">📄 . . .</span>
        </div>

        <div class="glass rounded-xl overflow-hidden">
            <div class="hidden md:grid grid-cols-12 gap-4 px-5 py-3 border-b border-slate-800/50 text-xs font-medium text-slate-500 uppercase tracking-wider">
                <div class="col-span-6">Nom</div>
                <div class="col-span-2">Type</div>
                <div class="col-span-2">Taille</div>
                <div class="col-span-2">Modifié</div>
            </div>

            @php
                $files = [
                    ['name' => 'logs',       'type' => 'Dossier',   'size' => '—',     'date' => '2025-06-19 14:32', 'icon' => '📁', 'is_dir' => true],
                    ['name' => 'plugins',    'type' => 'Dossier',   'size' => '—',     'date' => '2025-06-18 09:15', 'icon' => '📁', 'is_dir' => true],
                    ['name' => 'world',      'type' => 'Dossier',   'size' => '—',     'date' => '2025-06-20 03:00', 'icon' => '📁', 'is_dir' => true],
                    ['name' => 'world_nether','type' => 'Dossier',  'size' => '—',     'date' => '2025-06-20 03:00', 'icon' => '📁', 'is_dir' => true],
                    ['name' => 'world_the_end','type' => 'Dossier', 'size' => '—',     'date' => '2025-06-20 03:00', 'icon' => '📁', 'is_dir' => true],
                    ['name' => 'server.properties','type' => 'Fichier config','size' => '3.2 KB', 'date' => '2025-06-15 18:22', 'icon' => '⚙️', 'is_dir' => false],
                    ['name' => 'server.jar',  'type' => 'Archive Java','size' => '48.2 MB', 'date' => '2025-06-10 12:00', 'icon' => '📦', 'is_dir' => false],
                    ['name' => 'banned-ips.json','type' => 'Fichier JSON','size' => '156 B', 'date' => '2025-06-14 20:10', 'icon' => '📄', 'is_dir' => false],
                    ['name' => 'banned-players.json','type' => 'Fichier JSON','size' => '234 B', 'date' => '2025-06-14 20:10', 'icon' => '📄', 'is_dir' => false],
                    ['name' => 'ops.json',    'type' => 'Fichier JSON','size' => '89 B', 'date' => '2025-06-12 15:30', 'icon' => '📄', 'is_dir' => false],
                    ['name' => 'whitelist.json','type' => 'Fichier JSON','size' => '67 B','date' => '2025-06-12 15:30', 'icon' => '📄', 'is_dir' => false],
                    ['name' => 'start.bat',   'type' => 'Script batch','size' => '245 B','date' => '2025-06-08 10:00', 'icon' => '📜', 'is_dir' => false],
                    ['name' => 'eula.txt',    'type' => 'Fichier texte','size' => '48 B', 'date' => '2025-06-08 10:00', 'icon' => '📝', 'is_dir' => false],
                ];
            @endphp

            @foreach($files as $file)
            <div class="grid grid-cols-12 gap-4 items-center px-5 py-3 border-b border-slate-800/30 hover:bg-slate-800/30 transition-colors">
                <div class="col-span-12 md:col-span-6 flex items-center gap-3">
                    <span class="text-lg">{{ $file['icon'] }}</span>
                    <div>
                        <span class="text-sm font-medium {{ $file['is_dir'] ? 'text-slate-200' : 'text-slate-300' }}">{{ $file['name'] }}</span>
                        <span class="md:hidden text-xs text-slate-600 ml-2">{{ $file['type'] }} · {{ $file['size'] }}</span>
                    </div>
                </div>
                <div class="hidden md:block col-span-2 text-sm text-slate-500">{{ $file['type'] }}</div>
                <div class="hidden md:block col-span-2 text-sm text-slate-500">{{ $file['size'] }}</div>
                <div class="hidden md:block col-span-2 text-sm text-slate-500">{{ $file['date'] }}</div>
            </div>
            @endforeach
        </div>

        <div class="mt-4 flex items-center justify-between text-xs text-slate-600">
            <span><span class="font-medium text-slate-500">13 éléments</span> — {{ $server->path }}</span>
            <div class="flex items-center gap-3">
                <button class="flex items-center gap-1.5 text-slate-500 hover:text-amber-400 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nouveau dossier
                </button>
                <button class="flex items-center gap-1.5 text-slate-500 hover:text-amber-400 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Upload
                </button>
            </div>
        </div>

    @else
        {{-- Overview: cards --}}
        <div class="mb-8">
            <h1 class="text-2xl md:text-3xl font-bold text-white">Fichiers</h1>
            <p class="text-slate-400 mt-1">Sélectionnez un serveur pour explorer ses fichiers</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">            @forelse($servers as $sv)
                @php $online = $sv->id % 2 === 0; @endphp
                <a href="{{ route('servers.files', ['id' => $sv->id]) }}" class="group block relative overflow-hidden rounded-xl fade-in" style="animation-delay: {{ $loop->index * 0.07 }}s">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-900/10 to-slate-900/80 rounded-xl border border-amber-500/10 group-hover:border-amber-500/30 transition-all duration-300"></div>
                    <div class="absolute inset-0 rounded-xl bg-slate-900/60 backdrop-blur-sm"></div>
                    <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-amber-400/0 via-amber-400/60 to-amber-400/0 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

                    <div class="relative p-5">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg bg-amber-500/10 border border-amber-500/20">
                                <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-base font-semibold text-white truncate">{{ $sv->name }}</h3>
                                <span class="text-xs text-amber-400/70">{{ $sv->type_label }}</span>
                            </div>
                            <div class="flex items-center gap-1.5 flex-shrink-0">
                                <span class="relative flex w-2 h-2">
                                    @if($online)
                                        <span class="animate-ping absolute inset-0 w-full h-full rounded-full bg-green-400 opacity-40"></span>
                                    @endif
                                    <span class="relative w-2 h-2 rounded-full {{ $online ? 'bg-green-400' : 'bg-slate-600' }} inline-block"></span>
                                </span>
                                <span class="text-xs {{ $online ? 'text-green-400' : 'text-slate-500' }}">{{ $online ? 'ON' : 'OFF' }}</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 text-xs text-slate-500 mb-4">
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9h3m-3 6h3m10-6h3m-3 6h3M4 21h16a1 1 0 001-1V4a1 1 0 00-1-1H4a1 1 0 00-1 1v16a1 1 0 001 1z"/></svg>
                                {{ $sv->ram_display }}
                            </span>
                            <span class="text-slate-700">|</span>
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                :{{ $sv->port }}
                            </span>
                            <span class="text-slate-700">|</span>
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                {{ $sv->path }}
                            </span>
                        </div>

                        <div class="flex items-center gap-2 pt-3 border-t border-amber-500/10">
                            <div class="flex items-center gap-1.5 text-amber-400 text-sm font-medium group-hover:gap-2 transition-all">
                                <span>Explorer les fichiers</span>
                                <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </div>
                            <span class="text-xs text-slate-600 ml-auto">📁 /{{ $sv->name }}/</span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full text-center py-20">
                    <div class="text-7xl mb-5 opacity-50">📡</div>
                    <h3 class="text-xl font-semibold text-slate-300 mb-2">Aucun serveur</h3>
                    <p class="text-slate-500">Créez d'abord un serveur dans l'administration.</p>
                </div>
            @endforelse        </div>
    @endif
@endsection