@extends('layouts.app-public')
@section('title', 'À propos — AgroFinance+')
@section('meta-description', 'AgroFinance+ : mission, équipe et valeurs. Gestion financière agricole conçue au Bénin pour les exploitants béninois.')

@push('styles')
<style>
  .a-propos-hero-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 80px; align-items: center; }
  .a-propos-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
  .a-propos-mission-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 80px; align-items: center; }
  .a-propos-tech-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
  .a-propos-section { padding-left: clamp(1.25rem, 4vw, 3rem); padding-right: clamp(1.25rem, 4vw, 3rem); }
  @media (max-width: 900px) {
    .a-propos-hero-grid, .a-propos-mission-grid { grid-template-columns: 1fr; gap: 48px; }
    .a-propos-tech-grid { grid-template-columns: repeat(2, 1fr); }
  }
  @media (max-width: 520px) {
    .a-propos-stats { grid-template-columns: 1fr; }
    .a-propos-tech-grid { grid-template-columns: 1fr; }
  }
</style>
@endpush

@section('content')

{{-- ══ HERO ══════════════════════════════════════════════════════ --}}
<section class="a-propos-section" style="background:linear-gradient(180deg,#0A1500 0%,#0D1F0D 100%);
                padding-top:120px; padding-bottom:80px;">
  <div style="max-width:1100px; margin:0 auto;">

    {{-- Label --}}
    <div style="display:inline-flex; align-items:center; gap:8px; margin-bottom:20px;
                background:rgba(74,222,128,0.08); border:1px solid rgba(74,222,128,0.18);
                border-radius:999px; padding:6px 16px;
                backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px);">
      <span style="font-family:'Inter',sans-serif; font-size:12px; font-weight:500;
                   color:rgba(74,222,128,0.80); letter-spacing:0.08em;
                   text-transform:uppercase;">À propos</span>
    </div>

    <div class="a-propos-hero-grid">

      {{-- Gauche : Titre + mission --}}
      <div>
        <h1 style="font-family:'Space Grotesk',sans-serif; font-size:clamp(32px,5vw,48px);
                   font-weight:700; color:white; letter-spacing:-0.04em;
                   line-height:1.08; margin:0 0 24px;">
          Conçu au Bénin,<br>
          pour les <span style="color:#4ade80;">exploitants béninois.</span>
        </h1>
        <p style="font-family:'Inter',sans-serif; font-size:17px;
                  color:rgba(255,255,255,0.50); line-height:1.70;
                  margin:0; max-width:480px;">
          AgroFinance+ est né d'un constat simple : plus de 102 500 exploitants
          agricoles instruits au Bénin ne peuvent pas répondre à la question
          fondamentale — <strong style="color:rgba(255,255,255,0.80);">
          mon exploitation est-elle rentable ?</strong>
        </p>
      </div>

      {{-- Droite : Chiffres clés --}}
      <div class="a-propos-stats">
        @foreach([
          ['102 500+', 'Exploitants agricoles instruits au Bénin', '#4ade80'],
          ['8',        'Indicateurs financiers agricoles calculés automatiquement', '#4ade80'],
          ['75 jours', 'Essai gratuit sans carte bancaire', '#fbbf24'],
          ['0',        'Connaissance comptable requise', '#4ade80'],
        ] as [$val, $label, $color])
        <div style="background:rgba(255,255,255,0.05);
                    border:1px solid rgba(255,255,255,0.09);
                    border-radius:16px; padding:24px 20px;
                    backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px);
                    box-shadow:0 4px 24px rgba(0,0,0,0.15);">
          <div style="font-family:'Space Grotesk',sans-serif; font-size:32px;
                      font-weight:700; color:{{ $color }};
                      letter-spacing:-0.03em; margin-bottom:8px;">
            {{ $val }}
          </div>
          <div style="font-family:'Inter',sans-serif; font-size:13px;
                      color:rgba(255,255,255,0.42); line-height:1.50;">
            {{ $label }}
          </div>
        </div>
        @endforeach
      </div>

    </div>
  </div>
</section>

{{-- Séparateur vague --}}
<div style="background:#0A1500; line-height:0; margin-bottom:-1px;">
  <svg viewBox="0 0 1440 60" xmlns="http://www.w3.org/2000/svg"
       style="display:block;width:100%;">
    <path d="M0,30 C480,60 960,0 1440,30 L1440,60 L0,60 Z" fill="#0D1F0D"/>
  </svg>
</div>

