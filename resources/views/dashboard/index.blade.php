@extends($layout)
@section('title', 'Tableau de bord — AgroFinance+')
@section('page-title', 'Tableau de bord')
@section('page-subtitle', $exploitation->nom)

@section('topbar-actions')
    @if(($infoAbonnement['plan_metier'] ?? '') === 'cooperative' && ($canExportEntreprise ?? false))
    <a href="{{ route('dashboard.export.consolide.csv', ['periode' => ($periodeSelection ?? 'all')]) }}" class="btn-outline text-sm px-4 py-2 inline-flex items-center gap-2">
        <x-icon name="arrow-down-tray" class="w-4 h-4" /> Export CSV entreprise
    </a>
    @endif
    <a href="{{ route('transactions.create') }}" class="btn-primary text-sm px-4 py-2 inline-flex items-center gap-2">
        <x-icon name="plus" class="w-4 h-4" /> Nouvelle saisie
    </a>
    <a href="{{ route('rapports.index') }}" class="btn-outline text-sm px-4 py-2 inline-flex items-center gap-2">
        <x-icon name="document-text" class="w-4 h-4" /> Rapports
    </a>
@endsection

@push('head')
    @if($chartActiviteId && session('api_token'))
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @endif
@endpush

@section('content')
@php
    use App\Support\IndicateursLibelles;
    $statutHero = $heroInd['statut'] ?? ($statut ?? 'rouge');
    $statutHeroConfig = match ($statutHero) {
        'vert'   => ['label' => 'RENTABLE',    'color' => 'var(--af-color-accent)', 'bg' => 'var(--af-stat-vert-bg)',  'border' => 'var(--af-stat-vert-border)'],
        'orange' => ['label' => 'À SURVEILLER','color' => 'var(--af-color-warning)', 'bg' => 'var(--af-stat-orange-bg)',  'border' => 'var(--af-stat-orange-border)'],
        default  => ['label' => 'DÉFICITAIRE', 'color' => 'var(--af-color-danger)', 'bg' => 'var(--af-stat-rouge-bg)', 'border' => 'var(--af-stat-rouge-border)'],
    };
    $rneHero = $heroInd['RNE'] ?? ($consolide['RNE'] ?? 0);
    $pbHero  = $heroInd['PB']  ?? ($consolide['PB']  ?? 0);
    $mbHero  = $heroInd['MB']  ?? ($consolide['MB']  ?? 0);
    $ctHero  = $heroInd['CT']  ?? ($consolide['CT']  ?? 0);
    $rfHero  = $heroInd['RF']  ?? ($consolide['RF']  ?? 0);
    $heroTitre    = $heroInd['nom']  ?? $exploitation->nom;
    $heroSousTitre = $heroInd
        ? (($heroInd['type'] ?? '') ? str_replace('_', ' ', ucfirst($heroInd['type'])) . ' · ' : '') . 'Indicateurs sur la période autorisée'
        : 'Vue consolidée de toutes les campagnes actives';
    $transactionsRecentes = $dernieresTransactions->take(5);
    $entreprise = $entrepriseConsolide ?? ['PB' => 0, 'CT' => 0, 'MB' => 0, 'RNE' => 0, 'RF' => 0, 'statut' => 'rouge', 'nb_exploitations' => 0, 'nb_campagnes_actives' => 0];
    $statutEntrepriseConfig = match ($entreprise['statut'] ?? 'rouge') {
        'vert'   => ['label' => 'RENTABLE', 'color' => 'var(--af-color-accent)', 'bg' => 'var(--af-stat-vert-bg)', 'border' => 'var(--af-stat-vert-border)'],
        'orange' => ['label' => 'À SURVEILLER', 'color' => 'var(--af-color-warning)', 'bg' => 'var(--af-stat-orange-bg)', 'border' => 'var(--af-stat-orange-border)'],
        default  => ['label' => 'DÉFICITAIRE', 'color' => 'var(--af-color-danger)', 'bg' => 'var(--af-stat-rouge-bg)', 'border' => 'var(--af-stat-rouge-border)'],
    };

    $typeEmojis = [];
    $pbCons = $resultats['consolide']['PB'] ?? 0;
    $ctCons = $resultats['consolide']['CT'] ?? 0;
    $mbCons = $resultats['consolide']['MB'] ?? 0;
    $statutCons = $resultats['consolide']['statut'] ?? 'rouge';
    $statutConfigMobile = match ($statutCons) {
        'vert'   => ['emoji' => '🟢', 'label' => 'Rentable',     'color' => 'var(--af-color-accent)', 'bg' => 'var(--af-stat-mobile-vert-bg)',  'border' => 'var(--af-stat-mobile-vert-border)'],
        'orange' => ['emoji' => '🟠', 'label' => 'À surveiller', 'color' => 'var(--af-color-warning)', 'bg' => 'var(--af-stat-mobile-orange-bg)',  'border' => 'var(--af-stat-mobile-orange-border)'],
        default  => ['emoji' => '🔴', 'label' => 'Déficitaire',  'color' => 'var(--af-color-danger)', 'bg' => 'var(--af-stat-mobile-rouge-bg)', 'border' => 'var(--af-stat-mobile-rouge-border)'],
    };
@endphp

{{-- ══════════════════════════════════════════════════════════════
     MOBILE DASHBOARD
══════════════════════════════════════════════════════════════ --}}
@if($platform === 'mobile')

@push('styles')
<style>
/* ── Greeting ── */
.dash-greeting {
    padding: 16px 0 6px;
}
.dash-overview-title {
    font-family: var(--font-display), sans-serif;
    font-size: 15px;
    font-weight: 700;
    color: var(--af-text-heading-soft);
    letter-spacing: -0.02em;
    margin: 0 0 10px 0;
    line-height: 1.2;
}
.dash-greeting-hello {
    font-family: var(--font-ui), sans-serif;
    font-size: 12px;
    color: var(--af-text-dim);
    font-weight: 400;
    letter-spacing: 0.01em;
    margin-bottom: 2px;
}
.dash-greeting-name {
    font-family: var(--font-display), sans-serif;
    font-size: 19px;
    font-weight: 700;
    color: var(--af-text-high);
    letter-spacing: -0.03em;
    line-height: 1.15;
}
.dash-greeting-exploit {
    font-family: var(--font-ui), sans-serif;
    font-size: 11px;
    color: var(--af-text-caption);
    margin-top: 3px;
}

/* ── Hero Card ── */
.dash-hero {
    background: var(--af-mobile-surface-hero);
    border: 1px solid var(--af-mobile-border-strong);
    border-radius: var(--af-radius-lg);
    padding: 16px;
    margin-bottom: 14px;
    backdrop-filter: blur(var(--af-blur-hero-mobile)) saturate(165%);
    -webkit-backdrop-filter: blur(var(--af-blur-hero-mobile)) saturate(165%);
    box-shadow: var(--af-shadow-hero-mobile);
}
.dash-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    border-radius: 999px;
    padding: 3px 10px;
    font-family: var(--font-ui), sans-serif;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    margin-bottom: 10px;
}
.dash-hero-badge-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
    opacity: 0.85;
}
.dash-hero-label {
    font-family: var(--font-ui), sans-serif;
    font-size: 10px;
    font-weight: 500;
    color: var(--af-text-subtle);
    text-transform: uppercase;
    letter-spacing: 0.09em;
    margin-bottom: 5px;
}
.dash-hero-rne {
    font-family: var(--font-display), sans-serif;
    font-size: 34px;
    font-weight: 700;
    letter-spacing: -0.04em;
    line-height: 1;
    margin-bottom: 4px;
    color: var(--af-text-kpi);
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.55);
}
.dash-hero-rne-unit {
    font-size: 13px;
    font-weight: 400;
    font-family: var(--font-ui), sans-serif;
    color: var(--af-text-subtle);
    margin-left: 5px;
}
.dash-hero-campagne-name {
    font-family: var(--font-ui), sans-serif;
    font-size: 11px;
    color: var(--af-text-subtle);
    margin-bottom: 14px;
}

/* ── Mini métriques ── */
.dash-mini-metrics {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0;
    border-top: 1px solid var(--af-mobile-border-mid);
    padding-top: 12px;
    margin-top: 5px;
}
.dash-mini-cell {
    text-align: center;
    padding: 0 4px;
    border-right: 1px solid var(--af-mobile-border-mid);
}
.dash-mini-cell:last-child { border-right: none; }
.dash-mini-lbl {
    font-family: var(--font-ui), sans-serif;
    font-size: 9px;
    font-weight: 600;
    color: var(--af-text-caption);
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-bottom: 3px;
}
.dash-mini-val {
    font-family: var(--font-display), sans-serif;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: -0.02em;
    line-height: 1;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.45);
}

/* ── Hero actions ── */
.dash-hero-actions {
    display: flex;
    gap: 7px;
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid var(--af-mobile-border-mid);
}
.dash-hero-btn {
    flex: 1;
    font-family: var(--font-ui), sans-serif;
    font-size: 11px;
    font-weight: 600;
    text-align: center;
    padding: 7px 8px;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.15s;
    cursor: pointer;
    border: none;
}
.dash-hero-btn-primary {
    background: var(--af-color-accent-dark);
    color: white;
    border: 1px solid var(--af-chip-active-border);
}
.dash-hero-btn-ghost {
    background: var(--af-glass-12);
    color: var(--af-text-body);
    border: 1px solid var(--af-mobile-border-strong);
}

/* ── Campaign selector chips ── */
.dash-chips {
    display: flex;
    gap: 7px;
    overflow-x: auto;
    padding-bottom: 2px;
    margin-bottom: 14px;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}
