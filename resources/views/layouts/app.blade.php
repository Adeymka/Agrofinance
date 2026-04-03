<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#1B5E20">
    <title>@yield('title', 'AgroFinance+')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @auth
        <meta name="api-token" content="{{ session('api_token') }}">
        <meta name="user-id" content="{{ auth()->user()->id }}">
    @endauth
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen bg-agro-fond text-gray-900 antialiased pb-24 md:pb-8 max-w-[375px] md:max-w-lg mx-auto shadow-xl min-h-[100dvh]">
    <div id="offlineBanner" class="hidden bg-amber-100 border-b border-amber-300 text-amber-900 text-center text-sm py-2 px-4">
        <span class="inline-flex items-center justify-center gap-2">
            <x-icon name="wifi-off" class="w-4 h-4 shrink-0" />
            Mode hors ligne — des transactions peuvent être en attente de synchronisation.
        </span>
    </div>

    <header class="bg-white border-b border-gray-100 shadow-sm sticky top-0 z-40">
        <div class="px-4 py-3 flex items-center justify-center gap-2">
            <img src="{{ asset('images/logo-agrofinanceplus.png') }}" alt="Logo AgroFinance+" class="w-7 h-7 rounded-md object-contain" />
            <span class="text-agro-vert font-bold text-lg tracking-tight">AgroFinance+</span>
        </div>
    </header>

    <main class="px-4 py-4 min-h-[60vh]">
        @if (session('success'))
            <div class="bg-green-50 border border-green-300 rounded-xl mb-4 p-3 text-sm text-green-700">
                <span class="inline-flex items-start gap-2"><x-icon name="check-circle" class="w-4 h-4 shrink-0 mt-0.5 text-green-700" /> {{ session('success') }}</span>
            </div>
        @endif
        @if (session('alerte'))
            <div class="bg-amber-50 border border-amber-300 rounded-xl mb-4 p-3 text-sm text-amber-700">
                {{ session('alerte') }}
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-50 border border-red-300 rounded-xl mb-4 p-3 text-sm text-red-600">
                {{ session('error') }}
            </div>
        @endif
        @if (session('info'))
            <div class="bg-amber-50 border border-amber-200 rounded-xl mb-4 p-3 text-sm text-amber-800">
                {{ session('info') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="bg-red-50 border border-red-300 rounded-xl mb-4 p-3 text-sm text-red-600">
                {{ $errors->first() }}
            </div>
        @endif

        @yield('content')
    </main>

    @php($nav = $nav ?? 'dashboard')
    <nav class="fixed bottom-0 inset-x-0 bg-white border-t border-gray-200 z-50 safe-area-pb max-w-[375px] md:max-w-lg mx-auto">
        <div class="grid grid-cols-5 gap-0 text-center text-[10px] sm:text-xs">
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center py-2 {{ $nav === 'dashboard' ? 'text-agro-vert font-semibold' : 'text-gray-500' }}">
                <x-icon name="home" class="w-6 h-6 mb-0.5" />
                Accueil
            </a>
            <a href="{{ route('activites.index', array_filter(['exploitation_id' => $exploitationNavId ?? null])) }}" class="flex flex-col items-center py-2 {{ $nav === 'activites' ? 'text-agro-vert font-semibold' : 'text-gray-500' }}">
                <x-icon name="leaf" class="w-6 h-6 mb-0.5" />
                Activités
            </a>
            <a href="{{ route('transactions.create') }}" class="flex flex-col items-center py-1 -mt-2">
                <span class="bg-agro-vert text-white w-12 h-12 rounded-full flex items-center justify-center shadow-md">
                    <x-icon name="plus" class="w-7 h-7" />
                </span>
                <span class="text-[9px] text-gray-500 mt-0.5">Saisie</span>
            </a>
            <a href="{{ route('rapports.index') }}" class="flex flex-col items-center py-2 {{ $nav === 'rapports' ? 'text-agro-vert font-semibold' : 'text-gray-500' }}">
                <x-icon name="chart-bar" class="w-6 h-6 mb-0.5" />
                Rapports
            </a>
            <a href="{{ route('profil') }}" class="flex flex-col items-center py-2 {{ $nav === 'profil' ? 'text-agro-vert font-semibold' : 'text-gray-500' }}">
                <x-icon name="user-circle" class="w-6 h-6 mb-0.5" />
                Profil
            </a>
        </div>
    </nav>

    <script>
        (function () {
            var b = document.getElementById('offlineBanner');
            function sync() { if (b) b.classList.toggle('hidden', navigator.onLine); }
            window.addEventListener('online', sync);
            window.addEventListener('offline', sync);
            sync();
        })();
    </script>
    <script>
        (function () {
            var meta = document.querySelector('meta[name="api-token"]');
            var token = meta && meta.getAttribute('content');
            if (token) localStorage.setItem('agrofinance_token', token);
            else localStorage.removeItem('agrofinance_token');
        })();
    </script>
    @stack('scripts')
</body>
</html>