{{-- ══ LA MISSION ══════════════════════════════════════════════════ --}}
<section class="a-propos-section" style="background:#0D1F0D; padding-top:100px; padding-bottom:100px;">
  <div style="max-width:1100px; margin:0 auto;" class="a-propos-mission-grid">

    {{-- Gauche : Texte mission --}}
    <div>
      <div style="display:inline-flex; align-items:center; gap:8px; margin-bottom:20px;
                  background:rgba(255,255,255,0.04);
                  border:1px solid rgba(255,255,255,0.08);
                  border-radius:999px; padding:6px 16px;
                  backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px);">
        <span style="font-family:'Inter',sans-serif; font-size:12px; font-weight:500;
                     color:rgba(255,255,255,0.35); letter-spacing:0.08em;
                     text-transform:uppercase;">Notre mission</span>
      </div>

      <h2 style="font-family:'Space Grotesk',sans-serif; font-size:clamp(28px,4vw,36px);
                 font-weight:700; color:white; letter-spacing:-0.03em;
                 line-height:1.15; margin:0 0 20px;">
        Rendre la gestion financière<br>
        <span style="color:#4ade80;">accessible à tous</span>
      </h2>

      <p style="font-family:'Inter',sans-serif; font-size:16px;
                color:rgba(255,255,255,0.50); line-height:1.70; margin:0 0 20px;">
        Les outils de gestion financière agricole existants sont soit trop complexes,
        soit inadaptés au contexte africain. Ils supposent une connexion internet stable,
        une carte bancaire, et des connaissances comptables avancées.
      </p>
      <p style="font-family:'Inter',sans-serif; font-size:16px;
                color:rgba(255,255,255,0.50); line-height:1.70; margin:0;">
        AgroFinance+ a été conçu pour être utilisé sur un téléphone Android
        d'entrée de gamme, payé avec MTN MoMo, et compris par un exploitant
        sans formation comptable. Les indicateurs financiers agricoles issus des
        enseignements du
        <strong style="color:rgba(255,255,255,0.75);">
        Professeur Aoudji (agronomie L2S4)</strong>
        sont calculés automatiquement. Vous saisissez, l'application calcule.
      </p>
    </div>

    {{-- Droite : Les 3 valeurs --}}
    <div style="display:flex; flex-direction:column; gap:16px;">
      @foreach([
        ['🎯','Simplicité avant tout',
         '30 secondes pour saisir une transaction. Pas de formation nécessaire. Une interface pensée pour le terrain.'],
        ['🌍','Ancrage local',
         'Conçu à Cotonou, pour le Bénin. Catégories adaptées aux réalités locales. Paiement Mobile Money uniquement.'],
        ['📊','Rigueur scientifique',
         'Indicateurs financiers agricoles alignés sur les cours d\'agronomie. Des formules cohérentes avec l\'enseignement universitaire béninois.'],
      ] as [$emoji, $titre, $texte])
      <div style="display:flex; align-items:flex-start; gap:16px; padding:20px;
                  background:rgba(255,255,255,0.04);
                  border:1px solid rgba(255,255,255,0.08);
                  border-radius:16px;
                  backdrop-filter:blur(14px); -webkit-backdrop-filter:blur(14px);
                  transition:all 0.2s;"
           onmouseover="this.style.background='rgba(255,255,255,0.07)'"
           onmouseout="this.style.background='rgba(255,255,255,0.04)'">
        <div style="width:44px; height:44px; border-radius:12px; flex-shrink:0;
                    background:rgba(74,222,128,0.10);
                    border:1px solid rgba(74,222,128,0.20);
                    display:flex; align-items:center; justify-content:center;
                    font-size:20px;">
          {{ $emoji }}
        </div>
        <div>
          <div style="font-family:'Space Grotesk',sans-serif; font-size:15px;
                      font-weight:600; color:rgba(255,255,255,0.88);
                      letter-spacing:-0.015em; margin-bottom:6px;">
            {{ $titre }}
          </div>
          <div style="font-family:'Inter',sans-serif; font-size:13px;
                      color:rgba(255,255,255,0.42); line-height:1.55;">
            {{ $texte }}
          </div>
        </div>
      </div>
      @endforeach
    </div>

  </div>
</section>

{{-- Ligne séparateur --}}
<div style="max-width:900px; margin:0 auto; height:1px;
            background:linear-gradient(to right, transparent,
            rgba(74,222,128,0.20) 30%, rgba(74,222,128,0.20) 70%, transparent);">
