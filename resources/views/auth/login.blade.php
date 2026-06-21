<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — Minecraft Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-20px); } }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .float { animation: float 6s ease-in-out infinite; }
        .float-delay { animation: float 6s ease-in-out 3s infinite; }
        .fade-in-auth { animation: fadeIn 0.6s ease-out; }
        .input-field { transition: all 0.2s ease; }
        .input-field:focus { border-color: #4ade80; box-shadow: 0 0 0 3px rgba(74, 222, 128, 0.15); }
    </style>
</head>
<body class="min-h-screen bg-slate-900 flex items-center justify-center p-4 relative overflow-hidden">
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -left-40 w-80 h-80 bg-green-500/5 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -right-40 w-80 h-80 bg-emerald-500/5 rounded-full blur-3xl"></div>
        <div class="absolute top-1/4 right-1/4 w-4 h-4 bg-green-400/20 rounded-full float"></div>
        <div class="absolute top-1/3 left-1/4 w-3 h-3 bg-emerald-400/20 rounded-full float-delay"></div>
        <div class="absolute bottom-1/3 right-1/3 w-5 h-5 bg-green-500/10 rounded-full float" style="animation-delay: 2s;"></div>
    </div>

    <div class="fade-in-auth w-full max-w-md relative z-10">
        <div class="text-center mb-8">
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-green-400 to-emerald-600 flex items-center justify-center text-2xl font-bold text-white shadow-xl shadow-green-500/20">M</div>
            <h1 class="text-2xl font-bold text-white">Minecraft Panel</h1>
            <p class="text-slate-400 mt-1">Connectez-vous pour gérer vos serveurs</p>
        </div>

        <div class="bg-slate-800/50 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-8 shadow-2xl">
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-300 mb-1.5">Adresse email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                           class="input-field w-full bg-slate-900/50 border border-slate-600/50 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none"
                           placeholder="vous@email.com">
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-300 mb-1.5">Mot de passe</label>
                    <input type="password" name="password" id="password" required
                           class="input-field w-full bg-slate-900/50 border border-slate-600/50 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none"
                           placeholder="Votre mot de passe">
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                @error('login')
                    <div class="p-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm flex items-center gap-2">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ $message }}
                    </div>
                @enderror

                <button type="submit" class="btn-primary w-full py-3 rounded-xl text-sm font-semibold hover:shadow-lg hover:shadow-green-500/25 transition-all">
                    Se connecter
                </button>
            </form>
        </div>

        <p class="text-center mt-6 text-sm text-slate-500">
            Pas encore de compte ?
            <a href="{{ route('register') }}" class="text-green-400 hover:text-green-300 font-medium transition-colors">Créer un compte</a>
        </p>
    </div>
</body>
</html>