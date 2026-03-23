@extends($layout)
@section('title', 'Tableau de bord — AgroFinance+')
@section('page-title', 'Tableau de bord')
@section('page-subtitle', $exploitation->nom)

@section('topbar-actions')
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
    $statutHero = $heroInd['statut'] ?? ($statut ?? 'rouge');
    $statutHeroConfig = match ($statutHero) {
        'vert'   => ['label' => 'RENTABLE',    'color' => '#4ade80', 'bg' => 'rgba(74,222,128,0.12)',  'border' => 'rgba(74,222,128,0.25)'],
        'orange' => ['label' => 'À SURVEILLER','color' => '#fbbf24', 'bg' => 'rgba(251,191,36,0.12)',  'border' => 'rgba(251,191,36,0.25)'],
        default  => ['label' => 'DÉFICITAIRE', 'color' => '#f87171', 'bg' => 'rgba(248,113,113,0.12)', 'border' => 'rgba(248,113,113,0.25)'],
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

    $typeEmojis = [];
@endphp

{{-- ══════════════════════════════════════════════════════════════
     MOBILE DASHBOARD
══════════════════════════════════════════════════════════════ --}}
@if($platform === 'mobile')

@push('styles')
<style>
/* ── Greeting ── */
.dash-greeting {
    padding: 20px 0 8px;
}
.dash-greeting-hello {
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    color: rgba(255,255,255,0.45);
    font-weight: 400;
    letter-spacing: 0.01em;
    margin-bottom: 2px;
}
.dash-greeting-name {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 22px;
    font-weight: 700;
    color: rgba(255,255,255,0.94);
    letter-spacing: -0.03em;
    line-height: 1.15;
}
.dash-greeting-exploit {
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    color: rgba(255,255,255,0.30);
    margin-top: 3px;
}

/* ── Hero Card ── */
.dash-hero {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.09);
    border-radius: 24px;
    padding: 20px;
    margin-bottom: 16px;
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
}
.dash-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border-radius: 999px;
    padding: 4px 12px;
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    margin-bottom: 14px;
}
.dash-hero-badge-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
    opacity: 0.85;
}
.dash-hero-label {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    font-weight: 500;
    color: rgba(255,255,255,0.35);
    text-transform: uppercase;
    letter-spacing: 0.09em;
    margin-bottom: 6px;
}
.dash-hero-rne {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 40px;
    font-weight: 700;
    letter-spacing: -0.04em;
    line-height: 1;
    margin-bottom: 4px;
}
.dash-hero-rne-unit {
    font-size: 16px;
    font-weight: 400;
    font-family: 'Inter', sans-serif;
    color: rgba(255,255,255,0.35);
    margin-left: 6px;
}
.dash-hero-campagne-name {
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    color: rgba(255,255,255,0.35);
    margin-bottom: 18px;
}

/* ── Mini métriques ── */
.dash-mini-metrics {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0;
    border-top: 1px solid rgba(255,255,255,0.07);
    padding-top: 14px;
    margin-top: 6px;
}
.dash-mini-cell {
    text-align: center;
    padding: 0 4px;
    border-right: 1px solid rgba(255,255,255,0.07);
}
.dash-mini-cell:last-child { border-right: none; }
.dash-mini-lbl {
    font-family: 'Inter', sans-serif;
    font-size: 10px;
    font-weight: 600;
    color: rgba(255,255,255,0.30);
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-bottom: 4px;
}
.dash-mini-val {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: -0.02em;
    line-height: 1;
}

/* ── Hero actions ── */
.dash-hero-actions {
    display: flex;
    gap: 8px;
    margin-top: 16px;
    padding-top: 14px;
    border-top: 1px solid rgba(255,255,255,0.07);
}
.dash-hero-btn {
    flex: 1;
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    font-weight: 600;
    text-align: center;
    padding: 9px 10px;
    border-radius: 12px;
    text-decoration: none;
    transition: all 0.15s;
    cursor: pointer;
    border: none;
}
.dash-hero-btn-primary {
    background: #16a34a;
    color: white;
    border: 1px solid rgba(74,222,128,0.30);
}
.dash-hero-btn-ghost {
    background: rgba(255,255,255,0.06);
    color: rgba(255,255,255,0.65);
    border: 1px solid rgba(255,255,255,0.10);
}

