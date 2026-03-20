<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'AgroFinance+') — AgroFinance+</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @auth
        <meta name="api-token" content="{{ session('api_token') }}">
        <meta name="user-id" content="{{ auth()->user()->id }}">
    @endauth
    @stack('head')
</head>
@php
    $exploitationType = auth()->check()
        ? (auth()->user()->exploitations()->first()?->type ?? 'cultures_vivrieres')
        : 'cultures_vivrieres';
@endphp
<body class="min-h-screen overflow-hidden font-ui">

    <!-- Background layer A -->
    <div id="bgLayerA"
         class="bg-layer fade-in {{ $exploitationType !== 'mixte' ? 'bg-'.$exploitationType : '' }}">
    </div>

    <!-- Background layer B (fondu carrousel mixte) -->
    <div id="bgLayerB" class="bg-layer fade-out">
    </div>

    <!-- Overlay sombre -->
    <div class="fixed inset-0 pointer-events-none z-[-1]"
         style="background: linear-gradient(135deg,
           rgba(5,25,5,0.78) 0%,
           rgba(15,50,15,0.62) 50%,
           rgba(5,20,5,0.72) 100%);">
    </div>

    <div class="flex h-screen" id="appWrapper">

        <aside id="sidebar"
               class="flex flex-col h-full fixed left-0 top-0 z-30
                      transition-all duration-300 ease-in-out glass-sidebar"
               style="width: 260px;">

            <button type="button"
                    id="sidebarToggle"
                    onclick="toggleSidebar()"
                    title="Réduire / Agrandir"
                    class="absolute right-2 top-6 z-50 h-7 min-w-[1.75rem] px-1 rounded-full
                           flex items-center justify-center cursor-pointer
                           transition-all duration-200 hover:bg-white/12"
                    style="background: rgba(255,255,255,0.08);
                           backdrop-filter: blur(10px);
                           border: 1px solid rgba(255,255,255,0.14);">
                <span class="toggle-icon-slot">
                    <svg id="toggleIconLeft" xmlns="http://www.w3.org/2000/svg"
                         class="w-3.5 h-3.5 text-white transition-all duration-300"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                    <svg id="toggleIconRight" xmlns="http://www.w3.org/2000/svg"
                         class="w-3.5 h-3.5 text-white transition-all duration-300 hidden"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </span>
            </button>

            <a href="{{ route('dashboard') }}" id="logoBar"
               class="sidebar-logo flex items-center gap-3 pl-4 pr-12 py-5 overflow-hidden hover:bg-white/5 transition-colors"
               style="border-bottom: 1px solid rgba(255,255,255,0.08);"
               title="Aller au tableau de bord">
                <div class="w-9 h-9 rounded-xl flex-shrink-0 flex items-center justify-center"
                     style="background: rgba(34,197,94,0.25);
                            border: 1px solid rgba(34,197,94,0.35);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24"
                         fill="none" stroke="#4ade80" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2a10 10 0 0 1 10 10"/>
                        <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10"/>
                        <path d="M12 8v8M8 12h8"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </div>
                <div id="logoText"
                     class="overflow-hidden whitespace-nowrap transition-all duration-300"
                     style="max-width: 200px; opacity: 1;">
                    <div class="logo-text leading-tight">
                        AgroFinance<span class="logo-accent">+</span>
                    </div>
                    <div class="logo-sub">Gestion agricole</div>
                </div>
            </a>

            <nav class="flex-1 py-5 overflow-y-auto overflow-x-hidden sidebar-nav-inter"
                 style="padding-left: 12px; padding-right: 12px;">

                <p id="navLabelMenu" class="nav-section-label overflow-hidden whitespace-nowrap transition-all duration-300"
                   style="max-width: 200px; opacity: 1;">MENU</p>

                <a href="{{ route('dashboard') }}"
                   class="nav-link {{ request()->routeIs('dashboard') ? 'nav-active' : '' }}"
                   data-tooltip="Tableau de bord">
                    <span class="nav-indicator"></span>
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </span>
                    <span class="nav-text">Tableau de bord</span>
                </a>

                <a href="{{ route('activites.index') }}"
                   class="nav-link {{ request()->routeIs('activites.*') ? 'nav-active' : '' }}"
                   data-tooltip="Campagnes">
                    <span class="nav-indicator"></span>
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </span>
                    <span class="nav-text">Campagnes</span>
                </a>

                <a href="{{ route('transactions.create') }}"
                   class="nav-link {{ request()->routeIs('transactions.*') ? 'nav-active' : '' }}"
                   data-tooltip="Nouvelle saisie">
                    <span class="nav-indicator"></span>
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                    <span class="nav-text">Nouvelle saisie</span>
                </a>

                <a href="{{ route('rapports.index') }}"
                   class="nav-link {{ request()->routeIs('rapports.*') ? 'nav-active' : '' }}"
                   data-tooltip="Rapports PDF">
                    <span class="nav-indicator"></span>
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </span>
                    <span class="nav-text">Rapports PDF</span>
                </a>

                <div id="navSeparator"
                     class="overflow-hidden transition-all duration-300"
                     style="max-height: 96px; opacity: 1;">
                    <div class="nav-separator-line"></div>
                    <p id="navLabelCompte" class="nav-section-label nav-section-label--compte">MON COMPTE</p>
                </div>

                <a href="{{ route('profil') }}"
                   class="nav-link {{ request()->routeIs('profil') ? 'nav-active' : '' }}"
                   data-tooltip="Profil">
                    <span class="nav-indicator"></span>
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                    <span class="nav-text">Profil</span>
                </a>

                <a href="{{ route('abonnement') }}"
                   class="nav-link {{ request()->routeIs('abonnement') ? 'nav-active' : '' }}"
                   data-tooltip="Abonnement">
                    <span class="nav-indicator"></span>
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </span>
                    <span class="nav-text">Abonnement</span>
                </a>

                <a href="{{ route('profil') }}"
                   class="nav-link"
                   data-tooltip="Paramètres">
                    <span class="nav-indicator"></span>
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </span>
                    <span class="nav-text">Paramètres</span>
                </a>

            </nav>

            @auth
            <div style="border-top: 1px solid rgba(255,255,255,0.08); padding: 14px 12px;">

                <div id="userFull"
                     class="overflow-hidden transition-all duration-300"
                     style="max-height: 120px; opacity: 1;">
                    <div class="flex items-center gap-3 px-2 py-2 rounded-xl cursor-pointer
                                transition-all duration-200 hover:bg-white/5"
                         style="background: rgba(255,255,255,0.035);">
                        <div class="w-8 h-8 rounded-full flex-shrink-0
                                    flex items-center justify-center font-semibold text-sm"
                             style="background: rgba(34,197,94,0.25);
                                    border: 1px solid rgba(34,197,94,0.40);
                                    color: #4ade80;">
                            {{ strtoupper(mb_substr((string) auth()->user()->prenom, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0 overflow-hidden whitespace-nowrap">
                            <div class="user-name truncate">{{ auth()->user()->prenom }} {{ auth()->user()->nom }}</div>
                            <div class="user-phone truncate">{{ auth()->user()->telephone }}</div>
                        </div>
                        <form method="POST" action="{{ route('deconnexion') }}">
                            @csrf
                            <button type="submit"
                                    title="Déconnexion"
                                    class="w-7 h-7 rounded-lg flex items-center justify-center
                                           transition-all hover:bg-red-500/20"
                                    style="color: rgba(248,113,113,0.70);">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>

                <div id="userReduced"
                     class="hidden flex-col items-center gap-2">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center
                                font-semibold text-sm cursor-pointer"
                         style="background: rgba(34,197,94,0.25);
                                border: 1px solid rgba(34,197,94,0.40);
                                color: #4ade80;"
                         title="{{ auth()->user()->prenom }} {{ auth()->user()->nom }}">
                        {{ strtoupper(mb_substr((string) auth()->user()->prenom, 0, 1)) }}
                    </div>
                    <form method="POST" action="{{ route('deconnexion') }}">
                        @csrf
                        <button type="submit"
                                title="Déconnexion"
                                class="w-7 h-7 rounded-lg flex items-center justify-center
                                       transition-all hover:bg-red-500/20"
                                style="color: rgba(248,113,113,0.60);">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </button>
                    </form>
                </div>

            </div>
            @endauth

        </aside>

        <div id="mainContent"
             class="flex-1 flex flex-col min-h-screen overflow-hidden
                    transition-all duration-300 ease-in-out min-w-0"
             style="margin-left: 260px;">

            @if(session('success'))
            <div class="mx-8 mt-4 rounded-xl px-4 py-3 text-sm flex items-center gap-2"
                 style="background:rgba(34,197,94,0.15);
                        border:1px solid rgba(34,197,94,0.30);
                        color:#4ade80;">
                <x-icon name="check-circle" class="w-5 h-5 shrink-0 opacity-90" /> {{ session('success') }}
            </div>
            @endif
            @if(session('alerte'))
            <div class="mx-8 mt-4 rounded-xl px-4 py-3 text-sm flex items-center gap-2"
                 style="background:rgba(245,158,11,0.15);
                        border:1px solid rgba(245,158,11,0.30);
                        color:#fbbf24;">
                <span>⚠️</span> {{ session('alerte') }}
            </div>
            @endif
            @if(session('error'))
            <div class="mx-8 mt-4 rounded-xl px-4 py-3 text-sm flex items-center gap-2"
                 style="background:rgba(239,68,68,0.15);
                        border:1px solid rgba(239,68,68,0.30);
                        color:#f87171;">
                <x-icon name="x-circle" class="w-5 h-5 shrink-0 opacity-90" /> {{ session('error') }}
            </div>
            @endif
            @if(session('info'))
            <div class="mx-8 mt-4 rounded-xl px-4 py-3 text-sm flex items-center gap-2"
                 style="background:rgba(251,191,36,0.12);
                        border:1px solid rgba(251,191,36,0.25);
                        color:#fcd34d;">
                <x-icon name="information-circle" class="w-5 h-5 shrink-0 opacity-90" /> {{ session('info') }}
            </div>
            @endif
            @if ($errors->any())
            <div class="mx-8 mt-4 rounded-xl px-4 py-3 text-sm"
                 style="background:rgba(239,68,68,0.15);
                        border:1px solid rgba(239,68,68,0.30);
                        color:#f87171;">
                {{ $errors->first() }}
            </div>
            @endif

            <main class="flex-1 overflow-y-auto w-full min-h-0">
                <div class="max-w-[1280px] mx-auto w-full px-8 pt-6 pb-10">
                    <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between sm:gap-6">
                        <div class="min-w-0">
                            <h1 class="page-title">@yield('page-title', 'Tableau de bord')</h1>
                            <p class="page-subtitle">@yield('page-subtitle', '')</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 flex-shrink-0">
                            @yield('topbar-actions')
                        </div>
                    </div>

                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <script>
    (function () {
        const EXPLOITATION_TYPE = @json($exploitationType);

        const MIXTE_IMAGES = [
            'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=1920&q=80',
            'https://images.unsplash.com/photo-1516467508483-a7212febe31a?w=1920&q=80',
            'https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=1920&q=80',
            'https://images.unsplash.com/photo-1542838132-92c53300491e?w=1920&q=80',
        ];

        const TYPE_IMAGES = {
            'cultures_vivrieres': 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=1920&q=80',
            'elevage': 'https://images.unsplash.com/photo-1516467508483-a7212febe31a?w=1920&q=80',
            'maraichage': 'https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=1920&q=80',
            'transformation': 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=1920&q=80',
        };

        const layerA = document.getElementById('bgLayerA');
        const layerB = document.getElementById('bgLayerB');

        if (layerA && layerB) {
            if (EXPLOITATION_TYPE === 'mixte') {
                let currentIndex = 0;
                let activeLayer = 'A';

                layerA.style.backgroundImage = 'url(\'' + MIXTE_IMAGES[0] + '\')';
                layerA.style.opacity = '1';
                layerB.style.opacity = '0';

                function nextBackground() {
                    currentIndex = (currentIndex + 1) % MIXTE_IMAGES.length;
                    const nextImage = MIXTE_IMAGES[currentIndex];

                    if (activeLayer === 'A') {
                        layerB.style.backgroundImage = 'url(\'' + nextImage + '\')';
                        layerB.style.transition = 'opacity 2s ease-in-out';
                        layerA.style.transition = 'opacity 2s ease-in-out';
                        requestAnimationFrame(function () {
                            layerB.style.opacity = '1';
                            layerA.style.opacity = '0';
                        });
                        activeLayer = 'B';
                    } else {
                        layerA.style.backgroundImage = 'url(\'' + nextImage + '\')';
                        layerA.style.transition = 'opacity 2s ease-in-out';
                        layerB.style.transition = 'opacity 2s ease-in-out';
                        requestAnimationFrame(function () {
                            layerA.style.opacity = '1';
                            layerB.style.opacity = '0';
                        });
                        activeLayer = 'A';
                    }
                }

                setInterval(nextBackground, 30000);
            } else {
                const imageUrl = TYPE_IMAGES[EXPLOITATION_TYPE] || TYPE_IMAGES['cultures_vivrieres'];
                layerA.style.backgroundImage = 'url(\'' + imageUrl + '\')';
                layerA.style.opacity = '1';
                layerB.style.opacity = '0';
            }
        }

        let sidebarReduced = localStorage.getItem('sidebarReduced') === 'true';

        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const logoText = document.getElementById('logoText');
        const navLabelMenu = document.getElementById('navLabelMenu');
        const navSeparator = document.getElementById('navSeparator');
        const userFull = document.getElementById('userFull');
        const userReduced = document.getElementById('userReduced');
        const navTexts = document.querySelectorAll('.nav-text');
        const iconLeft = document.getElementById('toggleIconLeft');
        const iconRight = document.getElementById('toggleIconRight');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const logoBar = document.getElementById('logoBar');

        function applySidebarState(withAnimation) {
            if (!sidebar || !mainContent) return;

            if (!withAnimation) {
                sidebar.style.transition = 'none';
                mainContent.style.transition = 'none';
            }

            if (sidebarReduced) {
                sidebar.style.width = '72px';
                mainContent.style.marginLeft = '72px';
                sidebar.classList.add('sidebar-reduced');

                if (sidebarToggle) {
                    sidebarToggle.classList.add('hidden');
                    sidebarToggle.setAttribute('aria-hidden', 'true');
                }
                if (logoBar) {
                    logoBar.setAttribute('title', 'Cliquer pour afficher le menu complet');
                }

                if (iconLeft) iconLeft.classList.add('hidden');
                if (iconRight) iconRight.classList.remove('hidden');

                navTexts.forEach(function (el) {
                    el.style.maxWidth = '0';
                    el.style.opacity = '0';
                });

                if (logoText) {
                    logoText.style.maxWidth = '0';
                    logoText.style.opacity = '0';
                }
                if (navLabelMenu) {
                    navLabelMenu.style.maxWidth = '0';
                    navLabelMenu.style.opacity = '0';
                }
                if (navSeparator) {
                    navSeparator.style.maxHeight = '0';
                    navSeparator.style.opacity = '0';
                }
                if (userFull) {
                    userFull.style.maxHeight = '0';
                    userFull.style.opacity = '0';
                }
                if (userReduced) {
                    userReduced.classList.remove('hidden');
                    userReduced.style.display = 'flex';
                }

                document.querySelectorAll('.nav-link').forEach(function (link) {
                    link.style.justifyContent = 'center';
                    link.style.padding = '9px 0';
                });
            } else {
                sidebar.style.width = '260px';
                mainContent.style.marginLeft = '260px';
                sidebar.classList.remove('sidebar-reduced');

                if (sidebarToggle) {
                    sidebarToggle.classList.remove('hidden');
                    sidebarToggle.setAttribute('aria-hidden', 'false');
                }
                if (logoBar) {
                    logoBar.setAttribute('title', 'Aller au tableau de bord');
                }

                if (iconLeft) iconLeft.classList.remove('hidden');
                if (iconRight) iconRight.classList.add('hidden');

                navTexts.forEach(function (el) {
                    el.style.maxWidth = '180px';
                    el.style.opacity = '1';
                });

                if (logoText) {
                    logoText.style.maxWidth = '200px';
                    logoText.style.opacity = '1';
                }
                if (navLabelMenu) {
                    navLabelMenu.style.maxWidth = '200px';
                    navLabelMenu.style.opacity = '1';
                }
                if (navSeparator) {
                    navSeparator.style.maxHeight = '96px';
                    navSeparator.style.opacity = '1';
                }
                if (userFull) {
                    userFull.style.maxHeight = '120px';
                    userFull.style.opacity = '1';
                }
                if (userReduced) {
                    userReduced.classList.add('hidden');
                    userReduced.style.display = 'none';
                }

                document.querySelectorAll('.nav-link').forEach(function (link) {
                    link.style.justifyContent = '';
                    link.style.padding = '9px 14px';
                });
            }

            if (!withAnimation) {
                setTimeout(function () {
                    sidebar.style.transition = 'width 0.3s ease-in-out';
                    mainContent.style.transition = 'margin-left 0.3s ease-in-out';
                }, 50);
            } else if (sidebar && mainContent) {
                sidebar.style.transition = 'width 0.3s ease-in-out';
                mainContent.style.transition = 'margin-left 0.3s ease-in-out';
            }
        }

        window.toggleSidebar = function () {
            sidebarReduced = !sidebarReduced;
            localStorage.setItem('sidebarReduced', sidebarReduced ? 'true' : 'false');
            applySidebarState(true);
        };

        applySidebarState(false);

        if (logoBar) {
            logoBar.addEventListener('click', function (e) {
                if (sidebarReduced) {
                    e.preventDefault();
                    sidebarReduced = false;
                    localStorage.setItem('sidebarReduced', 'false');
                    applySidebarState(true);
                }
            });
        }

        var apiToken = document.querySelector('meta[name="api-token"]');
        apiToken = apiToken ? apiToken.getAttribute('content') : null;
        if (apiToken) {
            localStorage.setItem('agrofinance_token', apiToken);
        } else {
            localStorage.removeItem('agrofinance_token');
        }
    })();
    </script>
    @stack('scripts')
</body>
</html>
