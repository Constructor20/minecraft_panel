<?php
$path = "/var/www/html/resources/views/admin/index.blade.php";
$view = file_get_contents($path);

// Find exact boundaries of the old permissions section
$start = strpos($view, "{{-- === PERMISSIONS === --}}");
$end = strpos($view, "{{-- SERVER MODAL (Create)", $start);

// The old permissions section
$oldLen = $end - $start;

// New permissions section with tabs
$newSection = <<<'BLADE'
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
    </div>
BLADE;

// Replace using exact positions
$view = substr_replace($view, $newSection, $start, $oldLen);

// Also add the tab switching JS (add before the existing editPermission function)
$tabJS = "
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

";
$view = str_replace("function editPermission(", $tabJS . "function editPermission(", $view);

file_put_contents($path, $view);
echo "OK - permissions section replaced with tabs\n";