</div>

{{-- ══ L'ÉQUIPE ════════════════════════════════════════════════════ --}}
<section class="a-propos-section" style="background:#0D1F0D; padding-top:100px; padding-bottom:100px;">
  <div style="max-width:800px; margin:0 auto; text-align:center;">

    <div style="display:inline-flex; align-items:center; gap:8px; margin-bottom:20px;
                background:rgba(255,255,255,0.04);
                border:1px solid rgba(255,255,255,0.08);
                border-radius:999px; padding:6px 16px;
                backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px);">
      <span style="font-family:'Inter',sans-serif; font-size:12px; font-weight:500;
                   color:rgba(255,255,255,0.35); letter-spacing:0.08em;
                   text-transform:uppercase;">L'équipe</span>
    </div>

    <h2 style="font-family:'Space Grotesk',sans-serif; font-size:clamp(28px,4vw,36px);
               font-weight:700; color:white; letter-spacing:-0.03em;
               margin:0 0 16px;">
      Construit avec passion<br>à <span style="color:#4ade80;">Cotonou, Bénin</span>
    </h2>
    <p style="font-family:'Inter',sans-serif; font-size:16px;
              color:rgba(255,255,255,0.45); line-height:1.70;
              max-width:600px; margin:0 auto 56px;">
      AgroFinance+ est un projet de fin de formation développé dans le cadre
      de la certification Développeur Web Full-Stack à l'EIG Cotonou.
    </p>

    {{-- Card auteur --}}
    <div style="display:inline-flex; flex-direction:column; align-items:center;
                background:rgba(255,255,255,0.06);
                border:1px solid rgba(74,222,128,0.20);
                border-radius:24px; padding:40px 48px; max-width:420px;
                backdrop-filter:blur(20px); -webkit-backdrop-filter:blur(20px);
                box-shadow:0 8px 40px rgba(0,0,0,0.25);">

      {{-- Avatar avec initiales --}}
      <div style="width:80px; height:80px; border-radius:50%; margin-bottom:20px;
                  background:linear-gradient(135deg,rgba(74,222,128,0.25),rgba(27,94,32,0.50));
                  border:2px solid rgba(74,222,128,0.35);
                  display:flex; align-items:center; justify-content:center;
                  font-family:'Space Grotesk',sans-serif; font-size:28px;
                  font-weight:700; color:#4ade80;">
        DA
      </div>

      <div style="font-family:'Space Grotesk',sans-serif; font-size:22px;
                  font-weight:700; color:white; letter-spacing:-0.02em;
                  margin-bottom:6px;">
        Donald Adeynka ADJINDA
      </div>

      <div style="font-family:'Inter',sans-serif; font-size:13px;
                  color:rgba(74,222,128,0.80); font-weight:500; margin-bottom:20px;">
        Développeur Web Full-Stack
      </div>

      {{-- Tags --}}
      <div style="display:flex; flex-wrap:wrap; gap:8px;
                  justify-content:center; margin-bottom:24px;">
        @foreach (['Laravel','Blade','Tailwind CSS','Alpine.js','PHP','MySQL'] as $tag)
        <span style="font-family:'Inter',sans-serif; font-size:11px; font-weight:600;
                     background:rgba(255,255,255,0.07);
                     border:1px solid rgba(255,255,255,0.12);
                     border-radius:999px; padding:4px 12px;
                     color:rgba(255,255,255,0.55);">
          {{ $tag }}
        </span>
        @endforeach
      </div>

      <div style="height:1px; background:rgba(255,255,255,0.08);
                  width:100%; margin-bottom:20px;"></div>

      <div style="font-family:'Inter',sans-serif; font-size:13px;
                  color:rgba(255,255,255,0.38); line-height:1.60; text-align:center;">
        Formation : Certification Développeur Web Full-Stack<br>
        <strong style="color:rgba(255,255,255,0.55);">EIG Cotonou, Bénin — 2025-2026</strong>
      </div>

    </div>
  </div>
</section>

{{-- Ligne séparateur --}}
<div style="max-width:900px; margin:0 auto; height:1px;
            background:linear-gradient(to right, transparent,
            rgba(255,255,255,0.08) 30%, rgba(255,255,255,0.08) 70%, transparent);">
</div>

{{-- ══ LA TECHNOLOGIE ══════════════════════════════════════════════ --}}
<section class="a-propos-section" style="background:#0D1F0D; padding-top:100px; padding-bottom:100px;">
  <div style="max-width:1100px; margin:0 auto; text-align:center;">

    <h2 style="font-family:'Space Grotesk',sans-serif; font-size:32px;
               font-weight:700; color:white; letter-spacing:-0.03em;
               margin:0 0 16px;">
      Une stack professionnelle
    </h2>
    <p style="font-family:'Inter',sans-serif; font-size:15px;
              color:rgba(255,255,255,0.38); margin:0 0 48px;">
      Construite pour durer et évoluer.
    </p>

    <div class="a-propos-tech-grid">
      @foreach([
        ['🐘','Laravel 11',    'Backend robuste et sécurisé'],
        ['🗄️','MySQL',         'Base de données relationnelle'],
        ['🎨','Tailwind CSS v4','Interface moderne et rapide'],
        ['🔒','Sanctum',       'Authentification par tokens'],
        ['📄','DomPDF',        'Génération de rapports PDF'],
        ['💳','FedaPay',       'Paiement Mobile Money'],
        ['🧩','Alpine.js',     'Interactivité légère côté client'],
        ['⚡','Vite',          'Build rapide des assets front-end'],
      ] as [$emoji, $nom, $desc])
      <div style="background:rgba(255,255,255,0.04);
                  border:1px solid rgba(255,255,255,0.08);
                  border-radius:14px; padding:20px 16px; text-align:center;
                  backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px);
                  transition:all 0.2s;"
           onmouseover="this.style.background='rgba(255,255,255,0.08)';this.style.borderColor='rgba(74,222,128,0.20)'"
           onmouseout="this.style.background='rgba(255,255,255,0.04)';this.style.borderColor='rgba(255,255,255,0.08)'">
        <div style="font-size:24px; margin-bottom:10px;">{{ $emoji }}</div>
        <div style="font-family:'Space Grotesk',sans-serif; font-size:14px;
                    font-weight:600; color:rgba(255,255,255,0.85); margin-bottom:4px;">
          {{ $nom }}
        </div>
        <div style="font-family:'Inter',sans-serif; font-size:12px;
                    color:rgba(255,255,255,0.35);">
          {{ $desc }}
        </div>
      </div>
      @endforeach
    </div>

  </div>
