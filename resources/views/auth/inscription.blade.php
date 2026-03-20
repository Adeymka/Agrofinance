@extends('layouts.app-auth')
@section('title', 'Créer un compte')

@section('branding')
<!-- Logo -->
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

<!-- Titre accrocheur -->
<div style="margin-bottom:28px;">
    <h1 style="font-family:'Space Grotesk',sans-serif; font-size:44px;
               font-weight:700; color:white; line-height:1.08;
               letter-spacing:-0.04em; margin:0 0 16px 0;">
        Commençons<br>
        <span style="color:#4ade80;">ensemble.</span>
    </h1>
    <p style="font-family:'Inter',sans-serif; font-size:15px; font-weight:400;
              color:rgba(255,255,255,0.42); line-height:1.65; margin:0; max-width:320px;">
        Gérez vos exploitations agricoles, suivez vos indicateurs financiers
        et générez vos rapports en quelques clics.
    </p>
</div>

<!-- Avantages -->
<div style="display:flex; flex-direction:column; gap:14px; margin-top:36px;">
    @foreach([
        ['🌱', 'Indicateurs FSA-UAC automatiques', 'PB, MB, RNE, RF calculés en temps réel'],
        ['📄', 'Rapports PDF professionnels', 'Partagez avec votre agent de microfinance'],
        ['📱', 'Fonctionne hors ligne', 'Saisissez vos données même sans connexion'],
    ] as [$emoji, $titre, $desc])
        <div style="display:flex; align-items:flex-start; gap:14px;">
            <div style="width:36px; height:36px; border-radius:10px; flex-shrink:0;
                        background:rgba(74,222,128,0.10); border:1px solid rgba(74,222,128,0.20);
                        display:flex; align-items:center; justify-content:center;
                        font-size:16px; margin-top:1px;">
                {{ $emoji }}
            </div>
            <div>
                <div style="font-family:'Inter',sans-serif; font-size:13px; font-weight:600;
                            color:rgba(255,255,255,0.85); margin-bottom:2px;">
                    {{ $titre }}
                </div>
                <div style="font-family:'Inter',sans-serif; font-size:12px; font-weight:400;
                            color:rgba(255,255,255,0.35); line-height:1.4;">
                    {{ $desc }}
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection

@section('form')
<!-- Indicateur étapes -->
<div class="step-indicator">
    <div class="step-item">
        <div class="step-circle active">1</div>
        <div class="step-label active">Infos</div>
    </div>
    <div class="step-line"></div>
    <div class="step-item">
        <div class="step-circle">2</div>
        <div class="step-label">OTP</div>
    </div>
    <div class="step-line"></div>
    <div class="step-item">
        <div class="step-circle">3</div>
        <div class="step-label">PIN</div>
    </div>
</div>

<!-- Titre -->
<div class="auth-form-title">Créer un compte</div>
<div class="auth-form-subtitle">Étape 1 sur 3 — Vos informations personnelles</div>

<!-- Erreurs -->
@if ($errors->any())
    <div class="auth-error">
        @foreach ($errors->all() as $e)
            <div>• {{ $e }}</div>
        @endforeach
    </div>
@endif

<!-- Formulaire -->
<form method="POST" action="{{ route('inscription.store') }}">
    @csrf

    <!-- Prénom + Nom en ligne -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:0;">
        <div class="auth-field">
            <label class="auth-label">Prénom</label>
            <input type="text" name="prenom" value="{{ old('prenom') }}"
                   placeholder="Ex : Donald" class="auth-input" required>
        </div>
        <div class="auth-field">
            <label class="auth-label">Nom</label>
            <input type="text" name="nom" value="{{ old('nom') }}"
                   placeholder="Ex : Adjinda" class="auth-input" required>
        </div>
    </div>

    <!-- Téléphone -->
    <div class="auth-field">
        <label class="auth-label">Numéro de téléphone</label>
        <input type="tel" name="telephone" value="{{ old('telephone') }}"
               placeholder="+229 XX XX XX XX" class="auth-input" required>
        <div style="font-size:11px; color:rgba(255,255,255,0.25);
                    margin-top:5px; font-family:'Inter',sans-serif;">
            Format béninois : +229 suivi de 8 chiffres
        </div>
    </div>

    <!-- Type d'exploitation -->
    <div class="auth-field">
        <label class="auth-label">Type d'exploitation</label>
        <div class="type-grid">
            @foreach ([
                ['cultures_vivrieres', '🌽', 'Cultures vivrières'],
                ['elevage', '🐔', 'Élevage'],
                ['maraichage', '🥬', 'Maraîchage'],
                ['transformation', '🪴', 'Transformation'],
                ['mixte', '🌿', 'Mixte'],
            ] as [$val, $emoji, $label])
                <label class="type-card">
                    <input type="radio" name="type_exploitation" value="{{ $val }}"
                        @checked(old('type_exploitation', 'cultures_vivrieres') === $val)>
                    <div class="type-card-inner">
                        <span class="type-card-emoji">{{ $emoji }}</span>
                        <span class="type-card-label">{{ $label }}</span>
                    </div>
                </label>
            @endforeach
        </div>
    </div>

    <!-- Bouton -->
    <button type="submit" class="auth-btn">
        Continuer →
    </button>

    <!-- Lien connexion -->
    <div style="text-align:center; margin-top:20px;
                font-family:'Inter',sans-serif; font-size:13px;
                color:rgba(255,255,255,0.38);">
        Déjà un compte ?
        <a href="{{ route('connexion') }}" class="auth-link">Se connecter</a>
    </div>

</form>
@endsection
