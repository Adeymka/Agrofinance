@extends($layout)
@section('title', $activite->nom . ' — AgroFinance+')
@section('page-title', $activite->nom)
@section('page-subtitle', ucfirst($activite->type) . ' · depuis le ' . $activite->date_debut->format('d/m/Y'))

@section('topbar-actions')
    @if($activite->statut === \App\Models\Activite::STATUT_EN_COURS)
        <a href="{{ route('transactions.create', ['activite_id' => $activite->id]) }}" class="btn-primary inline-flex items-center gap-2">
            <x-icon name="plus" class="w-4 h-4" /> Saisir une transaction
        </a>
    @endif
    @if($infoAbonnement['peut_pdf'] ?? false)
        <form method="POST" action="{{ route('rapports.generer') }}" class="inline">
            @csrf
            <input type="hidden" name="activite_id" value="{{ $activite->id }}">
            <input type="hidden" name="type" value="campagne">
            <input type="hidden" name="periode_debut" value="{{ $activite->date_debut?->format('Y-m-d') ?? now()->toDateString() }}">
            <input type="hidden" name="periode_fin" value="{{ now()->toDateString() }}">
            <button type="submit" class="btn-outline inline-flex items-center gap-2">
                <x-icon name="document-text" class="w-4 h-4" /> Générer PDF
            </button>
        </form>
    @endif
@endsection

@section('content')

@php
    $niv = $alerteBudget['niveau'] ?? null;
    $alertClass = $niv === 'rouge' ? 'bg-red-50 border-red-300 text-red-900' : ($niv === 'orange' ? 'bg-orange-50 border-orange-300 text-orange-900' : 'bg-amber-50 border-amber-300 text-amber-900');
    $statutLabels = [
        \App\Models\Activite::STATUT_EN_COURS => 'En cours',
        \App\Models\Activite::STATUT_TERMINE  => 'Terminée',
        \App\Models\Activite::STATUT_ABANDONNE => 'Abandonnée',
    ];
@endphp

@if($platform === 'mobile')

@push('styles')
<style>
/* ── Show header hero ── */
.show-hero {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.09);
    border-radius: 24px;
    padding: 20px;
    margin-bottom: 16px;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}