</section>

{{-- ══ CTA CONTACT ══════════════════════════════════════════════════ --}}
<section class="a-propos-section" style="background:#0A1500; padding-top:80px; padding-bottom:80px;">
  <div style="max-width:600px; margin:0 auto; text-align:center;">
    <h2 style="font-family:'Space Grotesk',sans-serif; font-size:30px;
               font-weight:700; color:white; letter-spacing:-0.03em; margin:0 0 16px;">
      Une question ? Un partenariat ?
    </h2>
    <p style="font-family:'Inter',sans-serif; font-size:15px;
              color:rgba(255,255,255,0.40); margin:0 0 32px; line-height:1.65;">
      Nous sommes disponibles sur WhatsApp et par email.
      Réponse sous 24h.
    </p>
    <div style="display:flex; justify-content:center; gap:16px; flex-wrap:wrap;">
      <a href="{{ route('contact') }}"
         style="display:inline-flex; align-items:center; gap:8px;
                font-family:'Inter',sans-serif; font-size:14px; font-weight:600;
                color:white; text-decoration:none; background:#16a34a;
                padding:12px 24px; border-radius:12px;
                border:1px solid rgba(74,222,128,0.30);
                transition:all 0.2s;"
         onmouseover="this.style.background='#15803d';this.style.boxShadow='0 8px 24px rgba(22,163,74,0.35)'"
         onmouseout="this.style.background='#16a34a';this.style.boxShadow='none'">
        📨 Nous contacter
      </a>
      <a href="{{ route('aide.index') }}"
         style="display:inline-flex; align-items:center; gap:8px;
                font-family:'Inter',sans-serif; font-size:14px; font-weight:600;
                color:rgba(255,255,255,0.70); text-decoration:none;
                padding:12px 24px; border-radius:12px;
                border:1px solid rgba(255,255,255,0.15);
                background:rgba(255,255,255,0.05);
                backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px);
                transition:all 0.2s;"
         onmouseover="this.style.color='white';this.style.borderColor='rgba(255,255,255,0.30)'"
         onmouseout="this.style.color='rgba(255,255,255,0.70)';this.style.borderColor='rgba(255,255,255,0.15)'">
        💬 Centre d'aide
      </a>
    </div>
  </div>
</section>

@endsection