.dash-chips::-webkit-scrollbar { display: none; }
.dash-chip {
    flex-shrink: 0;
    font-family: var(--font-ui), sans-serif;
    font-size: 11px;
    font-weight: 500;
    padding: 5px 11px;
    border-radius: 999px;
    background: var(--af-glass-12);
    border: 1px solid var(--af-glass-14);
    color: rgba(255, 255, 255, 0.62);
    text-decoration: none;
    white-space: nowrap;
    transition: all 0.15s;
}
.dash-chip.active {
    background: var(--af-chip-active-bg);
    border-color: var(--af-chip-active-border);
    color: var(--af-color-accent);
    font-weight: 600;
}

/* ── Quick actions ── */
.dash-quick {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
    margin-bottom: 20px;
}
.dash-quick-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    padding: 10px 6px;
    background: var(--af-mobile-surface-card);
    border: 1px solid var(--af-mobile-border-strong);
    border-radius: var(--af-radius-md);
    text-decoration: none;
    color: var(--af-text-body-strong);
    font-family: var(--font-ui), sans-serif;
    font-size: 10px;
    font-weight: 500;
    backdrop-filter: blur(var(--af-blur-card-mobile)) saturate(180%);
    -webkit-backdrop-filter: blur(var(--af-blur-card-mobile)) saturate(180%);
    transition: background 0.15s;
    box-shadow: var(--af-shadow-card);
}
.dash-quick-btn:active { background: var(--af-mobile-surface-press); }
.dash-quick-icon {
    width: 36px;
    height: 36px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 17px;
}
.dash-quick-icon-green  { background: var(--af-green-icon-bg); border: 1px solid var(--af-green-icon-border); }
.dash-quick-icon-amber  { background: var(--af-amber-tint-bg);  border: 1px solid var(--af-amber-tint-border); }
.dash-quick-icon-blue   { background: var(--af-blue-tint-bg);  border: 1px solid var(--af-blue-tint-border); }

/* ── Section header ── */
.dash-section-hd {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
}
.dash-section-title {
    font-family: var(--font-display), sans-serif;
    font-size: 14px;
    font-weight: 600;
    color: var(--af-text-heading-soft);
    letter-spacing: -0.02em;
}
.dash-section-link {
    font-family: var(--font-ui), sans-serif;
    font-size: 11px;
    color: var(--af-color-accent);
    font-weight: 500;
    text-decoration: none;
}

/* ── Campagne card ── */
.dash-camp-card {
    background: var(--af-mobile-surface-card);
    border: 1px solid var(--af-mobile-border-strong);
    border-radius: 18px;
    padding: 13px;
    margin-bottom: 8px;
    text-decoration: none;
    display: block;
    backdrop-filter: blur(var(--af-blur-card-mobile)) saturate(160%);
    -webkit-backdrop-filter: blur(var(--af-blur-card-mobile)) saturate(160%);
    transition: background 0.15s;
    box-shadow: var(--af-shadow-card-lg);
}
.dash-camp-card:active { background: var(--af-mobile-surface-press); }
.dash-camp-card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
}
.dash-camp-card-left {
    display: flex;
    align-items: center;
    gap: 8px;
}
.dash-camp-emoji {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    background: var(--af-green-tint-bg);
    border: 1px solid var(--af-green-tint-border);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    flex-shrink: 0;
}
.dash-camp-nom {
    font-family: var(--font-display), sans-serif;
    font-size: 14px;
    font-weight: 600;
    color: var(--af-text-heading-soft);
    letter-spacing: -0.02em;
}
.dash-camp-type {
    font-family: var(--font-ui), sans-serif;
    font-size: 10px;
    color: var(--af-text-subtle);
    margin-top: 1px;
}
.dash-camp-badge {
    font-family: 'Inter', sans-serif;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    padding: 2px 8px;
    border-radius: 999px;
}
.dash-camp-metrics {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0;
    padding-top: 10px;
    border-top: 1px solid var(--af-mobile-divider);
}
.dash-camp-metric-cell {
    text-align: center;
    padding: 0 4px;
    border-right: 1px solid var(--af-mobile-divider);
}
.dash-camp-metric-cell:last-child { border-right: none; }
.dash-camp-metric-lbl {
    font-family: var(--font-ui), sans-serif;
    font-size: 10px;
    color: var(--af-text-faint);
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 3px;
}
.dash-camp-metric-val {
    font-family: var(--font-display), sans-serif;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: -0.02em;
}
.dash-camp-budget-bar {
    margin-top: 10px;
}
.dash-camp-budget-track {
    height: 4px;
    background: var(--af-glass-08);
    border-radius: 2px;
    overflow: hidden;
    margin-top: 4px;
}
.dash-camp-budget-fill {
    height: 100%;
    border-radius: 2px;
    transition: width 0.5s;
}
.dash-camp-budget-labels {
    display: flex;
    justify-content: space-between;
    font-family: var(--font-ui), sans-serif;
    font-size: 10px;
    color: var(--af-text-faint);
}

/* ── Alert budget strip ── */
.dash-alert {
    border-radius: 14px;
    padding: 10px 12px;
    margin-bottom: 14px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    backdrop-filter: blur(var(--af-blur-flash)) saturate(150%);
    -webkit-backdrop-filter: blur(var(--af-blur-flash)) saturate(150%);
}
.dash-alert-critique {
    background: var(--af-red-alert-bg);
    border: 1px solid var(--af-red-alert-border);
}
.dash-alert-warning {
    background: var(--af-amber-alert-bg);
    border: 1px solid var(--af-amber-alert-border);
}
.dash-alert-text {
    font-family: var(--font-ui), sans-serif;
    font-size: 11px;
    font-weight: 600;
    line-height: 1.4;
}

/* ── Transactions feed ── */
.dash-tx-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 0;
    border-bottom: 1px solid var(--af-mobile-divider-soft);
}
.dash-tx-item:last-child { border-bottom: none; }
.dash-tx-body { flex: 1; min-width: 0; }
.dash-tx-categorie {
    font-family: var(--font-ui), sans-serif;
    font-size: 13px;
    font-weight: 500;
    color: var(--af-text-body);
    text-transform: capitalize;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.dash-tx-meta {
    font-family: var(--font-ui), sans-serif;
    font-size: 10px;
    color: var(--af-text-muted);
    margin-top: 1px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.dash-tx-montant {
    font-family: var(--font-display), sans-serif;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: -0.02em;
    flex-shrink: 0;
}

/* ── Indicateurs consolidés (parité web) ── */
.dash-consol-intro {
    margin-bottom: 12px;
}
.dash-consol-sub {
    font-family: var(--font-ui), sans-serif;
    font-size: 11px;
    color: var(--af-text-muted);
    margin-top: 4px;
}
.dash-consol-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    margin-bottom: 8px;
}
.dash-consol-card {
    background: var(--af-mobile-surface-card);
    border: 1px solid var(--af-mobile-border-strong);
    border-radius: var(--af-radius-md);
    padding: 12px;
    backdrop-filter: blur(var(--af-blur-card-mobile)) saturate(165%);
    -webkit-backdrop-filter: blur(var(--af-blur-card-mobile)) saturate(165%);
    box-shadow: var(--af-shadow-card);
}
.dash-consol-card-hd {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
}
.dash-consol-lbl {
    font-family: var(--font-ui), sans-serif;
    font-size: 10px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.4);
    text-transform: uppercase;
    letter-spacing: 0.06em;
}
.dash-consol-val {
    font-family: var(--font-display), sans-serif;
    font-size: 15px;
    font-weight: 700;
    letter-spacing: -0.02em;
    line-height: 1.2;
}
.dash-consol-unit {
    font-family: var(--font-ui), sans-serif;
    font-size: 10px;
    font-weight: 500;
    color: var(--af-text-subtle);
}
/* Même pictos que le dashboard web (.kpi-glass .kpi-icon-wrap), taille mobile */
.dash-consol-kpi-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.dash-consol-kpi-icon svg {
    width: 18px;
    height: 18px;
}
.dash-consol-emoji {
    font-size: 17px;
    line-height: 1;
}

/* ── Graphique + alertes (parité web) ── */
.dash-chart-card {
    background: var(--af-mobile-surface-card);
    border: 1px solid var(--af-mobile-border-strong);
    border-radius: 18px;
    padding: 12px;
    margin-bottom: 10px;
    backdrop-filter: blur(var(--af-blur-card-mobile)) saturate(165%);
    -webkit-backdrop-filter: blur(var(--af-blur-card-mobile)) saturate(165%);
    min-height: 175px;
    box-shadow: var(--af-shadow-card-lg);
}
.dash-chart-card canvas {
    max-height: 210px;
}
.dash-alert-list-card {
    background: var(--af-mobile-surface-muted);
    border: 1px solid var(--af-glass-14);
    border-radius: var(--af-radius-md);
    padding: 12px 12px 8px;
    margin-bottom: 10px;
    backdrop-filter: blur(var(--af-blur-card-mobile)) saturate(165%);
    -webkit-backdrop-filter: blur(var(--af-blur-card-mobile)) saturate(165%);
    box-shadow: var(--af-shadow-card);
}
.dash-alert-list-title {
    font-family: var(--font-display), sans-serif;
    font-size: 13px;
    font-weight: 600;
    color: var(--af-text-heading-soft);
    margin-bottom: 8px;
}
.dash-alert-mini-row {
    margin-bottom: 10px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--af-mobile-divider);
}
.dash-alert-mini-row:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}
.dash-tx-actions {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    justify-content: center;
    gap: 4px;
    flex-shrink: 0;
}
.dash-tx-actions a {
    font-family: var(--font-ui), sans-serif;
    font-size: 10px;
    font-weight: 600;
    color: var(--af-color-accent);
    text-decoration: none;
}
</style>
@endpush

