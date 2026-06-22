@extends('layouts.app')
@section('title', 'Console')

@section('content')
    @if(isset($server) && $server)
        {{-- Terminal for a specific server --}}
        @php $online = $server->id % 2 === 0; @endphp
        <div class="mb-6">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <a href="{{ route('servers.console') }}" class="text-sm text-slate-500 hover:text-green-400 transition-colors">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            Tous les serveurs
                        </a>
                        <span class="text-slate-600">/</span>
                        <span class="text-sm text-green-400 font-medium">{{ $server->name }}</span>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-bold text-white">Console</h1>
                    <p class="text-slate-400 mt-1">{{ $server->name }} — Entrez des commandes Minecraft</p>
                </div>
                <div class="flex items-center gap-3">
                    @if(isset($servers))
                    <select onchange="if(this.value) window.location=this.value" class="bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2 text-sm text-slate-200 focus:outline-none focus:border-green-400/50">
                        @foreach($servers as $s)
                            <option value="{{ route('servers.console', ['id' => $s->id]) }}" {{ $server->id === $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                    @endif
                    <button class="btn btn-primary px-4 py-2 rounded-lg text-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Démarrer
                    </button>
                    <button class="btn btn-danger px-4 py-2 rounded-lg text-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/></svg>
                        Arrêter
                    </button>
                    <button class="btn btn-ghost px-4 py-2 rounded-lg text-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Redémarrer
                    </button>
                </div>
            </div>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
            <div class="glass-card rounded-lg p-3 text-center">
                <p class="text-xs text-slate-500">Type</p>
                <p class="text-sm font-semibold text-white">{{ $server->type_label }}</p>
            </div>
            <div class="glass-card rounded-lg p-3 text-center">
                <p class="text-xs text-slate-500">RAM</p>
                <p class="text-sm font-semibold text-white">{{ $server->ram_display }}</p>
            </div>
            <div class="glass-card rounded-lg p-3 text-center">
                <p class="text-xs text-slate-500">Port</p>
                <p class="text-sm font-semibold text-white">{{ $server->port }}</p>
            </div>
            <div class="glass-card rounded-lg p-3 text-center">
                <p class="text-xs text-slate-500">Joueurs</p>
                <p class="text-sm font-semibold text-green-400">0/{{ $server->max_players }}</p>
            </div>
            <div class="glass-card rounded-lg p-3 text-center">
                <p class="text-xs text-slate-500">Status</p>
                <p class="text-sm font-semibold {{ $online ? 'text-green-400' : 'text-slate-500' }} flex items-center justify-center gap-1.5">
                    <span class="relative flex w-2 h-2">
                        @if($online)
                            <span class="animate-ping absolute inset-0 w-full h-full rounded-full bg-green-400 opacity-40"></span>
                        @endif
                        <span class="relative w-2 h-2 rounded-full {{ $online ? 'bg-green-400' : 'bg-slate-600' }} inline-block"></span>
                    </span>
                    {{ $online ? 'En ligne' : 'Hors ligne' }}
                </p>
            </div>
        </div>

        {{-- Terminal --}}
        <div class="glass rounded-xl overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3 border-b border-slate-800/50">
                <div class="flex items-center gap-3">
                    <div class="flex gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-red-500/80"></span>
                        <span class="w-3 h-3 rounded-full bg-amber-500/80"></span>
                        <span class="w-3 h-3 rounded-full bg-green-500/80"></span>
                    </div>
                    <span class="text-xs text-slate-500 font-mono">terminal @ {{ $server->name }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="clearTerminal()" class="text-xs text-slate-500 hover:text-slate-300 transition-colors">Effacer</button>
                    <span class="text-xs text-slate-600">|</span>
                    <span class="text-xs text-slate-500">Ctrl+L</span>
                </div>
            </div>

            <div id="terminal" class="h-[400px] md:h-[500px] overflow-y-auto p-5 bg-slate-950 font-mono text-sm leading-relaxed terminal-scroll">
                <div class="text-green-400 font-semibold mb-2">Console {{ $server->name }}</div>
                <div class="text-slate-500" id="terminalOutput"></div>
            </div>

            <div class="px-5 py-3 border-t border-slate-800/50 bg-slate-950/50">
                <form id="commandForm" onsubmit="sendCommand(event)" class="flex items-center gap-3">
                    <span class="text-green-400 font-mono text-sm">$</span>
                    <input type="text" id="commandInput" autocomplete="off"
                           class="flex-1 bg-transparent border-none outline-none text-sm font-mono text-slate-200 placeholder-slate-600"
                           placeholder="Tapez une commande...">
                    <button type="submit" class="btn btn-primary px-4 py-1.5 rounded-lg text-xs">Envoyer</button>
                </form>
            </div>
        </div>

    @else
        {{-- Overview: cards --}}
        <div class="mb-8">
            <h1 class="text-2xl md:text-3xl font-bold text-white">Console</h1>
            <p class="text-slate-400 mt-1">Sélectionnez un serveur pour accéder à sa console</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">            @forelse($servers as $sv)
                @php $online = $sv->id % 2 === 0; @endphp
                <a href="{{ route('servers.console', ['id' => $sv->id]) }}" class="group block relative overflow-hidden rounded-xl fade-in" style="animation-delay: {{ $loop->index * 0.07 }}s">
                    <div class="absolute inset-0 bg-gradient-to-br from-green-900/10 to-slate-900/80 rounded-xl border border-green-500/10 group-hover:border-green-500/30 transition-all duration-300"></div>
                    <div class="absolute inset-0 rounded-xl bg-slate-900/60 backdrop-blur-sm"></div>
                    <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-green-400/0 via-green-400/60 to-green-400/0 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

                    <div class="relative p-5">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg bg-green-500/10 border border-green-500/20">
                                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-base font-semibold text-white truncate">{{ $sv->name }}</h3>
                                <span class="text-xs text-green-400/70 font-mono">{{ $sv->type_label }}</span>
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

                        <div class="flex items-center gap-3 text-xs text-slate-500 font-mono mb-4">
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
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
                                {{ $online ? rand(0, $sv->max_players) : '--' }}/{{ $sv->max_players }}
                            </span>
                        </div>

                        <div class="flex items-center gap-2 pt-3 border-t border-green-500/10">
                            <div class="flex items-center gap-1.5 text-green-400 text-sm font-medium group-hover:gap-2 transition-all">
                                <span>Ouvrir la console</span>
                                <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </div>
                            <span class="text-xs text-slate-600 font-mono ml-auto">$ ./console</span>
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

@push('scripts')
<script>
    const terminal = document.getElementById('terminal');
    const terminalOutput = document.getElementById('terminalOutput');
    const commandInput = document.getElementById('commandInput');

    function sendCommand(e) {
        e.preventDefault();
        const cmd = commandInput.value.trim();
        if (!cmd) return;
        addLine(`<span class="text-green-400 font-semibold">$</span> <span class="text-white">${escapeHtml(cmd)}</span>`);
        addLine(`<span class="text-slate-500">[${new Date().toLocaleTimeString()}] [API] Commande envoyée</span>`);
        commandInput.value = '';
        terminal.scrollTop = terminal.scrollHeight;
    }

    function addLine(html) {
        terminalOutput.insertAdjacentHTML('beforeend', '<div class="mb-0.5">' + html + '</div>');
        terminal.scrollTop = terminal.scrollHeight;
    }

    function clearTerminal() {
        terminalOutput.innerHTML = '';
    }

    function escapeHtml(text) {
        const d = document.createElement('div');
        d.textContent = text;
        return d.innerHTML;
    }

    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'l') { e.preventDefault(); clearTerminal(); }
    });
</script>
@endpush