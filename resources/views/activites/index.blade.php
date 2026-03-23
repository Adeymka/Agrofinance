@extends($layout)
@section('title', 'Campagnes — AgroFinance+')
@section('page-title', 'Mes campagnes agricoles')

@section('topbar-actions')
    <a href="{{ route('activites.create') }}" class="btn-primary inline-flex items-center gap-2">
        <x-icon name="plus" class="w-4 h-4" /> Nouvelle campagne
    </a>
@endsection

@section('content')

@if($platform === 'mobile')

@push('styles')
<style>
/* ── Filter chips ── */
.act-filters {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding-bottom: 2px;
    margin-bottom: 20px;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}
.act-filters::-webkit-scrollbar { display: none; }
.act-filter-chip {
    flex-shrink: 0;
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    font-weight: 600;
    padding: 8px 16px;
    border-radius: 999px;
    border: none;
    cursor: pointer;
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.10);
    color: rgba(255,255,255,0.45);
    transition: all 0.15s;
    white-space: nowrap;
}
.act-filter-chip.active {
    background: rgba(74,222,128,0.14);
    border-color: rgba(74,222,128,0.28);
    color: #4ade80;
}

/* ── Campaign card ── */
.act-card {
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
.act-card:active { background: rgba(255,255,255,0.09); }
.act-card-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 14px;
}
.act-card-icon {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    background: rgba(74,222,128,0.10);
    border: 1px solid rgba(74,222,128,0.18);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-right: 12px;
}
.act-card-title-wrap { flex: 1; min-width: 0; }
.act-card-nom {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 15px;
    font-weight: 700;
    color: rgba(255,255,255,0.92);
    letter-spacing: -0.02em;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 2px;
}
.act-card-meta {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    color: rgba(255,255,255,0.32);
}
.act-badge {
    font-family: 'Inter', sans-serif;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    padding: 3px 10px;
    border-radius: 999px;
    flex-shrink: 0;
    white-space: nowrap;
}
/* ── Metrics row ── */
.act-metrics {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    border-top: 1px solid rgba(255,255,255,0.06);
    padding-top: 12px;
}
.act-metric-cell { text-align: center; }
.act-metric-cell + .act-metric-cell { border-left: 1px solid rgba(255,255,255,0.06); }
.act-metric-lbl {
    font-family: 'Inter', sans-serif;
    font-size: 10px;
    color: rgba(255,255,255,0.28);
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 3px;
}
.act-metric-val {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: -0.02em;
}
/* ── Budget bar ── */
.act-budget { margin-bottom: 12px; }
.act-budget-labels {
    display: flex;
    justify-content: space-between;
    font-family: 'Inter', sans-serif;
    font-size: 10px;
    color: rgba(255,255,255,0.30);
    margin-bottom: 4px;
}
.act-budget-track {
    height: 4px;
    background: rgba(255,255,255,0.08);
    border-radius: 2px;
    overflow: hidden;
}
.act-budget-fill { height: 100%; border-radius: 2px; transition: width 0.5s; }
/* ── Carte "terminée/abandonnée" (opacité réduite) ── */
.act-card-muted { opacity: 0.65; }
/* ── FAB ── */
.act-fab {
    position: fixed;
    bottom: 84px;
    right: 20px;
    width: 54px;
    height: 54px;
    border-radius: 50%;
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    box-shadow: 0 4px 20px rgba(34,197,94,0.45);
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    z-index: 40;
    transition: transform 0.15s, box-shadow 0.15s;
}
.act-fab:active { transform: scale(0.95); }
/* ── Empty state ── */
.act-empty {
    text-align: center;
    padding: 40px 20px;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 20px;
    margin-bottom: 10px;
}
.act-empty-icon { font-size: 40px; margin-bottom: 12px; }
.act-empty-title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 15px;
    font-weight: 600;
    color: rgba(255,255,255,0.75);
    margin-bottom: 6px;
}
.act-empty-sub {
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    color: rgba(255,255,255,0.32);
}
</style>
@endpush

@php
$activeTab = request('tab', 'en_cours');
@endphp

