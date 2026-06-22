@extends('layouts.app')
@section('title', 'Administration')

@section('content')
    @php
        $types = ['vanilla' => 'Vanilla', 'paper' => 'Paper', 'spigot' => 'Spigot', 'purpur' => 'Purpur', 'forge' => 'Forge', 'fabric' => 'Fabric'];
    @endphp

    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-bold text-white">Administration</h1>
        <p class="text-slate-400 mt-1">Gestion des serveurs et permissions</p>
    </div>

    {{-- === SERVERS === --}}
    <div class="glass rounded-xl overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-slate-800/50 flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-green-500/10 flex items-center justify-center">
                    <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
                </div>
                <h2 class="text-lg font-semibold text-white">Serveurs ({{ count($servers) }})</h2>
            </div>
            <button onclick="document.getElementById('serverModal').classList.remove('hidden')" class="btn btn-primary px-4 py-2 rounded-lg text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Ajouter
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs text-slate-500 uppercase tracking-wider border-b border-slate-800/30">
                        <th class="text-left px-5 py-3 font-medium">Nom</th>
                        <th class="text-left px-5 py-3 font-medium">Type</th>
                        <th class="text-left px-5 py-3 font-medium">RAM</th>
                        <th class="text-left px-5 py-3 font-medium">Port</th>
                        <th class="text-left px-5 py-3 font-medium">Joueurs</th>
                        <th class="text-center px-5 py-3 font-medium">Auto</th>
                        <th class="text-right px-5 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($servers as $s)
                    <tr class="border-b border-slate-800/20 hover:bg-slate-800/20 transition-colors">
                        <td class="px-5 py-3 font-medium text-white">{{ $s->name }}</td>
                        <td class="px-5 py-3">
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full" style="background: {{ $s->type_color }}20; color: {{ $s->type_color }}">{{ $s->type_label }}</span>
                        </td>
                        <td class="px-5 py-3 text-slate-400">{{ $s->ram_display }}</td>
                        <td class="px-5 py-3 text-slate-400 font-mono">{{ $s->port }}</td>
                        <td class="px-5 py-3 text-slate-400">{{ $s->max_players }}</td>
                        <td class="px-5 py-3 text-center">
                            @if($s->auto_start)
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-green-500/10 text-green-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </span>
                            @else
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-500/10 text-slate-500">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button onclick="editServer('{{ $s->id }}', '{{ $s->name }}', '{{ $s->type }}', {{ $s->port }}, {{ $s->ram }}, '{{ $s->ram_unit }}', {{ $s->max_players }}, '{{ $s->path }}', '{{ $s->jar_file }}', '{{ $s->java_args }}', '{{ $s->pc_ip }}', {{ $s->auto_start ? 'true' : 'false' }})" class="p-1.5 rounded hover:bg-slate-700/50 text-slate-500 hover:text-amber-400 transition-colors" title="Éditer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <form method="POST" action="{{ route('admin.servers.destroy', $s) }}" onsubmit="return confirm('Supprimer {{ $s->name }} ?')" class="inline">
                                    @csrf @method('DELETE')
                                    <button class="p-1.5 rounded hover:bg-slate-700/50 text-slate-500 hover:text-red-400 transition-colors" title="Supprimer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- === USERS === --}}
    <div class="glass rounded-xl overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-slate-800/50 flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
                </div>
                <h2 class="text-lg font-semibold text-white">Utilisateurs ({{ count($users) }})</h2>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs text-slate-500 uppercase tracking-wider border-b border-slate-800/30">
                        <th class="text-left px-5 py-3 font-medium">ID</th>
                        <th class="text-left px-5 py-3 font-medium">Pseudo</th>
                        <th class="text-left px-5 py-3 font-medium">Email</th>
                        <th class="text-center px-5 py-3 font-medium">Serveurs</th>
                        <th class="text-right px-5 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $u)
                    <tr class="border-b border-slate-800/20 hover:bg-slate-800/20 transition-colors">
                        <td class="px-5 py-3 text-slate-500 font-mono text-xs">{{ $u->id }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-slate-700 flex items-center justify-center text-xs font-bold text-white">{{ strtoupper(substr($u->username ?? '?', 0, 1)) }}</div>
                                <span class="text-white font-medium">{{ $u->username }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-slate-400 text-xs">{{ $u->email }}</td>
                        <td class="px-5 py-3 text-center text-slate-400">{{ $u->permissions_count ?? $u->permissions->count() ?? 0 }}</td>
                        <td class="px-5 py-3 text-right">
                            <form method="POST" action="{{ route('admin.users.destroy', $u) }}" onsubmit="return confirm('Supprimer l\'utilisateur {{ $u->username }} ?')" class="inline">
                                @csrf @method('DELETE')
                                <button class="p-1.5 rounded hover:bg-slate-700/50 text-slate-500 hover:text-red-400 transition-colors" title="Supprimer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- === PERMISSIONS (Tabs per user) === --}}
    <div class="glass rounded-xl overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-slate-800/50">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-purple-500/10 flex items-center justify-center">
                        <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <h2 class="text-lg font-semibold text-white">Permissions</h2>
                </div>
                @if(count($users) > 0 && count($servers) > 0)
                <button onclick="document.getElementById('permModal').classList.remove('hidden')" class="btn btn-primary px-4 py-2 rounded-lg text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nouvelle permission
                </button>
                @endif
            </div>

            {{-- Tabs --}}
            <div class="flex items-center gap-1 mt-4 -mb-5">
                @foreach($users as $u)
                <button onclick="switchPermTab('tab-{{ $u->id }}', this)"
                        class="perm-tab px-4 py-2 text-sm font-medium rounded-t-lg transition-all duration-200
                               {{ $loop->first ? 'bg-slate-800/60 text-white border border-slate-700/30 border-b-transparent' : 'text-slate-500 hover:text-slate-300 hover:bg-slate-800/30' }}"
                        data-tab="tab-{{ $u->id }}">
                    <span class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-slate-700 flex items-center justify-center text-[10px] font-bold text-white">{{ strtoupper(substr($u->username, 0, 1)) }}</span>
                        {{ $u->username }}
                    </span>
                </button>
                @endforeach
            </div>
        </div>

        @foreach($users as $u)
        @php $userPerms = $permissions->where('user_id', $u->id); @endphp
        <div id="tab-{{ $u->id }}" class="perm-content {{ $loop->first ? '' : 'hidden' }}">
            @if($userPerms->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-xs text-slate-500 uppercase tracking-wider border-b border-slate-800/30">
                            <th class="text-left px-5 py-3 font-medium">Serveur</th>
                            <th class="text-center px-5 py-3 font-medium">Voir</th>
                            <th class="text-center px-5 py-3 font-medium">Start</th>
                            <th class="text-center px-5 py-3 font-medium">Stop</th>
                            <th class="text-center px-5 py-3 font-medium">Console</th>
                            <th class="text-center px-5 py-3 font-medium">Fichiers</th>
                            <th class="text-right px-5 py-3 font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($userPerms as $p)
                        <tr class="border-b border-slate-800/20 hover:bg-slate-800/20 transition-colors">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">{{ $p->server->type_icon ?? '🖥️' }}</span>
                                    <span class="text-slate-200 font-medium">{{ $p->server->name ?? '?' }}</span>
                                </div>
                            </td>
                            @foreach(['can_view', 'can_start', 'can_stop', 'can_console', 'can_files'] as $perm)
                            <td class="px-5 py-3 text-center">
                                @if($p->$perm)
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-green-500/10 text-green-400">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    </span>
                                @else
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-500/10 text-red-400">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </span>
                                @endif
                            </td>
                            @endforeach
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button onclick="editPermission({{ $p->id }}, {{ $p->can_view ? 'true' : 'false' }}, {{ $p->can_start ? 'true' : 'false' }}, {{ $p->can_stop ? 'true' : 'false' }}, {{ $p->can_console ? 'true' : 'false' }}, {{ $p->can_files ? 'true' : 'false' }})" class="p-1.5 rounded hover:bg-slate-700/50 text-slate-500 hover:text-amber-400 transition-colors" title="Éditer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <form method="POST" action="{{ route('admin.permissions.destroy', $p) }}" onsubmit="return confirm('Supprimer cette permission pour {{ $p->server->name }} ?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button class="p-1.5 rounded hover:bg-slate-700/50 text-slate-500 hover:text-red-400 transition-colors" title="Supprimer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-8 text-slate-500 text-sm">Aucune permission pour cet utilisateur</div>
            @endif
        </div>
        @endforeach
    </div>{{-- SERVER MODAL (Create) --}}
    <div id="serverModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" onclick="if(event.target===this)this.classList.add('hidden')">
        <div class="glass rounded-2xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-white">Nouveau serveur</h3>
                <button onclick="document.getElementById('serverModal').classList.add('hidden')" class="text-slate-500 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.servers.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf
                <div class="md:col-span-2"><label class="block text-sm text-slate-300 mb-1">Nom</label><input name="name" required class="w-full bg-slate-900/50 border border-slate-600/50 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-green-400/50"></div>
                <div><label class="block text-sm text-slate-300 mb-1">Type</label>
                    <select name="type" class="w-full bg-slate-900/50 border border-slate-600/50 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-green-400/50">
                        @foreach($types as $val => $label)<option value="{{ $val }}">{{ $label }}</option>@endforeach
                    </select>
                </div>
                <div><label class="block text-sm text-slate-300 mb-1">Port</label><input name="port" type="number" required class="w-full bg-slate-900/50 border border-slate-600/50 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-green-400/50"></div>
                <div><label class="block text-sm text-slate-300 mb-1">RAM</label><input name="ram" type="number" required class="w-full bg-slate-900/50 border border-slate-600/50 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-green-400/50"></div>
                <div><label class="block text-sm text-slate-300 mb-1">Unité</label>
                    <select name="ram_unit" class="w-full bg-slate-900/50 border border-slate-600/50 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-green-400/50">
                        <option value="G">Go</option><option value="M">Mo</option>
                    </select>
                </div>
                <div><label class="block text-sm text-slate-300 mb-1">Joueurs max</label><input name="max_players" type="number" required class="w-full bg-slate-900/50 border border-slate-600/50 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-green-400/50"></div>
                <div><label class="block text-sm text-slate-300 mb-1">IP du PC</label><input name="pc_ip" class="w-full bg-slate-900/50 border border-slate-600/50 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-green-400/50"></div>
                <div class="md:col-span-2"><label class="block text-sm text-slate-300 mb-1">Chemin</label><input name="path" class="w-full bg-slate-900/50 border border-slate-600/50 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-green-400/50"></div>
                <div class="md:col-span-2"><label class="block text-sm text-slate-300 mb-1">JAR</label><input name="jar_file" class="w-full bg-slate-900/50 border border-slate-600/50 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-green-400/50"></div>
                <div class="md:col-span-2"><label class="block text-sm text-slate-300 mb-1">Arguments Java</label><input name="java_args" class="w-full bg-slate-900/50 border border-slate-600/50 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-green-400/50"></div>
                <div class="md:col-span-2 flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('serverModal').classList.add('hidden')" class="btn btn-ghost px-5 py-2.5 rounded-xl text-sm">Annuler</button>
                    <button type="submit" class="btn btn-primary px-5 py-2.5 rounded-xl text-sm">Créer</button>
                </div>
            </form>
        </div>
    </div>

    {{-- SERVER MODAL (Edit) --}}
    <div id="editServerModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" onclick="if(event.target===this)this.classList.add('hidden')">
        <div class="glass rounded-2xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-white">Modifier le serveur</h3>
                <button onclick="document.getElementById('editServerModal').classList.add('hidden')" class="text-slate-500 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="editServerForm" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf @method('PUT')
                <div class="md:col-span-2"><label class="block text-sm text-slate-300 mb-1">Nom</label><input name="name" id="edit_name" required class="w-full bg-slate-900/50 border border-slate-600/50 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-green-400/50"></div>
                <div><label class="block text-sm text-slate-300 mb-1">Type</label>
                    <select name="type" id="edit_type" class="w-full bg-slate-900/50 border border-slate-600/50 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-green-400/50">
                        @foreach($types as $val => $label)<option value="{{ $val }}">{{ $label }}</option>@endforeach
                    </select>
                </div>
                <div><label class="block text-sm text-slate-300 mb-1">Port</label><input name="port" id="edit_port" type="number" required class="w-full bg-slate-900/50 border border-slate-600/50 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-green-400/50"></div>
                <div><label class="block text-sm text-slate-300 mb-1">RAM</label><input name="ram" id="edit_ram" type="number" required class="w-full bg-slate-900/50 border border-slate-600/50 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-green-400/50"></div>
                <div><label class="block text-sm text-slate-300 mb-1">Unité</label>
                    <select name="ram_unit" id="edit_ram_unit" class="w-full bg-slate-900/50 border border-slate-600/50 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-green-400/50">
                        <option value="G">Go</option><option value="M">Mo</option>
                    </select>
                </div>
                <div><label class="block text-sm text-slate-300 mb-1">Joueurs max</label><input name="max_players" id="edit_max_players" type="number" required class="w-full bg-slate-900/50 border border-slate-600/50 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-green-400/50"></div>
                <div><label class="block text-sm text-slate-300 mb-1">IP du PC</label><input name="pc_ip" id="edit_pc_ip" class="w-full bg-slate-900/50 border border-slate-600/50 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-green-400/50"></div>
                <div class="md:col-span-2"><label class="block text-sm text-slate-300 mb-1">Chemin</label><input name="path" id="edit_path" class="w-full bg-slate-900/50 border border-slate-600/50 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-green-400/50"></div>
                <div class="md:col-span-2"><label class="block text-sm text-slate-300 mb-1">JAR</label><input name="jar_file" id="edit_jar_file" class="w-full bg-slate-900/50 border border-slate-600/50 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-green-400/50"></div>
                <div class="md:col-span-2"><label class="block text-sm text-slate-300 mb-1">Arguments Java</label><input name="java_args" id="edit_java_args" class="w-full bg-slate-900/50 border border-slate-600/50 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-green-400/50"></div>
                <div class="md:col-span-2 flex items-center gap-2">
                    <input type="checkbox" name="auto_start" id="edit_auto_start" value="1" class="rounded bg-slate-900/50 border-slate-600/50 text-green-500 focus:ring-green-500/30">
                    <label for="edit_auto_start" class="text-sm text-slate-300">Démarrage automatique</label>
                </div>
                <div class="md:col-span-2 flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('editServerModal').classList.add('hidden')" class="btn btn-ghost px-5 py-2.5 rounded-xl text-sm">Annuler</button>
                    <button type="submit" class="btn btn-primary px-5 py-2.5 rounded-xl text-sm">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    {{-- PERMISSION MODAL --}}
    <div id="permModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" onclick="if(event.target===this)this.classList.add('hidden')">
        <div class="glass rounded-2xl p-6 w-full max-w-md" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-white">Nouvelle permission</h3>
                <button onclick="document.getElementById('permModal').classList.add('hidden')" class="text-slate-500 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.permissions.store') }}" class="space-y-4">
                @csrf
                <div><label class="block text-sm text-slate-300 mb-1">Utilisateur</label>
                    <select name="user_id" required class="w-full bg-slate-900/50 border border-slate-600/50 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-green-400/50">
                        @foreach($users as $u)<option value="{{ $u->id }}">{{ $u->username }} ({{ $u->email }})</option>@endforeach
                    </select>
                </div>
                <div><label class="block text-sm text-slate-300 mb-1">Serveur</label>
                    <select name="server_id" required class="w-full bg-slate-900/50 border border-slate-600/50 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-green-400/50">
                        @foreach($servers as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    @foreach(['can_view' => 'Voir', 'can_start' => 'Démarrer', 'can_stop' => 'Arrêter', 'can_console' => 'Console', 'can_files' => 'Fichiers'] as $field => $label)
                    <label class="flex items-center gap-2 text-sm text-slate-300">
                        <input type="checkbox" name="{{ $field }}" value="1" checked class="rounded bg-slate-900/50 border-slate-600/50 text-green-500 focus:ring-green-500/30">
                        {{ $label }}
                    </label>
                    @endforeach
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('permModal').classList.add('hidden')" class="btn btn-ghost px-5 py-2.5 rounded-xl text-sm">Annuler</button>
                    <button type="submit" class="btn btn-primary px-5 py-2.5 rounded-xl text-sm">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
    {{-- PERMISSION EDIT MODAL --}}
    <div id="editPermModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" onclick="if(event.target===this)this.classList.add('hidden')">
        <div class="glass rounded-2xl p-6 w-full max-w-md" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-white">Modifier la permission</h3>
                <button onclick="document.getElementById('editPermModal').classList.add('hidden')" class="text-slate-500 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="editPermForm" method="POST" class="space-y-4">
                @csrf @method('PUT')
                <div class="grid grid-cols-2 gap-3">
                    @foreach(['can_view' => 'Voir', 'can_start' => 'Démarrer', 'can_stop' => 'Arrêter', 'can_console' => 'Console', 'can_files' => 'Fichiers'] as $field => $label)
                    <label class="flex items-center gap-2 text-sm text-slate-300">
                        <input type="checkbox" name="{{ $field }}" value="1" id="edit_{{ $field }}" class="rounded bg-slate-900/50 border-slate-600/50 text-green-500 focus:ring-green-500/30">
                        {{ $label }}
                    </label>
                    @endforeach
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('editPermModal').classList.add('hidden')" class="btn btn-ghost px-5 py-2.5 rounded-xl text-sm">Annuler</button>
                    <button type="submit" class="btn btn-primary px-5 py-2.5 rounded-xl text-sm">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function switchPermTab(tabId, btn) {
    document.querySelectorAll('.perm-content').forEach(el => el.classList.add('hidden'));
    document.getElementById(tabId).classList.remove('hidden');
    document.querySelectorAll('.perm-tab').forEach(el => {
        el.classList.remove('bg-slate-800/60', 'text-white', 'border', 'border-slate-700/30', 'border-b-transparent');
        el.classList.add('text-slate-500', 'hover:text-slate-300', 'hover:bg-slate-800/30');
    });
    btn.classList.remove('text-slate-500', 'hover:text-slate-300', 'hover:bg-slate-800/30');
    btn.classList.add('bg-slate-800/60', 'text-white', 'border', 'border-slate-700/30', 'border-b-transparent');
}


function switchPermTab(tabId, btn) {
    document.querySelectorAll('.perm-content').forEach(function(el) { el.classList.add('hidden'); });
    document.getElementById(tabId).classList.remove('hidden');
    document.querySelectorAll('.perm-tab').forEach(function(el) {
        el.classList.remove('bg-slate-800/60', 'text-white', 'border', 'border-slate-700/30', 'border-b-transparent');
        el.classList.add('text-slate-500', 'hover:text-slate-300', 'hover:bg-slate-800/30');
    });
    btn.classList.remove('text-slate-500', 'hover:text-slate-300', 'hover:bg-slate-800/30');
    btn.classList.add('bg-slate-800/60', 'text-white', 'border', 'border-slate-700/30', 'border-b-transparent');
}

function editPermission(id, canView, canStart, canStop, canConsole, canFiles) {
    document.getElementById('editPermForm').action = '/admin/permissions/' + id;
    document.getElementById('edit_can_view').checked = canView;
    document.getElementById('edit_can_start').checked = canStart;
    document.getElementById('edit_can_stop').checked = canStop;
    document.getElementById('edit_can_console').checked = canConsole;
    document.getElementById('edit_can_files').checked = canFiles;
    document.getElementById('editPermModal').classList.remove('hidden');
}

function editServer(id, name, type, port, ram, ramUnit, maxPlayers, path, jarFile, javaArgs, pcIp, autoStart) {
    document.getElementById('editServerForm').action = '/admin/servers/' + id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_type').value = type;
    document.getElementById('edit_port').value = port;
    document.getElementById('edit_ram').value = ram;
    document.getElementById('edit_ram_unit').value = ramUnit;
    document.getElementById('edit_max_players').value = maxPlayers;
    document.getElementById('edit_path').value = path;
    document.getElementById('edit_jar_file').value = jarFile;
    document.getElementById('edit_java_args').value = javaArgs;
    document.getElementById('edit_pc_ip').value = pcIp;
    document.getElementById('edit_auto_start').checked = autoStart;
    document.getElementById('editServerModal').classList.remove('hidden');
}
</script>
@endpush