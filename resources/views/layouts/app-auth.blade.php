<!DOCTYPE html>
<html lang="fr">
<head>
    <script>try{if(localStorage.getItem('af_outdoor_boost')==='1')document.documentElement.classList.add('af-outdoor');}catch(e){}</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0D1F0D">
    <title>@yield('title', 'AgroFinance+') — AgroFinance+</title>
    <link rel="manifest" href="{{ route('pwa.manifest') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

@if(($platform ?? 'desktop') === 'mobile')
<style>
/* ══════════════════════════════════════════
   AUTH MOBILE — plein écran single column
══════════════════════════════════════════ */
*, *::before, *::after { box-sizing: border-box; }

.auth-mobile-shell {
    min-height: 100dvh;
    background: linear-gradient(180deg, #060f06 0%, #0D1F0D 55%, #091409 100%);
    color: rgba(255,255,255,0.88);
    display: flex;
    flex-direction: column;
    max-width: 448px;
    margin: 0 auto;
    position: relative;
    overflow: hidden;
}

/* Grain subtil */
.auth-mobile-shell::before {
    content: '';
    position: fixed;
    inset: 0;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
    pointer-events: none;
    z-index: 0;
    opacity: 0.3;
}

/* ── Décor top-right ── */
.auth-mobile-deco {
    position: absolute;
    top: -80px;
    right: -80px;
    width: 240px;
    height: 240px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(74,222,128,0.08) 0%, transparent 70%);
    pointer-events: none;
    z-index: 0;
}

/* ── Logo bar ── */
.auth-mobile-topbar {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px 0;
}
.auth-mobile-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
}
.auth-mobile-logo-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: rgba(74,222,128,0.14);
    border: 1px solid rgba(74,222,128,0.28);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}
.auth-mobile-logo-text {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 16px;
    font-weight: 700;
    color: white;
    letter-spacing: -0.02em;
}
.auth-mobile-logo-text span { color: #4ade80; }
.auth-mobile-topbar-link {
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    font-weight: 600;
    color: rgba(255,255,255,0.38);
    text-decoration: none;
    padding: 6px 12px;
    border-radius: 999px;
    border: 1px solid rgba(255,255,255,0.10);
    background: rgba(255,255,255,0.04);
    transition: color 0.15s;
}
.auth-mobile-topbar-link:active { color: rgba(255,255,255,0.70); }

/* ── Hero header ── */
.auth-mobile-hero {
    position: relative;
    z-index: 1;
    padding: 28px 20px 20px;
}
.auth-mobile-hero-title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 36px;
    font-weight: 800;
    color: white;
    line-height: 1.08;
    letter-spacing: -0.04em;
    margin: 0 0 10px;
}
.auth-mobile-hero-title span { color: #4ade80; }
.auth-mobile-hero-sub {
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    color: rgba(255,255,255,0.38);
    line-height: 1.60;
    margin: 0;
}

/* ── Stepper ── */
.auth-mobile-steps {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 0 20px;
    margin-bottom: 16px;
    position: relative;
    z-index: 1;
}
.auth-mobile-step-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    background: rgba(255,255,255,0.12);
    transition: all 0.2s;
}
.auth-mobile-step-dot.done {
    background: rgba(74,222,128,0.50);
}
.auth-mobile-step-dot.active {
    width: 24px;
    border-radius: 4px;
    background: #4ade80;
}
.auth-mobile-step-label {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    color: rgba(255,255,255,0.30);
    margin-left: 4px;
}
.auth-mobile-step-label.active {
    color: #4ade80;
    font-weight: 600;
}

/* ── Form card ── */
.auth-mobile-card {
    position: relative;
    z-index: 1;
    margin: 0 12px;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.09);
    border-radius: 24px;
    padding: 24px 20px;
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    flex: 1;
    margin-bottom: 24px;
}

