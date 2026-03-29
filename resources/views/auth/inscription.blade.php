@extends('layouts.app-auth')
@section('title', 'Créer un compte')

{{-- ════ MOBILE ════ --}}
@section('mobile-topbar-link')
    <a href="{{ route('connexion') }}" class="auth-mobile-topbar-link">Se connecter</a>
@endsection

@section('mobile-hero')
<div class="auth-mobile-hero" style="padding-bottom:12px;">
    <h1 class="auth-mobile-hero-title">
        Commençons<br><span>ensemble.</span>
    </h1>
    <p class="auth-mobile-hero-sub">
        Gérez vos exploitations et suivez vos indicateurs agricoles.
    </p>
</div>
@endsection

@section('mobile-steps')
<div class="auth-mobile-steps">
    <div class="auth-mobile-step-dot active"></div>
    <div class="auth-mobile-step-dot"></div>
    <div class="auth-mobile-step-dot"></div>
    <span class="auth-mobile-step-label active">Étape 1 / 3 — Informations</span>
</div>
@endsection

@section('mobile-form')
<form method="POST" action="{{ route('inscription.store') }}">
    @csrf

    <div class="auth-mobile-grid2 auth-mobile-field">
        <div>
            <label class="auth-mobile-label">Prénom</label>
            <input type="text" name="prenom" value="{{ old('prenom') }}"
                   placeholder="Donald" class="auth-mobile-input" required>
        </div>
        <div>
            <label class="auth-mobile-label">Nom</label>
            <input type="text" name="nom" value="{{ old('nom') }}"
                   placeholder="Adjinda" class="auth-mobile-input" required>
        </div>
    </div>

    <div class="auth-mobile-field">
        <label class="auth-mobile-label">Numéro de téléphone</label>
        <input type="tel" name="telephone" value="{{ old('telephone') }}"
               placeholder="+229 XX XX XX XX"
               class="auth-mobile-input" required inputmode="tel">
        <p style="font-family:'Inter',sans-serif; font-size:11px; color:rgba(255,255,255,0.22); margin:5px 0 0;">
            Format : +229 suivi de 8 chiffres
        </p>
    </div>

    <div class="auth-mobile-field">
        <label class="auth-mobile-label">Type d'exploitation</label>
        <div class="auth-mobile-type-grid">
            @foreach ([
                ['cultures_vivrieres', '🌽', 'Cultures vivrières'],
                ['elevage',            '🐔', 'Élevage'],
                ['maraichage',         '🥬', 'Maraîchage'],
                ['transformation',     '🪴', 'Transformation'],
                ['mixte',              '🌿', 'Mixte'],
            ] as [$val, $emoji, $label])
                <label class="auth-mobile-type-card">
                    <input type="radio" name="type_exploitation" value="{{ $val }}"
                           @checked(old('type_exploitation', 'cultures_vivrieres') === $val)>
                    <div class="auth-mobile-type-card-inner">
                        <span class="auth-mobile-type-emoji">{{ $emoji }}</span>
                        <span class="auth-mobile-type-label">{{ $label }}</span>
                    </div>
                </label>
            @endforeach
        </div>
    </div>

    <button type="submit" class="auth-mobile-btn">Continuer →</button>
</form>
<div class="auth-mobile-link-row">
    Déjà un compte ?
    <a href="{{ route('connexion') }}" class="auth-mobile-link">Se connecter</a>
</div>
<p style="text-align:center; margin-top:18px; font-family:'Inter',sans-serif; font-size:11px; color:rgba(255,255,255,0.28); line-height:1.5;">
    <a href="{{ route('confidentialite') }}" style="color:rgba(255,255,255,0.45); text-decoration:underline;">Confidentialité</a>
    <span style="margin:0 6px;">·</span>
    <a href="{{ route('conditions-utilisation') }}" style="color:rgba(255,255,255,0.45); text-decoration:underline;">Conditions d’utilisation</a>
</p>
@endsection

{{-- ════ DESKTOP (inchangé) ════ --}}
@section('branding')
<div style="display:flex; align-items:center; gap:12px; margin-bottom:48px;">
    <div style="width:42px; height:42px; border-radius:12px;
                background:rgba(74,222,128,0.20); border:1px solid rgba(74,222,128,0.35);
                display:flex; align-items:center; justify-content:center;">
        <svg xmlns="http://www.w3.org/2000/svg" style="width:22px;height:22px;" fill="none"
             viewBox="0 0 24 24" stroke="#4ade80" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
        </svg>
    </div>
    <div>
        <div style="font-family:'Space Grotesk',sans-serif; font-size:16px;
                    font-weight:700; color:white; letter-spacing:-0.02em;">
            AgroFinance<span style="color:#4ade80;">+</span>
        </div>
        <div style="font-family:'Inter',sans-serif; font-size:10px;
                    color:rgba(255,255,255,0.30); text-transform:uppercase; letter-spacing:0.12em;">
            Gestion agricole
        </div>
    </div>