{{-- ── Titre ── --}}
<div style="padding: 20px 0 4px;">
    <h1 style="font-family:'Space Grotesk',sans-serif; font-size:22px; font-weight:700; color:rgba(255,255,255,0.94); letter-spacing:-0.03em;">Mes campagnes</h1>
    <p style="font-family:'Inter',sans-serif; font-size:12px; color:rgba(255,255,255,0.30); margin-top:3px;">{{ $actives->count() }} en cours · {{ $terminees->count() }} terminée(s)</p>
</div>

{{-- ── Filtres chips ── --}}
<div class="act-filters">
    <a href="{{ route('activites.index', ['tab' => 'en_cours']) }}"
       class="act-filter-chip {{ $activeTab === 'en_cours' ? 'active' : '' }}">
        En cours ({{ $actives->count() }})
    </a>
    <a href="{{ route('activites.index', ['tab' => 'terminees']) }}"
       class="act-filter-chip {{ $activeTab === 'terminees' ? 'active' : '' }}">
        Terminées ({{ $terminees->count() }})
    </a>
    <a href="{{ route('activites.index', ['tab' => 'abandonnees']) }}"
       class="act-filter-chip {{ $activeTab === 'abandonnees' ? 'active' : '' }}">
        Abandonnées ({{ $abandonnees->count() }})
    </a>
</div>

{{-- ── Liste selon le filtre actif ── --}}
@php
    $listeActive = match($activeTab) {
        'terminees'   => $terminees,
        'abandonnees' => $abandonnees,
        default       => $actives,
    };
    $indicateursActifs = match($activeTab) {
        'terminees'   => $indicateursTerminees,
        'abandonnees' => $indicateursAbandonnees,
        default       => $indicateursParActivite,
    };
    $isMuted = $activeTab !== 'en_cours';
@endphp

@forelse($listeActive as $a)
    @php
        $ind    = $indicateursActifs[$a->id] ?? [];
        $mb     = $ind['MB']  ?? 0;
        $pb     = $ind['PB']  ?? 0;
        $ct     = $ind['CT']  ?? 0;
        $rne    = $ind['RNE'] ?? 0;
        $statut = $ind['statut'] ?? 'rouge';
        $badge  = match($statut) {
            'vert'   => ['label' => 'RENTABLE',   'color' => '#4ade80', 'bg' => 'rgba(74,222,128,0.12)',  'border' => 'rgba(74,222,128,0.22)'],
            'orange' => ['label' => 'SURVEILLER', 'color' => '#fbbf24', 'bg' => 'rgba(251,191,36,0.12)',  'border' => 'rgba(251,191,36,0.22)'],
            default  => ['label' => 'DÉFICIT',    'color' => '#f87171', 'bg' => 'rgba(248,113,113,0.12)', 'border' => 'rgba(248,113,113,0.22)'],
        };
        $dateFmt = $isMuted
            ? ($a->date_fin?->format('d/m/Y') ?? '—')
            : ($a->date_debut?->format('d/m/Y') ?? '—');
        $dateLabel = $isMuted ? 'Fin : ' : 'Début : ';
    @endphp
    <a href="{{ route('activites.show', $a->id) }}" class="act-card {{ $isMuted ? 'act-card-muted' : '' }}">

        {{-- Header --}}
        <div class="act-card-header">
            <div style="display:flex; align-items:center; flex:1; min-width:0;">
                <div class="act-card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" style="width:20px;height:20px;color:rgba(74,222,128,0.75);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div class="act-card-title-wrap">
                    <div class="act-card-nom">{{ $a->nom }}</div>
                    <div class="act-card-meta">{{ ucfirst(str_replace('_', ' ', $a->type)) }} · {{ $dateLabel }}{{ $dateFmt }}</div>
                </div>
            </div>
            <span class="act-badge"
                  style="background:{{ $badge['bg'] }}; border:1px solid {{ $badge['border'] }}; color:{{ $badge['color'] }};">
                {{ $badge['label'] }}
            </span>
        </div>

        {{-- Métriques --}}
        <div class="act-metrics">
            <div class="act-metric-cell">
                <div class="act-metric-lbl">Recettes</div>
                <div class="act-metric-val" style="color:#4ade80;">{{ number_format($pb / 1000, 1, ',', ' ') }}K</div>
            </div>
            <div class="act-metric-cell">
                <div class="act-metric-lbl">Dépenses</div>
                <div class="act-metric-val" style="color:#f87171;">{{ number_format($ct / 1000, 1, ',', ' ') }}K</div>
            </div>
            <div class="act-metric-cell">
                <div class="act-metric-lbl">Marge</div>
                <div class="act-metric-val" style="color:{{ $mb >= 0 ? '#4ade80' : '#f87171' }};">
                    {{ $mb >= 0 ? '+' : '' }}{{ number_format($mb / 1000, 1, ',', ' ') }}K
                </div>
            </div>
        </div>

    </a>
