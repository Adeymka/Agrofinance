<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0D1F0D">
    <meta name="description" content="@yield('meta-description', 'AgroFinance+ — Gérez vos exploitations agricoles facilement.')">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="AgroFinance+">
    <title>@yield('title', 'AgroFinance+')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @auth
        <meta name="api-token" content="{{ session('api_token') }}">
        <meta name="user-id" content="{{ auth()->user()->id }}">
    @endauth
    <style>
    /* ── Base dark shell ── */
    .mobile-shell {
        background: linear-gradient(180deg, #0A1500 0%, #0D1F0D 55%, #0A1A0A 100%);
        min-height: 100dvh;
        color: rgba(255,255,255,0.88);
    }

    /* Grain subtil pour éviter l'effet "flat" */
    .mobile-shell::before {
        content: '';
        position: fixed;
        inset: 0;
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.04'/%3E%3C/svg%3E");
        pointer-events: none;
        z-index: 0;
        opacity: 0.35;
    }

    /* ── Glass Header ── */
    .mobile-header {
        position: sticky;
        top: 0;
        z-index: 40;
        background: rgba(10,21,10,0.82);
        backdrop-filter: blur(24px) saturate(180%);
        -webkit-backdrop-filter: blur(24px) saturate(180%);
        border-bottom: 1px solid rgba(74,222,128,0.12);
        padding: env(safe-area-inset-top, 0) 0 0;
    }

    .mobile-header-inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        gap: 12px;
    }

    /* Avatar initiales */
    .mobile-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, rgba(74,222,128,0.22), rgba(22,163,74,0.40));
        border: 1.5px solid rgba(74,222,128,0.30);
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Space Grotesk', sans-serif;
        font-size: 13px;
        font-weight: 700;
        color: #4ade80;
        text-decoration: none;
        flex-shrink: 0;
        cursor: pointer;
    }

    /* Logo centré */
    .mobile-logo {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 18px;
        font-weight: 700;
        color: rgba(255,255,255,0.95);
        letter-spacing: -0.025em;
        text-decoration: none;
        flex: 1;
        text-align: center;
    }
    .mobile-logo span { color: #4ade80; }

    /* Cloche alertes */
    .mobile-notif-btn {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: rgba(255,255,255,0.06);
        border: 1px solid rgba(255,255,255,0.10);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        text-decoration: none;
        color: rgba(255,255,255,0.55);
        flex-shrink: 0;
        transition: background 0.2s;
    }
    .mobile-notif-btn:hover { background: rgba(255,255,255,0.11); }

    /* ── Bannière hors ligne ── */
    .mobile-offline-banner {
        background: rgba(251,191,36,0.10);
        border-bottom: 1px solid rgba(251,191,36,0.25);
        color: #fcd34d;
        text-align: center;
        font-size: 12px;
        padding: 8px 16px;
        display: none;
        align-items: center;
        justify-content: center;
        gap: 6px;
        font-family: 'Inter', sans-serif;
        font-weight: 500;
    }
    .mobile-offline-banner.visible { display: flex; }

    /* ── Alertes flash (dark) ── */
    .flash-dark {
        border-radius: 14px;
        padding: 12px 14px;
        margin-bottom: 12px;
        font-family: 'Inter', sans-serif;
        font-size: 13px;
        font-weight: 500;
        line-height: 1.50;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
    }
    .flash-success {
        background: rgba(74,222,128,0.09);
        border: 1px solid rgba(74,222,128,0.22);
        color: #86efac;
    }
    .flash-warning {
        background: rgba(251,191,36,0.09);
        border: 1px solid rgba(251,191,36,0.22);
        color: #fcd34d;
    }
    .flash-error {
        background: rgba(239,68,68,0.09);
        border: 1px solid rgba(239,68,68,0.22);
        color: #fca5a5;
    }
    .flash-info {
        background: rgba(96,165,250,0.09);
        border: 1px solid rgba(96,165,250,0.22);
        color: #93c5fd;
    }

    /* ── Bottom Navigation Dock ── */
    .mobile-dock {
        position: fixed;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 100%;
        max-width: 448px;  /* md:max-w-lg */
        z-index: 50;
        background: rgba(8,17,8,0.90);
        backdrop-filter: blur(28px) saturate(200%);
        -webkit-backdrop-filter: blur(28px) saturate(200%);
        border-top: 1px solid rgba(74,222,128,0.11);
        padding-bottom: env(safe-area-inset-bottom, 0);
    }

    .mobile-dock-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        text-align: center;
    }

    /* Tab items */
    .dock-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-end;
        padding: 10px 0 8px;
        text-decoration: none;
        color: rgba(255,255,255,0.28);
        font-family: 'Inter', sans-serif;
        font-size: 10px;
        font-weight: 500;
        letter-spacing: 0.01em;
        transition: color 0.15s;
        min-height: 56px;
        gap: 3px;
    }
    .dock-item.active {
        color: #4ade80;
        font-weight: 600;
    }
    .dock-item svg { transition: transform 0.15s; }
    .dock-item.active svg { transform: scale(1.08); }

    /* Bouton FAB central (saisie) */
    .dock-fab {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding-bottom: 8px;
        text-decoration: none;
        font-family: 'Inter', sans-serif;
        font-size: 10px;
        font-weight: 500;
        color: rgba(255,255,255,0.38);
        gap: 3px;
        padding-top: 2px;
    }
    .dock-fab-circle {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        box-shadow: 0 4px 20px rgba(34,197,94,0.45), 0 0 0 1px rgba(74,222,128,0.25);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: -14px;
        transition: transform 0.15s, box-shadow 0.15s;
    }
    .dock-fab:hover .dock-fab-circle,
    .dock-fab:active .dock-fab-circle {
        transform: scale(1.08);
        box-shadow: 0 6px 28px rgba(34,197,94,0.60), 0 0 0 1px rgba(74,222,128,0.35);
    }

    /* ── Contenu principal ── */
    .mobile-main {
        position: relative;
        z-index: 1;
        padding: 16px 16px 96px;
        min-height: 60vh;
    }
    </style>
    @stack('styles')
    @stack('head')
