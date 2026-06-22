@extends('layouts.app')
@section('title', 'Serveurs')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-bold text-white">Serveurs</h1>
        <p class="text-slate-400 mt-1">{{ count($servers) }} serveur(s) configuré(s)</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @forelse($servers as $sv)
                @php $online = $sv->id % 2 === 0; @endphp
                <div class="group relative glass-card rounded-xl overflow-hidden fade-in cursor-pointer" onclick="window.location='{{ route('servers.console', ['id' => $sv->id]) }}'" style="animation-delay: {{ $loop->index * 0.07 }}s">
                    {{-- Bande de couleur en haut, ça rend les cards plus stylées --}}
                    <div class="h-1.5 w-full" style="background: linear-gradient(90deg, {{ $sv->type_color }}, {{ $sv->type_color }}88)"></div>

                    <div class="p-5">
                        {{-- En-tête avec le type de serveur, son nom, et si c'est allumé --}}
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl shadow-lg" style="background: {{ $sv->type_color }}15; box-shadow: 0 0 20px {{ $sv->type_color }}10">
                                    {{ $sv->type_icon }}
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-white group-hover:text-{{ $online ? 'green' : 'slate' }}-200 transition-colors">{{ $sv->name }}</h3>
                                    <span class="text-xs font-medium px-2 py-0.5 rounded-full" style="background: {{ $sv->type_color }}20; color: {{ $sv->type_color }}">{{ $sv->type_label }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="relative flex w-2.5 h-2.5">
                                    @if($online)
                                        <span class="animate-ping absolute inset-0 w-full h-full rounded-full bg-green-400 opacity-40"></span>
                                    @endif
                                    <span class="relative w-2.5 h-2.5 rounded-full {{ $online ? 'bg-green-400' : 'bg-slate-600' }} inline-block"></span>
                                </span>
                                <span class="text-xs font-medium {{ $online ? 'text-green-400' : 'text-slate-500' }}">{{ $online ? 'En ligne' : 'Hors ligne' }}</span>
                            </div>
                        </div>

                        {{-- RAM, port, joueurs en petits blocs --}}
                        <div class="grid grid-cols-3 gap-2 mb-4">
                            <div class="flex items-center gap-2 p-2.5 rounded-lg bg-slate-800/40 border border-slate-700/30">
                                <svg class="w-4 h-4 text-slate-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9h3m-3 6h3m10-6h3m-3 6h3M4 21h16a1 1 0 001-1V4a1 1 0 00-1-1H4a1 1 0 00-1 1v16a1 1 0 001 1z"/></svg>
                                <div>
                                    <p class="text-[10px] text-slate-600 uppercase tracking-wider leading-tight">RAM</p>
                                    <p class="text-sm font-bold text-white leading-tight">{{ $sv->ram_display }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 p-2.5 rounded-lg bg-slate-800/40 border border-slate-700/30">
                                <svg class="w-4 h-4 text-slate-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                <div>
                                    <p class="text-[10px] text-slate-600 uppercase tracking-wider leading-tight">Port</p>
                                    <p class="text-sm font-bold text-white leading-tight">{{ $sv->port }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 p-2.5 rounded-lg bg-slate-800/40 border border-slate-700/30">
                                <svg class="w-4 h-4 text-slate-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
                                <div>
                                    <p class="text-[10px] text-slate-600 uppercase tracking-wider leading-tight">Joueurs</p>
                                    <p class="text-sm font-bold {{ $online ? 'text-green-400' : 'text-slate-500' }} leading-tight">{{ $online ? rand(0, $sv->max_players) : '--' }}/{{ $sv->max_players }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Boutons pour agir sur le serveur --}}
                        <div class="flex items-center gap-2">
                            @if(!$online)
                                <button disabled onclick="event.stopPropagation()" class="flex-1 flex items-center justify-center gap-1.5 py-2.5 rounded-lg text-sm font-medium bg-slate-800/30 text-slate-600 cursor-not-allowed border border-slate-700/20">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/></svg>
                                    Démarrer
                                </button>
                            @endif
                            <a href="{{ route('servers.console', ['id' => $sv->id]) }}"
                               class="flex-1 flex items-center justify-center gap-1.5 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 bg-green-500/10 text-green-400 border border-green-500/20 hover:bg-green-500/20 hover:border-green-500/40 hover:shadow-lg hover:shadow-green-500/10">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Console
                            </a>
                            <a href="{{ route('servers.files', ['id' => $sv->id]) }}"
                               class="flex-1 flex items-center justify-center gap-1.5 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 bg-blue-500/10 text-blue-400 border border-blue-500/20 hover:bg-blue-500/20 hover:border-blue-500/40 hover:shadow-lg hover:shadow-blue-500/10">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                                Fichiers
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-20">
                    <div class="text-7xl mb-5 opacity-50">📡</div>
                    <h3 class="text-xl font-semibold text-slate-300 mb-2">Aucun serveur</h3>
                    <p class="text-slate-500">Créez d'abord un serveur dans l'administration.</p>
                </div>
            @endforelse
    </div>
@endsection