/* ── Campaign selector chips ── */
.dash-chips {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding-bottom: 2px;
    margin-bottom: 16px;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}
.dash-chips::-webkit-scrollbar { display: none; }
.dash-chip {
    flex-shrink: 0;
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    font-weight: 500;
    padding: 6px 14px;
    border-radius: 999px;
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.10);
    color: rgba(255,255,255,0.50);
    text-decoration: none;
    white-space: nowrap;
    transition: all 0.15s;
}
.dash-chip.active {
    background: rgba(74,222,128,0.15);
    border-color: rgba(74,222,128,0.30);
    color: #4ade80;
    font-weight: 600;
}

/* ── Quick actions ── */
.dash-quick {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-bottom: 24px;
}
.dash-quick-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 14px 8px;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.09);
    border-radius: 18px;
    text-decoration: none;
    color: rgba(255,255,255,0.70);
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    font-weight: 500;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    transition: background 0.15s;
}
.dash-quick-btn:active { background: rgba(255,255,255,0.10); }
.dash-quick-icon {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}
.dash-quick-icon-green  { background: rgba(74,222,128,0.15); border: 1px solid rgba(74,222,128,0.22); }
.dash-quick-icon-amber  { background: rgba(251,191,36,0.12);  border: 1px solid rgba(251,191,36,0.20); }
.dash-quick-icon-blue   { background: rgba(96,165,250,0.12);  border: 1px solid rgba(96,165,250,0.20); }

/* ── Section header ── */
.dash-section-hd {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}
.dash-section-title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 16px;
    font-weight: 600;
    color: rgba(255,255,255,0.90);
    letter-spacing: -0.02em;
}
.dash-section-link {
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    color: #4ade80;
    font-weight: 500;
    text-decoration: none;
}

