@extends('layouts.app-desktop')
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
            'vert' => ['class' => 'badge-vert', 'label' => 'RENTABLE'],
            'orange' => ['class' => 'badge-orange', 'label' => 'À SURVEILLER'],
            default => ['class' => 'badge-rouge', 'label' => 'DÉFICITAIRE'],
        };
        $rneHero = $heroInd['RNE'] ?? ($consolide['RNE'] ?? 0);
        $pbHero = $heroInd['PB'] ?? ($consolide['PB'] ?? 0);
        $mbHero = $heroInd['MB'] ?? ($consolide['MB'] ?? 0);
        $ctHero = $heroInd['CT'] ?? ($consolide['CT'] ?? 0);
        $rfHero = $heroInd['RF'] ?? ($consolide['RF'] ?? 0);
        $heroTitre = $heroInd['nom'] ?? $exploitation->nom;
        $heroSousTitre = $heroInd
            ? (($heroInd['type'] ?? '') ? str_replace('_', ' ', ucfirst($heroInd['type'])) . ' · ' : '') . 'Indicateurs sur la période autorisée'
            : 'Vue consolidée de toutes les campagnes actives';
        $transactionsRecentes = $dernieresTransactions->take(5);
    @endphp

    <!-- Carte résumé (focus campagne ou exploitation) -->
    <div class="dashboard-hero glass mb-8">
        <div class="dashboard-hero__top">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <div class="flex flex-wrap items-center gap-3 mb-2">
                        <span class="{{ $statutHeroConfig['class'] }} font-ui tracking-wide">{{ $statutHeroConfig['label'] }}</span>
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
            <div>
                <div class="kpi-value text-emerald-400">
                    {{ number_format($pbCons / 1000, 0, ',', ' ') }}
                    <span class="kpi-unit">K FCFA</span>
                </div>
            </div>
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
            <div>
                <div class="kpi-value text-red-400">
                    {{ number_format($ctCons / 1000, 0, ',', ' ') }}
                    <span class="kpi-unit">K FCFA</span>
                </div>
            </div>
        </div>

        <div class="kpi-glass">
            <div class="flex items-center justify-between">
                <span class="kpi-label">Marge brute</span>
                <div class="kpi-icon-wrap"
                     style="background:{{ $mbCons >= 0 ? 'rgba(74,222,128,0.15)' : 'rgba(248,113,113,0.15)' }};
                            border:1px solid {{ $mbCons >= 0 ? 'rgba(74,222,128,0.25)' : 'rgba(248,113,113,0.25)' }};">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                         stroke="{{ $mbCons >= 0 ? '#4ade80' : '#f87171' }}" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2
                                 m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1
                                 c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div>
                <div class="kpi-value" style="color:{{ $mbCons >= 0 ? '#4ade80' : '#f87171' }};">
                    {{ $mbCons >= 0 ? '+' : '' }}{{ number_format($mbCons / 1000, 0, ',', ' ') }}
                    <span class="kpi-unit">K FCFA</span>
                </div>
            </div>
        </div>

        @php
            $statutConfig = match ($statutCons) {
                'vert' => ['emoji' => '🟢', 'label' => 'Rentable', 'color' => '#4ade80', 'bg' => 'rgba(74,222,128,0.15)', 'border' => 'rgba(74,222,128,0.25)'],
                'orange' => ['emoji' => '🟠', 'label' => 'À surveiller', 'color' => '#fbbf24', 'bg' => 'rgba(251,191,36,0.15)', 'border' => 'rgba(251,191,36,0.25)'],
                default => ['emoji' => '🔴', 'label' => 'Déficitaire', 'color' => '#f87171', 'bg' => 'rgba(248,113,113,0.15)', 'border' => 'rgba(248,113,113,0.25)'],
            };
        @endphp
        <div class="kpi-glass">
            <div class="flex items-center justify-between">
                <span class="kpi-label">Statut global</span>
                <div class="kpi-icon-wrap"
                     style="background:{{ $statutConfig['bg'] }};
                            border:1px solid {{ $statutConfig['border'] }};
                            font-size:22px;">
                    {{ $statutConfig['emoji'] }}
                </div>
            </div>
            <div>
                <div class="kpi-value" style="color:{{ $statutConfig['color'] }}; font-size:26px;">
                    {{ $statutConfig['label'] }}
                </div>
                <div class="font-ui text-xs text-white/35 mt-1">
                    Rendement : {{ number_format($resultats['consolide']['RF'] ?? 0, 1, ',', ' ') }}%
                </div>
            </div>
        </div>
    </div>

    <!-- Graphique + colonne droite -->
    <div class="dashboard-section-spaced">
        <div class="mb-5 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="section-title">Évolution — Marge brute</h2>
                <p class="section-subtitle mt-1">
                    @if($chartActiviteId && $heroInd)
                        Campagne : {{ $heroInd['nom'] ?? '—' }} · 12 derniers mois
                    @else
                        12 derniers mois
                    @endif
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
                                <span class="text-xs font-semibold {{ ($a['budget_pct'] ?? 0) >= 100 ? 'text-red-400' : 'text-amber-400' }}">
                                    {{ number_format($a['budget_pct'] ?? 0, 0, ',', ' ') }} %
                                </span>
                            </div>
                            <div class="mt-2 h-1.5 rounded-full bg-white/10 overflow-hidden">
                                <div class="h-full rounded-full transition-all {{ ($a['budget_pct'] ?? 0) >= 100 ? 'bg-red-400' : 'bg-amber-400' }}"
                                     style="width: {{ min(100, $a['budget_pct'] ?? 0) }}%"></div>
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
                                    <p class="text-xs text-white/40">{{ $t->date_transaction->format('d/m/Y') }}
                                        @if($t->activite)
                                            · {{ $t->activite->nom }}
                                        @endif
                                    </p>
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
            <p class="section-subtitle mt-1">Jusqu’à 20 opérations les plus récentes</p>
        </div>
        <div class="card overflow-x-auto">
            <table class="table-glass min-w-[880px]">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Campagne</th>
                        <th>Catégorie</th>
                        <th>Type</th>
                        <th>Nature</th>
                        <th class="text-right">Montant</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dernieresTransactions as $t)
                        @php
                            $nat = $t->nature;
                            $natLabel = $nat === 'fixe' ? 'Fixe' : ($nat === 'variable' ? 'Variable' : '—');
                            $typeLabel = $t->type === 'recette' ? 'Recette' : 'Dépense';
                        @endphp
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
                                <form action="{{ route('transactions.destroy', $t->id) }}" method="post" class="inline"
                                      onsubmit="return confirm('Supprimer cette transaction ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:underline text-sm font-medium bg-transparent border-0 cursor-pointer p-0">
                                        Supprimer
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-white/45 py-8">Aucune transaction.</td>
                        </tr>
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
            <a href="{{ route('activites.create') }}"
               class="font-ui text-xs font-semibold text-white/60 px-3.5 py-1.5 border border-white/15 rounded-lg hover:text-white hover:border-white/35 transition-colors">
                + Nouvelle campagne
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            @forelse($activitesCards as $c)
                @php
                    $data = $parActivite[$c['id']] ?? [];
                    $budgetPrev = $c['budget_prev'] ?? 0;
                    $pbA = $data['PB'] ?? 0;
                    $ctA = $data['CT'] ?? 0;
                    $mbA = $data['MB'] ?? 0;
                    if ($budgetPrev > 0) {
                        $pourcent = min(100, round(($ctA / $budgetPrev) * 100));
                        $couleurBarre = $pourcent >= 100 ? '#f87171' : ($pourcent >= 90 ? '#fbbf24' : '#4ade80');
                    } else {
                        $pourcent = 0;
                        $couleurBarre = '#4ade80';
                    }
                @endphp
                <a href="{{ route('activites.show', $c['id']) }}" class="card hover:shadow-lg transition-shadow cursor-pointer block no-underline">
                    <div class="flex justify-between items-start mb-2">
                        <p class="font-semibold text-emerald-400 font-ui">{{ $c['nom'] }}</p>
                        <x-status-indicator :statut="$c['statut_indicateurs'] ?? 'rouge'" />
                    </div>

                    <div class="mt-3 mb-2">
                        <div class="flex justify-between mb-1">
                            <span class="font-ui text-[10px] text-white/35">
                                @if($budgetPrev > 0)
                                    Budget : {{ $pourcent }}% utilisé
                                @else
                                    Aucun budget défini
                                @endif
                            </span>
                            @if($budgetPrev > 0)
                                <span class="font-ui text-[10px]" style="color:{{ $couleurBarre }};">
                                    {{ number_format($budgetPrev / 1000, 0, ',', ' ') }}K FCFA
                                </span>
                            @endif
                        </div>
                        <div class="h-1 bg-white/10 rounded overflow-hidden">
                            <div class="h-full transition-all duration-500 rounded"
                                 style="width:{{ $pourcent }}%; background:{{ $couleurBarre }};"></div>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-2 mb-3">
                        <div class="text-center">
                            <div class="font-ui text-[10px] text-white/35 mb-0.5">Rec.</div>
                            <div class="font-display text-sm font-semibold text-emerald-400">
                                {{ number_format($pbA / 1000, 1, ',', ' ') }}K
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="font-ui text-[10px] text-white/35 mb-0.5">Dép.</div>
                            <div class="font-display text-sm font-semibold text-red-400">
                                {{ number_format($ctA / 1000, 1, ',', ' ') }}K
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="font-ui text-[10px] text-white/35 mb-0.5">Marge</div>
                            <div class="font-display text-sm font-semibold {{ $mbA >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                {{ number_format($mbA, 0, ',', ' ') }}
                            </div>
                        </div>
                    </div>

                    <p class="text-xs text-white/45 font-ui inline-flex items-center gap-1">
                        Voir détails <x-icon name="arrow-right" class="w-3.5 h-3.5 opacity-70" />
                    </p>
                </a>
            @empty
                <p class="text-sm text-white/45 col-span-3">Aucune campagne active.</p>
            @endforelse
        </div>
    </div>
@endsection

@if($chartActiviteId && session('api_token'))
@push('scripts')
<script>
(function () {
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
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    borderColor: '#4ade80',
                    backgroundColor: 'rgba(74, 222, 128, 0.12)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 2
                }]
            },
            options: {
                responsive: true,
                font: { family: fontInter },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        titleFont: { family: fontSpace, size: 13, weight: '600' },
                        bodyFont: { family: fontInter, size: 12, weight: '400' },
                        padding: 10
                    }
                },
                scales: {
                    y: {
                        ticks: {
                            font: { family: fontInter, size: 11, weight: '500' },
                            color: 'rgba(255,255,255,0.40)',
                            callback: function (v) { return v.toFixed(0) + ' K FCFA'; }
                        },
                        grid: { color: 'rgba(255,255,255,0.05)' }
                    },
                    x: {
                        ticks: {
                            font: { family: fontInter, size: 10, weight: '400' },
                            color: 'rgba(255,255,255,0.36)'
                        },
                        grid: { display: false }
                    }
                }
            }
        });
    }).catch(function () {});
})();
</script>
@endpush
@endif