/* ── Errors / success ── */
.auth-mobile-error {
    position: relative;
    padding: 12px 14px 12px 38px;
    background: rgba(248,113,113,0.08);
    border: 1px solid rgba(248,113,113,0.22);
    border-radius: 12px;
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    color: #fecaca;
    margin-bottom: 16px;
    line-height: 1.55;
}
.auth-mobile-error::before {
    content: "⚠";
    position: absolute;
    left: 12px;
    top: 12px;
    font-size: 16px;
    line-height: 1.2;
}
.auth-mobile-success {
    background: rgba(74,222,128,0.08);
    border: 1px solid rgba(74,222,128,0.20);
    border-radius: 12px;
    padding: 12px 14px;
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    color: #86efac;
    margin-bottom: 16px;
}
.auth-mobile-info {
    background: rgba(251,191,36,0.08);
    border: 1px solid rgba(251,191,36,0.22);
    border-radius: 12px;
    padding: 12px 14px;
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    color: #fcd34d;
    margin-bottom: 16px;
}

/* ── Field ── */
.auth-mobile-field { margin-bottom: 14px; }
.auth-mobile-label {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: rgba(255,255,255,0.30);
    margin-bottom: 6px;
    display: block;
}
.auth-mobile-input {
    width: 100%;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 14px;
    padding: 14px 16px;
    font-family: 'Inter', sans-serif;
    font-size: 15px;
    color: rgba(255,255,255,0.88);
    outline: none;
    -webkit-appearance: none;
    transition: border-color 0.15s;
}
.auth-mobile-input:focus { border-color: rgba(74,222,128,0.40); }
.auth-mobile-input::placeholder { color: rgba(255,255,255,0.18); }

/* ── Grid 2 colonnes ── */
.auth-mobile-grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }

/* ── PIN input ── */
.auth-mobile-pin {
    width: 100%;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 14px;
    padding: 18px 16px;
    font-family: 'Space Grotesk', sans-serif;
    font-size: 28px;
    font-weight: 700;
    color: #4ade80;
    text-align: center;
    letter-spacing: 0.5em;
    outline: none;
    -webkit-appearance: none;
    -webkit-text-security: disc;
    transition: border-color 0.15s;
}
.auth-mobile-pin:focus { border-color: rgba(74,222,128,0.40); }

/* ── OTP input ── */
.auth-mobile-otp {
    width: 100%;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 14px;
    padding: 20px 16px;
    font-family: 'Space Grotesk', sans-serif;
    font-size: 32px;
    font-weight: 800;
    color: #4ade80;
    text-align: center;
    letter-spacing: 0.35em;
    outline: none;
    -webkit-appearance: none;
    transition: border-color 0.15s;
}
.auth-mobile-otp:focus { border-color: rgba(74,222,128,0.40); }

/* ── Type exploitation grid ── */
.auth-mobile-type-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
    margin-top: 2px;
}
.auth-mobile-type-card input[type="radio"] { display: none; }
.auth-mobile-type-card-inner {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 5px;
    padding: 12px 6px;
    border-radius: 14px;
    border: 1px solid rgba(255,255,255,0.10);
    background: rgba(255,255,255,0.04);
    cursor: pointer;
    transition: all 0.15s;
    text-align: center;
}
.auth-mobile-type-card input:checked + .auth-mobile-type-card-inner {
    background: rgba(255,255,255,0.08);
    border-color: rgba(74,222,128,0.40);
}
.auth-mobile-type-emoji { font-size: 20px; line-height: 1; }
.auth-mobile-type-label {
    font-family: 'Inter', sans-serif;
    font-size: 10px;
    font-weight: 600;
    color: rgba(255,255,255,0.50);
    line-height: 1.2;
}
.auth-mobile-type-card input:checked + .auth-mobile-type-card-inner .auth-mobile-type-label {
    color: #4ade80;
}

/* ── Submit button ── */
.auth-mobile-btn {
    width: 100%;
    min-height: 44px;
    background: #16a34a;
    color: white;
    font-family: 'Inter', sans-serif;
    font-size: 15px;
    font-weight: 700;
    padding: 16px;
    border-radius: 14px;
    border: 1px solid rgba(74,222,128,0.30);
    cursor: pointer;
    margin-top: 6px;
    transition: opacity 0.15s;
    letter-spacing: 0.01em;
}
.auth-mobile-btn:active { opacity: 0.80; }