/* ── Campagne card ── */
.dash-camp-card {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.09);
    border-radius: 20px;
    padding: 16px;
    margin-bottom: 10px;
    text-decoration: none;
    display: block;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    transition: background 0.15s;
}
.dash-camp-card:active { background: rgba(255,255,255,0.08); }
.dash-camp-card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
}
.dash-camp-card-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.dash-camp-emoji {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: rgba(74,222,128,0.10);
    border: 1px solid rgba(74,222,128,0.18);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.dash-camp-nom {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 15px;
    font-weight: 600;
    color: rgba(255,255,255,0.90);
    letter-spacing: -0.02em;
}
.dash-camp-type {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    color: rgba(255,255,255,0.35);
    margin-top: 1px;
}
.dash-camp-badge {
    font-family: 'Inter', sans-serif;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    padding: 3px 10px;
    border-radius: 999px;
}
.dash-camp-metrics {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0;
    padding-top: 10px;
    border-top: 1px solid rgba(255,255,255,0.06);
}
.dash-camp-metric-cell {
    text-align: center;
    padding: 0 4px;
    border-right: 1px solid rgba(255,255,255,0.06);
}
.dash-camp-metric-cell:last-child { border-right: none; }
.dash-camp-metric-lbl {
    font-family: 'Inter', sans-serif;
    font-size: 10px;
    color: rgba(255,255,255,0.28);
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 3px;
}
.dash-camp-metric-val {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: -0.02em;
}
.dash-camp-budget-bar {
    margin-top: 10px;
}
.dash-camp-budget-track {
    height: 4px;
    background: rgba(255,255,255,0.08);
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
    font-family: 'Inter', sans-serif;
    font-size: 10px;
    color: rgba(255,255,255,0.28);
}

/* ── Alert budget strip ── */
.dash-alert {
    border-radius: 16px;
    padding: 12px 14px;
    margin-bottom: 16px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    backdrop-filter: blur(12px);
}
.dash-alert-critique {
    background: rgba(239,68,68,0.09);
    border: 1px solid rgba(239,68,68,0.22);
}
.dash-alert-warning {
    background: rgba(251,191,36,0.09);
    border: 1px solid rgba(251,191,36,0.22);
}
.dash-alert-text {
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    font-weight: 600;
    line-height: 1.4;
}

/* ── Transactions feed ── */
.dash-tx-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}
.dash-tx-item:last-child { border-bottom: none; }
.dash-tx-icon {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 16px;
}
.dash-tx-icon-recette {
    background: rgba(74,222,128,0.12);
    border: 1px solid rgba(74,222,128,0.20);
}
.dash-tx-icon-depense {
    background: rgba(248,113,113,0.12);
    border: 1px solid rgba(248,113,113,0.20);
}
.dash-tx-body { flex: 1; min-width: 0; }
.dash-tx-categorie {
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    font-weight: 500;
    color: rgba(255,255,255,0.88);
    text-transform: capitalize;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.dash-tx-meta {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    color: rgba(255,255,255,0.32);
    margin-top: 1px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.dash-tx-montant {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 14px;
    font-weight: 700;
    letter-spacing: -0.02em;
    flex-shrink: 0;
}
</style>
@endpush

{{-- ── Greeting ── --}}
<div class="dash-greeting">
    <p class="dash-greeting-hello">Bonjour,</p>
    <h1 class="dash-greeting-name">{{ $user->prenom ?? '' }} {{ mb_strtoupper(mb_substr($user->nom ?? '', 0, 1)) }}. 👋</h1>
    <p class="dash-greeting-exploit">{{ $exploitation->nom }}</p>
</div>

{{-- ── Sélecteur campagne (chips scrollables) ── --}}
@if(count($activitesCards) > 1)
    <div class="dash-chips">
        @foreach($activitesCards as $c)
            <a href="{{ route('dashboard', ['campagne' => $c['id']]) }}"
               class="dash-chip {{ $heroActiviteId == $c['id'] ? 'active' : '' }}">
                {{ $c['nom'] }}
            </a>
        @endforeach
    </div>
@endif

{{-- ── Alerte budget ── --}}
@if(count($alertesBudget) > 0)
    <div class="dash-alert {{ $bannerBudgetCritique ? 'dash-alert-critique' : 'dash-alert-warning' }}">
        <span style="font-size:18px; flex-shrink:0; margin-top:1px;">⚠️</span>
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
    <p class="dash-hero-label">Résultat net d'exploitation</p>
    <div class="dash-hero-rne" style="color:{{ $rneHero >= 0 ? '#4ade80' : '#f87171' }};">
        {{ $rneHero >= 0 ? '+' : '−' }}{{ number_format(abs($rneHero), 0, ',', ' ') }}
        <span class="dash-hero-rne-unit">FCFA</span>
    </div>
    <p class="dash-hero-campagne-name">{{ $heroTitre }}</p>

    {{-- 4 mini métriques --}}
    <div class="dash-mini-metrics">
        <div class="dash-mini-cell">
            <div class="dash-mini-lbl">PB</div>
            <div class="dash-mini-val" style="color:#4ade80;">{{ number_format($pbHero / 1000, 1, ',', ' ') }}K</div>
        </div>
        <div class="dash-mini-cell">
            <div class="dash-mini-lbl">MB</div>
            <div class="dash-mini-val" style="color:{{ $mbHero >= 0 ? '#4ade80' : '#f87171' }};">
                {{ $mbHero >= 0 ? '+' : '' }}{{ number_format($mbHero / 1000, 1, ',', ' ') }}K
            </div>
        </div>
        <div class="dash-mini-cell">
            <div class="dash-mini-lbl">CT</div>
            <div class="dash-mini-val" style="color:#f87171;">{{ number_format($ctHero / 1000, 1, ',', ' ') }}K</div>
        </div>
        <div class="dash-mini-cell">
            <div class="dash-mini-lbl">RF</div>
            <div class="dash-mini-val" style="color:rgba(255,255,255,0.88);">{{ number_format($rfHero, 1, ',', ' ') }}%</div>
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

{{-- ── Quick actions ── --}}
<div class="dash-quick">
    <a href="{{ route('transactions.create') }}" class="dash-quick-btn">
        <div class="dash-quick-icon dash-quick-icon-green">
            <svg xmlns="http://www.w3.org/2000/svg" style="width:22px;height:22px;color:#4ade80;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
        </div>
        Saisir
    </a>
    <a href="{{ route('activites.index') }}" class="dash-quick-btn">
        <div class="dash-quick-icon dash-quick-icon-amber">
            <svg xmlns="http://www.w3.org/2000/svg" style="width:22px;height:22px;color:#fbbf24;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
        </div>
        Campagnes
    </a>
    <a href="{{ route('rapports.index') }}" class="dash-quick-btn">
        <div class="dash-quick-icon dash-quick-icon-blue">
            <svg xmlns="http://www.w3.org/2000/svg" style="width:20px;height:20px;color:#93c5fd;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        Rapports
    </a>
</div>

{{-- ── Mes campagnes ── --}}
@if(count($activitesCards) > 0)
<div style="margin-bottom: 24px;">
    <div class="dash-section-hd">
        <span class="dash-section-title">Mes campagnes</span>
        <a href="{{ route('activites.create') }}" class="dash-section-link">+ Nouvelle</a>
    </div>

    @foreach($activitesCards as $c)
        @php
            $mbC     = $c['marge'] ?? 0;
            $pctC    = $c['budget_pct'] ?? null;
            $prevC   = $c['budget_prev'] ?? 0;
            $couleurC = ($pctC !== null && $pctC >= 100) ? '#f87171' : (($pctC !== null && $pctC >= 85) ? '#fbbf24' : '#4ade80');
            $badgeC = match($c['statut_indicateurs'] ?? 'rouge') {
                'vert'   => ['label' => 'RENTABLE',    'color' => '#4ade80', 'bg' => 'rgba(74,222,128,0.12)',  'border' => 'rgba(74,222,128,0.22)'],
                'orange' => ['label' => 'SURVEILLER',  'color' => '#fbbf24', 'bg' => 'rgba(251,191,36,0.12)',  'border' => 'rgba(251,191,36,0.22)'],
                default  => ['label' => 'DÉFICIT',     'color' => '#f87171', 'bg' => 'rgba(248,113,113,0.12)', 'border' => 'rgba(248,113,113,0.22)'],
            };
        @endphp
        <a href="{{ route('activites.show', $c['id']) }}" class="dash-camp-card">

            {{-- Haut : emoji + nom + badge --}}
            <div class="dash-camp-card-top">
                <div class="dash-camp-card-left">
                    <div class="dash-camp-emoji">
                        <svg xmlns="http://www.w3.org/2000/svg" style="width:20px;height:20px;color:rgba(74,222,128,0.75);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
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
                    <div class="dash-camp-metric-val" style="color:#4ade80;">{{ number_format(($c['recettes'] ?? 0) / 1000, 1, ',', ' ') }}K</div>
                </div>
                <div class="dash-camp-metric-cell">
                    <div class="dash-camp-metric-lbl">Dépenses</div>
                    <div class="dash-camp-metric-val" style="color:#f87171;">{{ number_format(($c['depenses'] ?? 0) / 1000, 1, ',', ' ') }}K</div>
                </div>
                <div class="dash-camp-metric-cell">
                    <div class="dash-camp-metric-lbl">Marge</div>
                    <div class="dash-camp-metric-val" style="color:{{ $mbC >= 0 ? '#4ade80' : '#f87171' }};">
                        {{ $mbC >= 0 ? '+' : '' }}{{ number_format($mbC / 1000, 1, ',', ' ') }}K
                    </div>
                </div>
            </div>
        </a>
    @endforeach
</div>
@else
{{-- Aucune campagne --}}
<div style="background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); border-radius:20px; padding:28px 20px; text-align:center; margin-bottom:24px;">
    <div style="font-size:36px; margin-bottom:12px;">🌱</div>
    <p style="font-family:'Space Grotesk',sans-serif; font-size:15px; font-weight:600; color:rgba(255,255,255,0.80); margin-bottom:6px;">Aucune campagne active</p>
    <p style="font-family:'Inter',sans-serif; font-size:13px; color:rgba(255,255,255,0.35); margin-bottom:16px;">Créez votre première campagne pour commencer à suivre vos résultats.</p>
    <a href="{{ route('activites.create') }}"
       style="display:inline-flex; align-items:center; gap:6px; font-family:'Inter',sans-serif; font-size:13px; font-weight:600; color:white; background:#16a34a; padding:10px 20px; border-radius:12px; text-decoration:none;">
        + Créer une campagne
    </a>
</div>
@endif

{{-- ── Dernières transactions ── --}}
@if($transactionsRecentes->isNotEmpty())
<div style="margin-bottom: 16px;">
    <div class="dash-section-hd">
        <span class="dash-section-title">Transactions récentes</span>
        <a href="{{ route('activites.index') }}" class="dash-section-link">Voir tout</a>
    </div>

    <div style="background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); border-radius:20px; padding:4px 16px;">
        @foreach($transactionsRecentes as $t)
            @php
                $catLabel = ucfirst(str_replace('_', ' ', $t->categorie));
                $isRecette = $t->type === 'recette';
            @endphp
            <div class="dash-tx-item">
                <div class="dash-tx-icon {{ $isRecette ? 'dash-tx-icon-recette' : 'dash-tx-icon-depense' }}">
                    @if($isRecette)
                        <svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;color:#4ade80;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                        </svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;color:#f87171;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                        </svg>
                    @endif
                </div>
                <div class="dash-tx-body">
                    <div class="dash-tx-categorie">{{ $catLabel }}</div>
                    <div class="dash-tx-meta">
                        {{ $t->date_transaction->format('d/m') }}
                        @if($t->activite) · {{ $t->activite->nom }} @endif
                    </div>
                </div>
                <div class="dash-tx-montant" style="color:{{ $isRecette ? '#4ade80' : '#f87171' }};">
                    {{ $isRecette ? '+' : '−' }}{{ number_format($t->montant, 0, ',', ' ') }}
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

    <!-- Carte résumé (focus campagne ou exploitation) -->
    <div class="dashboard-hero glass mb-8">
        <div class="dashboard-hero__top">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <div class="flex flex-wrap items-center gap-3 mb-2">
                        <span class="{{ match($statutHero) { 'vert' => 'badge-vert', 'orange' => 'badge-orange', default => 'badge-rouge' } }} font-ui tracking-wide">
                            {{ $statutHeroConfig['label'] }}
                        </span>
                        @if(count($activitesCards) > 1)
                            <form method="get" action="{{ route('dashboard') }}" class="inline-flex items-center gap-2">
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
            <p class="kpi-label mb-1">Résultat net (RNE)</p>
            <div class="dashboard-hero__rne-value font-display {{ $rneHero >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                {{ $rneHero >= 0 ? '+' : '−' }}{{ number_format(abs($rneHero), 0, ',', ' ') }}
                <span class="text-xl font-ui font-normal text-white/40 ml-1">FCFA</span>
            </div>
        </div>

        <div class="dashboard-mini-metrics">
            <div class="dashboard-mini-metrics__cell">
                <span class="dashboard-mini-metrics__label">PB</span>
                <span class="dashboard-mini-metrics__val text-emerald-400">{{ number_format($pbHero / 1000, 1, ',', ' ') }}K</span>
            </div>
            <div class="dashboard-mini-metrics__cell">
                <span class="dashboard-mini-metrics__label">MB</span>
                <span class="dashboard-mini-metrics__val {{ $mbHero >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                    {{ $mbHero >= 0 ? '+' : '' }}{{ number_format($mbHero / 1000, 1, ',', ' ') }}K
                </span>
            </div>
            <div class="dashboard-mini-metrics__cell">
                <span class="dashboard-mini-metrics__label">CT</span>
                <span class="dashboard-mini-metrics__val text-red-400">{{ number_format($ctHero / 1000, 1, ',', ' ') }}K</span>
            </div>
            <div class="dashboard-mini-metrics__cell">
                <span class="dashboard-mini-metrics__label">RF</span>
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
        <p class="section-subtitle mt-1">Synthèse exploitation sur la période affichée</p>
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
                <span class="kpi-label">Recettes totales</span>
                <div class="kpi-icon-wrap" style="background:rgba(74,222,128,0.15);border:1px solid rgba(74,222,128,0.25);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="#4ade80" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
            <div><div class="kpi-value text-emerald-400">{{ number_format($pbCons / 1000, 0, ',', ' ') }}<span class="kpi-unit">K FCFA</span></div></div>
        </div>
        <div class="kpi-glass">
            <div class="flex items-center justify-between">
                <span class="kpi-label">Coût total</span>
                <div class="kpi-icon-wrap" style="background:rgba(248,113,113,0.15);border:1px solid rgba(248,113,113,0.25);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="#f87171" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 17H5m0 0V9m0 8l8-8 4 4 6-6"/>
                    </svg>
                </div>
            </div>
            <div><div class="kpi-value text-red-400">{{ number_format($ctCons / 1000, 0, ',', ' ') }}<span class="kpi-unit">K FCFA</span></div></div>
        </div>
        <div class="kpi-glass">
            <div class="flex items-center justify-between">
                <span class="kpi-label">Marge brute</span>
                <div class="kpi-icon-wrap"
                     style="background:{{ $mbCons >= 0 ? 'rgba(74,222,128,0.15)' : 'rgba(248,113,113,0.15)' }};border:1px solid {{ $mbCons >= 0 ? 'rgba(74,222,128,0.25)' : 'rgba(248,113,113,0.25)' }};">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="{{ $mbCons >= 0 ? '#4ade80' : '#f87171' }}" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div><div class="kpi-value" style="color:{{ $mbCons >= 0 ? '#4ade80' : '#f87171' }};">{{ $mbCons >= 0 ? '+' : '' }}{{ number_format($mbCons / 1000, 0, ',', ' ') }}<span class="kpi-unit">K FCFA</span></div></div>
        </div>
        @php
            $statutConfig = match ($statutCons) {
                'vert'   => ['emoji' => '🟢', 'label' => 'Rentable',     'color' => '#4ade80', 'bg' => 'rgba(74,222,128,0.15)',  'border' => 'rgba(74,222,128,0.25)'],
                'orange' => ['emoji' => '🟠', 'label' => 'À surveiller', 'color' => '#fbbf24', 'bg' => 'rgba(251,191,36,0.15)',  'border' => 'rgba(251,191,36,0.25)'],
                default  => ['emoji' => '🔴', 'label' => 'Déficitaire',  'color' => '#f87171', 'bg' => 'rgba(248,113,113,0.15)', 'border' => 'rgba(248,113,113,0.25)'],
            };
        @endphp
        <div class="kpi-glass">
            <div class="flex items-center justify-between">
                <span class="kpi-label">Statut global</span>
                <div class="kpi-icon-wrap" style="background:{{ $statutConfig['bg'] }};border:1px solid {{ $statutConfig['border'] }};font-size:22px;">{{ $statutConfig['emoji'] }}</div>
            </div>
            <div>
                <div class="kpi-value" style="color:{{ $statutConfig['color'] }}; font-size:26px;">{{ $statutConfig['label'] }}</div>
                <div class="font-ui text-xs text-white/35 mt-1">Rendement : {{ number_format($resultats['consolide']['RF'] ?? 0, 1, ',', ' ') }}%</div>
            </div>
        </div>
    </div>

    <!-- Graphique + colonne droite -->
    <div class="dashboard-section-spaced">
        <div class="mb-5 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="section-title">Évolution — Marge brute</h2>
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
                                    <p class="text-xs text-white/40">{{ $t->date_transaction->format('d/m/Y') }}@if($t->activite) · {{ $t->activite->nom }}@endif</p>
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
            <a href="{{ route('activites.create') }}" class="font-ui text-xs font-semibold text-white/60 px-3.5 py-1.5 border border-white/15 rounded-lg hover:text-white hover:border-white/35 transition-colors">
                + Nouvelle campagne
            </a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            @forelse($activitesCards as $c)
                @php
                    $data = $parActivite[$c['id']] ?? [];
                    $budgetPrev = $c['budget_prev'] ?? 0;
                    $pbA = $data['PB'] ?? 0; $ctA = $data['CT'] ?? 0; $mbA = $data['MB'] ?? 0;
                    if ($budgetPrev > 0) { $pourcent = min(100, round(($ctA / $budgetPrev) * 100)); $couleurBarre = $pourcent >= 100 ? '#f87171' : ($pourcent >= 90 ? '#fbbf24' : '#4ade80'); }
                    else { $pourcent = 0; $couleurBarre = '#4ade80'; }
                @endphp
                <a href="{{ route('activites.show', $c['id']) }}" class="card hover:shadow-lg transition-shadow cursor-pointer block no-underline">
                    <div class="flex justify-between items-start mb-2">
                        <p class="font-semibold text-emerald-400 font-ui">{{ $c['nom'] }}</p>
                        <x-status-indicator :statut="$c['statut_indicateurs'] ?? 'rouge'" />
                    </div>
                    <div class="mt-3 mb-2">
                        <div class="flex justify-between mb-1">
                            <span class="font-ui text-[10px] text-white/35">@if($budgetPrev > 0) Budget : {{ $pourcent }}% utilisé @else Aucun budget défini @endif</span>
                            @if($budgetPrev > 0)<span class="font-ui text-[10px]" style="color:{{ $couleurBarre }};">{{ number_format($budgetPrev / 1000, 0, ',', ' ') }}K FCFA</span>@endif
                        </div>
                        <div class="h-1 bg-white/10 rounded overflow-hidden">
                            <div class="h-full transition-all duration-500 rounded" style="width:{{ $pourcent }}%; background:{{ $couleurBarre }};"></div>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2 mb-3">
                        <div class="text-center"><div class="font-ui text-[10px] text-white/35 mb-0.5">Rec.</div><div class="font-display text-sm font-semibold text-emerald-400">{{ number_format($pbA / 1000, 1, ',', ' ') }}K</div></div>
                        <div class="text-center"><div class="font-ui text-[10px] text-white/35 mb-0.5">Dép.</div><div class="font-display text-sm font-semibold text-red-400">{{ number_format($ctA / 1000, 1, ',', ' ') }}K</div></div>
                        <div class="text-center"><div class="font-ui text-[10px] text-white/35 mb-0.5">Marge</div><div class="font-display text-sm font-semibold {{ $mbA >= 0 ? 'text-emerald-400' : 'text-red-400' }}">{{ number_format($mbA, 0, ',', ' ') }}</div></div>
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
    if (typeof window !== 'undefined' && document.documentElement.dataset.platform === 'mobile') return;
    var id = {{ (int) $chartActiviteId }};
    var token = @json(session('api_token'));
    var ctx = document.getElementById('chartMB');
    if (!ctx || !token) return;
    fetch(@json(url('/api/indicateurs/activite')) + '/' + id + '/evolution', {
        headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' },
        credentials: 'same-origin'
    }).then(function (r) { return r.json(); }).then(function (json) {
        if (!json.succes || !json.data || !json.data.evolution) return;
        var ev = json.data.evolution;
        var labels = ev.map(function (e) { return e.mois_num || e.mois; });
        var values = ev.map(function (e) { return (e.MB || 0) / 1000; });
        var fontInter = "'Inter', system-ui, -apple-system, sans-serif";
        var fontSpace = "'Space Grotesk', system-ui, sans-serif";
        new Chart(ctx, {
            type: 'line',
            data: { labels: labels, datasets: [{ data: values, borderColor: '#4ade80', backgroundColor: 'rgba(74,222,128,0.12)', fill: true, tension: 0.3, pointRadius: 2 }] },
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
