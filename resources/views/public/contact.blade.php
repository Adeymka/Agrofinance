@extends('layouts.app-public')
@section('title', 'Contact — AgroFinance+')
@section('meta-description', 'Contactez AgroFinance+ — Cotonou, Bénin. WhatsApp, e-mail ou formulaire. Réponse sous 24 h.')

@push('styles')
<style>
  .contact-section { padding-left: clamp(1.25rem, 4vw, 3rem); padding-right: clamp(1.25rem, 4vw, 3rem); }
  .contact-grid { display: grid; grid-template-columns: 1fr 1.6fr; gap: 48px; align-items: start; }
  @media (max-width: 900px) {
    .contact-grid { grid-template-columns: 1fr; gap: 40px; }
  }
  .contact-select {
    width: 100%;
    padding: 12px 40px 12px 16px;
    background-color: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 12px;
    color: rgba(255, 255, 255, 0.88);
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    outline: none;
    box-sizing: border-box;
    appearance: none;
    -webkit-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24'%3E%3Cpath stroke='rgba(255,255,255,0.45)' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 18px;
  }
  .contact-select:focus {
    border-color: rgba(74, 222, 128, 0.5);
    background-color: rgba(255, 255, 255, 0.09);
  }
  .contact-select option { background: #0d1f0d; color: #fff; }
</style>
@endpush

@section('content')

<section class="contact-section" style="background:linear-gradient(180deg,#0A1500 0%,#0D1F0D 100%);
                padding-top:120px; padding-bottom:80px;">
  <div style="max-width:1100px; margin:0 auto;">

    {{-- En-tête --}}
    <div style="text-align:center; margin-bottom:64px;">
      <div style="display:inline-flex; align-items:center; gap:8px; margin-bottom:20px;
                  background:rgba(74,222,128,0.08);
                  border:1px solid rgba(74,222,128,0.18);
                  border-radius:999px; padding:6px 16px;
                  backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px);">
        <span style="font-family:'Inter',sans-serif; font-size:12px; font-weight:500;
                     color:rgba(74,222,128,0.80); letter-spacing:0.08em;
                     text-transform:uppercase;">Contact</span>
      </div>
      <h1 style="font-family:'Space Grotesk',sans-serif; font-size:clamp(32px,5vw,44px);
                 font-weight:700; color:white; letter-spacing:-0.04em;
                 line-height:1.08; margin:0 0 16px;">
        Parlons de votre<br>
        <span style="color:#4ade80;">exploitation.</span>
      </h1>
      <p style="font-family:'Inter',sans-serif; font-size:17px;
                color:rgba(255,255,255,0.45); max-width:500px;
                margin:0 auto; line-height:1.65;">
        Une question, un partenariat, un retour sur l'application.
        Nous répondons sous 24 heures.
      </p>
    </div>

    {{-- Layout 2 colonnes --}}
    <div class="contact-grid">

      {{-- ── COLONNE GAUCHE : Infos contact ────────────────────── --}}
      <div>

        {{-- Canaux de contact --}}
        <div style="display:flex; flex-direction:column; gap:14px; margin-bottom:40px;">
          @foreach([
            ['📱','WhatsApp','Réponse rapide (moins de 2h en journée)','https://wa.me/22900000000','Écrire sur WhatsApp','rgba(74,222,128,0.10)','rgba(74,222,128,0.20)'],
            ['📧','Email','contact@agrofinanceplus.bj','mailto:contact@agrofinanceplus.bj','Envoyer un email','rgba(255,255,255,0.06)','rgba(255,255,255,0.12)'],
            ['📍','Localisation','Cotonou, Bénin',null,null,'rgba(255,255,255,0.04)','rgba(255,255,255,0.08)'],
          ] as [$emoji,$label,$val,$href,$cta,$bg,$bd])
          <div style="background:{{ $bg }}; border:1px solid {{ $bd }};
                      border-radius:16px; padding:20px;
                      backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px);">
            <div style="display:flex; align-items:center; gap:12px; margin-bottom:10px;">
              <div style="width:38px; height:38px; border-radius:10px; flex-shrink:0;
                          background:rgba(255,255,255,0.06);
                          display:flex; align-items:center; justify-content:center;
                          font-size:18px;">
                {{ $emoji }}
              </div>
              <div>
                <div style="font-family:'Inter',sans-serif; font-size:11px; font-weight:600;
                            color:rgba(255,255,255,0.30); text-transform:uppercase;
                            letter-spacing:0.10em; margin-bottom:2px;">
                  {{ $label }}
                </div>
                <div style="font-family:'Inter',sans-serif; font-size:14px;
                            color:rgba(255,255,255,0.70);">
                  {{ $val }}
                </div>
              </div>
            </div>
            @if($cta && $href)
            <a href="{{ $href }}"
               style="display:inline-flex; align-items:center; gap:6px;
                      font-family:'Inter',sans-serif; font-size:12px; font-weight:600;
                      color:rgba(74,222,128,0.75); text-decoration:none;
                      transition:color 0.2s;"
               onmouseover="this.style.color='#4ade80'"
               onmouseout="this.style.color='rgba(74,222,128,0.75)'"
               @if(str_starts_with((string) $href, 'https://wa.me')) target="_blank" rel="noopener noreferrer" @endif>
              {{ $cta }} →
            </a>
            @endif
          </div>
          @endforeach
        </div>

        {{-- FAQ rapide --}}
        <div>
          <p style="font-family:'Inter',sans-serif; font-size:11px; font-weight:600;
                    color:rgba(255,255,255,0.28); text-transform:uppercase;
                    letter-spacing:0.12em; margin:0 0 16px;">
            Questions fréquentes
          </p>
          <div style="display:flex; flex-direction:column; gap:12px;">
            @foreach([
              ['Comment payer sans carte bancaire ?',
               'MTN MoMo ou Moov Money directement depuis votre téléphone.'],
              ['Mes données sont-elles sécurisées ?',
               'Oui. Hébergées sur serveur sécurisé. Accès uniquement à vous.'],
              ['Que se passe-t-il après les 75 jours ?',
               'Vous choisissez un plan ou vos données restent consultables.'],
              ['Fonctionne sur quel téléphone ?',
               'Tout téléphone avec un navigateur Chrome ou Firefox.'],
              ['La microfinance accepte le rapport PDF ?',
               'Oui, format professionnel avec les indicateurs financiers agricoles.'],
            ] as [$q, $r])
            <div style="border-bottom:1px solid rgba(255,255,255,0.06);
                        padding-bottom:12px;">
              <button type="button"
                      onclick="this.nextElementSibling.style.display =
                               this.nextElementSibling.style.display === 'none'
                               ? 'block' : 'none'"
                      style="width:100%; background:none; border:none; cursor:pointer;
                             display:flex; align-items:center; justify-content:space-between;
                             gap:12px; padding:0; text-align:left;">
                <span style="font-family:'Inter',sans-serif; font-size:13px;
                             font-weight:500; color:rgba(255,255,255,0.70);">
                  {{ $q }}
                </span>
                <span style="color:rgba(74,222,128,0.60); flex-shrink:0; font-size:16px;">+</span>
              </button>
              <div style="display:none; margin-top:8px; padding-left:2px;">
                <p style="font-family:'Inter',sans-serif; font-size:13px;
                          color:rgba(255,255,255,0.40); margin:0; line-height:1.55;">
                  {{ $r }}
                </p>
              </div>
            </div>
            @endforeach
          </div>
        </div>

      </div>

      {{-- ── COLONNE DROITE : Formulaire ────────────────────────── --}}
      <div style="background:rgba(255,255,255,0.05);
                  border:1px solid rgba(255,255,255,0.10);
                  border-radius:24px; padding:40px 36px;
                  backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px);
                  box-shadow:0 4px 32px rgba(0,0,0,0.2);">

        <h2 style="font-family:'Space Grotesk',sans-serif; font-size:22px;
                   font-weight:600; color:white; letter-spacing:-0.02em;
                   margin:0 0 6px;">
          Envoyer un message
        </h2>
        <p style="font-family:'Inter',sans-serif; font-size:13px;
                  color:rgba(255,255,255,0.35); margin:0 0 32px;">
          Nous répondons sous 24 heures.
        </p>

        {{-- Message succès --}}
        @if(session('success'))
        <div style="background:rgba(74,222,128,0.10);
                    border:1px solid rgba(74,222,128,0.25);
                    border-radius:12px; padding:14px 18px;
                    margin-bottom:24px; display:flex; align-items:center; gap:10px;">
          <span style="font-size:18px;">✓</span>
          <p style="font-family:'Inter',sans-serif; font-size:14px;
                    color:rgba(74,222,128,0.90); margin:0;">
            {{ session('success') }}
          </p>
        </div>
        @endif

        {{-- Erreurs globales --}}
        @if($errors->any())
        <div style="background:rgba(239,68,68,0.10);
                    border:1px solid rgba(239,68,68,0.25);
                    border-radius:12px; padding:14px 18px; margin-bottom:24px;">
          @foreach($errors->all() as $e)
          <p style="font-family:'Inter',sans-serif; font-size:13px;
                    color:#fca5a5; margin:0 0 4px;">• {{ $e }}</p>
          @endforeach
        </div>
        @endif

        {{-- Formulaire --}}
        <form method="POST" action="{{ route('contact.envoyer') }}">
          @csrf

          {{-- Nom --}}
          <div style="margin-bottom:22px;">
            <label for="nom" style="display:block; font-family:'Inter',sans-serif;
                          font-size:11px; font-weight:600;
                          color:rgba(255,255,255,0.38); text-transform:uppercase;
                          letter-spacing:0.10em; margin-bottom:8px;">
              Votre nom
            </label>
            <input type="text" name="nom" id="nom" value="{{ old('nom') }}"
                   placeholder="Ex : Donald Adjinda"
                   required maxlength="100"
                   style="width:100%; padding:12px 16px;
                          background:rgba(255,255,255,0.06);
                          border:1px solid rgba(255,255,255,0.12);
                          border-radius:12px; color:white;
                          font-family:'Inter',sans-serif; font-size:14px;
                          outline:none; box-sizing:border-box; transition:all 0.2s;"
                   onfocus="this.style.borderColor='rgba(74,222,128,0.50)';this.style.background='rgba(255,255,255,0.09)'"
                   onblur="this.style.borderColor='rgba(255,255,255,0.12)';this.style.background='rgba(255,255,255,0.06)'">
          </div>

          {{-- Téléphone ou email --}}
          <div style="margin-bottom:22px;">
            <label for="contact-input" style="display:block; font-family:'Inter',sans-serif;
                          font-size:11px; font-weight:600;
                          color:rgba(255,255,255,0.38); text-transform:uppercase;
                          letter-spacing:0.10em; margin-bottom:8px;">
              Téléphone ou Email
            </label>
            <input type="text" name="contact" id="contact-input" value="{{ old('contact') }}"
                   placeholder="+229 67 XX XX XX ou email@exemple.com"
                   required maxlength="100"
                   style="width:100%; padding:12px 16px;
                          background:rgba(255,255,255,0.06);
                          border:1px solid rgba(255,255,255,0.12);
                          border-radius:12px; color:white;
                          font-family:'Inter',sans-serif; font-size:14px;
                          outline:none; box-sizing:border-box; transition:all 0.2s;"
                   onfocus="this.style.borderColor='rgba(74,222,128,0.50)';this.style.background='rgba(255,255,255,0.09)'"
                   onblur="this.style.borderColor='rgba(255,255,255,0.12)';this.style.background='rgba(255,255,255,0.06)'">
          </div>

          {{-- Sujet --}}
          <div style="margin-bottom:22px;">
            <label for="sujet" style="display:block; font-family:'Inter',sans-serif;
                          font-size:11px; font-weight:600;
                          color:rgba(255,255,255,0.38); text-transform:uppercase;
                          letter-spacing:0.10em; margin-bottom:8px;">
              Sujet
            </label>
            <select name="sujet" id="sujet" required class="contact-select">
              <option value="" disabled {{ old('sujet') ? '' : 'selected' }}>
                Choisissez un sujet
              </option>
              @foreach([
                'Essai gratuit et inscription',
                'Problème technique',
                'Abonnement et paiement',
                'Partenariat ou collaboration',
                'Formation ou démo',
                'Autre',
              ] as $opt)
              <option value="{{ $opt }}" {{ old('sujet') === $opt ? 'selected' : '' }}>
                {{ $opt }}
              </option>
              @endforeach
            </select>
          </div>

          {{-- Message --}}
          <div style="margin-bottom:28px;">
            <label for="message" style="display:block; font-family:'Inter',sans-serif;
                          font-size:11px; font-weight:600;
                          color:rgba(255,255,255,0.38); text-transform:uppercase;
                          letter-spacing:0.10em; margin-bottom:8px;">
              Message
            </label>
            <textarea name="message" id="message" rows="5" required maxlength="1000"
                      placeholder="Décrivez votre question ou votre besoin..."
                      style="width:100%; padding:12px 16px;
                             background:rgba(255,255,255,0.06);
                             border:1px solid rgba(255,255,255,0.12);
                             border-radius:12px; color:white;
                             font-family:'Inter',sans-serif; font-size:14px;
                             outline:none; resize:vertical;
                             box-sizing:border-box; transition:all 0.2s;
                             min-height:120px;"
                      onfocus="this.style.borderColor='rgba(74,222,128,0.50)';this.style.background='rgba(255,255,255,0.09)'"
                      onblur="this.style.borderColor='rgba(255,255,255,0.12)';this.style.background='rgba(255,255,255,0.06)'">{{ old('message') }}</textarea>
          </div>

          {{-- Bouton submit --}}
          <button type="submit"
                  style="width:100%; padding:14px 24px; cursor:pointer;
                         background:#16a34a; color:white;
                         border:1px solid rgba(74,222,128,0.30);
                         border-radius:12px; font-family:'Inter',sans-serif;
                         font-size:15px; font-weight:700;
                         transition:all 0.2s;"
                  onmouseover="this.style.background='#15803d';this.style.boxShadow='0 8px 24px rgba(22,163,74,0.35)';this.style.transform='translateY(-1px)'"
                  onmouseout="this.style.background='#16a34a';this.style.boxShadow='none';this.style.transform='translateY(0)'">
            Envoyer le message →
          </button>

          <p style="font-family:'Inter',sans-serif; font-size:12px;
                    color:rgba(255,255,255,0.25); text-align:center;
                    margin:16px 0 0;">
            Réponse sous 24h · Vos données ne sont pas partagées
          </p>

        </form>
      </div>

    </div>
  </div>
</section>

@endsection