@empty
    <div class="act-empty">
        <div class="act-empty-icon">📋</div>
        <div class="act-empty-title">
            @if($activeTab === 'en_cours') Aucune campagne en cours
            @elseif($activeTab === 'terminees') Aucune campagne terminée
            @else Aucune campagne abandonnée @endif
        </div>
        @if($activeTab === 'en_cours')
        <div class="act-empty-sub" style="margin-bottom:16px;">Commencez par créer votre première campagne agricole.</div>
        @endif
    </div>
@endforelse

{{-- ── FAB + Nouveau ── --}}
<a href="{{ route('activites.create') }}" class="act-fab" aria-label="Nouvelle campagne">
    <svg xmlns="http://www.w3.org/2000/svg" style="width:26px;height:26px;color:white;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
    </svg>
</a>

<x-confirm-abandon-modal />

@else
{{-- ════ DESKTOP (original) ════ --}}
    <div class="mb-6 flex gap-4 border-b border-gray-200 flex-wrap">
        <button type="button" data-tab="1" class="tab-btn border-b-2 border-agro-vert text-agro-vert font-semibold pb-2 px-2">En cours ({{ $actives->count() }})</button>
        <button type="button" data-tab="2" class="tab-btn text-gray-500 pb-2 px-2 border-b-2 border-transparent">Terminées ({{ $terminees->count() }})</button>
        <button type="button" data-tab="3" class="tab-btn text-gray-500 pb-2 px-2 border-b-2 border-transparent">Abandonnées ({{ $abandonnees->count() }})</button>
    </div>

    <div id="panel-en-cours" class="tab-panel" data-panel="1">
        <div class="card overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="text-left text-gray-500 border-b"><th class="py-2 pr-4">Campagne</th><th class="py-2 pr-4">Type</th><th class="py-2 pr-4">Début</th><th class="py-2 pr-4 text-right">Recettes</th><th class="py-2 pr-4 text-right">Dépenses</th><th class="py-2 pr-4 text-right">Marge</th><th class="py-2 pr-4">Statut</th><th class="py-2">Actions</th></tr></thead>
                <tbody>
                    @forelse($actives as $a)
                        @php $ind = $indicateursParActivite[$a->id] ?? []; @endphp
                        <tr class="border-b border-gray-50 hover:bg-gray-50">
                            <td class="py-3 pr-4 font-medium">{{ $a->nom }}</td>
                            <td class="py-3 pr-4">{{ $a->type }}</td>
                            <td class="py-3 pr-4">{{ $a->date_debut?->format('d/m/Y') }}</td>
                            <td class="py-3 pr-4 text-right text-green-700">{{ number_format($ind['PB'] ?? 0, 0, ',', ' ') }}</td>
                            <td class="py-3 pr-4 text-right text-red-600">{{ number_format($ind['CT'] ?? 0, 0, ',', ' ') }}</td>
                            <td class="py-3 pr-4 text-right">{{ number_format($ind['MB'] ?? 0, 0, ',', ' ') }}</td>
                            <td class="py-3 pr-4"><x-status-indicator :statut="$ind['statut'] ?? 'rouge'" /></td>
                            <td class="py-3">
                                <a href="{{ route('activites.show', $a->id) }}" class="text-agro-vert font-medium mr-2">Voir</a>
                                <form method="POST" action="{{ route('activites.cloturer', $a->id) }}" class="inline" onsubmit="return confirm('Clôturer ?');">@csrf<button type="submit" class="text-amber-700 text-xs mr-2">Clôturer</button></form>
                                <button type="button" class="text-gray-500 text-xs hover:text-white/80 transition-colors" data-open-abandon-modal data-abandon-url="{{ route('activites.abandonner', $a->id) }}">Abandonner</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-8 text-center text-gray-500">Aucune campagne en cours.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div id="panel-terminees" class="tab-panel hidden" data-panel="2">
        <div class="card overflow-x-auto bg-gray-50/50">
            <table class="w-full text-sm">
                <thead><tr class="text-left text-gray-500 border-b"><th class="py-2 pr-4">Campagne</th><th class="py-2 pr-4">Type</th><th class="py-2 pr-4">Date fin</th><th class="py-2 pr-4 text-right">Marge</th><th class="py-2 pr-4 text-right">RNE</th><th class="py-2">Actions</th></tr></thead>
                <tbody>
                    @forelse($terminees as $a)
                        @php $ind = $indicateursTerminees[$a->id] ?? []; @endphp
                        <tr class="border-b border-gray-100">
                            <td class="py-3 pr-4 font-medium text-gray-700">{{ $a->nom }}</td><td class="py-3 pr-4">{{ $a->type }}</td><td class="py-3 pr-4">{{ $a->date_fin?->format('d/m/Y') ?? '—' }}</td><td class="py-3 pr-4 text-right">{{ number_format($ind['MB'] ?? 0, 0, ',', ' ') }}</td><td class="py-3 pr-4 text-right">{{ number_format($ind['RNE'] ?? 0, 0, ',', ' ') }}</td><td class="py-3"><a href="{{ route('activites.show', $a->id) }}" class="text-agro-vert font-medium">Voir</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-gray-500">Aucune campagne terminée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div id="panel-abandonnees" class="tab-panel hidden" data-panel="3">
        <div class="card overflow-x-auto bg-gray-50/50 border border-dashed border-gray-200">
            <table class="w-full text-sm">
                <thead><tr class="text-left text-gray-500 border-b"><th class="py-2 pr-4">Campagne</th><th class="py-2 pr-4">Type</th><th class="py-2 pr-4">Date fin</th><th class="py-2 pr-4 text-right">Marge</th><th class="py-2 pr-4 text-right">RNE</th><th class="py-2">Actions</th></tr></thead>
                <tbody>
                    @forelse($abandonnees as $a)
                        @php $ind = $indicateursAbandonnees[$a->id] ?? []; @endphp
                        <tr class="border-b border-gray-100">
                            <td class="py-3 pr-4 font-medium text-gray-700">{{ $a->nom }}</td><td class="py-3 pr-4">{{ $a->type }}</td><td class="py-3 pr-4">{{ $a->date_fin?->format('d/m/Y') ?? '—' }}</td><td class="py-3 pr-4 text-right">{{ number_format($ind['MB'] ?? 0, 0, ',', ' ') }}</td><td class="py-3 pr-4 text-right">{{ number_format($ind['RNE'] ?? 0, 0, ',', ' ') }}</td><td class="py-3"><a href="{{ route('activites.show', $a->id) }}" class="text-agro-vert font-medium">Voir</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-gray-500">Aucune campagne abandonnée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        (function () {
            var buttons = document.querySelectorAll('.tab-btn');
            var panels  = document.querySelectorAll('.tab-panel');
            function show(tab) {
                panels.forEach(function (p) { p.classList.toggle('hidden', p.getAttribute('data-panel') !== String(tab)); });
                buttons.forEach(function (b) {
                    var active = b.getAttribute('data-tab') === String(tab);
                    b.classList.toggle('border-agro-vert', active); b.classList.toggle('text-agro-vert', active); b.classList.toggle('font-semibold', active);
                    b.classList.toggle('text-gray-500', !active); b.classList.toggle('border-transparent', !active);
                });
            }
            buttons.forEach(function (b) { b.addEventListener('click', function () { show(b.getAttribute('data-tab')); }); });
        })();
    </script>
    <x-confirm-abandon-modal />
@endif

@endsection
