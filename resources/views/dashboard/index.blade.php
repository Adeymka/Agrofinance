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
    @if($premierActiviteId && session('api_token'))
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @endif
@endpush

@section('content')
    @php
        $k = fn ($n) => $n >= 1000 ? number_format($n / 1000, 1, ',', ' ') . ' K' : number_format($n, 0, ',', ' ');
        $nbCampagnes = count($activitesCards);
    @endphp

    <div class="mb-6">
        <h2 class="section-title">Indicateurs clés</h2>
        <p class="section-subtitle">Synthèse des montants sur la période affichée</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-8">
        <div class="kpi-card border-l-4 border-l-green-500">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-100/90 flex items-center justify-center text-emerald-700">
                    <x-icon name="trending-up" class="w-5 h-5" />
                </div>
                <div>
                    <p class="kpi-label">Recettes totales</p>
                    <p class="kpi-value mt-1 text-green-700">{{ number_format($recettes, 0, ',', ' ') }}
                        <span style="font-family:var(--font-ui);font-size:13px;font-weight:400;color:rgba(255,255,255,0.40);">FCFA</span></p>
                </div>
            </div>
        </div>
        <div class="kpi-card border-l-4 border-l-red-400">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-lg bg-red-50/90 flex items-center justify-center text-red-600">
                    <x-icon name="trending-down" class="w-5 h-5" />
                </div>
                <div>
                    <p class="kpi-label">Coût total</p>
                    <p class="kpi-value mt-1 text-red-600">{{ number_format($depenses, 0, ',', ' ') }}
                        <span style="font-family:var(--font-ui);font-size:13px;font-weight:400;color:rgba(255,255,255,0.40);">FCFA</span></p>
                </div>
            </div>
        </div>
        <div class="kpi-card border-l-4 {{ $marge >= 0 ? 'border-l-green-600' : 'border-l-red-500' }}">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-lg {{ $marge >= 0 ? 'bg-green-50/90 text-emerald-700' : 'bg-red-50/90 text-red-600' }} flex items-center justify-center">
                    <x-icon name="currency-dollar" class="w-5 h-5" />
                </div>
                <div>
                    <p class="kpi-label">Marge brute</p>
                    <p class="kpi-value mt-1 {{ $marge >= 0 ? 'text-green-700' : 'text-red-600' }}">{{ $marge >= 0 ? '+' : '' }}{{ number_format($marge, 0, ',', ' ') }}
                        <span style="font-family:var(--font-ui);font-size:13px;font-weight:400;color:rgba(255,255,255,0.40);">FCFA</span></p>
                </div>
            </div>
        </div>
        <div class="kpi-card border-l-4 border-l-amber-400">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center ring-1 ring-white/15">
                    <x-status-indicator :statut="$statut" class="!h-3 !w-3" />
                </div>
                <div>
                    <p class="kpi-label">Statut global</p>
                    <p class="kpi-value mt-1 text-gray-900">RF {{ number_format($rf, 1) }}%</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="lg:col-span-2 card">
            <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-baseline sm:justify-between">
                <h3 class="chart-title m-0">Évolution — Marge brute</h3>
                <span class="chart-subtitle">12 derniers mois</span>
            </div>
            @if($premierActiviteId && session('api_token'))
                <canvas id="chartMB" height="300"></canvas>
            @else
                <p class="text-sm text-gray-500">Aucune donnée ou reconnectez-vous pour le graphique.</p>
            @endif
        </div>
        <div class="card">
            <h3 class="chart-title mb-3">Dernières transactions</h3>
            <ul class="divide-y divide-gray-100">
                @forelse($dernieresTransactions as $t)
                    <li class="py-2 flex justify-between text-sm">
                        <div>
                            <p class="font-medium text-gray-800">{{ str_replace('_', ' ', $t->categorie) }}</p>
                            <p class="text-xs text-gray-500">{{ $t->date_transaction->format('d/m/Y') }}</p>
                        </div>
                        <span class="font-semibold {{ $t->type === 'recette' ? 'text-green-700' : 'text-red-600' }}">
                            {{ $t->type === 'recette' ? '+' : '−' }}{{ number_format($t->montant, 0, ',', ' ') }}
                        </span>
                    </li>
                @empty
                    <li class="text-sm text-gray-500 py-4">Aucune transaction.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div>
        <h2 class="section-title mb-1">Campagnes actives</h2>
        <p class="section-subtitle mb-4">{{ $nbCampagnes }} campagne(s) en cours</p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            @forelse($activitesCards as $c)
                <a href="{{ route('activites.show', $c['id']) }}" class="card hover:shadow-md transition-shadow cursor-pointer block">
                    <div class="flex justify-between items-start mb-2">
                        <p class="font-semibold text-agro-vert font-ui">{{ $c['nom'] }}</p>
                        <x-status-indicator :statut="$c['statut_fsa'] ?? 'rouge'" />
                    </div>
                    <div class="grid grid-cols-3 gap-2 text-center text-xs mb-3">
                        <div><span class="text-gray-500 block font-ui">Rec.</span><span class="text-lg font-semibold text-green-700 leading-tight" style="font-family: var(--font-display); font-weight: 600; letter-spacing: -0.02em;">{{ $k($c['recettes']) }}</span></div>
                        <div><span class="text-gray-500 block font-ui">Dép.</span><span class="text-lg font-semibold text-red-600 leading-tight" style="font-family: var(--font-display); font-weight: 600; letter-spacing: -0.02em;">{{ $k($c['depenses']) }}</span></div>
                        <div><span class="text-gray-500 block font-ui">Marge</span><span class="text-lg font-semibold text-gray-900 leading-tight" style="font-family: var(--font-display); font-weight: 600; letter-spacing: -0.02em;">{{ $k($c['marge']) }}</span></div>
                    </div>
                    @if($c['budget_prev'] && $c['budget_pct'] !== null)
                        <div class="h-2 bg-gray-100 rounded-full overflow-hidden mb-2">
                            <div class="h-full bg-agro-vert rounded-full" style="width: {{ min(100, $c['budget_pct']) }}%"></div>
                        </div>
                    @endif
                    <p class="text-xs text-gray-500 font-ui inline-flex items-center gap-1">Voir détails <x-icon name="arrow-right" class="w-3.5 h-3.5 opacity-70" /></p>
                </a>
            @empty
                <p class="text-sm text-gray-500 col-span-3">Aucune campagne active.</p>
            @endforelse
        </div>
    </div>

@endsection

@if($premierActiviteId && session('api_token'))
@push('scripts')
<script>
(function () {
    var id = {{ (int) $premierActiviteId }};
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
                    borderColor: '#1B5E20',
                    backgroundColor: 'rgba(27, 94, 32, 0.12)',
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