{{-- ── Vue d'ensemble (hiérarchie D4) ── --}}
{{-- ── Greeting ── --}}
<section class="dash-greeting" aria-labelledby="dash-mobile-overview">
    <h1 id="dash-mobile-overview" class="dash-overview-title">Vue d'ensemble</h1>
    <p class="dash-greeting-hello">Bonjour,</p>
    <p class="dash-greeting-name">{{ $user->prenom ?? '' }} {{ mb_strtoupper(mb_substr($user->nom ?? '', 0, 1)) }}. 👋</p>
    <p class="dash-greeting-exploit">{{ $exploitation->nom }}</p>
    <p class="text-[11px] text-white/45 leading-relaxed mt-2 px-0.5">{{ $periodeTableauBord['libelle_periode'] ?? '' }}</p>
    @if(!empty($messagePlancherAbonnement))
        <p class="text-[11px] text-amber-200/85 mt-1 px-0.5">{{ $messagePlancherAbonnement }}</p>
    @endif
    @if(!empty($resultats['consolide']['donnees_indicatives']))
        <p class="text-[11px] text-amber-100/90 mt-1 px-0.5">Peu de données saisies : les indicateurs sont indicatifs.</p>
    @endif
    @if(($resultats['consolide']['nb_campagnes_actives'] ?? 0) > 1)
        <p class="text-[11px] text-white/52 mt-1 px-0.5">Total sur {{ $resultats['consolide']['nb_campagnes_actives'] }} campagne(s) en cours — le détail par campagne est ci-dessous.</p>
    @endif
</section>

{{-- ── Entreprise (global) ── --}}
<div class="dash-hero" style="border-color:{{ $statutEntrepriseConfig['border'] }};">
    <div class="dash-hero-badge"
         style="background:{{ $statutEntrepriseConfig['bg'] }}; border:1px solid {{ $statutEntrepriseConfig['border'] }}; color:{{ $statutEntrepriseConfig['color'] }};">
        <span class="dash-hero-badge-dot"></span>
        Entreprise · {{ $statutEntrepriseConfig['label'] }}
    </div>
    <p class="dash-hero-label">Rentabilité globale</p>
    <div class="dash-hero-rne" style="color:{{ ($entreprise['RNE'] ?? 0) >= 0 ? 'var(--af-color-accent)' : 'var(--af-color-danger)' }};">
        {{ ($entreprise['RNE'] ?? 0) >= 0 ? '+' : '−' }}{{ number_format(abs($entreprise['RNE'] ?? 0), 0, ',', ' ') }}
        <span class="dash-hero-rne-unit">FCFA</span>
    </div>
    <p class="dash-hero-campagne-name">
        {{ (int) ($entreprise['nb_exploitations'] ?? 0) }} exploitation(s) ·
        {{ (int) ($entreprise['nb_campagnes_actives'] ?? 0) }} campagne(s) active(s)
    </p>
    <div class="dash-mini-metrics">
        <div class="dash-mini-cell"><div class="dash-mini-lbl">{{ IndicateursLibelles::labelCourt('PB') }}</div><div class="dash-mini-val" style="color:var(--af-color-accent);">{{ number_format(($entreprise['PB'] ?? 0) / 1000, 1, ',', ' ') }}K</div></div>
        <div class="dash-mini-cell"><div class="dash-mini-lbl">{{ IndicateursLibelles::labelCourt('MB') }}</div><div class="dash-mini-val" style="color:{{ ($entreprise['MB'] ?? 0) >= 0 ? 'var(--af-color-accent)' : 'var(--af-color-danger)' }};">{{ ($entreprise['MB'] ?? 0) >= 0 ? '+' : '' }}{{ number_format(($entreprise['MB'] ?? 0) / 1000, 1, ',', ' ') }}K</div></div>
        <div class="dash-mini-cell"><div class="dash-mini-lbl">{{ IndicateursLibelles::labelCourt('CT') }}</div><div class="dash-mini-val" style="color:var(--af-color-danger);">{{ number_format(($entreprise['CT'] ?? 0) / 1000, 1, ',', ' ') }}K</div></div>
        <div class="dash-mini-cell"><div class="dash-mini-lbl">{{ IndicateursLibelles::labelCourt('RF') }}</div><div class="dash-mini-val">{{ number_format($entreprise['RF'] ?? 0, 1, ',', ' ') }}%</div></div>
    </div>
</div>

{{-- ── Exploitations (choix du contexte) ── --}}
<div class="dash-consol-intro">
    <div class="dash-section-hd" style="margin-bottom:4px;">
        <span class="dash-section-title">Rentabilité par exploitation</span>
    </div>
    <p class="dash-consol-sub">Choisissez une exploitation pour afficher ses campagnes.</p>
</div>
<div class="mb-2">
    <form method="get" action="{{ route('dashboard') }}" class="inline-flex items-center gap-2">
        <input type="hidden" name="exploitation_id" value="{{ $exploitation->id }}">
        <input type="hidden" name="tri_exploitations" value="{{ $triExploitations ?? 'rne_desc' }}">
        <input type="hidden" name="seuil_alerte" value="{{ $seuilAlerte ?? 85 }}">
        <input type="hidden" name="seuil_critique" value="{{ $seuilCritique ?? 100 }}">
        @if($heroActiviteId)
            <input type="hidden" name="campagne" value="{{ $heroActiviteId }}">
        @endif
        <label for="periode-mobile" class="text-[11px] text-white/55">Période</label>
        <select id="periode-mobile" name="periode" onchange="this.form.submit()"
                class="input-glass text-xs py-1.5 min-w-[170px]">
            <option value="all" @selected(($periodeSelection ?? 'all') === 'all')>Toute période</option>
            <option value="12m" @selected(($periodeSelection ?? 'all') === '12m')>12 derniers mois</option>
            <option value="90j" @selected(($periodeSelection ?? 'all') === '90j')>90 derniers jours</option>
            <option value="30j" @selected(($periodeSelection ?? 'all') === '30j')>30 derniers jours</option>
        </select>
    </form>
</div>
<div class="mb-2">
    <form method="get" action="{{ route('dashboard') }}" class="inline-flex items-center gap-2">
        <input type="hidden" name="exploitation_id" value="{{ $exploitation->id }}">
        <input type="hidden" name="periode" value="{{ $periodeSelection ?? 'all' }}">
        <input type="hidden" name="seuil_alerte" value="{{ $seuilAlerte ?? 85 }}">
        <input type="hidden" name="seuil_critique" value="{{ $seuilCritique ?? 100 }}">
        @if($heroActiviteId)
            <input type="hidden" name="campagne" value="{{ $heroActiviteId }}">
        @endif
        <label for="tri-exploitations-mobile" class="text-[11px] text-white/55">Tri</label>
        <select id="tri-exploitations-mobile" name="tri_exploitations" onchange="this.form.submit()"
                class="input-glass text-xs py-1.5 min-w-[170px]">
            <option value="rne_desc" @selected(($triExploitations ?? 'rne_desc') === 'rne_desc')>RNE (du plus élevé)</option>
            <option value="rf_desc" @selected(($triExploitations ?? 'rne_desc') === 'rf_desc')>RF (du plus élevé)</option>
            <option value="mb_desc" @selected(($triExploitations ?? 'rne_desc') === 'mb_desc')>MB (du plus élevé)</option>
            <option value="nom_asc" @selected(($triExploitations ?? 'rne_desc') === 'nom_asc')>Nom (A → Z)</option>
        </select>
    </form>
</div>
<div class="dash-chips">
    @foreach($exploitationsResume as $exp)
        <a href="{{ route('dashboard', ['exploitation_id' => $exp['id'], 'tri_exploitations' => ($triExploitations ?? 'rne_desc'), 'periode' => ($periodeSelection ?? 'all'), 'seuil_alerte' => ($seuilAlerte ?? 85), 'seuil_critique' => ($seuilCritique ?? 100)]) }}"
           class="dash-chip {{ $exp['active'] ? 'active' : '' }}">
            {{ $exp['nom'] }} · {{ number_format($exp['RNE'], 0, ',', ' ') }}
        </a>
    @endforeach
</div>

{{-- ── Sélecteur campagne (chips scrollables) ── --}}
@if(count($activitesCards) > 1)
    <div class="dash-chips">
        @foreach($activitesCards as $c)
            <a href="{{ route('dashboard', ['exploitation_id' => $exploitation->id, 'campagne' => $c['id'], 'tri_exploitations' => ($triExploitations ?? 'rne_desc'), 'periode' => ($periodeSelection ?? 'all'), 'seuil_alerte' => ($seuilAlerte ?? 85), 'seuil_critique' => ($seuilCritique ?? 100)]) }}"
               class="dash-chip {{ $heroActiviteId == $c['id'] ? 'active' : '' }}">
                {{ $c['nom'] }}
            </a>
        @endforeach
    </div>
@endif

@if($isCooperative ?? false)
<div class="mb-3">
    <form method="get" action="{{ route('dashboard') }}" class="inline-flex items-center gap-2 flex-wrap">
        <input type="hidden" name="exploitation_id" value="{{ $exploitation->id }}">
        <input type="hidden" name="tri_exploitations" value="{{ $triExploitations ?? 'rne_desc' }}">
        <input type="hidden" name="periode" value="{{ $periodeSelection ?? 'all' }}">
        @if($heroActiviteId)
            <input type="hidden" name="campagne" value="{{ $heroActiviteId }}">
        @endif
        <span class="text-[11px] text-white/55">Seuils alertes</span>
        <label class="text-[11px] text-white/50">Alerte</label>
        <input type="number" min="1" max="100" step="1" name="seuil_alerte" value="{{ (int) ($seuilAlerte ?? 85) }}" class="input-glass text-xs py-1.5 w-16">
        <label class="text-[11px] text-white/50">Critique</label>
        <input type="number" min="1" max="200" step="1" name="seuil_critique" value="{{ (int) ($seuilCritique ?? 100) }}" class="input-glass text-xs py-1.5 w-16">
        <button type="submit" class="btn-outline text-xs px-3 py-1.5">Appliquer</button>
    </form>
</div>
@endif

{{-- ── Alerte budget ── --}}
@if(count($alertesBudget) > 0)
    <div class="dash-alert {{ $bannerBudgetCritique ? 'dash-alert-critique' : 'dash-alert-warning' }}">
        <span style="font-size:16px; flex-shrink:0; margin-top:1px;">⚠️</span>
        <div>
            <p class="dash-alert-text" style="color:{{ $bannerBudgetCritique ? '#fca5a5' : '#fcd34d' }};">
                @if($bannerBudgetCritique)
                    Budget dépassé sur {{ count($alertesBudget) }} campagne(s)
                @else
                    Budget à {{ number_format($alertesBudget[0]['budget_pct'] ?? 0, 0) }}% — {{ $alertesBudget[0]['nom'] }}
                @endif
            </p>
        </div>
    </div>
@endif

{{-- ── Hero Card ── --}}
<div class="dash-hero" style="border-color:{{ $statutHeroConfig['border'] }};">

    {{-- Badge statut --}}
    <div class="dash-hero-badge"
         style="background:{{ $statutHeroConfig['bg'] }}; border:1px solid {{ $statutHeroConfig['border'] }}; color:{{ $statutHeroConfig['color'] }};">
        <span class="dash-hero-badge-dot"></span>
        {{ $statutHeroConfig['label'] }}
    </div>

    {{-- Label + grand chiffre RNE --}}
    <p class="dash-hero-label">{{ IndicateursLibelles::label('RNE') }}</p>
    <div class="dash-hero-rne" style="color:{{ $rneHero >= 0 ? 'var(--af-color-accent)' : 'var(--af-color-danger)' }};">
        {{ $rneHero >= 0 ? '+' : '−' }}{{ number_format(abs($rneHero), 0, ',', ' ') }}
        <span class="dash-hero-rne-unit">FCFA</span>
    </div>
    <p class="dash-hero-campagne-name">{{ $heroTitre }}</p>

    {{-- 4 mini métriques --}}
    <div class="dash-mini-metrics">
        <div class="dash-mini-cell" title="{{ IndicateursLibelles::label('PB') }}">
            <div class="dash-mini-lbl">{{ IndicateursLibelles::labelCourt('PB') }}</div>
            <div class="dash-mini-val" style="color:var(--af-color-accent);">{{ number_format($pbHero / 1000, 1, ',', ' ') }}K</div>
        </div>
        <div class="dash-mini-cell" title="{{ IndicateursLibelles::label('MB') }}">
            <div class="dash-mini-lbl">{{ IndicateursLibelles::labelCourt('MB') }}</div>
            <div class="dash-mini-val" style="color:{{ $mbHero >= 0 ? 'var(--af-color-accent)' : 'var(--af-color-danger)' }};">
                {{ $mbHero >= 0 ? '+' : '' }}{{ number_format($mbHero / 1000, 1, ',', ' ') }}K
            </div>
        </div>
        <div class="dash-mini-cell" title="{{ IndicateursLibelles::label('CT') }}">
            <div class="dash-mini-lbl">{{ IndicateursLibelles::labelCourt('CT') }}</div>
            <div class="dash-mini-val" style="color:var(--af-color-danger);">{{ number_format($ctHero / 1000, 1, ',', ' ') }}K</div>
        </div>
        <div class="dash-mini-cell" title="{{ IndicateursLibelles::label('RF') }}">
            <div class="dash-mini-lbl">{{ IndicateursLibelles::labelCourt('RF') }}</div>
            <div class="dash-mini-val" style="color:var(--af-text-body);">{{ number_format($rfHero, 1, ',', ' ') }}%</div>
        </div>
    </div>

    {{-- Actions hero --}}
    @if($heroActiviteId)
    <div class="dash-hero-actions">
        <a href="{{ route('activites.show', $heroActiviteId) }}" class="dash-hero-btn dash-hero-btn-primary">
            Voir le détail →
        </a>
        @if($infoAbonnement['peut_pdf'] ?? false)
            <a href="{{ route('rapports.index', ['activite_id' => $heroActiviteId]) }}" class="dash-hero-btn dash-hero-btn-ghost">
                📄 PDF
            </a>
        @else
            <a href="{{ route('abonnement') }}" class="dash-hero-btn dash-hero-btn-ghost" title="Nécessite un abonnement">
                🔒 PDF
            </a>
        @endif
    </div>
    @endif
</div>

{{-- ── Quick actions (mêmes pictos que la barre web : plus, document-text + building pour campagnes) ── --}}
<div class="dash-quick">
    <a href="{{ route('transactions.create') }}" class="dash-quick-btn">
        <div class="dash-quick-icon dash-quick-icon-green">
            <x-icon name="plus" class="w-[19px] h-[19px] text-[var(--af-color-accent)]" />
        </div>
        Saisir
    </a>
    <a href="{{ route('activites.index', ['exploitation_id' => $exploitation->id]) }}" class="dash-quick-btn">
        <div class="dash-quick-icon dash-quick-icon-amber">
            <x-icon name="building-office-2" class="w-[19px] h-[19px] text-[var(--af-color-warning)]" />
        </div>
        Campagnes
    </a>
    <a href="{{ route('rapports.index') }}" class="dash-quick-btn">
        <div class="dash-quick-icon dash-quick-icon-blue">
            <x-icon name="document-text" class="w-[17px] h-[17px] text-[var(--af-color-info)]" />
        </div>
        Rapports
    </a>
</div>

{{-- ── Indicateurs consolidés (parité web) ── --}}
<div class="dash-consol-intro">
    <div class="dash-section-hd" style="margin-bottom:4px;">
        <span class="dash-section-title">Indicateurs consolidés</span>
    </div>
    <p class="dash-consol-sub">{{ $periodeTableauBord['libelle_periode'] ?? 'Synthèse sur les campagnes en cours.' }}</p>
    @if(!empty($messagePlancherAbonnement))
        <p class="text-[11px] text-amber-200/85 mt-1">{{ $messagePlancherAbonnement }}</p>
    @endif
    @if(!empty($resultats['consolide']['donnees_indicatives']))
        <p class="text-[11px] text-amber-100/90 mt-1">Peu de données saisies : les indicateurs sont indicatifs.</p>
    @endif
    @if(($resultats['consolide']['nb_campagnes_actives'] ?? 0) > 1)
        <p class="text-[11px] text-white/50 mt-1">Détail par campagne plus bas — {{ $resultats['consolide']['nb_campagnes_actives'] }} campagne(s) en cours.</p>
    @endif
</div>
<div class="dash-consol-grid">
    {{-- Pictos identiques au bloc web « Indicateurs consolidés » (mêmes path SVG, stroke-width 2) --}}
    <div class="dash-consol-card">
        <div class="dash-consol-card-hd">
            <span class="dash-consol-lbl">{{ IndicateursLibelles::label('PB') }}</span>
            <div class="dash-consol-kpi-icon" style="background:var(--af-stat-mobile-vert-bg);border:1px solid var(--af-stat-mobile-vert-border);" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="var(--af-color-accent)" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
        </div>
        <div class="dash-consol-val" style="color:var(--af-color-accent);">{{ number_format($pbCons / 1000, 0, ',', ' ') }}<span class="dash-consol-unit"> K FCFA</span></div>
    </div>
    <div class="dash-consol-card">
        <div class="dash-consol-card-hd">
            <span class="dash-consol-lbl">{{ IndicateursLibelles::label('CT') }}</span>
            <div class="dash-consol-kpi-icon" style="background:var(--af-stat-mobile-rouge-bg);border:1px solid var(--af-stat-mobile-rouge-border);" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="var(--af-color-danger)" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 17H5m0 0V9m0 8l8-8 4 4 6-6"/>
                </svg>
            </div>
        </div>
        <div class="dash-consol-val" style="color:var(--af-color-danger);">{{ number_format($ctCons / 1000, 0, ',', ' ') }}<span class="dash-consol-unit"> K FCFA</span></div>
    </div>
    <div class="dash-consol-card">
        <div class="dash-consol-card-hd">
            <span class="dash-consol-lbl">{{ IndicateursLibelles::label('MB') }}</span>
            <div class="dash-consol-kpi-icon"
                 style="background:{{ $mbCons >= 0 ? 'var(--af-stat-mobile-vert-bg)' : 'var(--af-stat-mobile-rouge-bg)' }};border:1px solid {{ $mbCons >= 0 ? 'var(--af-stat-mobile-vert-border)' : 'var(--af-stat-mobile-rouge-border)' }};"
                 aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="{{ $mbCons >= 0 ? 'var(--af-color-accent)' : 'var(--af-color-danger)' }}" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <div class="dash-consol-val" style="color:{{ $mbCons >= 0 ? 'var(--af-color-accent)' : 'var(--af-color-danger)' }};">
            {{ $mbCons >= 0 ? '+' : '' }}{{ number_format($mbCons / 1000, 0, ',', ' ') }}<span class="dash-consol-unit"> K FCFA</span>
        </div>
    </div>
    <div class="dash-consol-card">
        <div class="dash-consol-card-hd">
            <span class="dash-consol-lbl">Statut global</span>
            <div class="dash-consol-kpi-icon" style="background:{{ $statutConfigMobile['bg'] }};border:1px solid {{ $statutConfigMobile['border'] }};font-size:18px;line-height:1;">{{ $statutConfigMobile['emoji'] }}</div>
        </div>
        <div class="dash-consol-val" style="color:{{ $statutConfigMobile['color'] }}; font-size:13px;">{{ $statutConfigMobile['label'] }}</div>
        <div class="dash-consol-sub" style="margin-top:6px;">{{ IndicateursLibelles::label('RF') }} : {{ number_format($resultats['consolide']['RF'] ?? 0, 1, ',', ' ') }}%</div>
        <p class="dash-consol-sub" style="margin-top:8px;line-height:1.35;">Synthèse sur les totaux — le détail par campagne indique si le seuil d’équilibre est atteint.</p>
    </div>
</div>

{{-- ── Évolution reste avant fixes + alertes budget (parité web) ── --}}
<div class="dash-section-hd" style="margin-top:8px;">
    <div>
        <span class="dash-section-title">Évolution — {{ IndicateursLibelles::label('MB') }}</span>
        <p class="dash-consol-sub" style="margin-top:4px;">
            @if($chartActiviteId && $heroInd) Campagne : {{ $heroInd['nom'] ?? '—' }} · 12 derniers mois
            @else 12 derniers mois @endif
        </p>
    </div>
</div>
<div class="dash-chart-card">
    @if($chartActiviteId && session('api_token'))
        <canvas id="chartMB" height="200"></canvas>
    @else
        <p class="dash-consol-sub" style="padding:24px 8px; text-align:center;">Aucune campagne active ou reconnectez-vous pour afficher le graphique.</p>
    @endif
</div>

@if(count($alertesBudget) > 0)
<div class="dash-alert-list-card">
    <div class="dash-alert-list-title">Alertes budget</div>
    @foreach($alertesBudget as $a)
        <div class="dash-alert-mini-row">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:8px;">
                <span style="font-family:var(--font-ui),sans-serif; font-size:13px; color:var(--af-text-body);">{{ $a['nom'] }}</span>
                <span style="font-family:var(--font-ui),sans-serif; font-size:11px; font-weight:700; color:{{ ($a['budget_pct'] ?? 0) >= 100 ? 'var(--af-color-danger)' : 'var(--af-color-warning)' }};">{{ number_format($a['budget_pct'] ?? 0, 0, ',', ' ') }} %</span>
            </div>
            <div style="margin-top:6px; height:4px; border-radius:999px; background:var(--af-glass-08); overflow:hidden;">
                <div style="height:100%; border-radius:999px; width:{{ min(100, $a['budget_pct'] ?? 0) }}%; background:{{ ($a['budget_pct'] ?? 0) >= 100 ? 'var(--af-color-danger)' : 'var(--af-color-warning)' }};"></div>
            </div>
        </div>
    @endforeach
</div>
@endif

{{-- ── Mes campagnes ── --}}
@if(count($activitesCards) > 0)
<div style="margin-bottom: 24px;">
    <div class="dash-section-hd">
        <span class="dash-section-title">Mes campagnes</span>
        <a href="{{ route('activites.create', ['exploitation_id' => $exploitation->id]) }}" class="dash-section-link">+ Nouvelle</a>
    </div>

    @foreach($activitesCards as $c)
        @php
            $mbC     = $c['marge'] ?? 0;
            $pctC    = $c['budget_pct'] ?? null;
            $prevC   = $c['budget_prev'] ?? 0;
            $couleurC = ($pctC !== null && $pctC >= 100) ? 'var(--af-color-danger)' : (($pctC !== null && $pctC >= 85) ? 'var(--af-color-warning)' : 'var(--af-color-accent)');
            $badgeC = match($c['statut_indicateurs'] ?? 'rouge') {
                'vert'   => ['label' => 'RENTABLE',    'color' => 'var(--af-color-accent)', 'bg' => 'var(--af-stat-vert-bg)',  'border' => 'var(--af-stat-vert-border-tight)'],
                'orange' => ['label' => 'SURVEILLER',  'color' => 'var(--af-color-warning)', 'bg' => 'var(--af-stat-orange-bg)',  'border' => 'var(--af-amber-tint-border)'],
                default  => ['label' => 'DÉFICIT',     'color' => 'var(--af-color-danger)', 'bg' => 'var(--af-stat-rouge-bg)', 'border' => 'var(--af-stat-rouge-border)'],
            };
        @endphp
        <a href="{{ route('activites.show', $c['id']) }}" class="dash-camp-card">

            {{-- Haut : emoji + nom + badge --}}
            <div class="dash-camp-card-top">
                <div class="dash-camp-card-left">
                    <div class="dash-camp-emoji">
                        <x-icon name="building-office-2" class="w-[17px] h-[17px] text-[var(--af-color-accent)] opacity-80" />
                    </div>
                    <div>
                        <div class="dash-camp-nom">{{ $c['nom'] }}</div>
                        <div class="dash-camp-type">{{ ucfirst(str_replace('_', ' ', $c['type'])) }}</div>
                    </div>
                </div>
                <span class="dash-camp-badge"
                      style="background:{{ $badgeC['bg'] }}; border:1px solid {{ $badgeC['border'] }}; color:{{ $badgeC['color'] }};">
                    {{ $badgeC['label'] }}
                </span>
            </div>

            {{-- Barre budget --}}
            @if($prevC > 0 && $pctC !== null)
            <div class="dash-camp-budget-bar">
                <div class="dash-camp-budget-labels">
                    <span>Budget utilisé</span>
                    <span style="color:{{ $couleurC }}; font-weight:600;">{{ $pctC }}% — {{ number_format($prevC / 1000, 0, ',', ' ') }}K FCFA</span>
                </div>
                <div class="dash-camp-budget-track">
                    <div class="dash-camp-budget-fill" style="width:{{ min(100, $pctC) }}%; background:{{ $couleurC }};"></div>
                </div>
            </div>
            @endif

            {{-- Métriques bas --}}
            <div class="dash-camp-metrics" style="margin-top:12px;">
                <div class="dash-camp-metric-cell">
                    <div class="dash-camp-metric-lbl">Recettes</div>
                    <div class="dash-camp-metric-val" style="color:var(--af-color-accent);">{{ number_format(($c['recettes'] ?? 0) / 1000, 1, ',', ' ') }}K</div>
                </div>
                <div class="dash-camp-metric-cell">
                    <div class="dash-camp-metric-lbl">Dépenses</div>
                    <div class="dash-camp-metric-val" style="color:var(--af-color-danger);">{{ number_format(($c['depenses'] ?? 0) / 1000, 1, ',', ' ') }}K</div>
                </div>
                <div class="dash-camp-metric-cell">
                    <div class="dash-camp-metric-lbl">{{ IndicateursLibelles::labelCourt('MB') }}</div>
                    <div class="dash-camp-metric-val" style="color:{{ $mbC >= 0 ? 'var(--af-color-accent)' : 'var(--af-color-danger)' }};">
                        {{ $mbC >= 0 ? '+' : '' }}{{ number_format($mbC / 1000, 1, ',', ' ') }}K
                    </div>
                </div>
            </div>
        </a>
    @endforeach
</div>
@else
{{-- Aucune campagne --}}
<div style="background:var(--af-mobile-surface-card); border:1px solid var(--af-mobile-border-strong); border-radius:var(--af-radius-lg); padding:28px 20px; text-align:center; margin-bottom:24px; backdrop-filter:blur(var(--af-blur-card-mobile)); -webkit-backdrop-filter:blur(var(--af-blur-card-mobile)); box-shadow:var(--af-shadow-card-lg);">
    <div style="font-size:30px; margin-bottom:10px;">🌱</div>
    <p style="font-family:var(--font-display),sans-serif; font-size:14px; font-weight:600; color:rgba(255, 255, 255, 0.8); margin-bottom:6px;">Aucune campagne active</p>
    <p style="font-family:var(--font-ui),sans-serif; font-size:12px; color:var(--af-text-subtle); margin-bottom:14px;">Créez votre première campagne pour commencer à suivre vos résultats.</p>
    <a href="{{ route('activites.create', ['exploitation_id' => $exploitation->id]) }}"
       style="display:inline-flex; align-items:center; gap:6px; font-family:var(--font-ui),sans-serif; font-size:12px; font-weight:600; color:white; background:var(--af-color-accent-dark); padding:8px 16px; border-radius:10px; text-decoration:none;">
        + Créer une campagne
    </a>
</div>
@endif

{{-- ── Dernières transactions (parité web : jusqu'à 20) ── --}}
@if($dernieresTransactions->isNotEmpty())
<div style="margin-bottom: 16px;">
    <div class="dash-section-hd">
        <div>
            <span class="dash-section-title">Dernières transactions</span>
            <p class="dash-consol-sub" style="margin-top:4px;">Jusqu'à 20 opérations les plus récentes</p>
        </div>
        <a href="{{ route('activites.index', ['exploitation_id' => $exploitation->id]) }}" class="dash-section-link">Voir tout</a>
    </div>

    <div style="background:var(--af-mobile-surface-muted); border:1px solid var(--af-glass-14); border-radius:var(--af-radius-lg); padding:4px 16px; backdrop-filter:blur(var(--af-blur-flash)); -webkit-backdrop-filter:blur(var(--af-blur-flash)); box-shadow:var(--af-shadow-card-lg);">
        @foreach($dernieresTransactions as $t)
            @php
                $catLabel = ucfirst(str_replace('_', ' ', $t->categorie));
                $isRecette = $t->type === 'recette';
            @endphp
            <div class="dash-tx-item">
                <div class="dash-tx-body">
                    <div class="dash-tx-categorie">{{ $catLabel }}</div>
                    <div class="dash-tx-meta">
                        {{ $t->date_transaction->format('d/m/Y') }}
                        @if($t->activite) · {{ $t->activite->nom }} @endif
                    </div>
                </div>
                <div class="dash-tx-actions">
                    <div class="dash-tx-montant" style="color:{{ $isRecette ? 'var(--af-color-accent)' : 'var(--af-color-danger)' }};">
                        {{ $isRecette ? '+' : '−' }}{{ number_format($t->montant, 0, ',', ' ') }}
                    </div>
                    <a href="{{ route('transactions.edit', $t->id) }}">Modifier</a>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════
     FIN MOBILE — début DESKTOP (code original inchangé)
══════════════════════════════════════════════════════════════ --}}
@else

    <p class="text-xs font-semibold uppercase tracking-wider text-white/45 mb-3" id="dash-desktop-overview">Vue d’ensemble</p>

    <div class="card mb-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="section-subtitle">Entreprise</p>
                <h2 class="section-title mb-1">Rentabilité globale</h2>
                <p class="section-subtitle">
                    {{ (int) ($entreprise['nb_exploitations'] ?? 0) }} exploitation(s) ·
                    {{ (int) ($entreprise['nb_campagnes_actives'] ?? 0) }} campagne(s) active(s)
                </p>
            </div>
            <span class="font-ui text-xs px-3 py-1.5 rounded-full"
                  style="background:{{ $statutEntrepriseConfig['bg'] }};border:1px solid {{ $statutEntrepriseConfig['border'] }};color:{{ $statutEntrepriseConfig['color'] }};">
                {{ $statutEntrepriseConfig['label'] }}
            </span>
        </div>
        <div class="dashboard-mini-metrics mt-4">
            <div class="dashboard-mini-metrics__cell"><span class="dashboard-mini-metrics__label">{{ IndicateursLibelles::labelCourt('RNE') }}</span><span class="dashboard-mini-metrics__val {{ ($entreprise['RNE'] ?? 0) >= 0 ? 'text-emerald-400' : 'text-red-400' }}">{{ ($entreprise['RNE'] ?? 0) >= 0 ? '+' : '−' }}{{ number_format(abs($entreprise['RNE'] ?? 0), 0, ',', ' ') }}</span></div>
            <div class="dashboard-mini-metrics__cell"><span class="dashboard-mini-metrics__label">{{ IndicateursLibelles::labelCourt('PB') }}</span><span class="dashboard-mini-metrics__val text-emerald-400">{{ number_format(($entreprise['PB'] ?? 0) / 1000, 1, ',', ' ') }}K</span></div>
            <div class="dashboard-mini-metrics__cell"><span class="dashboard-mini-metrics__label">{{ IndicateursLibelles::labelCourt('MB') }}</span><span class="dashboard-mini-metrics__val {{ ($entreprise['MB'] ?? 0) >= 0 ? 'text-emerald-400' : 'text-red-400' }}">{{ ($entreprise['MB'] ?? 0) >= 0 ? '+' : '' }}{{ number_format(($entreprise['MB'] ?? 0) / 1000, 1, ',', ' ') }}K</span></div>
            <div class="dashboard-mini-metrics__cell"><span class="dashboard-mini-metrics__label">{{ IndicateursLibelles::labelCourt('RF') }}</span><span class="dashboard-mini-metrics__val text-white/90">{{ number_format($entreprise['RF'] ?? 0, 1, ',', ' ') }}%</span></div>
        </div>
    </div>

    <div class="card mb-6">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
            <h2 class="section-title">Rentabilité par exploitation</h2>
            <p class="section-subtitle">Sélection actuelle : <strong>{{ $exploitation->nom }}</strong></p>
        </div>
        <form method="get" action="{{ route('dashboard') }}" class="mb-3 inline-flex items-center gap-2">
            <input type="hidden" name="exploitation_id" value="{{ $exploitation->id }}">
            <input type="hidden" name="tri_exploitations" value="{{ $triExploitations ?? 'rne_desc' }}">
            <input type="hidden" name="seuil_alerte" value="{{ $seuilAlerte ?? 85 }}">
            <input type="hidden" name="seuil_critique" value="{{ $seuilCritique ?? 100 }}">
            @if($heroActiviteId)
                <input type="hidden" name="campagne" value="{{ $heroActiviteId }}">
            @endif
            <label for="periode-desktop" class="section-subtitle whitespace-nowrap">Période</label>
            <select id="periode-desktop" name="periode" onchange="this.form.submit()"
                    class="input-glass text-sm py-1.5 min-w-[210px]">
                <option value="all" @selected(($periodeSelection ?? 'all') === 'all')>Toute période</option>
                <option value="12m" @selected(($periodeSelection ?? 'all') === '12m')>12 derniers mois</option>
                <option value="90j" @selected(($periodeSelection ?? 'all') === '90j')>90 derniers jours</option>
                <option value="30j" @selected(($periodeSelection ?? 'all') === '30j')>30 derniers jours</option>
            </select>
        </form>
        <form method="get" action="{{ route('dashboard') }}" class="mb-3 inline-flex items-center gap-2">
            <input type="hidden" name="exploitation_id" value="{{ $exploitation->id }}">
            <input type="hidden" name="periode" value="{{ $periodeSelection ?? 'all' }}">
            <input type="hidden" name="seuil_alerte" value="{{ $seuilAlerte ?? 85 }}">
            <input type="hidden" name="seuil_critique" value="{{ $seuilCritique ?? 100 }}">
            @if($heroActiviteId)
                <input type="hidden" name="campagne" value="{{ $heroActiviteId }}">
            @endif
            <label for="tri-exploitations-desktop" class="section-subtitle whitespace-nowrap">Tri des exploitations</label>
            <select id="tri-exploitations-desktop" name="tri_exploitations" onchange="this.form.submit()"
                    class="input-glass text-sm py-1.5 min-w-[230px]">
                <option value="rne_desc" @selected(($triExploitations ?? 'rne_desc') === 'rne_desc')>RNE (du plus élevé)</option>
                <option value="rf_desc" @selected(($triExploitations ?? 'rne_desc') === 'rf_desc')>RF (du plus élevé)</option>
                <option value="mb_desc" @selected(($triExploitations ?? 'rne_desc') === 'mb_desc')>MB (du plus élevé)</option>
                <option value="nom_asc" @selected(($triExploitations ?? 'rne_desc') === 'nom_asc')>Nom (A → Z)</option>
            </select>
        </form>
        @if($isCooperative ?? false)
        <form method="get" action="{{ route('dashboard') }}" class="mb-3 inline-flex items-center gap-2 flex-wrap">
            <input type="hidden" name="exploitation_id" value="{{ $exploitation->id }}">
            <input type="hidden" name="tri_exploitations" value="{{ $triExploitations ?? 'rne_desc' }}">
            <input type="hidden" name="periode" value="{{ $periodeSelection ?? 'all' }}">
            @if($heroActiviteId)
                <input type="hidden" name="campagne" value="{{ $heroActiviteId }}">
            @endif
            <label class="section-subtitle whitespace-nowrap">Seuil alerte</label>
            <input type="number" min="1" max="100" step="1" name="seuil_alerte" value="{{ (int) ($seuilAlerte ?? 85) }}" class="input-glass text-sm py-1.5 w-24">
            <label class="section-subtitle whitespace-nowrap">Seuil critique</label>
            <input type="number" min="1" max="200" step="1" name="seuil_critique" value="{{ (int) ($seuilCritique ?? 100) }}" class="input-glass text-sm py-1.5 w-24">
            <button type="submit" class="btn-outline text-sm px-4 py-2">Appliquer</button>
        </form>
        @endif
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-3">
            @foreach($exploitationsResume as $exp)
                <a href="{{ route('dashboard', ['exploitation_id' => $exp['id'], 'tri_exploitations' => ($triExploitations ?? 'rne_desc'), 'periode' => ($periodeSelection ?? 'all'), 'seuil_alerte' => ($seuilAlerte ?? 85), 'seuil_critique' => ($seuilCritique ?? 100)]) }}"
                   class="rounded-xl border px-4 py-3 transition {{ $exp['active'] ? 'border-emerald-400/60 bg-emerald-500/10' : 'border-white/15 bg-white/5 hover:bg-white/10' }}">
                    <div class="flex items-center justify-between gap-3">
                        <p class="font-ui text-sm font-semibold text-white/90">{{ $exp['nom'] }}</p>
                        <span class="text-xs {{ ($exp['RNE'] ?? 0) >= 0 ? 'text-emerald-300' : 'text-red-300' }}">
                            RNE {{ ($exp['RNE'] ?? 0) >= 0 ? '+' : '−' }}{{ number_format(abs($exp['RNE'] ?? 0), 0, ',', ' ') }}
                        </span>
                    </div>
                    <p class="mt-1 text-xs text-white/60">
                        {{ (int) ($exp['nb_campagnes_actives'] ?? 0) }} campagne(s) · RF {{ number_format($exp['RF'] ?? 0, 1, ',', ' ') }}%
                    </p>
                </a>
            @endforeach
        </div>
    </div>

    <!-- Carte résumé (focus campagne ou exploitation) -->
    <div class="dashboard-hero glass mb-8" aria-labelledby="dash-desktop-overview">
        <div class="dashboard-hero__top">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <div class="flex flex-wrap items-center gap-3 mb-2">
                        <span class="{{ match($statutHero) { 'vert' => 'badge-vert', 'orange' => 'badge-orange', default => 'badge-rouge' } }} font-ui tracking-wide">
                            {{ $statutHeroConfig['label'] }}
                        </span>
                        @if(count($activitesCards) > 1)
                            <form method="get" action="{{ route('dashboard') }}" class="inline-flex items-center gap-2">
                                <input type="hidden" name="exploitation_id" value="{{ $exploitation->id }}">
                                <input type="hidden" name="tri_exploitations" value="{{ $triExploitations ?? 'rne_desc' }}">
                                <input type="hidden" name="periode" value="{{ $periodeSelection ?? 'all' }}">
                                <input type="hidden" name="seuil_alerte" value="{{ $seuilAlerte ?? 85 }}">
                                <input type="hidden" name="seuil_critique" value="{{ $seuilCritique ?? 100 }}">
                                <label for="dash-campagne" class="section-subtitle whitespace-nowrap">Campagne</label>
                                <select id="dash-campagne" name="campagne" onchange="this.form.submit()"
                                        class="input-glass text-sm py-1.5 min-w-[180px]">
                                    @foreach($activitesCards as $c)
                                        <option value="{{ $c['id'] }}" @selected($heroActiviteId == $c['id'])>{{ $c['nom'] }}</option>
                                    @endforeach
                                </select>
                            </form>
                        @endif
                    </div>
                    <h2 class="section-title mb-1">{{ $heroTitre }}</h2>
                    <p class="section-subtitle">{{ $heroSousTitre }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    @if($heroActiviteId)
                        <a href="{{ route('activites.show', $heroActiviteId) }}" class="btn-glass-green text-sm px-4 py-2">
                            Voir le détail
                        </a>
                    @endif
                    @if(($infoAbonnement['peut_pdf'] ?? false) && $heroActiviteId)
                        <a href="{{ route('rapports.index', ['activite_id' => $heroActiviteId]) }}"
                           class="btn-glass text-sm px-4 py-2">
                            Générer un PDF
                        </a>
                    @elseif(!($infoAbonnement['peut_pdf'] ?? false))
                        <a href="{{ route('abonnement') }}" class="btn-glass text-sm px-4 py-2 opacity-80" title="Plan Essentielle+">
                            PDF (abonnement)
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="dashboard-hero__rne">
            <p class="kpi-label mb-1">{{ IndicateursLibelles::label('RNE') }}</p>
            <div class="dashboard-hero__rne-value font-display {{ $rneHero >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                {{ $rneHero >= 0 ? '+' : '−' }}{{ number_format(abs($rneHero), 0, ',', ' ') }}
                <span class="text-xl font-ui font-normal text-white/52 ml-1">FCFA</span>
            </div>
        </div>

        <div class="dashboard-mini-metrics">
            <div class="dashboard-mini-metrics__cell">
                <span class="dashboard-mini-metrics__label" title="{{ IndicateursLibelles::label('PB') }}">{{ IndicateursLibelles::labelCourt('PB') }}</span>
                <span class="dashboard-mini-metrics__val text-emerald-400">{{ number_format($pbHero / 1000, 1, ',', ' ') }}K</span>
            </div>
            <div class="dashboard-mini-metrics__cell">
                <span class="dashboard-mini-metrics__label" title="{{ IndicateursLibelles::label('MB') }}">{{ IndicateursLibelles::labelCourt('MB') }}</span>
                <span class="dashboard-mini-metrics__val {{ $mbHero >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                    {{ $mbHero >= 0 ? '+' : '' }}{{ number_format($mbHero / 1000, 1, ',', ' ') }}K
                </span>
            </div>
            <div class="dashboard-mini-metrics__cell">
                <span class="dashboard-mini-metrics__label" title="{{ IndicateursLibelles::label('CT') }}">{{ IndicateursLibelles::labelCourt('CT') }}</span>
                <span class="dashboard-mini-metrics__val text-red-400">{{ number_format($ctHero / 1000, 1, ',', ' ') }}K</span>
            </div>
            <div class="dashboard-mini-metrics__cell">
                <span class="dashboard-mini-metrics__label" title="{{ IndicateursLibelles::label('RF') }}">{{ IndicateursLibelles::labelCourt('RF') }}</span>
                <span class="dashboard-mini-metrics__val text-white/90">{{ number_format($rfHero, 1, ',', ' ') }}%</span>
            </div>
        </div>
    </div>

    @if(count($alertesBudget) > 0)
        <div class="dashboard-alert-strip mb-8 {{ $bannerBudgetCritique ? 'dashboard-alert-strip--critique' : 'dashboard-alert-strip--warning' }}">
            <div class="flex flex-wrap items-center gap-3">
                <span class="text-lg" aria-hidden="true">⚠️</span>
                <div>
                    <p class="font-ui font-semibold text-sm text-white/95">
                        @if($bannerBudgetCritique)
                            Budget dépassé sur au moins une campagne — ajustez vos dépenses ou révisez le prévisionnel.
                        @else
                            Attention : un ou plusieurs budgets approchent la limite (≥ 85 %).
                        @endif
                    </p>
                    <ul class="mt-1 text-xs text-white/70 list-disc list-inside">
                        @foreach($alertesBudget as $a)
                            <li>
                                <strong class="text-white/90">{{ $a['nom'] }}</strong>
                                — {{ number_format($a['budget_pct'] ?? 0, 0, ',', ' ') }} % du budget utilisé
                                ({{ number_format(($a['budget_prev'] ?? 0) / 1000, 0, ',', ' ') }} K FCFA)
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- KPI rapides (consolidé) -->
    <div class="mb-6">
        <h2 class="section-title">Indicateurs consolidés</h2>
        <p class="section-subtitle mt-1">{{ $periodeTableauBord['libelle_periode'] ?? 'Synthèse sur les campagnes en cours.' }}</p>
        @if(!empty($messagePlancherAbonnement))
            <p class="text-xs text-amber-200/80 mt-1">{{ $messagePlancherAbonnement }}</p>
        @endif
        @if(!empty($resultats['consolide']['donnees_indicatives']))
            <p class="text-xs text-amber-100/90 mt-1">Peu de données saisies : les indicateurs sont indicatifs.</p>
        @endif
        @if(($resultats['consolide']['nb_campagnes_actives'] ?? 0) > 1)
            <p class="text-xs text-white/45 mt-1">Détail par campagne ci-dessous — total sur {{ $resultats['consolide']['nb_campagnes_actives'] }} campagne(s) en cours.</p>
        @endif
    </div>

    <div class="dashboard-kpi-grid">
        @php
            $pbCons = $resultats['consolide']['PB'] ?? 0;
            $ctCons = $resultats['consolide']['CT'] ?? 0;
            $mbCons = $resultats['consolide']['MB'] ?? 0;
            $statutCons = $resultats['consolide']['statut'] ?? 'rouge';
        @endphp
        <div class="kpi-glass">
            <div class="flex items-center justify-between">
                <span class="kpi-label">{{ IndicateursLibelles::label('PB') }}</span>
                <div class="kpi-icon-wrap" style="background:var(--af-stat-mobile-vert-bg);border:1px solid var(--af-stat-mobile-vert-border);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="var(--af-color-accent)" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
            <div><div class="kpi-value text-emerald-400">{{ number_format($pbCons / 1000, 0, ',', ' ') }}<span class="kpi-unit">K FCFA</span></div></div>
        </div>
        <div class="kpi-glass">
            <div class="flex items-center justify-between">
                <span class="kpi-label">{{ IndicateursLibelles::label('CT') }}</span>
                <div class="kpi-icon-wrap" style="background:var(--af-stat-mobile-rouge-bg);border:1px solid var(--af-stat-mobile-rouge-border);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="var(--af-color-danger)" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 17H5m0 0V9m0 8l8-8 4 4 6-6"/>
                    </svg>
                </div>
            </div>
            <div><div class="kpi-value text-red-400">{{ number_format($ctCons / 1000, 0, ',', ' ') }}<span class="kpi-unit">K FCFA</span></div></div>
        </div>
        <div class="kpi-glass">
            <div class="flex items-center justify-between">
                <span class="kpi-label">{{ IndicateursLibelles::label('MB') }}</span>
                <div class="kpi-icon-wrap"
                     style="background:{{ $mbCons >= 0 ? 'var(--af-stat-mobile-vert-bg)' : 'var(--af-stat-mobile-rouge-bg)' }};border:1px solid {{ $mbCons >= 0 ? 'var(--af-stat-mobile-vert-border)' : 'var(--af-stat-mobile-rouge-border)' }};">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="{{ $mbCons >= 0 ? 'var(--af-color-accent)' : 'var(--af-color-danger)' }}" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div><div class="kpi-value" style="color:{{ $mbCons >= 0 ? 'var(--af-color-accent)' : 'var(--af-color-danger)' }};">{{ $mbCons >= 0 ? '+' : '' }}{{ number_format($mbCons / 1000, 0, ',', ' ') }}<span class="kpi-unit">K FCFA</span></div></div>
        </div>
        @php
            $statutConfig = match ($statutCons) {
                'vert'   => ['emoji' => '🟢', 'label' => 'Rentable',     'color' => 'var(--af-color-accent)', 'bg' => 'var(--af-stat-mobile-vert-bg)',  'border' => 'var(--af-stat-mobile-vert-border)'],
                'orange' => ['emoji' => '🟠', 'label' => 'À surveiller', 'color' => 'var(--af-color-warning)', 'bg' => 'var(--af-stat-mobile-orange-bg)',  'border' => 'var(--af-stat-mobile-orange-border)'],
                default  => ['emoji' => '🔴', 'label' => 'Déficitaire',  'color' => 'var(--af-color-danger)', 'bg' => 'var(--af-stat-mobile-rouge-bg)', 'border' => 'var(--af-stat-mobile-rouge-border)'],
            };
        @endphp
        <div class="kpi-glass">
            <div class="flex items-center justify-between">
                <span class="kpi-label">Statut global</span>
                <div class="kpi-icon-wrap" style="background:{{ $statutConfig['bg'] }};border:1px solid {{ $statutConfig['border'] }};font-size:22px;">{{ $statutConfig['emoji'] }}</div>
            </div>
            <div>
                <div class="kpi-value" style="color:{{ $statutConfig['color'] }}; font-size:26px;">{{ $statutConfig['label'] }}</div>
                <div class="font-ui text-xs text-white/48 mt-1">{{ IndicateursLibelles::label('RF') }} : {{ number_format($resultats['consolide']['RF'] ?? 0, 1, ',', ' ') }}%</div>
                <p class="font-ui text-[11px] text-white/48 mt-2 max-w-md leading-snug">Ce statut est une synthèse sur les totaux ; chaque campagne peut avoir une couleur et un seuil d’équilibre différents.</p>
            </div>
        </div>
    </div>

    <!-- Graphique + colonne droite -->
    <div class="dashboard-section-spaced">
        <div class="mb-5 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="section-title">Évolution — {{ IndicateursLibelles::label('MB') }}</h2>
                <p class="section-subtitle mt-1">
                    @if($chartActiviteId && $heroInd) Campagne : {{ $heroInd['nom'] ?? '—' }} · 12 derniers mois
                    @else 12 derniers mois @endif
                </p>
            </div>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 card min-h-[280px]">
                @if($chartActiviteId && session('api_token'))
                    <canvas id="chartMB" height="300"></canvas>
                @else
                    <p class="text-sm text-white/45">Aucune campagne active ou reconnectez-vous pour afficher le graphique.</p>
                @endif
            </div>
            <div class="flex flex-col gap-6">
                <div class="card flex-1">
                    <h3 class="font-display text-sm font-semibold text-white/90 mb-3">Alertes budget</h3>
                    @forelse($alertesBudget as $a)
                        <div class="mb-3 pb-3 border-b border-white/10 last:border-0 last:pb-0 last:mb-0">
                            <div class="flex justify-between items-start gap-2">
                                <span class="font-ui text-sm text-white/85">{{ $a['nom'] }}</span>
                                <span class="text-xs font-semibold {{ ($a['budget_pct'] ?? 0) >= 100 ? 'text-red-400' : 'text-amber-400' }}">{{ number_format($a['budget_pct'] ?? 0, 0, ',', ' ') }} %</span>
                            </div>
                            <div class="mt-2 h-1.5 rounded-full bg-white/10 overflow-hidden">
                                <div class="h-full rounded-full transition-all {{ ($a['budget_pct'] ?? 0) >= 100 ? 'bg-red-400' : 'bg-amber-400' }}" style="width: {{ min(100, $a['budget_pct'] ?? 0) }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-white/45">Aucune alerte budgétaire.</p>
                    @endforelse
                </div>
                <div class="card flex-1">
                    <h3 class="font-display text-sm font-semibold text-white/90 mb-3">Activité récente</h3>
                    <ul class="space-y-3">
                        @forelse($transactionsRecentes as $t)
                            <li class="flex justify-between gap-3 text-sm">
                                <div class="min-w-0">
                                    <p class="font-medium text-white/90 truncate">{{ str_replace('_', ' ', $t->categorie) }}</p>
                                    <p class="text-xs text-white/52">{{ $t->date_transaction->format('d/m/Y') }}@if($t->activite) · {{ $t->activite->nom }}@endif</p>
                                </div>
                                <span class="font-semibold shrink-0 {{ $t->type === 'recette' ? 'text-emerald-400' : 'text-red-400' }}">
                                    {{ $t->type === 'recette' ? '+' : '−' }}{{ number_format($t->montant, 0, ',', ' ') }}
                                </span>
                            </li>
                        @empty
                            <li class="text-sm text-white/45">Aucune transaction récente.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau détaillé -->
    <div class="dashboard-section-spaced">
        <div class="mb-5">
            <h2 class="section-title">Dernières transactions</h2>
            <p class="section-subtitle mt-1">Jusqu'à 20 opérations les plus récentes</p>
        </div>
        <div class="card overflow-x-auto">
            <table class="table-glass min-w-[880px]">
                <thead>
                    <tr><th>Date</th><th>Campagne</th><th>Catégorie</th><th>Type</th><th>Nature</th><th class="text-right">Montant</th><th class="text-right">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($dernieresTransactions as $t)
                        @php $nat = $t->nature; $natLabel = $nat === 'fixe' ? 'Fixe' : ($nat === 'variable' ? 'Variable' : '—'); $typeLabel = $t->type === 'recette' ? 'Recette' : 'Dépense'; @endphp
                        <tr>
                            <td class="whitespace-nowrap">{{ $t->date_transaction->format('d/m/Y') }}</td>
                            <td>{{ $t->activite->nom ?? '—' }}</td>
                            <td>{{ str_replace('_', ' ', $t->categorie) }}</td>
                            <td><span class="badge-gris">{{ $typeLabel }}</span></td>
                            <td class="text-white/60">{{ $natLabel }}</td>
                            <td class="text-right font-semibold {{ $t->type === 'recette' ? 'text-emerald-400' : 'text-red-400' }}">
                                {{ $t->type === 'recette' ? '+' : '−' }}{{ number_format($t->montant, 0, ',', ' ') }}
                            </td>
                            <td class="text-right whitespace-nowrap">
                                <a href="{{ route('transactions.edit', $t->id) }}" class="text-emerald-400 hover:underline text-sm font-medium mr-3">Éditer</a>
                                <form action="{{ route('transactions.destroy', $t->id) }}" method="post" class="inline" onsubmit="return confirm('Supprimer cette transaction ?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:underline text-sm font-medium bg-transparent border-0 cursor-pointer p-0">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-white/45 py-8">Aucune transaction.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Campagnes actives -->
    <div class="dashboard-campagnes-section">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="section-title">Campagnes actives</h2>
                <p class="section-subtitle mt-1">{{ count($resultats['par_activite'] ?? []) }} campagne(s) en cours</p>
            </div>
            <a href="{{ route('activites.create', ['exploitation_id' => $exploitation->id]) }}" class="font-ui text-xs font-semibold text-white/60 px-3.5 py-1.5 border border-white/15 rounded-lg hover:text-white hover:border-white/35 transition-colors">
                + Nouvelle campagne
            </a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            @forelse($activitesCards as $c)
                @php
                    $data = $parActivite[$c['id']] ?? [];
                    $budgetPrev = $c['budget_prev'] ?? 0;
                    $pbA = $data['PB'] ?? 0; $ctA = $data['CT'] ?? 0; $mbA = $data['MB'] ?? 0;
                    if ($budgetPrev > 0) { $pourcent = min(100, round(($ctA / $budgetPrev) * 100)); $couleurBarre = $pourcent >= 100 ? 'var(--af-color-danger)' : ($pourcent >= 90 ? 'var(--af-color-warning)' : 'var(--af-color-accent)'); }
                    else { $pourcent = 0; $couleurBarre = 'var(--af-color-accent)'; }
                @endphp
                <a href="{{ route('activites.show', $c['id']) }}" class="card hover:shadow-lg transition-shadow cursor-pointer block no-underline">
                    <div class="flex justify-between items-start mb-2">
                        <p class="font-semibold text-emerald-400 font-ui">{{ $c['nom'] }}</p>
                        <x-status-indicator :statut="$c['statut_indicateurs'] ?? 'rouge'" />
                    </div>
                    <div class="mt-3 mb-2">
                        <div class="flex justify-between mb-1">
                            <span class="font-ui text-[10px] text-white/48">@if($budgetPrev > 0) Budget : {{ $pourcent }}% utilisé @else Aucun budget défini @endif</span>
                            @if($budgetPrev > 0)<span class="font-ui text-[10px]" style="color:{{ $couleurBarre }};">{{ number_format($budgetPrev / 1000, 0, ',', ' ') }}K FCFA</span>@endif
                        </div>
                        <div class="h-1 bg-white/10 rounded overflow-hidden">
                            <div class="h-full transition-all duration-500 rounded" style="width:{{ $pourcent }}%; background:{{ $couleurBarre }};"></div>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2 mb-3">
                        <div class="text-center"><div class="font-ui text-[10px] text-white/48 mb-0.5">Rec.</div><div class="font-display text-sm font-semibold text-emerald-400">{{ number_format($pbA / 1000, 1, ',', ' ') }}K</div></div>
                        <div class="text-center"><div class="font-ui text-[10px] text-white/48 mb-0.5">Dép.</div><div class="font-display text-sm font-semibold text-red-400">{{ number_format($ctA / 1000, 1, ',', ' ') }}K</div></div>
                        <div class="text-center"><div class="font-ui text-[10px] text-white/48 mb-0.5">{{ IndicateursLibelles::labelCourt('MB') }}</div><div class="font-display text-sm font-semibold {{ $mbA >= 0 ? 'text-emerald-400' : 'text-red-400' }}">{{ number_format($mbA, 0, ',', ' ') }}</div></div>
                    </div>
                    <p class="text-xs text-white/45 font-ui inline-flex items-center gap-1">Voir détails <x-icon name="arrow-right" class="w-3.5 h-3.5 opacity-70" /></p>
                </a>
            @empty
                <p class="text-sm text-white/45 col-span-3">Aucune campagne active.</p>
            @endforelse
        </div>
    </div>

@endif {{-- fin @if($platform === 'mobile') --}}
@endsection

@if($chartActiviteId && session('api_token'))
@push('scripts')
<script>
(function () {
    var id = {{ (int) $chartActiviteId }};
    var token = @json(session('api_token'));
    var ctx = document.getElementById('chartMB');
    if (!ctx || !token) return;
    fetch(@json(url('/api/v1/indicateurs/activite')) + '/' + id + '/evolution', {
        headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' },
        credentials: 'same-origin'
    }).then(function (r) { return r.json(); }).then(function (json) {
        if (!json.succes || !json.data || !json.data.evolution) return;
        var ev = json.data.evolution;
        var labels = ev.map(function (e) { return e.mois_num || e.mois; });
        var values = ev.map(function (e) { return (e.MB || 0) / 1000; });
        var fontInter = "'Inter', system-ui, -apple-system, sans-serif";
        var fontSpace = "'Space Grotesk', system-ui, sans-serif";
        var rootStyle = getComputedStyle(document.documentElement);
        var lineColor = (rootStyle.getPropertyValue('--af-color-accent') || '').trim() || '#4ade80';
        var fillColor = (rootStyle.getPropertyValue('--af-stat-vert-bg') || '').trim() || 'rgba(74,222,128,0.12)';
        new Chart(ctx, {
            type: 'line',
            data: { labels: labels, datasets: [{ data: values, borderColor: lineColor, backgroundColor: fillColor, fill: true, tension: 0.3, pointRadius: 2 }] },
            options: {
                responsive: true, font: { family: fontInter },
                plugins: { legend: { display: false }, tooltip: { titleFont: { family: fontSpace, size: 13, weight: '600' }, bodyFont: { family: fontInter, size: 12 }, padding: 10 } },
                scales: {
                    y: { ticks: { font: { family: fontInter, size: 11 }, color: 'rgba(255,255,255,0.40)', callback: function (v) { return v.toFixed(0) + ' K'; } }, grid: { color: 'rgba(255,255,255,0.05)' } },
                    x: { ticks: { font: { family: fontInter, size: 10 }, color: 'rgba(255,255,255,0.36)' }, grid: { display: false } }
                }
            }
        });
    }).catch(function () {});
})();
</script>
@endpush
@endif