/* ── Secondary link ── */
.auth-mobile-link-row {
    text-align: center;
    margin-top: 16px;
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    color: rgba(255,255,255,0.35);
}
.auth-mobile-link {
    color: #4ade80;
    font-weight: 600;
    text-decoration: none;
}
.auth-mobile-ghost-btn {
    width: 100%;
    background: transparent;
    border: none;
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    color: rgba(255,255,255,0.35);
    cursor: pointer;
    padding: 10px;
    margin-top: 4px;
    text-align: center;
}
.auth-mobile-ghost-btn span { color: #4ade80; font-weight: 600; }

/* ── Dev banner ── */
.auth-mobile-dev {
    background: rgba(245,158,11,0.08);
    border: 1px solid rgba(245,158,11,0.20);
    border-radius: 12px;
    padding: 10px 14px;
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    color: rgba(245,158,11,0.70);
    margin: 0 12px 12px;
    position: relative;
    z-index: 1;
}
</style>
@endif

</head>

@php $isMobile = ($platform ?? 'desktop') === 'mobile'; @endphp

<body style="margin:0; padding:0; font-family:'Inter',sans-serif;
             {{ $isMobile ? 'background:#060f06;' : 'background:#050f05; min-height:100vh; overflow-x:hidden; overflow-y:auto;' }}">

@if($isMobile)
{{-- ════════════════════════════════════════
     LAYOUT MOBILE — Single column
════════════════════════════════════════ --}}
<div class="auth-mobile-shell">
    <div class="auth-mobile-deco"></div>

    {{-- Top bar --}}
    <div class="auth-mobile-topbar">
        <a href="{{ route('accueil') }}" class="auth-mobile-logo">
            <div class="auth-mobile-logo-icon">
                <img src="{{ asset('images/logo-agrofinanceplus.png') }}" alt="Logo AgroFinance+" style="width:28px; height:28px; object-fit:contain; border-radius:6px;">
            </div>
            <span class="auth-mobile-logo-text">AgroFinance<span>+</span></span>
        </a>
    </div>

    {{-- Hero / titre vu par chaque page --}}
    @yield('mobile-hero')

    {{-- Stepper optionnel --}}
    @yield('mobile-steps')

    {{-- Dev banner (affiché uniquement si la vue définit la section) --}}
    @yield('mobile-dev')

    {{-- Card formulaire --}}
    <div class="auth-mobile-card">

        {{-- Erreurs — texte + icône (pas la couleur seule) --}}
        @if ($errors->any())
            <div class="auth-mobile-error" role="alert" aria-live="polite">
                <div>
                    @foreach ($errors->all() as $e)
                        <div>{{ $e }}</div>
                    @endforeach
                </div>
            </div>
        @endif
        @if (session('success'))
            <div class="auth-mobile-success" role="status">{{ session('success') }}</div>
        @endif
        @if (session('info'))
            <div class="auth-mobile-info">{{ session('info') }}</div>
        @endif

        @yield('mobile-form')
    </div>
</div>

@else
{{-- ════════════════════════════════════════
     LAYOUT DESKTOP — 2 colonnes
════════════════════════════════════════ --}}

    <!-- BACKGROUND PLEIN ÉCRAN -->
    <div style="position:fixed; inset:0; z-index:0;
                background-image: url('https://images.unsplash.com/photo-1508193638397-1c4234db14d8?w=1920&q=80');
                background-size:cover; background-position:center;">
        <div style="position:absolute; inset:0;
                    background: linear-gradient(135deg,
                      rgba(3,15,3,0.88) 0%,
                      rgba(8,30,8,0.75) 50%,
                      rgba(3,12,3,0.85) 100%);">
        </div>
    </div>

    <!-- CONTENEUR PRINCIPAL 2 COLONNES -->
    <div class="auth-split" style="position:relative; z-index:1; display:flex; min-height:100vh; width:100%;">

        <!-- ══ COLONNE GAUCHE : Branding ══ -->
        <div class="auth-split-brand" style="width:42%; display:flex; flex-direction:column;
                    justify-content:center; padding:60px 50px;
                    border-right:1px solid rgba(255,255,255,0.08); box-sizing:border-box;">
            @yield('branding')
        </div>

        <!-- ══ COLONNE DROITE : Formulaire ══ -->
        <div class="auth-split-form" style="width:58%; display:flex; align-items:center;
                    justify-content:center; padding:60px 70px; box-sizing:border-box;">
            <div style="width:100%; max-width:440px;">
                @yield('form')
            </div>
        </div>

    </div>

@endif

</body>
</html>
