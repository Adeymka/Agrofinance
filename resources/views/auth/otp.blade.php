@extends('layouts.app-auth')
@section('title', 'Vérification OTP')

{{-- ════ MOBILE ════ --}}
@section('mobile-topbar-link')
    {{-- pas de lien secondaire sur cette étape --}}
@endsection

@section('mobile-hero')
<div class="auth-mobile-hero">
    <h1 class="auth-mobile-hero-title">
        Vérifiez<br><span>votre numéro.</span>
    </h1>
    <p class="auth-mobile-hero-sub">
        Un code à 6 chiffres a été envoyé au
        <strong style="color:rgba(255,255,255,0.75);">{{ $telephone }}</strong>
    </p>
</div>
@endsection

@section('mobile-steps')
<div class="auth-mobile-steps">
    <div class="auth-mobile-step-dot done"></div>
    <div class="auth-mobile-step-dot active"></div>
    <div class="auth-mobile-step-dot"></div>
    <span class="auth-mobile-step-label active">Étape 2 / 3 — Vérification</span>
</div>
@endsection

@if(app()->isLocal())
@section('mobile-dev')
<div class="auth-mobile-dev">
    <strong>Dev :</strong> code dans storage/logs/laravel.log
</div>
@endsection
@endif

@section('mobile-form')

{{-- Indicateur SMS --}}
<div style="display:flex; align-items:center; gap:10px; padding:12px 14px;
            background:rgba(74,222,128,0.06); border:1px solid rgba(74,222,128,0.14);
            border-radius:14px; margin-bottom:20px;">
    <div style="font-size:22px; flex-shrink:0;">🔐</div>
    <div>
        <div style="font-family:'Inter',sans-serif; font-size:12px; font-weight:600;
                    color:rgba(255,255,255,0.70); margin-bottom:2px;">Code valable 10 minutes</div>
        <div style="font-family:'Inter',sans-serif; font-size:11px; color:rgba(255,255,255,0.28);">
            5 tentatives max avant blocage
        </div>
    </div>
</div>

<form method="POST" action="{{ route('verification.otp.submit') }}">
    @csrf
    <div class="auth-mobile-field" style="margin-bottom:24px;">
        <label class="auth-mobile-label">Code de vérification</label>
        <input type="text" name="code" maxlength="6" inputmode="numeric" pattern="[0-9]{6}"
               placeholder="— — — — — —"
               class="auth-mobile-otp"
               autocomplete="one-time-code" required autofocus>
    </div>
    <button type="submit" class="auth-mobile-btn">Vérifier →</button>
</form>

<form method="POST" action="{{ route('renvoyer.otp') }}">
    @csrf
    <button type="submit" class="auth-mobile-ghost-btn">
        Pas reçu ? <span>Renvoyer le code</span>
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
    Vérifiez<br>votre <span style="color:#4ade80;">numéro.</span>
</h1>
<p style="font-family:'Inter',sans-serif; font-size:15px; color:rgba(255,255,255,0.42); line-height:1.65; max-width:300px; margin:0 0 36px 0;">
    Un code de vérification à 6 chiffres a été envoyé au numéro
    <span style="color:rgba(255,255,255,0.75); font-weight:600;">{{ $telephone }}</span>
</p>
<div style="display:flex; align-items:center; gap:12px; padding:16px; background:rgba(74,222,128,0.08); border:1px solid rgba(74,222,128,0.18); border-radius:12px; max-width:320px;">
    <div style="font-size:24px;">🔐</div>
    <div>
        <div style="font-family:'Inter',sans-serif; font-size:13px; font-weight:600; color:rgba(255,255,255,0.80); margin-bottom:2px;">Code valable 10 minutes</div>
        <div style="font-family:'Inter',sans-serif; font-size:12px; color:rgba(255,255,255,0.35);">5 tentatives maximum avant blocage</div>
    </div>
</div>
@if (app()->isLocal())
    <div style="margin-top:20px; padding:12px 16px; background:rgba(245,158,11,0.10); border:1px solid rgba(245,158,11,0.25); border-radius:10px; max-width:320px;">
        <div style="font-family:'Inter',sans-serif; font-size:12px; font-weight:600; color:rgba(245,158,11,0.80); margin-bottom:4px;">Mode développement</div>
        <div style="font-family:'Inter',sans-serif; font-size:11px; color:rgba(255,255,255,0.35);">Consultez : storage/logs/laravel.log</div>
    </div>
@endif
@endsection

@section('form')
<div class="step-indicator">
    <div class="step-item"><div class="step-circle done"><svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></div><div class="step-label">Infos</div></div>
    <div class="step-line done"></div>
    <div class="step-item"><div class="step-circle active">2</div><div class="step-label active">OTP</div></div>
    <div class="step-line"></div>
    <div class="step-item"><div class="step-circle">3</div><div class="step-label">PIN</div></div>
</div>
<div class="auth-form-title">Code de vérification</div>
<div class="auth-form-subtitle">Entrez les 6 chiffres reçus par SMS</div>
@if (session('info'))
    <div class="auth-success" style="color:#fcd34d; border-color:rgba(251,191,36,0.35); background:rgba(245,158,11,0.12);">{{ session('info') }}</div>
@endif
@if ($errors->any())
    <div class="auth-error">@foreach ($errors->all() as $e)<div>• {{ $e }}</div>@endforeach</div>
@endif
@if (session('success'))
    <div class="auth-success">{{ session('success') }}</div>
@endif
<form method="POST" action="{{ route('verification.otp.submit') }}">
    @csrf
    <div class="auth-field" style="margin-bottom:36px;">
        <input type="text" name="code" maxlength="6" inputmode="numeric" pattern="[0-9]{6}"
               placeholder="— — — — — —" class="otp-input" autocomplete="one-time-code" required autofocus>
    </div>
    <button type="submit" class="auth-btn">Vérifier le code</button>
</form>
<form method="POST" action="{{ route('renvoyer.otp') }}" style="margin-top:16px;">
    @csrf
    <button type="submit" style="width:100%; background:transparent; border:none; font-family:'Inter',sans-serif; font-size:13px; color:rgba(255,255,255,0.38); cursor:pointer; padding:10px;">
        Pas reçu ? <span class="auth-link" style="background:none;border:none;padding:0;font:inherit;">Renvoyer le code</span>
    </button>
</form>
@endsection
