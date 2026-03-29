@extends('layouts.app-auth')
@section('title', 'Créer votre PIN')

{{-- ════ MOBILE ════ --}}
@section('mobile-topbar-link')
    {{-- pas de lien sur la dernière étape --}}
@endsection

@section('mobile-hero')
<div class="auth-mobile-hero">
    <h1 class="auth-mobile-hero-title">
        Dernière<br><span>étape.</span>
    </h1>
    <p class="auth-mobile-hero-sub">
        Choisissez un PIN à 4 chiffres. Vous l'utiliserez à chaque connexion.
    </p>
</div>
@endsection

@section('mobile-steps')
<div class="auth-mobile-steps">
    <div class="auth-mobile-step-dot done"></div>
    <div class="auth-mobile-step-dot done"></div>
    <div class="auth-mobile-step-dot active"></div>
    <span class="auth-mobile-step-label active">Étape 3 / 3 — Sécurité</span>
</div>
@endsection

@section('mobile-form')

{{-- Conseils PIN --}}
<div style="display:flex; flex-direction:column; gap:8px; padding:14px;
            background:rgba(74,222,128,0.05); border:1px solid rgba(74,222,128,0.12);
            border-radius:14px; margin-bottom:20px;">
    @foreach ([
        ['✅', 'Facile à retenir pour vous'],
        ['✅', 'Difficile à deviner pour les autres'],
        ['❌', 'Évitez 1234, 0000, votre date de naissance'],
    ] as [$icon, $text])
        <div style="display:flex; align-items:center; gap:8px;
                    font-family:'Inter',sans-serif; font-size:12px;
                    color:rgba(255,255,255,0.42);">
            <span>{{ $icon }}</span> {{ $text }}
        </div>
    @endforeach
</div>

<form method="POST" action="{{ route('creer.pin.store') }}">
    @csrf
    <div class="auth-mobile-field">
        <label class="auth-mobile-label" for="pin_mobile">Votre PIN</label>
        <input id="pin_mobile" type="password" name="pin" maxlength="4"
               inputmode="numeric" placeholder="• • • •"
               class="auth-mobile-pin" required autofocus autocomplete="new-password">
    </div>
    <div class="auth-mobile-field" style="margin-bottom:8px;">
        <label class="auth-mobile-label" for="pin_mobile_confirmation">Confirmer le PIN</label>
        <input id="pin_mobile_confirmation" type="password" name="pin_confirmation" maxlength="4"
               inputmode="numeric" placeholder="• • • •"
               class="auth-mobile-pin" required autocomplete="new-password">
    </div>
    <button type="submit" class="auth-mobile-btn" style="margin-top:16px;">
        Créer mon compte ✓
    </button>
</form>

@endsection

{{-- ════ DESKTOP (inchangé) ════ --}}
@section('branding')
<div style="display:flex; align-items:center; gap:12px; margin-bottom:48px;">
    <div style="width:42px; height:42px; border-radius:12px;
                background:rgba(74,222,128,0.20); border:1px solid rgba(74,222,128,0.35);
                display:flex; align-items:center; justify-content:center; font-size:20px;">🌱</div>
    <div>
        <div style="font-family:'Space Grotesk',sans-serif; font-size:16px; font-weight:700; color:white;">AgroFinance<span style="color:#4ade80;">+</span></div>
        <div style="font-family:'Inter',sans-serif; font-size:10px; color:rgba(255,255,255,0.30); text-transform:uppercase; letter-spacing:0.12em;">Gestion agricole</div>
    </div>
</div>
<h1 style="font-family:'Space Grotesk',sans-serif; font-size:40px; font-weight:700; color:white; line-height:1.1; letter-spacing:-0.04em; margin:0 0 20px 0;">
    Dernière<br><span style="color:#4ade80;">étape.</span>
</h1>
<p style="font-family:'Inter',sans-serif; font-size:15px; color:rgba(255,255,255,0.42); line-height:1.65; max-width:300px; margin:0 0 36px 0;">
    Choisissez un PIN à 4 chiffres. Vous l'utiliserez à chaque connexion à la place d'un mot de passe.
</p>
<div style="display:flex; flex-direction:column; gap:10px; max-width:300px;">
    @foreach ([
        ['✅', 'Facile à retenir pour vous'],
        ['✅', 'Difficile à deviner pour les autres'],
        ['❌', 'Évitez 1234, 0000, votre date de naissance'],
    ] as [$icon, $text])
        <div style="display:flex; align-items:center; gap:10px; font-family:'Inter',sans-serif; font-size:13px; color:rgba(255,255,255,0.45);">
            <span>{{ $icon }}</span> {{ $text }}
        </div>
    @endforeach
</div>
@endsection

@section('form')
<div class="step-indicator">
    <div class="step-item"><div class="step-circle done"><svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></div><div class="step-label">Infos</div></div>
    <div class="step-line done"></div>
    <div class="step-item"><div class="step-circle done"><svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></div><div class="step-label">OTP</div></div>
    <div class="step-line done"></div>
    <div class="step-item"><div class="step-circle active">3</div><div class="step-label active">PIN</div></div>
</div>
<div class="auth-form-title">Créez votre PIN</div>
<div class="auth-form-subtitle">4 chiffres — rapide et sécurisé</div>
@if ($errors->any())
    <div class="auth-error" role="alert" aria-live="polite">@foreach ($errors->all() as $e)<div>• {{ $e }}</div>@endforeach</div>
@endif
<form method="POST" action="{{ route('creer.pin.store') }}">
    @csrf
    <div class="auth-field">
        <label class="auth-label" for="pin_desktop">Votre PIN</label>
        <input id="pin_desktop" type="password" name="pin" maxlength="4" inputmode="numeric" placeholder="• • • •" class="pin-input" required autofocus autocomplete="new-password">
    </div>
    <div class="auth-field">
        <label class="auth-label" for="pin_desktop_confirmation">Confirmer le PIN</label>
        <input id="pin_desktop_confirmation" type="password" name="pin_confirmation" maxlength="4" inputmode="numeric" placeholder="• • • •" class="pin-input" required autocomplete="new-password">
    </div>
    <button type="submit" class="auth-btn" style="margin-top:16px;">Créer mon compte ✓</button>
</form>
@endsection