</div>
<div style="margin-bottom:28px;">
    <h1 style="font-family:'Space Grotesk',sans-serif; font-size:44px;
               font-weight:700; color:white; line-height:1.08;
               letter-spacing:-0.04em; margin:0 0 16px 0;">
        Commençons<br><span style="color:#4ade80;">ensemble.</span>
    </h1>
    <p style="font-family:'Inter',sans-serif; font-size:15px;
              color:rgba(255,255,255,0.42); line-height:1.65; margin:0; max-width:320px;">
        Gérez vos exploitations agricoles, suivez vos indicateurs financiers et générez vos rapports.
    </p>
</div>
<div style="display:flex; flex-direction:column; gap:14px; margin-top:36px;">
    @foreach([
        ['🌱', 'Indicateurs financiers automatiques', 'PB, MB, RNE, RF calculés en temps réel'],
        ['📄', 'Rapports PDF professionnels', 'Partagez avec votre agent de microfinance'],
        ['📱', 'Fonctionne hors ligne', 'Saisissez vos données même sans connexion'],
    ] as [$emoji, $titre, $desc])
        <div style="display:flex; align-items:flex-start; gap:14px;">
            <div style="width:36px; height:36px; border-radius:10px; flex-shrink:0;
                        background:rgba(74,222,128,0.10); border:1px solid rgba(74,222,128,0.20);
                        display:flex; align-items:center; justify-content:center;
                        font-size:16px; margin-top:1px;">{{ $emoji }}</div>
            <div>
                <div style="font-family:'Inter',sans-serif; font-size:13px; font-weight:600;
                            color:rgba(255,255,255,0.85); margin-bottom:2px;">{{ $titre }}</div>
                <div style="font-family:'Inter',sans-serif; font-size:12px;
                            color:rgba(255,255,255,0.35); line-height:1.4;">{{ $desc }}</div>
            </div>
        </div>
    @endforeach
</div>
@endsection

@section('form')
<div class="step-indicator">
    <div class="step-item"><div class="step-circle active">1</div><div class="step-label active">Infos</div></div>
    <div class="step-line"></div>
    <div class="step-item"><div class="step-circle">2</div><div class="step-label">OTP</div></div>
    <div class="step-line"></div>
    <div class="step-item"><div class="step-circle">3</div><div class="step-label">PIN</div></div>
</div>
<div class="auth-form-title">Créer un compte</div>
<div class="auth-form-subtitle">Étape 1 sur 3 — Vos informations personnelles</div>
@if ($errors->any())
    <div class="auth-error">@foreach ($errors->all() as $e)<div>• {{ $e }}</div>@endforeach</div>
@endif
<form method="POST" action="{{ route('inscription.store') }}">
    @csrf
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:0;">
        <div class="auth-field">
            <label class="auth-label">Prénom</label>
            <input type="text" name="prenom" value="{{ old('prenom') }}" placeholder="Ex : Donald" class="auth-input" required>
        </div>
        <div class="auth-field">
            <label class="auth-label">Nom</label>
            <input type="text" name="nom" value="{{ old('nom') }}" placeholder="Ex : Adjinda" class="auth-input" required>
        </div>
    </div>
    <div class="auth-field">
        <label class="auth-label">Numéro de téléphone</label>
        <input type="tel" name="telephone" value="{{ old('telephone') }}" placeholder="+229 XX XX XX XX" class="auth-input" required>
        <div style="font-size:11px; color:rgba(255,255,255,0.25); margin-top:5px; font-family:'Inter',sans-serif;">Format béninois : +229 suivi de 8 chiffres</div>
    </div>
    <div class="auth-field">
        <label class="auth-label">Type d'exploitation</label>
        <div class="type-grid">
            @foreach ([
                ['cultures_vivrieres','🌽','Cultures vivrières'],
                ['elevage','🐔','Élevage'],
                ['maraichage','🥬','Maraîchage'],
                ['transformation','🪴','Transformation'],
                ['mixte','🌿','Mixte'],
            ] as [$val, $emoji, $label])
                <label class="type-card">
                    <input type="radio" name="type_exploitation" value="{{ $val }}" @checked(old('type_exploitation', 'cultures_vivrieres') === $val)>
                    <div class="type-card-inner"><span class="type-card-emoji">{{ $emoji }}</span><span class="type-card-label">{{ $label }}</span></div>
                </label>
            @endforeach
        </div>
    </div>
    <button type="submit" class="auth-btn">Continuer →</button>
    <div style="text-align:center; margin-top:20px; font-family:'Inter',sans-serif; font-size:13px; color:rgba(255,255,255,0.38);">
        Déjà un compte ? <a href="{{ route('connexion') }}" class="auth-link">Se connecter</a>
    </div>
    <p style="text-align:center; margin-top:16px; font-family:'Inter',sans-serif; font-size:11px; color:rgba(255,255,255,0.28);">
        <a href="{{ route('confidentialite') }}" style="color:rgba(255,255,255,0.42); text-decoration:underline;">Confidentialité</a>
        <span style="margin:0 8px;">·</span>
        <a href="{{ route('conditions-utilisation') }}" style="color:rgba(255,255,255,0.42); text-decoration:underline;">Conditions d’utilisation</a>
    </p>
</form>
@endsection
