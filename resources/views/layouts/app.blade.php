<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Minecraft Panel') — Minecraft Panel</title>
    <script src="https://cdn.tailwindcss.com">function toggleProfileMenu() { const m=document.getElementById('profileMenu'); const c=document.getElementById('profileChevron'); m.classList.toggle('hidden'); c.classList.toggle('rotate-180'); } document.addEventListener('click',function(e){ const p=document.getElementById('profileMenu'); if(p&&!p.parentElement.contains(e.target)) p.classList.add('hidden'); document.getElementById('profileChevron')?.classList.remove('rotate-180'); });</script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #1e293b; }
        ::-webkit-scrollbar-thumb { background: #475569; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #64748b; }

        .terminal-scroll::-webkit-scrollbar { width: 8px; }
        .terminal-scroll::-webkit-scrollbar-track { background: #0f172a; }
        .terminal-scroll::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }

        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 5px rgba(74, 222, 128, 0.3); }
            50% { box-shadow: 0 0 20px rgba(74, 222, 128, 0.6); }
        }
        .status-online { animation: pulse-glow 2s infinite; }

        @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0; } }
        .cursor-blink::after { content: '▊'; animation: blink 1s infinite; color: #4ade80; }

        @keyframes slide-in { from { transform: translateX(-20px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        .slide-in { animation: slide-in 0.3s ease-out; }

        @keyframes fade-in { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .fade-in { animation: fade-in 0.4s ease-out; }

        .sidebar-link { transition: all 0.2s ease; position: relative; }
        .sidebar-link::before { content: ''; position: absolute; left: 0; top: 50%; transform: translateY(-50%); width: 3px; height: 0; background: #4ade80; border-radius: 0 3px 3px 0; transition: height 0.2s ease; }
        .sidebar-link:hover::before, .sidebar-link.active::before { height: 60%; }
        .sidebar-link.active { background: rgba(74, 222, 128, 0.1); color: #4ade80; }

        .glass { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(74, 222, 128, 0.1); }
        .glass-card { background: rgba(30, 41, 59, 0.5); backdrop-filter: blur(8px); border: 1px solid rgba(51, 65, 85, 0.5); transition: all 0.3s ease; }
        .glass-card:hover { border-color: rgba(74, 222, 128, 0.3); transform: translateY(-2px); box-shadow: 0 8px 30px rgba(0,0,0,0.3); }

        .btn { transition: all 0.2s ease; font-weight: 500; }
        .btn:active { transform: scale(0.97); }
        .btn-primary { background: linear-gradient(135deg, #4ade80, #22c55e); color: #0f172a; }
        .btn-primary:hover { box-shadow: 0 4px 20px rgba(74, 222, 128, 0.4); }
        .btn-danger { background: linear-gradient(135deg, #ef4444, #dc2626); color: white; }
        .btn-danger:hover { box-shadow: 0 4px 20px rgba(239, 68, 68, 0.4); }
        .btn-ghost { background: rgba(51, 65, 85, 0.3); color: #94a3b8; }
        .btn-ghost:hover { background: rgba(51, 65, 85, 0.6); color: #e2e8f0; }

        input, select, textarea { transition: all 0.2s ease; }
        input:focus, select:focus, textarea:focus { border-color: #4ade80; box-shadow: 0 0 0 3px rgba(74, 222, 128, 0.15); }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .sidebar-overlay { display: none; }
            .sidebar.open ~ .sidebar-overlay { display: block; }
        }
    </style>
</head>
<body class="bg-slate-900 text-slate-200 min-h-screen">
    <div class="flex h-screen">
        {{-- Overlay mobile --}}
        <div id="sidebarOverlay" class="sidebar-overlay fixed inset-0 bg-black/50 z-20 hidden" onclick="toggleSidebar()"></div>

        {{-- SIDEBAR --}}
        <aside id="sidebar" class="sidebar fixed md:relative z-30 w-64 h-full bg-slate-950/90 backdrop-blur-xl border-r border-slate-800/50 flex flex-col transition-transform duration-300">
            <div class="p-5 border-b border-slate-800/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-green-400 to-emerald-600 flex items-center justify-center text-xl font-bold text-white shadow-lg shadow-green-500/20">M</div>
                    <div>
                        <h1 class="text-lg font-bold text-white tracking-tight">Minecraft Panel</h1>
                        <p class="text-xs text-slate-500">Gestion de serveurs</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 p-3 space-y-1 overflow-y-auto">
                @php
                    $navItems = [
                        ['route' => 'dashboard',      'label' => 'Dashboard',       'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>'],
                        ['route' => 'servers.index',   'label' => 'Serveurs',        'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>'],
                        ['route' => 'servers.console',  'label' => 'Console',         'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'],
                        ['route' => 'servers.files',    'label' => 'Fichiers',        'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>'],
                        ['route' => 'admin.index',      'label' => 'Administration',  'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'],
                    ];
                @endphp

                @foreach($navItems as $item)
                    <a href="{{ route($item['route']) }}"
                       class="sidebar-link @if(Route::currentRouteName() === $item['route'] || (Route::currentRouteName() === 'profile' && $item['route'] === 'dashboard')) active @endif
                              flex items-center gap-3 px-4 py-2.5 rounded-lg text-slate-400 hover:text-slate-200 hover:bg-slate-800/40">
                        {!! $item['icon'] !!}
                        <span class="text-sm font-medium">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="p-4 border-t border-slate-800/50 relative">
                <button onclick="toggleProfileMenu()" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg bg-slate-800/30 hover:bg-slate-800/50 transition-colors text-left">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-green-400 to-emerald-600 flex items-center justify-center text-xs font-bold text-white">
                        {{ strtoupper(substr(auth()->user()->username, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-200 truncate">{{ auth()->user()->username }}</p>
                        <p class="text-xs text-slate-500 truncate">{{ auth()->user()->email }}</p>
                    </div>
                    <svg class="w-4 h-4 text-slate-500 transition-transform" id="profileChevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div id="profileMenu" class="hidden absolute bottom-full left-4 right-4 mb-2 bg-slate-800 border border-slate-700/50 rounded-xl overflow-hidden shadow-xl">
                    <div class="px-4 py-3 border-b border-slate-700/50">
                        <p class="text-xs text-slate-500">Connecté en tant que</p>
                        <p class="text-sm font-medium text-white">{{ auth()->user()->username }}</p>
                    </div>
                    <a href="{{ route('profile') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-300 hover:bg-slate-700/50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Mon profil
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-400 hover:bg-red-500/10 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Déconnexion
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- MAIN CONTENT --}}
        <main class="flex-1 flex flex-col min-w-0 bg-slate-900">
            {{-- Top bar mobile --}}
            <div class="md:hidden flex items-center justify-between px-4 py-3 border-b border-slate-800/50 bg-slate-950/50">
                <button onclick="toggleSidebar()" class="text-slate-400 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full bg-green-500/20 flex items-center justify-center text-xs font-bold text-green-400">{{ strtoupper(substr(auth()->user()->username, 0, 1)) }}</div>
                    <span class="text-sm font-medium">{{ auth()->user()->username }}</span>
                </div>
            </div>

            {{-- Page content --}}
            <div class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-8">
                <div class="fade-in">
                    @if(session('success'))
                        <div class="mb-6 p-4 rounded-lg bg-green-500/10 border border-green-500/20 text-green-400 text-sm">{{ session('success') }}</div>
                    @endif
                    @yield('content')
                </div>
            </div>
        </main>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('sidebarOverlay').classList.toggle('hidden');
        }
    function toggleProfileMenu() { const m=document.getElementById('profileMenu'); const c=document.getElementById('profileChevron'); m.classList.toggle('hidden'); c.classList.toggle('rotate-180'); } document.addEventListener('click',function(e){ const p=document.getElementById('profileMenu'); if(p&&!p.parentElement.contains(e.target)) p.classList.add('hidden'); document.getElementById('profileChevron')?.classList.remove('rotate-180'); });</script>
    @stack('scripts')
</body>
</html>