@extends('layouts.app-auth')
@section('title', 'Connexion')

{{-- ════ MOBILE ════ --}}
@section('mobile-topbar-link')
    <a href="{{ route('inscription') }}" class="auth-mobile-topbar-link">Créer un compte</a>
@endsection

@section('mobile-hero')
<div class="auth-mobile-hero">
    <h1 class="auth-mobile-hero-title">
        Bon retour<br><span>parmi nous.</span>
    </h1>
    <p class="auth-mobile-hero-sub">
        Connectez-vous pour accéder à votre tableau de bord agricole.
    </p>
</div>
@endsection

@section('mobile-form')
<form method="POST" action="{{ route('connexion.store') }}">
    @csrf
    <div class="auth-mobile-field">
        <label class="auth-mobile-label" for="connexion_mobile_telephone">Numéro de téléphone</label>
        <input id="connexion_mobile_telephone" type="tel" name="telephone" value="{{ old('telephone') }}"
               placeholder="+229 XX XX XX XX"
               class="auth-mobile-input" required autofocus inputmode="tel"
               autocomplete="tel">
    </div>
    <div class="auth-mobile-field" style="margin-bottom:6px;">
        <label class="auth-mobile-label" for="connexion_mobile_pin">PIN (4 chiffres)</label>
        <input id="connexion_mobile_pin" type="password" name="pin" maxlength="4"
               inputmode="numeric" placeholder="• • • •"
               class="auth-mobile-pin" required
               autocomplete="current-password">
    </div>
    <button type="submit" class="auth-mobile-btn" style="margin-top:20px;">
        Se connecter →
    </button>
</form>
<div class="auth-mobile-link-row">
    Pas encore de compte ?
    <a href="{{ route('inscription') }}" class="auth-mobile-link">Créer un compte</a>
</div>
@endsection

{{-- ════ DESKTOP (inchangé) ════ --}}
@section('branding')
<div style="display:flex; align-items:center; gap:12px; margin-bottom:56px;">
    <div style="width:42px; height:42px; border-radius:12px;
                background:rgba(74,222,128,0.20); border:1px solid rgba(74,222,128,0.35);
                display:flex; align-items:center; justify-content:center; overflow:hidden;">
        <img src="{{ asset('images/logo-agrofinanceplus.png') }}" alt="Logo AgroFinance+" style="width:32px; height:32px; object-fit:contain; border-radius:8px;">
    </div>
    <div>
        <div style="font-family:'Space Grotesk',sans-serif; font-size:16px;
                    font-weight:700; color:white;">AgroFinance<span style="color:#4ade80;">+</span></div>
        <div style="font-family:'Inter',sans-serif; font-size:10px;
                    color:rgba(255,255,255,0.30); text-transform:uppercase; letter-spacing:0.12em;">Gestion agricole</div>
    </div>
</div>
<h1 style="font-family:'Space Grotesk',sans-serif; font-size:44px;
           font-weight:700; color:white; line-height:1.08;
           letter-spacing:-0.04em; margin:0 0 20px 0;">
    Bon retour<br>
    <span style="color:#4ade80;">parmi nous.</span>
</h1>
<p style="font-family:'Inter',sans-serif; font-size:15px;
          color:rgba(255,255,255,0.42); line-height:1.65;
          max-width:300px; margin:0 0 48px 0;">
    Vos données financières agricoles sont prêtes. Connectez-vous pour accéder à votre tableau de bord.
</p>
<div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; max-width:300px;">
    @foreach ([
        ['102 500+', 'Exploitants au Bénin'],
        ['8', 'Indicateurs financiers agricoles'],
        ['75 jours', 'Essai gratuit'],
        ['24/7', 'Accès hors ligne'],
    ] as [$stat, $label])
        <div style="padding:14px; background:rgba(255,255,255,0.04);
                    border:1px solid rgba(255,255,255,0.08); border-radius:10px;">
            <div style="font-family:'Space Grotesk',sans-serif; font-size:20px; font-weight:700;
                        color:#4ade80; letter-spacing:-0.02em; margin-bottom:3px;">{{ $stat }}</div>
            <div style="font-family:'Inter',sans-serif; font-size:11px;
                        color:rgba(255,255,255,0.38); line-height:1.3;">{{ $label }}</div>
        </div>
    @endforeach
</div>
@endsection

@section('form')
<div class="auth-form-title">Connexion</div>
<div class="auth-form-subtitle">Entrez votre numéro et votre PIN pour accéder à votre espace</div>

@if ($errors->any())
    <div class="auth-error" role="alert" aria-live="polite">
        @foreach ($errors->all() as $e)
            <div>• {{ $e }}</div>
        @endforeach
    </div>
@endif
@if (session('success'))
    <div class="auth-success" role="status">{{ session('success') }}</div>
@endif

<form method="POST" action="{{ route('connexion.store') }}">
    @csrf
    <div class="auth-field">
        <label class="auth-label" for="connexion_desktop_telephone">Numéro de téléphone</label>
        <input id="connexion_desktop_telephone" type="tel" name="telephone" value="{{ old('telephone') }}"
               placeholder="+229 XX XX XX XX"
               class="auth-input" required autofocus
               autocomplete="tel">
    </div>
    <div class="auth-field">
        <label class="auth-label" for="connexion_desktop_pin">PIN (4 chiffres)</label>
        <input id="connexion_desktop_pin" type="password" name="pin" maxlength="4"
               inputmode="numeric" placeholder="• • • •"
               class="pin-input" required
               autocomplete="current-password">
    </div>
    <button type="submit" class="auth-btn" style="margin-top:24px;">
        Se connecter
    </button>
</form>

<div style="text-align:center; margin-top:20px;
            font-family:'Inter',sans-serif; font-size:13px;
            color:rgba(255,255,255,0.38);">
    Pas encore de compte ?
    <a href="{{ route('inscription') }}" class="auth-link">Créer un compte</a>
</div>
@endsection