</head>

@php
    $initiales = '';
    $userLabel  = '';
    if (auth()->check()) {
        $u = auth()->user();
        $initiales = mb_strtoupper(
            mb_substr($u->prenom ?? '', 0, 1) . mb_substr($u->nom ?? '', 0, 1)
        );
        $userLabel = trim(($u->prenom ?? '') . ' ' . mb_strtoupper(mb_substr($u->nom ?? '', 0, 1) . '.'));
    }
    // Détection automatique de l'onglet actif depuis le nom de route
    $currentRoute = request()->route()?->getName() ?? '';
    $nav = match(true) {
        str_starts_with($currentRoute, 'dashboard')    => 'dashboard',
        str_starts_with($currentRoute, 'activites')    => 'activites',
        str_starts_with($currentRoute, 'transactions') => 'activites',
        str_starts_with($currentRoute, 'rapports')     => 'rapports',
        str_starts_with($currentRoute, 'profil')       => 'profil',
        str_starts_with($currentRoute, 'abonnement')   => 'profil',
        default                                        => $nav ?? 'dashboard',
    };
@endphp

<body class="mobile-shell font-ui antialiased max-w-[375px] md:max-w-lg mx-auto shadow-2xl">

    {{-- ── Bannière hors ligne ── --}}
    <div id="offlineBanner" class="mobile-offline-banner">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636a9 9 0 010 12.728M5.636 5.636a9 9 0 000 12.728M9 9s1.5 1-1 3m6-3s-1.5 1 1 3M12 17h.01"/>
        </svg>
        <span>Mode hors ligne — synchronisation en attente</span>
    </div>

    {{-- ── Header Glass ── --}}
    <header class="mobile-header">
        <div class="mobile-header-inner">

            {{-- Avatar / initiales → profil --}}
            @auth
                <a href="{{ route('profil') }}" class="mobile-avatar" title="{{ $userLabel }}">
                    {{ $initiales ?: '?' }}
                </a>
            @else
                <a href="{{ route('connexion') }}" class="mobile-avatar" title="Connexion">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </a>
            @endauth

            {{-- Logo centré --}}
            <span class="mobile-logo">AgroFinance<span>+</span></span>

            {{-- Cloche → abonnement / statut compte --}}
            @auth
                <a href="{{ route('abonnement') }}" class="mobile-notif-btn" title="Mon abonnement">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </a>
            @else
                <div style="width:36px;"></div>
            @endauth

        </div>
    </header>

    {{-- ── Contenu principal ── --}}
    <main class="mobile-main">

        {{-- Flash success --}}
        @if (session('success'))
            <div class="flash-dark flash-success">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        {{-- Flash alerte / warning --}}
        @if (session('alerte'))
            <div class="flash-dark flash-warning">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                <span>{{ session('alerte') }}</span>
            </div>
        @endif

        {{-- Flash error --}}
        @if (session('error'))
            <div class="flash-dark flash-error">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- Flash info --}}
        @if (session('info'))
            <div class="flash-dark flash-info">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('info') }}</span>
            </div>
        @endif

        {{-- Erreurs de validation --}}
        @if ($errors->any())
            <div class="flash-dark flash-error">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    {{-- ── Bottom Navigation Dock ── --}}
    @auth
    <nav class="mobile-dock" role="navigation" aria-label="Navigation principale">
        <div class="mobile-dock-grid">

            <a href="{{ route('dashboard') }}"
               class="dock-item {{ $nav === 'dashboard' ? 'active' : '' }}"
               aria-label="Accueil">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="{{ $nav === 'dashboard' ? '2.2' : '1.8' }}">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Accueil
            </a>

            <a href="{{ route('activites.index') }}"
               class="dock-item {{ $nav === 'activites' ? 'active' : '' }}"
               aria-label="Activités">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="{{ $nav === 'activites' ? '2.2' : '1.8' }}">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                </svg>
                Activités
            </a>

            {{-- FAB Saisie (bouton central surélevé) --}}
            <a href="{{ route('transactions.create') }}" class="dock-fab" aria-label="Saisir une transaction">
                <span class="dock-fab-circle">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                </span>
                <span style="margin-top:2px;">Saisie</span>
            </a>

            <a href="{{ route('rapports.index') }}"
               class="dock-item {{ $nav === 'rapports' ? 'active' : '' }}"
               aria-label="Rapports">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="{{ $nav === 'rapports' ? '2.2' : '1.8' }}">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Rapports
            </a>

            <a href="{{ route('profil') }}"
               class="dock-item {{ $nav === 'profil' ? 'active' : '' }}"
               aria-label="Profil">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="{{ $nav === 'profil' ? '2.2' : '1.8' }}">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Profil
            </a>

        </div>
    </nav>
    @endauth

    <script>
    (function () {
        var b = document.getElementById('offlineBanner');
        function sync() {
            if (!b) return;
            b.classList.toggle('visible', !navigator.onLine);
        }
        window.addEventListener('online',  sync);
        window.addEventListener('offline', sync);
        sync();
    })();
    </script>

    @stack('scripts')
    @stack('modals')
    <x-pwa-install-prompt />
    <script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('{{ asset('sw.js') }}').catch(function () {});
        });
    }
    </script>
</body>
</html>