.show-hero-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
}
.show-hero-badge {
    font-family: 'Inter', sans-serif;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    padding: 4px 12px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.show-hero-badge-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
}
.show-hero-rne-label {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    color: rgba(255,255,255,0.38);
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 4px;
}
.show-hero-rne {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 36px;
    font-weight: 800;
    letter-spacing: -0.04em;
    line-height: 1;
    margin-bottom: 16px;
}
.show-hero-rne-unit {
    font-size: 16px;
    font-weight: 600;
    opacity: 0.7;
    margin-left: 3px;
}
/* ── Indicators grid 2x4 ── */
.show-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
    margin-bottom: 16px;
}
.show-kpi-cell {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 14px;
    padding: 10px 6px;
    text-align: center;
}
.show-kpi-lbl {
    font-family: 'Inter', sans-serif;
    font-size: 9px;
    color: rgba(255,255,255,0.28);
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 4px;
}
.show-kpi-val {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: -0.02em;
}
/* ── SR bar ── */
.show-sr {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 16px;
    padding: 14px 16px;
    margin-bottom: 16px;
}
.show-sr-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
}
.show-sr-label {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    color: rgba(255,255,255,0.38);
}
.show-sr-val {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 14px;
    font-weight: 700;
    color: #4ade80;
    letter-spacing: -0.02em;
}
/* ── Actions hero ── */
.show-hero-actions { display: flex; gap: 10px; margin-top: 4px; }
.show-hero-btn {
    flex: 1;
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    font-weight: 600;
    padding: 10px 14px;
    border-radius: 12px;
    text-align: center;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: opacity 0.15s;
}
.show-hero-btn:active { opacity: 0.75; }
.show-hero-btn-primary {
    background: #16a34a;
    color: white;
    border: 1px solid rgba(74,222,128,0.30);
}
.show-hero-btn-ghost {
    background: rgba(255,255,255,0.06);
    color: rgba(255,255,255,0.65);
    border: 1px solid rgba(255,255,255,0.12);
}
/* ── Section header ── */
.show-sec-hd {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}
.show-sec-title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 14px;
    font-weight: 700;
    color: rgba(255,255,255,0.75);
    letter-spacing: -0.02em;
}
.show-sec-link {
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    color: #4ade80;
    text-decoration: none;
    font-weight: 600;
}
/* ── Transaction item ── */
.show-tx-wrap {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 20px;
    padding: 4px 16px;
    margin-bottom: 16px;
}
.show-tx-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}
.show-tx-item:last-child { border-bottom: none; }
.show-tx-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.show-tx-icon-recette { background: rgba(74,222,128,0.12); }
.show-tx-icon-depense { background: rgba(248,113,113,0.12); }
.show-tx-body { flex: 1; min-width: 0; }
.show-tx-cat {
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    font-weight: 600;
    color: rgba(255,255,255,0.82);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.show-tx-meta {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    color: rgba(255,255,255,0.30);
    margin-top: 1px;
}
.show-tx-amount {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 14px;
    font-weight: 700;
    letter-spacing: -0.02em;
    flex-shrink: 0;
}
/* ── Alert banner ── */
.show-alert {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 12px 16px;
    border-radius: 14px;
    margin-bottom: 14px;
    border: 1px solid;
}
.show-alert-rouge { background: rgba(248,113,113,0.08); border-color: rgba(248,113,113,0.22); }
.show-alert-orange { background: rgba(251,191,36,0.08);  border-color: rgba(251,191,36,0.22);  }
/* ── Fixed bottom actions ── */
.show-bottom-bar {
    position: fixed;
    bottom: 70px;
    left: 0;
    right: 0;
    padding: 12px 16px;
    background: rgba(13,31,13,0.92);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-top: 1px solid rgba(255,255,255,0.07);
    display: flex;
    gap: 10px;
    z-index: 30;
}
.show-bottom-btn {
    flex: 1;
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    font-weight: 600;
    padding: 12px 10px;
    border-radius: 14px;
    text-align: center;
    border: none;
    cursor: pointer;
    transition: opacity 0.15s;
    text-decoration: none;
    display: block;
}
.show-bottom-btn:active { opacity: 0.75; }
.show-bottom-btn-primary { background: #16a34a; color: white; border: 1px solid rgba(74,222,128,0.30); }
.show-bottom-btn-amber { background: rgba(251,191,36,0.12); color: #fbbf24; border: 1px solid rgba(251,191,36,0.22); }
.show-bottom-btn-red { background: rgba(248,113,113,0.10); color: #f87171; border: 1px solid rgba(248,113,113,0.20); }
/* bottom padding for fixed bar */
.show-content-pad { padding-bottom: 110px; }
</style>
@endpush

@php
    $rne    = $indicateurs['RNE'] ?? 0;
    $pb     = $indicateurs['PB']  ?? 0;
    $mb     = $indicateurs['MB']  ?? 0;
    $ct     = $indicateurs['CT']  ?? 0;
    $cv     = $indicateurs['CV']  ?? 0;
    $cf     = $indicateurs['CF']  ?? 0;
    $vab    = $indicateurs['VAB'] ?? 0;
    $rf     = $indicateurs['RF']  ?? 0;
    $sr     = $indicateurs['SR']  ?? 0;
    $statut = $indicateurs['statut'] ?? 'rouge';
    $statutHeroConfig = match($statut) {
        'vert'   => ['label' => 'RENTABLE',   'color' => '#4ade80', 'bg' => 'rgba(74,222,128,0.12)',  'border' => 'rgba(74,222,128,0.22)'],
        'orange' => ['label' => 'SURVEILLER', 'color' => '#fbbf24', 'bg' => 'rgba(251,191,36,0.12)',  'border' => 'rgba(251,191,36,0.22)'],
        default  => ['label' => 'DÉFICIT',    'color' => '#f87171', 'bg' => 'rgba(248,113,113,0.12)', 'border' => 'rgba(248,113,113,0.22)'],
    };
    $enCours = $activite->statut === \App\Models\Activite::STATUT_EN_COURS;
@endphp

<div class="show-content-pad">

    {{-- Alerte statut clôturée/abandonnée --}}
    @if($activite->statut !== \App\Models\Activite::STATUT_EN_COURS)
        <div class="show-alert show-alert-orange" style="margin-top:8px;">
            <span style="font-size:16px; flex-shrink:0;">ℹ️</span>
            <div>
                <p style="font-family:'Inter',sans-serif; font-size:13px; font-weight:600; color:#fbbf24; margin:0 0 2px;">
                    {{ $statutLabels[$activite->statut] ?? $activite->statut }}
                </p>
                <p style="font-family:'Inter',sans-serif; font-size:12px; color:rgba(255,255,255,0.40); margin:0;">
                    Les transactions ne sont plus modifiables.
                </p>
            </div>
        </div>
    @endif

    {{-- Alerte budget --}}
    @if($alerteBudget)
        <div class="show-alert {{ $niv === 'rouge' ? 'show-alert-rouge' : 'show-alert-orange' }}">
            <span style="font-size:16px; flex-shrink:0;">⚠️</span>
            <p style="font-family:'Inter',sans-serif; font-size:13px; color:{{ $niv === 'rouge' ? '#f87171' : '#fbbf24' }}; margin:0;">
                Budget consommé : {{ $alerteBudget['pourcent'] }}% — {{ $niv === 'rouge' ? 'Dépassement' : 'Attention' }}
            </p>
        </div>
    @endif

    {{-- ── Hero RNE ── --}}
    <div class="show-hero">
        <div class="show-hero-top">
            <div>
                <p style="font-family:'Inter',sans-serif; font-size:12px; color:rgba(255,255,255,0.35);">
                    {{ ucfirst(str_replace('_', ' ', $activite->type)) }} · depuis le {{ $activite->date_debut->format('d/m/Y') }}
                </p>
            </div>
            <span class="show-hero-badge"
                  style="background:{{ $statutHeroConfig['bg'] }}; border:1px solid {{ $statutHeroConfig['border'] }}; color:{{ $statutHeroConfig['color'] }};">
                <span class="show-hero-badge-dot"></span>
                {{ $statutHeroConfig['label'] }}
            </span>
        </div>
        <p class="show-hero-rne-label">Résultat net d'exploitation</p>
        <div class="show-hero-rne" style="color:{{ $rne >= 0 ? '#4ade80' : '#f87171' }};">
            {{ $rne >= 0 ? '+' : '−' }}{{ number_format(abs($rne), 0, ',', ' ') }}<span class="show-hero-rne-unit">FCFA</span>
        </div>

        {{-- KPI 2×4 --}}
        <div class="show-kpi-grid">
            <div class="show-kpi-cell">
                <div class="show-kpi-lbl">PB</div>
                <div class="show-kpi-val" style="color:#4ade80;">{{ number_format($pb/1000,1,',', ' ') }}K</div>
            </div>
            <div class="show-kpi-cell">
                <div class="show-kpi-lbl">MB</div>
                <div class="show-kpi-val" style="color:{{ $mb>=0?'#4ade80':'#f87171' }};">{{ number_format($mb/1000,1,',',' ') }}K</div>
            </div>
            <div class="show-kpi-cell">
                <div class="show-kpi-lbl">CT</div>
                <div class="show-kpi-val" style="color:#f87171;">{{ number_format($ct/1000,1,',',' ') }}K</div>
            </div>
            <div class="show-kpi-cell">
                <div class="show-kpi-lbl">RF</div>
                <div class="show-kpi-val" style="color:rgba(255,255,255,0.82);">{{ number_format($rf,1,',',' ') }}%</div>
            </div>
            <div class="show-kpi-cell">
                <div class="show-kpi-lbl">CV</div>
                <div class="show-kpi-val" style="color:rgba(255,255,255,0.65);">{{ number_format($cv/1000,1,',',' ') }}K</div>
            </div>
            <div class="show-kpi-cell">
                <div class="show-kpi-lbl">CF</div>
                <div class="show-kpi-val" style="color:rgba(255,255,255,0.65);">{{ number_format($cf/1000,1,',',' ') }}K</div>
            </div>
            <div class="show-kpi-cell">
                <div class="show-kpi-lbl">VAB</div>
                <div class="show-kpi-val" style="color:rgba(255,255,255,0.65);">{{ number_format($vab/1000,1,',',' ') }}K</div>
            </div>
            <div class="show-kpi-cell">
                <div class="show-kpi-lbl">RNE</div>
                <div class="show-kpi-val" style="color:{{ $rne>=0?'#4ade80':'#f87171' }};">{{ number_format($rne/1000,1,',',' ') }}K</div>
            </div>
        </div>

        {{-- SR --}}
        <div class="show-sr">
            <div class="show-sr-top">
                <span class="show-sr-label">Seuil de rentabilité (SR)</span>
                <span class="show-sr-val">{{ number_format($sr, 0, ',', ' ') }} FCFA</span>
            </div>
            @if($srAtteint)
                <p style="font-family:'Inter',sans-serif; font-size:12px; color:#4ade80; font-weight:600;">✅ Seuil atteint</p>
            @else
                <p style="font-family:'Inter',sans-serif; font-size:12px; color:#f87171; font-weight:600;">❌ Seuil non atteint</p>
            @endif
        </div>

        {{-- Actions PDF / Détail --}}
        <div class="show-hero-actions">
            @if($enCours)
                <a href="{{ route('transactions.create', ['activite_id' => $activite->id]) }}" class="show-hero-btn show-hero-btn-primary">
                    + Saisir
                </a>
            @endif
            @if($infoAbonnement['peut_pdf'] ?? false)
                <form method="POST" action="{{ route('rapports.generer') }}" style="flex:1;">
                    @csrf
                    <input type="hidden" name="activite_id" value="{{ $activite->id }}">
                    <input type="hidden" name="type" value="campagne">
                    <input type="hidden" name="periode_debut" value="{{ $activite->date_debut?->format('Y-m-d') ?? now()->toDateString() }}">
                    <input type="hidden" name="periode_fin" value="{{ now()->toDateString() }}">
                    <button type="submit" class="show-hero-btn show-hero-btn-ghost" style="width:100%;">📄 PDF</button>
                </form>
            @else
                <a href="{{ route('abonnement') }}" class="show-hero-btn show-hero-btn-ghost">🔒 PDF</a>
            @endif
        </div>
    </div>

    {{-- ── Transactions ── --}}
    <div>
        <div class="show-sec-hd">
            <span class="show-sec-title">Transactions</span>
            <span style="font-family:'Inter',sans-serif; font-size:11px; color:rgba(255,255,255,0.28);">{{ $transactions->total() }} au total</span>
        </div>

        @if($transactions->isNotEmpty())
            <div class="show-tx-wrap">
                @foreach($transactions as $t)
                    @php
                        $isRec = $t->type === 'recette';
                        $catLabel = ucfirst(str_replace('_', ' ', $t->categorie));
                    @endphp
                    <div class="show-tx-item">
                        <div class="show-tx-icon {{ $isRec ? 'show-tx-icon-recette' : 'show-tx-icon-depense' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;color:{{ $isRec ? '#4ade80' : '#f87171' }};" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $isRec ? 'M5 10l7-7m0 0l7 7m-7-7v18' : 'M19 14l-7 7m0 0l-7-7m7 7V3' }}"/>
                            </svg>
                        </div>
                        <div class="show-tx-body">
                            <div class="show-tx-cat">{{ $catLabel }}</div>
                            <div class="show-tx-meta">
                                {{ $t->date_transaction->format('d/m/Y') }}
                                @if($t->nature) · {{ ucfirst($t->nature) }} @endif
                                @if($t->note) · {{ Str::limit($t->note, 25) }} @endif
                            </div>
                        </div>
                        <div style="display:flex; align-items:center; gap:8px; flex-shrink:0;">
                            <div class="show-tx-amount" style="color:{{ $isRec ? '#4ade80' : '#f87171' }};">
                                {{ $isRec ? '+' : '−' }}{{ number_format($t->montant, 0, ',', ' ') }}
                            </div>
                            @if($enCours)
                                <a href="{{ route('transactions.edit', $t->id) }}" style="color:rgba(255,255,255,0.30); display:flex;" title="Modifier">
                                    <svg xmlns="http://www.w3.org/2000/svg" style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 11l6.071-6.071a2.5 2.5 0 113.536 3.536L12.5 14.5H9v-3.5z"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            <div style="padding: 0 4px; margin-bottom: 12px;">{{ $transactions->links() }}</div>
        @else
            <div style="text-align:center; padding:30px 20px; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.07); border-radius:20px; margin-bottom:16px;">
                <p style="font-family:'Inter',sans-serif; font-size:13px; color:rgba(255,255,255,0.32);">Aucune transaction enregistrée.</p>
            </div>
        @endif
    </div>

</div>

{{-- ── Barre actions fixe en bas ── --}}
@if($enCours)
<div class="show-bottom-bar">
    <a href="{{ route('transactions.create', ['activite_id' => $activite->id]) }}" class="show-bottom-btn show-bottom-btn-primary">
        + Saisir
    </a>
    <form method="POST" action="{{ route('activites.cloturer', $activite->id) }}" style="flex:1;" onsubmit="return confirm('Clôturer cette campagne ?');">
        @csrf
        <button type="submit" class="show-bottom-btn show-bottom-btn-amber" style="width:100%;">Clôturer</button>
    </form>
    <button type="button" class="show-bottom-btn show-bottom-btn-red" data-open-abandon-modal
            data-abandon-url="{{ route('activites.abandonner', $activite->id) }}">
        Abandonner
    </button>
</div>
@endif

<x-confirm-abandon-modal />

@else
{{-- ════ DESKTOP (original) ════ --}}
    @if($activite->statut !== \App\Models\Activite::STATUT_EN_COURS)
        <div class="rounded-xl border border-amber-200 bg-amber-50 text-amber-900 px-4 py-3 mb-6 text-sm">
            Statut : <strong>{{ $statutLabels[$activite->statut] ?? $activite->statut }}</strong>
            @if($activite->statut === \App\Models\Activite::STATUT_TERMINE)
                — Cette campagne est clôturée. Les transactions ne sont plus modifiables.
            @elseif($activite->statut === \App\Models\Activite::STATUT_ABANDONNE)
                — Cette campagne est marquée comme abandonnée. Les transactions ne sont plus modifiables.
            @endif
        </div>
    @endif

    @if($alerteBudget)
        <div class="rounded-xl border p-4 mb-6 text-sm {{ $alertClass }}">
            Budget consommé : {{ $alerteBudget['pourcent'] }}% — {{ $niv === 'rouge' ? 'Dépassement' : 'Attention' }}
        </div>
    @endif

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        @foreach ([
            'PB' => 'Produit Brut (PB)',
            'CV' => 'Charges variables (CV)',
            'CF' => 'Charges fixes (CF)',
            'CT' => 'Coût total (CT)',
            'VAB' => 'Valeur ajoutée (VAB)',
            'MB' => 'Marge brute (MB)',
            'RNE' => 'Résultat net (RNE)',
            'RF' => 'Rentabilité (RF %)',
        ] as $key => $label)
            <div class="card bg-gray-50/80">
                <p class="text-xs text-gray-500">{{ $label }}</p>
                <p class="text-lg font-bold text-gray-900 mt-1">
                    @if($key === 'RF')
                        {{ number_format($indicateurs['RF'] ?? 0, 1) }} %
                    @else
                        {{ number_format($indicateurs[$key] ?? 0, 0, ',', ' ') }} FCFA
                    @endif
                </p>
            </div>
        @endforeach
    </div>

    <div class="card mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-sm text-gray-600">Seuil de rentabilité (SR)</p>
            <p class="text-xl font-bold text-agro-vert">{{ number_format($indicateurs['SR'] ?? 0, 0, ',', ' ') }} FCFA</p>
        </div>
        @if($srAtteint)
            <span class="badge-vert">✅ Atteint</span>
        @else
            <span class="badge-rouge">❌ Non atteint</span>
        @endif
    </div>

    <div class="card overflow-x-auto">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Transactions</h3>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="py-2 pr-3">Date</th><th class="py-2 pr-3">Type</th><th class="py-2 pr-3">Catégorie</th>
                    <th class="py-2 pr-3">Nature</th><th class="py-2 pr-3 text-right">Montant</th>
                    <th class="py-2 pr-3">Note</th><th class="py-2 pr-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $t)
                    <tr class="border-b {{ $t->type === 'recette' ? 'bg-green-50/40' : 'bg-red-50/30' }}">
                        <td class="py-2 pr-3">{{ $t->date_transaction->format('d/m/Y') }}</td>
                        <td class="py-2 pr-3">{{ $t->type === 'recette' ? 'Recette' : 'Dépense' }}</td>
                        <td class="py-2 pr-3">{{ str_replace('_', ' ', $t->categorie) }}</td>
                        <td class="py-2 pr-3">{{ $t->nature ?? '—' }}</td>
                        <td class="py-2 pr-3 text-right font-semibold">{{ number_format($t->montant, 0, ',', ' ') }}</td>
                        <td class="py-2 pr-3 max-w-xs truncate">{{ $t->note ?? '—' }}</td>
                        <td class="py-2 pr-3 whitespace-nowrap">
                            @if($activite->statut === \App\Models\Activite::STATUT_EN_COURS)
                                <a href="{{ route('transactions.edit', $t->id) }}" class="mr-2 inline-flex text-agro-vert" title="Modifier"><x-icon name="pencil-square" class="w-4 h-4" /></a>
                                <form method="POST" action="{{ route('transactions.destroy', $t->id) }}" class="inline" onsubmit="return confirm('Supprimer ?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 inline-flex p-0.5" title="Supprimer"><x-icon name="trash" class="w-4 h-4" /></button>
                                </form>
                            @else
                                <span class="text-gray-400 text-xs">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-8 text-center text-gray-500">Aucune transaction.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $transactions->links() }}</div>
    </div>

    @if($activite->statut === \App\Models\Activite::STATUT_EN_COURS)
        <div class="mt-8 flex flex-wrap gap-3">
            <form method="POST" action="{{ route('activites.cloturer', $activite->id) }}" onsubmit="return confirm('Clôturer cette campagne ? Le bilan final sera calculé.');">
                @csrf
                <button type="submit" class="btn-outline text-red-700 border-red-200">Clôturer la campagne</button>
            </form>
            <button type="button" class="btn-outline text-gray-600 border-gray-200" data-open-abandon-modal data-abandon-url="{{ route('activites.abandonner', $activite->id) }}">
                Marquer comme abandonnée
            </button>
        </div>
    @endif

    <x-confirm-abandon-modal />
@endif

@endsection
