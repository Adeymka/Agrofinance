@extends('layouts.app-public')
@section('title', 'AgroFinance+ — Gérez vos finances agricoles')
@section('meta-description', 'AgroFinance+ calcule automatiquement vos indicateurs financiers agricoles et génère vos rapports PDF pour la microfinance. Essai gratuit 75 jours. Paiement Mobile Money.')

@section('content')

{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- SECTION 1 : HERO --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
<section style="position:relative; min-height:100vh;
                display:flex; flex-direction:column; justify-content:center;
                overflow:hidden;">

  {{-- Image de fond UNIQUEMENT sur le Hero --}}
  <div style="position:absolute; inset:0; z-index:0;">
    <img src="https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=1920&q=80"
         alt="Champ agricole africain"
         style="width:100%; height:100%; object-fit:cover;
                object-position:center;
                -webkit-mask-image:linear-gradient(to bottom, black 50%, transparent 100%);
                mask-image:linear-gradient(to bottom, black 50%, transparent 100%);">
  </div>

  {{-- Overlay sombre --}}
  <div style="position:absolute; inset:0; z-index:1;
              background:linear-gradient(135deg,
                rgba(3,15,3,0.90) 0%,
                rgba(8,30,8,0.78) 50%,
                rgba(3,12,3,0.88) 100%);">
  </div>

  {{-- Contenu Hero --}}
  <div class="public-hero-grid" style="position:relative; z-index:2; max-width:1280px; margin:0 auto;
              padding:120px 48px 80px;
              display:grid; grid-template-columns:1fr 1fr; gap:80px; align-items:center;">

    {{-- Gauche : Texte + CTA --}}
    <div>

      {{-- Badge --}}
      <div style="display:inline-flex; align-items:center; gap:8px;
                  background:rgba(74,222,128,0.12); border:1px solid rgba(74,222,128,0.25);
                  border-radius:999px; padding:6px 14px; margin-bottom:28px;">
        <div style="width:6px; height:6px; border-radius:50%; background:#4ade80;
                    box-shadow:0 0 8px #4ade80;"></div>
        <span style="font-family:'Inter',sans-serif; font-size:12px; font-weight:500;
                     color:#4ade80; letter-spacing:0.04em;">
          Essai gratuit 75 jours — Sans carte bancaire
        </span>
      </div>

      {{-- Titre principal --}}
      <h1 style="font-family:'Space Grotesk',sans-serif; font-size:56px;
                 font-weight:700; color:white; line-height:1.05;
                 letter-spacing:-0.04em; margin:0 0 24px;">
        Votre exploitation<br>
        est-elle <span style="color:#4ade80;">rentable</span> ?
      </h1>

      {{-- Sous-titre --}}
      <p style="font-family:'Inter',sans-serif; font-size:18px; font-weight:400;
                color:rgba(255,255,255,0.58); line-height:1.7;
                margin:0 0 40px; max-width:480px;">
        AgroFinance+ calcule automatiquement vos
        <strong style="color:rgba(255,255,255,0.80); font-weight:600;">
          8 indicateurs financiers agricoles
        </strong>
        et génère vos rapports PDF pour la microfinance —
        en 30 secondes.
      </p>

      {{-- Boutons CTA --}}
      <div class="public-hero-cta-row" style="display:flex; align-items:center; gap:16px; margin-bottom:48px; flex-wrap:wrap;">
        <a href="{{ route('inscription') }}"
           style="font-family:'Inter',sans-serif; font-size:15px; font-weight:700;
                  color:white; text-decoration:none; background:#16a34a;
                  padding:14px 28px; border-radius:12px;
                  border:1px solid rgba(74,222,128,0.30);
                  transition:all 0.2s; display:inline-flex; align-items:center; gap:8px;"
           onmouseover="this.style.background='#15803d';this.style.boxShadow='0 12px 32px rgba(22,163,74,0.40)';this.style.transform='translateY(-2px)'"
           onmouseout="this.style.background='#16a34a';this.style.boxShadow='none';this.style.transform='translateY(0)'">
          Commencer gratuitement
          <svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;"
               fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
          </svg>
        </a>
        <a href="{{ route('aide.index') }}"
           style="font-family:'Inter',sans-serif; font-size:15px; font-weight:600;
                  color:rgba(255,255,255,0.70); text-decoration:none;
                  padding:14px 24px; border-radius:12px;
                  border:1px solid rgba(255,255,255,0.15);
                  background:rgba(255,255,255,0.05);
                  transition:all 0.2s; display:inline-flex; align-items:center; gap:8px;"
           onmouseover="this.style.color='white';this.style.borderColor='rgba(255,255,255,0.30)';this.style.background='rgba(255,255,255,0.10)'"
           onmouseout="this.style.color='rgba(255,255,255,0.70)';this.style.borderColor='rgba(255,255,255,0.15)';this.style.background='rgba(255,255,255,0.05)'">
          Voir comment ça marche
        </a>
      </div>

      {{-- 3 preuves rapides --}}
      <div class="public-hero-proofs" style="display:flex; align-items:center; gap:24px; flex-wrap:wrap;">
        @foreach([
          ['✓', '102 500+', 'exploitants au Bénin'],
          ['✓', '75 jours', 'essai gratuit'],
          ['✓', 'Mobile Money', 'MTN · Moov'],
        ] as [$icon, $val, $label])
        <div style="display:flex; align-items:center; gap:8px;">
          <span style="font-size:13px; color:#4ade80; font-weight:700;">{{ $icon }}</span>
          <div>
            <span style="font-family:'Space Grotesk',sans-serif; font-size:14px;
                         font-weight:600; color:rgba(255,255,255,0.88);">{{ $val }}</span>
            <span style="font-family:'Inter',sans-serif; font-size:12px;
                         color:rgba(255,255,255,0.38); margin-left:4px;">{{ $label }}</span>
          </div>
        </div>
        @endforeach
      </div>

    </div>

    {{-- Droite : Screenshot dashboard avec forme --}}
    <div class="public-hero-mock" style="position:relative;">

      {{-- Halo vert derrière --}}
      <div style="position:absolute; inset:-40px; z-index:0;
                  background:radial-gradient(ellipse at center,
                    rgba(74,222,128,0.10) 0%,
                    transparent 70%);
                  filter:blur(40px);">
      </div>

      {{-- Screenshot avec forme asymétrique --}}
      <div style="position:relative; z-index:1;
                  border-radius:20px 20px 60px 20px;
                  overflow:hidden;
                  border:1px solid rgba(74,222,128,0.20);
                  box-shadow:
                    0 0 0 1px rgba(74,222,128,0.10),
                    0 32px 64px rgba(0,0,0,0.60),
                    0 0 100px rgba(74,222,128,0.08);
                  -webkit-mask-image:linear-gradient(to bottom, black 75%, transparent 100%);
                  mask-image:linear-gradient(to bottom, black 75%, transparent 100%);">

        {{-- Header barre Mac-style --}}
        <div style="background:rgba(0,0,0,0.60); padding:12px 16px;
                    display:flex; align-items:center; gap:8px;
                    border-bottom:1px solid rgba(255,255,255,0.08);">
          <div style="width:10px;height:10px;border-radius:50%;background:#ff5f57;"></div>
          <div style="width:10px;height:10px;border-radius:50%;background:#ffbd2e;"></div>
          <div style="width:10px;height:10px;border-radius:50%;background:#28c840;"></div>
          <span style="font-family:'Inter',sans-serif; font-size:11px;
                       color:rgba(255,255,255,0.30); margin-left:8px;">
            agrofinanceplus.bj/dashboard
          </span>
        </div>

        {{-- Aperçu dashboard simulé --}}
        <div style="background:rgba(5,20,5,0.92); padding:24px;">

          {{-- Topbar simulée --}}
          <div style="display:flex; justify-content:space-between;
                      align-items:center; margin-bottom:20px;">
            <div>
              <div style="font-family:'Space Grotesk',sans-serif; font-size:18px;
                           font-weight:600; color:white;">Tableau de bord</div>
              <div style="font-family:'Inter',sans-serif; font-size:11px;
                           color:rgba(255,255,255,0.35);">Ferme Akobi</div>
            </div>
            <div style="background:#16a34a; padding:6px 14px; border-radius:8px;">
              <span style="font-family:'Inter',sans-serif; font-size:11px;
                           font-weight:600; color:white;">+ Nouvelle saisie</span>
            </div>
          </div>

          {{-- Mini KPI cards --}}
          <div class="public-mock-kpi-grid" style="display:grid; grid-template-columns:repeat(4,1fr); gap:10px; margin-bottom:16px;">
            @foreach([
              ['📈','Recettes','450 K','#4ade80'],
              ['📉','Dépenses','189 K','#f87171'],
              ['💰','Reste avant fixes','261 K','#4ade80'],
              ['🟢','Statut','Rentable','#4ade80'],
            ] as [$icon,$label,$val,$color])
            <div style="background:rgba(255,255,255,0.06);
                        border:1px solid rgba(255,255,255,0.10);
                        border-radius:12px; padding:12px;">
              <div style="font-family:'Inter',sans-serif; font-size:9px;
                           color:rgba(255,255,255,0.35); text-transform:uppercase;
                           letter-spacing:0.08em; margin-bottom:6px;">{{ $label }}</div>
              <div style="font-family:'Space Grotesk',sans-serif; font-size:16px;
                           font-weight:700; color:{{ $color }};">{{ $val }}</div>
            </div>
            @endforeach
          </div>

          {{-- Barre graphique simulée --}}
          <div style="background:rgba(255,255,255,0.04);
                      border:1px solid rgba(255,255,255,0.08);
                      border-radius:12px; padding:14px;">
            <div style="font-family:'Space Grotesk',sans-serif; font-size:12px;
                         font-weight:600; color:rgba(255,255,255,0.70);
                         margin-bottom:12px;">Évolution — reste avant charges fixes</div>
            <div style="display:flex; align-items:flex-end; gap:4px; height:50px;">
              @foreach([20,35,25,45,30,60,40,75,55,80,65,90] as $h)
              <div style="flex:1; background:rgba(74,222,128,{{ $h > 50 ? '0.60' : '0.25' }});
                           border-radius:3px 3px 0 0; height:{{ $h }}%;
                           transition:height 0.3s;"></div>
              @endforeach
            </div>
          </div>

        </div>
      </div>

      {{-- Badge flottant en bas à gauche --}}
      <div style="position:absolute; bottom:-16px; left:-16px; z-index:2;
                  background:rgba(13,31,13,0.90);
                  backdrop-filter:blur(16px);
                  border:1px solid rgba(74,222,128,0.25);
                  border-radius:14px; padding:12px 16px;
                  display:flex; align-items:center; gap:10px;
                  box-shadow:0 8px 32px rgba(0,0,0,0.40);">
        <div style="width:36px; height:36px; border-radius:10px;
                    background:rgba(74,222,128,0.15);
                    display:flex; align-items:center; justify-content:center;">
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" aria-hidden="true">
            <path fill="#ef5350" d="M13 9h5.5L13 3.5zM6 2h8l6 6v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2m4.93 10.44c.41.9.93 1.64 1.53 2.15l.41.32c-.87.16-2.07.44-3.34.93l-.11.04l.5-1.04c.45-.87.78-1.66 1.01-2.4m6.48 3.81c.18-.18.27-.41.28-.66c.03-.2-.02-.39-.12-.55c-.29-.47-1.04-.69-2.28-.69l-1.29.07l-.87-.58c-.63-.52-1.2-1.43-1.6-2.56l.04-.14c.33-1.33.64-2.94-.02-3.6a.85.85 0 0 0-.61-.24h-.24c-.37 0-.7.39-.79.77c-.37 1.33-.15 2.06.22 3.27v.01c-.25.88-.57 1.9-1.08 2.93l-.96 1.8l-.89.49c-1.2.75-1.77 1.59-1.88 2.12c-.04.19-.02.36.05.54l.03.05l.48.31l.44.11c.81 0 1.73-.95 2.97-3.07l.18-.07c1.03-.33 2.31-.56 4.03-.75c1.03.51 2.24.74 3 .74c.44 0 .74-.11.91-.3m-.41-.71l.09.11c-.01.1-.04.11-.09.13h-.04l-.19.02c-.46 0-1.17-.19-1.9-.51c.09-.1.13-.1.23-.1c1.4 0 1.8.25 1.9.35M7.83 17c-.65 1.19-1.24 1.85-1.69 2c.05-.38.5-1.04 1.21-1.69zm3.02-6.91c-.23-.9-.24-1.63-.07-2.05l.07-.12l.15.05c.17.24.19.56.09 1.1l-.03.16l-.16.82z"/>
          </svg>
        </div>
        <div>
          <div style="font-family:'Inter',sans-serif; font-size:12px;
                       font-weight:600; color:white;">Rapport PDF généré</div>
          <div style="font-family:'Inter',sans-serif; font-size:10px;
                       color:rgba(255,255,255,0.40);">Partagé avec votre microfinance ✓</div>
        </div>
      </div>

    </div>
  </div>

  {{-- Vague SVG transition vers section suivante --}}
  <div style="position:absolute; bottom:-1px; left:0; right:0; z-index:3; line-height:0;">
    <svg viewBox="0 0 1440 80" xmlns="http://www.w3.org/2000/svg"
         style="display:block; width:100%;">
      <path d="M0,40 C360,80 1080,0 1440,40 L1440,80 L0,80 Z"
            fill="#0D1F0D"/>
    </svg>
  </div>

</section>
{{-- FIN HERO --}}


{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- SECTION 2 : PROBLÈME + SOLUTION --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
<section style="background:#0D1F0D; padding:100px 48px;">
  <div style="max-width:1280px; margin:0 auto; text-align:center;">

    {{-- Label section --}}
    <div style="display:inline-flex; align-items:center; gap:8px; margin-bottom:20px;
                background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08);
                border-radius:999px; padding:6px 16px;">
      <span style="font-family:'Inter',sans-serif; font-size:12px; font-weight:500;
                   color:rgba(255,255,255,0.72); letter-spacing:0.08em;
                   text-transform:uppercase;">Le problème</span>
    </div>

    <h2 class="reveal"
        style="font-family:'Space Grotesk',sans-serif; font-size:44px;
               font-weight:700; color:white; letter-spacing:-0.03em;
               line-height:1.15; margin:0 0 20px;
               opacity:0; transform:translateY(20px); transition:all 0.5s ease;">
      102 500 exploitants béninois<br>
      ne savent pas si leur <span style="color:#f59e0b;">exploitation est rentable</span>.
    </h2>

    <p class="reveal"
       style="font-family:'Inter',sans-serif; font-size:18px; font-weight:400;
              color:rgba(255,255,255,0.72); line-height:1.7; max-width:620px;
              margin:0 auto 64px;
              opacity:0; transform:translateY(20px); transition:all 0.5s ease 0.1s;">
      Ils savent combien ils ont dépensé. Ils savent ce qu'ils ont vendu.
      Mais ils ne savent pas si leur travail leur rapporte réellement.
      AgroFinance+ change ça.
    </p>

    {{-- 3 bénéfices en glass-cards --}}
    <div class="public-benefits-grid" style="display:grid; grid-template-columns:repeat(3,1fr); gap:24px;">
      @foreach([
        [
          'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" fill-rule="evenodd" d="M14 20.5V4.25c0-.728-.002-1.2-.048-1.546c-.044-.325-.115-.427-.172-.484s-.159-.128-.484-.172C12.949 2.002 12.478 2 11.75 2s-1.2.002-1.546.048c-.325.044-.427.115-.484.172s-.128.159-.172.484c-.046.347-.048.818-.048 1.546V20.5z" clip-rule="evenodd"/><path fill="currentColor" d="M8 8.75A.75.75 0 0 0 7.25 8h-3a.75.75 0 0 0-.75.75V20.5H8zm12 5a.75.75 0 0 0-.75-.75h-3a.75.75 0 0 0-.75.75v6.75H20z" opacity="0.7"/><path fill="currentColor" d="M1.75 20.5a.75.75 0 0 0 0 1.5h20a.75.75 0 0 0 0-1.5z" opacity="0.5"/></svg>',
          'bg'   => 'rgba(74,222,128,0.10)',
          'bd'   => 'rgba(74,222,128,0.20)',
          'color'=> '#4ade80',
          'titre'=> 'Savoir en temps réel',
          'texte'=> 'Recettes, dépenses, reste avant charges fixes, seuil d’équilibre. Huit indicateurs financiers agricoles calculés automatiquement à chaque saisie.',
          'stat' => '8 indicateurs',
          'delay'=> '0s',
        ],
        [
          'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M15 7h5.5L15 1.5zM8 0h8l6 6v12a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2M4 4v18h16v2H4a2 2 0 0 1-2-2V4z"/></svg>',
          'bg'   => 'rgba(245,158,11,0.10)',
          'bd'   => 'rgba(245,158,11,0.20)',
          'color'=> '#fbbf24',
          'titre'=> 'Rapport pour la microfinance',
          'texte'=> 'Générez un PDF professionnel. Partagez le lien WhatsApp en 1 clic. Votre agent de microfinance le consulte sans compte.',
          'stat' => 'Lien 72h',
          'delay'=> '0.1s',
        ],
        [
          'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M7 22h10c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2H7c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2M7 4h10v16H7z"/><path fill="currentColor" d="M12 17a1 1 0 1 0 0 2a1 1 0 1 0 0-2"/></svg>',
          'bg'   => 'rgba(99,179,237,0.10)',
          'bd'   => 'rgba(99,179,237,0.20)',
          'color'=> '#93c5fd',
          'titre'=> 'Fonctionne sans connexion',
          'texte'=> 'Saisissez vos dépenses sur le terrain même sans réseau. Tout se synchronise automatiquement au retour du réseau.',
          'stat' => 'Mode hors ligne',
          'delay'=> '0.2s',
        ],
      ] as $card)
      <div class="reveal"
           style="background:rgba(255,255,255,0.05);
                  backdrop-filter:blur(12px);
                  border:1px solid rgba(255,255,255,0.10);
                  border-radius:20px; padding:32px;
                  text-align:left;
                  opacity:0; transform:translateY(20px);
                  transition:all 0.5s ease {{ $card['delay'] }};"
           onmouseover="this.style.background='rgba(255,255,255,0.08)';this.style.borderColor='rgba(255,255,255,0.18)';this.style.transform='translateY(-4px)'"
           onmouseout="this.style.background='rgba(255,255,255,0.05)';this.style.borderColor='rgba(255,255,255,0.10)';this.style.transform='translateY(0)'">

        {{-- Icône --}}
        <div style="width:52px; height:52px; border-radius:14px; font-size:24px;
                    background:{{ $card['bg'] }}; border:1px solid {{ $card['bd'] }};
                    display:flex; align-items:center; justify-content:center;
                    margin-bottom:20px;">
          <span style="color:{{ $card['color'] }}; display:inline-flex; align-items:center; justify-content:center;">{!! $card['icon'] !!}</span>
        </div>

        {{-- Badge stat --}}
        <div style="display:inline-block; background:{{ $card['bg'] }};
                    border:1px solid {{ $card['bd'] }}; border-radius:999px;
                    padding:3px 10px; margin-bottom:14px;">
          <span style="font-family:'Inter',sans-serif; font-size:11px; font-weight:600;
                       color:{{ $card['color'] }};">{{ $card['stat'] }}</span>
        </div>

        <h3 style="font-family:'Space Grotesk',sans-serif; font-size:20px;
                   font-weight:600; color:rgba(255,255,255,0.92);
                   letter-spacing:-0.02em; margin:0 0 12px;">
          {{ $card['titre'] }}
        </h3>
        <p style="font-family:'Inter',sans-serif; font-size:14px;
                  color:rgba(255,255,255,0.45); line-height:1.65; margin:0;">
          {{ $card['texte'] }}
        </p>

      </div>
      @endforeach
    </div>

  </div>
</section>

{{-- Séparateur ligne lumineuse --}}
<div style="max-width:800px; margin:0 auto; height:1px;
            background:linear-gradient(to right, transparent,
              rgba(74,222,128,0.25) 30%, rgba(74,222,128,0.25) 70%, transparent);">
</div>


{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- SECTION 3 : INDICATEURS FINANCIERS AGRICOLES --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
<section style="background:#0A1500; padding:100px 48px;">
  <div class="public-indicators-grid" style="max-width:1280px; margin:0 auto;
              display:grid; grid-template-columns:1fr 1fr; gap:80px; align-items:center;">

    {{-- Gauche : Texte --}}
    <div>
      <div style="display:inline-flex; align-items:center; gap:8px; margin-bottom:20px;
                  background:rgba(74,222,128,0.08); border:1px solid rgba(74,222,128,0.18);
                  border-radius:999px; padding:6px 16px;">
        <span style="font-family:'Inter',sans-serif; font-size:12px; font-weight:500;
                     color:rgba(74,222,128,0.80); letter-spacing:0.08em;
                     text-transform:uppercase;">Indicateurs financiers agricoles</span>
      </div>

      <h2 class="reveal"
          style="font-family:'Space Grotesk',sans-serif; font-size:40px;
                 font-weight:700; color:white; letter-spacing:-0.03em;
                 line-height:1.15; margin:0 0 20px;
                 opacity:0; transform:translateY(20px); transition:all 0.5s ease;">
        Clarté sur votre rentabilité.<br>
        <span style="color:#4ade80;">Indicateurs calculés pour vous.</span>
      </h2>

      <p style="font-family:'Inter',sans-serif; font-size:16px;
                color:rgba(255,255,255,0.45); line-height:1.7; margin:0 0 32px;">
        AgroFinance+ s’appuie sur des indicateurs financiers agricoles reconnus
        (ventes totales, reste avant charges fixes, gain ou perte, rentabilité, seuil d’équilibre…). Ils sont
        calculés automatiquement à partir de vos saisies — aucune formule à
        retaper à la main.
      </p>

      {{-- Feux tricolores --}}
      <div style="display:flex; flex-direction:column; gap:12px;">
        @foreach([
          ['🟢','Rentable',   'Gain ou perte positif et objectif d’équilibre atteint',   'rgba(74,222,128,0.10)', 'rgba(74,222,128,0.25)'],
          ['🟠','À surveiller','Reste positif mais charges fixes pas encore couvertes','rgba(251,191,36,0.10)','rgba(251,191,36,0.25)'],
          ['🔴','Déficitaire', 'Reste avant charges fixes négatif — action requise',       'rgba(248,113,113,0.10)','rgba(248,113,113,0.25)'],
        ] as [$emoji,$label,$desc,$bg,$bd])
        <div style="display:flex; align-items:center; gap:14px; padding:14px 16px;
                    background:{{ $bg }}; border:1px solid {{ $bd }};
                    border-radius:12px;">
          <span style="font-size:20px; flex-shrink:0;">{{ $emoji }}</span>
          <div>
            <div style="font-family:'Inter',sans-serif; font-size:13px; font-weight:600;
                         color:rgba(255,255,255,0.85); margin-bottom:2px;">{{ $label }}</div>
            <div style="font-family:'Inter',sans-serif; font-size:12px;
                         color:rgba(255,255,255,0.38);">{{ $desc }}</div>
          </div>
        </div>
        @endforeach
      </div>
    </div>

    {{-- Droite : Tableau indicateurs avec forme --}}
    <div class="reveal"
         style="opacity:0; transform:translateY(20px); transition:all 0.5s ease 0.2s;">
      <div style="background:rgba(255,255,255,0.05);
                  border:1px solid rgba(255,255,255,0.10);
                  border-radius:20px 20px 60px 20px;
                  overflow:hidden;
                  box-shadow:0 32px 64px rgba(0,0,0,0.40);">

        {{-- Header tableau --}}
        <div style="padding:20px 24px; border-bottom:1px solid rgba(255,255,255,0.08);
                    display:flex; align-items:center; justify-content:space-between;">
          <span style="font-family:'Space Grotesk',sans-serif; font-size:14px;
                       font-weight:600; color:rgba(255,255,255,0.80);">
            Campagne Maïs 2025 · 1ha
          </span>
          <span style="display:inline-flex; align-items:center; gap:6px;
                       background:rgba(74,222,128,0.12);
                       border:1px solid rgba(74,222,128,0.25);
                       border-radius:999px; padding:4px 12px;
                       font-family:'Inter',sans-serif; font-size:11px;
                       font-weight:600; color:#4ade80;">
            🟢 Rentable
          </span>
        </div>

        {{-- Indicateurs --}}
        <div style="padding:8px 0;">
          @foreach([
            ['Ventes — total',             '450 000', 'FCFA', '#4ade80', true],
            ['Dépenses liées au volume',   '151 000', 'FCFA', '#f87171', false],
            ['Dépenses fixes',             ' 38 000', 'FCFA', '#f87171', false],
            ['Reste avant charges fixes',   '299 000', 'FCFA', '#4ade80', true],
            ['Gain ou perte finale',        '261 000', 'FCFA', '#4ade80', true],
            ['Rentabilité',                    '138', '%',     '#fbbf24', true],
          ] as [$label, $val, $unit, $color, $positive])
          <div style="display:flex; align-items:center; justify-content:space-between;
                      padding:14px 24px;
                      border-bottom:1px solid rgba(255,255,255,0.05);">
            <span style="font-family:'Inter',sans-serif; font-size:13px;
                         color:rgba(255,255,255,0.50);">{{ $label }}</span>
            <span style="font-family:'Space Grotesk',sans-serif; font-size:15px;
                         font-weight:600; color:{{ $color }};">
              {{ $val }}
              <span style="font-family:'Inter',sans-serif; font-size:11px;
                           font-weight:400; color:rgba(255,255,255,0.30); margin-left:2px;">
                {{ $unit }}
              </span>
            </span>
          </div>
          @endforeach
        </div>

        {{-- Footer --}}
        <div style="padding:16px 24px; background:rgba(74,222,128,0.06);
                    border-top:1px solid rgba(74,222,128,0.12);">
          <p style="font-family:'Inter',sans-serif; font-size:12px;
                    color:rgba(74,222,128,0.70); margin:0; text-align:center;">
            ✓ Calculé automatiquement — 0 formule à retenir
          </p>
        </div>

      </div>
    </div>

  </div>
</section>

{{-- Séparateur vague --}}
<div style="background:#0A1500; line-height:0; margin-bottom:-1px;">
  <svg viewBox="0 0 1440 60" xmlns="http://www.w3.org/2000/svg" style="display:block;width:100%;">
    <path d="M0,30 C480,60 960,0 1440,30 L1440,60 L0,60 Z" fill="#0D1F0D"/>
  </svg>
</div>


{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- SECTION 4 : COMMENT ÇA MARCHE (résumé 4 étapes) --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
<section style="background:#0D1F0D; padding:100px 48px;">
  <div style="max-width:1280px; margin:0 auto; text-align:center;">

    <h2 class="reveal"
        style="font-family:'Space Grotesk',sans-serif; font-size:40px;
               font-weight:700; color:white; letter-spacing:-0.03em;
               margin:0 0 16px;
               opacity:0; transform:translateY(20px); transition:all 0.5s ease;">
      Démarrez en <span style="color:#4ade80;">4 étapes simples</span>
    </h2>
    <p style="font-family:'Inter',sans-serif; font-size:16px;
              color:rgba(255,255,255,0.40); margin:0 0 64px;">
      Pas de formation nécessaire. Pas de comptable nécessaire.
    </p>

    <div class="public-steps-grid" style="display:grid; grid-template-columns:repeat(4,1fr); gap:24px;">
      @foreach([
        ['01','Créez votre compte','En 3 minutes. Juste votre numéro et un PIN 4 chiffres.','rgba(74,222,128,0.15)','rgba(74,222,128,0.30)','#4ade80','0s'],
        ['02','Ajoutez vos campagnes','Maïs, poulets, maraîchage — créez une campagne par activité.','rgba(74,222,128,0.10)','rgba(74,222,128,0.20)','#4ade80','0.1s'],
        ['03','Saisissez vos mouvements','Dépense ou recette en 30 secondes. Même sans connexion.','rgba(74,222,128,0.08)','rgba(74,222,128,0.15)','#4ade80','0.2s'],
        ['04','Consultez et partagez','Dashboard en temps réel. PDF pour la microfinance en 1 clic.','rgba(74,222,128,0.06)','rgba(74,222,128,0.12)','#4ade80','0.3s'],
      ] as [$num,$titre,$texte,$bg,$bd,$color,$delay])
      <div class="reveal"
           style="background:rgba(255,255,255,0.04);
                  border:1px solid rgba(255,255,255,0.08);
                  border-radius:20px; padding:32px 24px; text-align:left;
                  position:relative; overflow:hidden;
                  opacity:0; transform:translateY(20px);
                  transition:all 0.5s ease {{ $delay }};">

        {{-- Numéro en fond --}}
        <div style="position:absolute; top:-10px; right:16px;
                    font-family:'Space Grotesk',sans-serif; font-size:80px;
                    font-weight:700; color:rgba(74,222,128,0.05);
                    line-height:1; pointer-events:none; user-select:none;">
          {{ $num }}
        </div>

        {{-- Numéro visible --}}
        <div style="display:inline-flex; align-items:center; justify-content:center;
                    width:40px; height:40px; border-radius:12px;
                    background:{{ $bg }}; border:1px solid {{ $bd }};
                    margin-bottom:20px;">
          <span style="font-family:'Space Grotesk',sans-serif; font-size:16px;
                       font-weight:700; color:{{ $color }};">{{ $num }}</span>
        </div>

        <h3 style="font-family:'Space Grotesk',sans-serif; font-size:18px;
                   font-weight:600; color:rgba(255,255,255,0.90);
                   margin:0 0 10px; letter-spacing:-0.015em;">
          {{ $titre }}
        </h3>
        <p style="font-family:'Inter',sans-serif; font-size:14px;
                  color:rgba(255,255,255,0.42); line-height:1.6; margin:0;">
          {{ $texte }}
        </p>
      </div>
      @endforeach
    </div>

    <div style="margin-top:48px;">
      <a href="{{ route('aide.index') }}"
         style="font-family:'Inter',sans-serif; font-size:14px; font-weight:600;
                color:rgba(74,222,128,0.80); text-decoration:none;
                display:inline-flex; align-items:center; gap:8px;
                transition:color 0.2s;"
         onmouseover="this.style.color='#4ade80'"
         onmouseout="this.style.color='rgba(74,222,128,0.80)'">
        Voir le guide complet
        <svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;"
             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
        </svg>
      </a>
    </div>

  </div>
</section>

{{-- Ligne séparateur --}}
<div style="max-width:900px; margin:0 auto; height:1px;
            background:linear-gradient(to right, transparent,
              rgba(74,222,128,0.20) 30%, rgba(74,222,128,0.20) 70%, transparent);">
</div>


{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- SECTION 5 : TARIFS --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
<section id="tarifs" style="background:#0D1F0D; padding:100px 48px;">
  <div style="max-width:1280px; margin:0 auto; text-align:center;">

    <h2 class="reveal"
        style="font-family:'Space Grotesk',sans-serif; font-size:40px;
               font-weight:700; color:white; letter-spacing:-0.03em;
               margin:0 0 16px;
               opacity:0; transform:translateY(20px); transition:all 0.5s ease;">
      Tarifs simples,<br>paiement <span style="color:#4ade80;">Mobile Money</span>
    </h2>
    <p style="font-family:'Inter',sans-serif; font-size:16px;
              color:rgba(255,255,255,0.40); margin:0 0 64px;">
      Pas de carte bancaire. MTN MoMo ou Moov Money. Annulez quand vous voulez.
    </p>

    <div class="public-pricing-grid" style="display:grid; grid-template-columns:repeat(4,1fr); gap:20px; max-width:1200px; margin:0 auto;">
      @foreach($plansAccueilTarifs as $plan)
      <div class="reveal"
           style="background:{{ $plan['bg'] }};
                  backdrop-filter:blur(12px);
                  border:1px solid {{ $plan['bd'] }};
                  border-radius:20px; padding:36px 28px;
                  position:relative; text-align:left;
                  opacity:0; transform:translateY(20px); transition:all 0.5s ease;">

        @if($plan['star'])
        {{-- Badge "populaire" --}}
        <div style="position:absolute; top:-12px; left:50%; transform:translateX(-50%);
                    background:#16a34a; border:1px solid rgba(74,222,128,0.40);
                    border-radius:999px; padding:4px 16px;
                    font-family:'Inter',sans-serif; font-size:11px;
                    font-weight:700; color:white; white-space:nowrap;">
          ⭐ {{ $plan['badge'] }}
        </div>
        @endif

        <p style="font-family:'Inter',sans-serif; font-size:11px; font-weight:600;
                  color:{{ $plan['color'] }}; text-transform:uppercase;
                  letter-spacing:0.12em; margin:0 0 16px;">
          {{ $plan['nom'] }}
        </p>

        <div style="display:flex; align-items:baseline; gap:6px; margin-bottom:6px;">
          <span style="font-family:'Space Grotesk',sans-serif; font-size:40px;
                       font-weight:700; color:white; letter-spacing:-0.03em;
                       line-height:1;">
            {{ $plan['prix'] }}
          </span>
          <span style="font-family:'Inter',sans-serif; font-size:13px;
                       color:rgba(255,255,255,0.38);">
            {{ $plan['duree'] }}
          </span>
        </div>

        <div style="height:1px; background:rgba(255,255,255,0.08); margin:24px 0;"></div>

        <div style="display:flex; flex-direction:column; gap:10px; margin-bottom:28px;">
          @foreach($plan['items'] as $item)
          <div style="display:flex; align-items:center; gap:10px;">
            <svg xmlns="http://www.w3.org/2000/svg"
                 style="width:16px;height:16px;flex-shrink:0;
                        color:{{ str_starts_with($item, 'Pas de') ? 'rgba(255,255,255,0.20)' : $plan['color'] }};"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              @if(str_starts_with($item, 'Pas de'))
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
              @else
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
              @endif
            </svg>
            <span style="font-family:'Inter',sans-serif; font-size:13px;
                         color:{{ str_starts_with($item, 'Pas de') ? 'rgba(255,255,255,0.25)' : 'rgba(255,255,255,0.70)' }};">
              {{ $item }}
            </span>
          </div>
          @endforeach
        </div>

        @php $variant = $plan['cta_variant'] ?? ($plan['star'] ? 'green' : 'muted'); @endphp
        @if($variant === 'green')
        <a href="{{ route('inscription') }}"
           style="display:block; text-align:center;
                  font-family:'Inter',sans-serif; font-size:14px; font-weight:600;
                  color:white; text-decoration:none;
                  background:#16a34a;
                  border:1px solid rgba(74,222,128,0.30);
                  padding:12px; border-radius:12px; transition:all 0.2s;"
           onmouseover="this.style.opacity='0.85'"
           onmouseout="this.style.opacity='1'">
          {{ $plan['cta'] }}
        </a>
        @elseif($variant === 'amber')
        <a href="{{ route('inscription') }}"
           style="display:block; text-align:center;
                  font-family:'Inter',sans-serif; font-size:14px; font-weight:600;
                  color:#92400e; text-decoration:none;
                  background:rgba(255,255,255,0.92);
                  border:1px solid rgba(245,158,11,0.45);
                  padding:12px; border-radius:12px; transition:all 0.2s;"
           onmouseover="this.style.background='rgba(255,251,235,1)'"
           onmouseout="this.style.background='rgba(255,255,255,0.92)'">
          {{ $plan['cta'] }}
        </a>
        @elseif($variant === 'violet')
        <a href="{{ route('inscription') }}"
           style="display:block; text-align:center;
                  font-family:'Inter',sans-serif; font-size:14px; font-weight:600;
                  color:#f5f3ff; text-decoration:none;
                  background:rgba(91,33,182,0.55);
                  border:1px solid rgba(167,139,250,0.45);
                  padding:12px; border-radius:12px; transition:all 0.2s;"
           onmouseover="this.style.background='rgba(91,33,182,0.75)'"
           onmouseout="this.style.background='rgba(91,33,182,0.55)'">
          {{ $plan['cta'] }}
        </a>
        @else
        <a href="{{ route('inscription') }}"
           style="display:block; text-align:center;
                  font-family:'Inter',sans-serif; font-size:14px; font-weight:600;
                  color:rgba(255,255,255,0.70);
                  text-decoration:none;
                  background:rgba(255,255,255,0.06);
                  border:1px solid rgba(255,255,255,0.12);
                  padding:12px; border-radius:12px; transition:all 0.2s;"
           onmouseover="this.style.opacity='0.85'"
           onmouseout="this.style.opacity='1'">
          {{ $plan['cta'] }}
        </a>
        @endif

      </div>
      @endforeach
    </div>

  </div>
</section>


{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- SECTION 6 : CTA FINAL --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
<section style="position:relative; padding:120px 48px; overflow:hidden;">

  {{-- Image de fond (uniquement ici en plus du Hero) --}}
  <div style="position:absolute; inset:0; z-index:0;">
    <img src="https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=1920&q=80"
         alt="Agriculture Bénin"
         style="width:100%; height:100%; object-fit:cover;
                clip-path:polygon(0 8%, 100% 0%, 100% 100%, 0% 100%);
                -webkit-mask-image:linear-gradient(to bottom, transparent 0%, black 20%, black 80%, transparent 100%);
                mask-image:linear-gradient(to bottom, transparent 0%, black 20%, black 80%, transparent 100%);">
  </div>
  <div style="position:absolute; inset:0; z-index:1;
              background:rgba(3,15,3,0.86);"></div>

  {{-- Contenu CTA --}}
  <div style="position:relative; z-index:2; max-width:700px; margin:0 auto; text-align:center;">

    <div style="display:inline-flex; align-items:center; gap:8px; margin-bottom:24px;
                background:rgba(74,222,128,0.12); border:1px solid rgba(74,222,128,0.25);
                border-radius:999px; padding:6px 16px;">
      <div style="width:6px; height:6px; border-radius:50%; background:#4ade80;
                  box-shadow:0 0 8px #4ade80;"></div>
      <span style="font-family:'Inter',sans-serif; font-size:12px; font-weight:500;
                   color:#4ade80;">75 jours gratuits — Aucune carte bancaire requise</span>
    </div>

    <h2 style="font-family:'Space Grotesk',sans-serif; font-size:48px;
               font-weight:700; color:white; letter-spacing:-0.04em;
               line-height:1.08; margin:0 0 20px;">
      Votre exploitation mérite<br>
      de savoir si elle est<br>
      <span style="color:#4ade80;">rentable.</span>
    </h2>

    <p style="font-family:'Inter',sans-serif; font-size:17px;
              color:rgba(255,255,255,0.50); line-height:1.65; margin:0 0 40px;">
      Rejoignez AgroFinance+ aujourd'hui.
      Créez votre compte en 3 minutes.
      Payez avec MTN MoMo ou Moov Money.
    </p>

    <a href="{{ route('inscription') }}"
       style="display:inline-flex; align-items:center; gap:10px;
              font-family:'Inter',sans-serif; font-size:16px; font-weight:700;
              color:white; text-decoration:none; background:#16a34a;
              padding:16px 36px; border-radius:14px;
              border:1px solid rgba(74,222,128,0.35);
              box-shadow:0 0 40px rgba(22,163,74,0.30);
              transition:all 0.2s;"
       onmouseover="this.style.background='#15803d';this.style.transform='translateY(-2px)';this.style.boxShadow='0 12px 40px rgba(22,163,74,0.45)'"
       onmouseout="this.style.background='#16a34a';this.style.transform='translateY(0)';this.style.boxShadow='0 0 40px rgba(22,163,74,0.30)'">
      Créer mon compte gratuit
      <svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;"
           fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
      </svg>
    </a>

    <p style="font-family:'Inter',sans-serif; font-size:13px;
              color:rgba(255,255,255,0.30); margin:20px 0 0;">
      75 jours gratuits · Sans engagement · Annulation à tout moment
    </p>

  </div>
</section>

@endsection
