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
/* Tokens : --af-* (app.css) */
.show-hero {
    background: var(--af-glass-05);
    border: 1px solid rgba(255, 255, 255, 0.09);
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
.show-hero-meta {
    font-family: var(--font-ui), sans-serif;
    font-size: 12px;
    color: rgba(255, 255, 255, 0.35);
    margin: 0;
}
.show-hero-badge {
    font-family: var(--font-ui), sans-serif;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    padding: 4px 12px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    border: 1px solid transparent;
}
.show-hero-badge--vert {
    background: var(--af-filter-active-bg);
    border-color: var(--af-filter-active-border);
    color: var(--af-color-accent);
}
.show-hero-badge--orange {
    background: var(--af-amber-tint-bg);
    border-color: var(--af-amber-tint-border);
    color: var(--af-color-warning);
}
.show-hero-badge--rouge {
    background: var(--af-red-tint-bg);
    border-color: var(--af-red-tint-border);
    color: var(--af-color-danger);
}
.show-hero-badge-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
}
.show-hero-rne-label {
    font-family: var(--font-ui), sans-serif;
    font-size: 11px;
    color: rgba(255, 255, 255, 0.38);
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 4px;
}
.show-hero-rne {
    font-family: var(--font-display), sans-serif;
    font-size: 36px;
    font-weight: 800;
    letter-spacing: -0.04em;
    line-height: 1;
    margin-bottom: 16px;
}
.show-hero-rne--pos { color: var(--af-color-accent); }
.show-hero-rne--neg { color: var(--af-color-danger); }
.show-hero-rne-unit {
    font-size: 16px;
    font-weight: 600;
    opacity: 0.7;
    margin-left: 3px;
}
.show-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
    margin-bottom: 16px;
}
.show-kpi-cell {
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.07);
    border-radius: 14px;
    padding: 10px 6px;
    text-align: center;
}
.show-kpi-lbl {
    font-family: var(--font-ui), sans-serif;
    font-size: 9px;
    color: rgba(255, 255, 255, 0.28);
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 4px;
}
.show-kpi-val {
    font-family: var(--font-display), sans-serif;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: -0.02em;
}
.show-kpi-val--accent { color: var(--af-color-accent); }
.show-kpi-val--danger { color: var(--af-color-danger); }
.show-kpi-val--strong { color: var(--af-text-body-strong); }
.show-kpi-val--muted { color: rgba(255, 255, 255, 0.65); }
.show-sr {
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.07);
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
    font-family: var(--font-ui), sans-serif;
    font-size: 11px;
    color: rgba(255, 255, 255, 0.38);
}
.show-sr-val {
    font-family: var(--font-display), sans-serif;
    font-size: 14px;
    font-weight: 700;
    color: var(--af-color-accent);
    letter-spacing: -0.02em;
}
.show-sr-status {
    font-family: var(--font-ui), sans-serif;
    font-size: 12px;
    font-weight: 600;
    margin: 0;
}
.show-sr-status--ok { color: var(--af-color-accent); }
.show-sr-status--bad { color: var(--af-color-danger); }
.show-hero-actions { display: flex; gap: 10px; margin-top: 4px; }
.show-hero-form--grow { flex: 1; }
.show-hero-btn {
    flex: 1;
    font-family: var(--font-ui), sans-serif;
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
.show-hero-btn--block { width: 100%; }
.show-hero-btn:active { opacity: 0.75; }
.show-hero-btn-primary {
    background: var(--af-color-accent-dark);
    color: #fff;
    border: 1px solid var(--af-tx-type-rec-border);
}
.show-hero-btn-ghost {
    background: var(--af-glass-06);
    color: rgba(255, 255, 255, 0.65);
    border: 1px solid rgba(255, 255, 255, 0.12);
}
.show-sec-hd {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}
.show-sec-title {
    font-family: var(--font-display), sans-serif;
    font-size: 14px;
    font-weight: 700;
    color: rgba(255, 255, 255, 0.75);
    letter-spacing: -0.02em;
}
.show-sec-count {
    font-family: var(--font-ui), sans-serif;
    font-size: 11px;
    color: rgba(255, 255, 255, 0.28);
}
.show-tx-wrap {
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 20px;
    padding: 4px 16px;
    margin-bottom: 16px;
}
.show-tx-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
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
.show-tx-icon-recette { background: var(--af-filter-active-bg); }
.show-tx-icon-recette svg { width: 18px; height: 18px; color: var(--af-color-accent); }
.show-tx-icon-depense { background: var(--af-red-tint-bg); }
.show-tx-icon-depense svg { width: 18px; height: 18px; color: var(--af-color-danger); }
.show-tx-body { flex: 1; min-width: 0; }
.show-tx-cat {
    font-family: var(--font-ui), sans-serif;
    font-size: 13px;
    font-weight: 600;
    color: var(--af-text-body-strong);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.show-tx-meta {
    font-family: var(--font-ui), sans-serif;
    font-size: 11px;
    color: rgba(255, 255, 255, 0.3);
    margin-top: 1px;
}
.show-tx-row-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}
.show-tx-amount {
    font-family: var(--font-display), sans-serif;
    font-size: 14px;
    font-weight: 700;
    letter-spacing: -0.02em;
    flex-shrink: 0;
}
.show-tx-amount--rec { color: var(--af-color-accent); }
.show-tx-amount--dep { color: var(--af-color-danger); }
.show-tx-edit { color: rgba(255, 255, 255, 0.3); display: flex; }
.show-tx-edit svg { width: 14px; height: 14px; }
.show-tx-pager { padding: 0 4px; margin-bottom: 12px; }
.show-tx-empty {
    text-align: center;
    padding: 30px 20px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.07);
    border-radius: var(--af-radius-lg);
    margin-bottom: 16px;
}
.show-tx-empty p {
    font-family: var(--font-ui), sans-serif;
    font-size: 13px;
    color: rgba(255, 255, 255, 0.32);
    margin: 0;
}
.show-alert {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 12px 16px;
    border-radius: 14px;
    margin-bottom: 14px;
    border: 1px solid;
}
.show-alert--mt { margin-top: 8px; }
.show-alert-emoji { font-size: 16px; flex-shrink: 0; }
.show-alert-rouge { background: var(--af-red-alert-bg); border-color: var(--af-red-alert-border); }
.show-alert-orange { background: var(--af-amber-alert-bg); border-color: var(--af-amber-alert-border); }
.show-alert-title {
    font-family: var(--font-ui), sans-serif;
    font-size: 13px;
    font-weight: 600;
    color: var(--af-color-warning);
    margin: 0 0 2px;
}
.show-alert-sub {
    font-family: var(--font-ui), sans-serif;
    font-size: 12px;
    color: rgba(255, 255, 255, 0.4);
    margin: 0;
}
.show-alert-msg {
    font-family: var(--font-ui), sans-serif;
    font-size: 13px;
    margin: 0;
}
.show-alert-msg--danger { color: var(--af-color-danger); }
.show-alert-msg--warn { color: var(--af-color-warning); }
.show-bottom-bar {
    position: fixed;
    bottom: 70px;
    left: 0;
    right: 0;
    padding: 12px 16px;
    background: var(--af-tx-footer-scrim);
    backdrop-filter: blur(var(--af-blur-flash));
    -webkit-backdrop-filter: blur(var(--af-blur-flash));
    border-top: 1px solid rgba(255, 255, 255, 0.07);
    display: flex;
    gap: 10px;
    z-index: 30;
}
.show-bottom-form--grow { flex: 1; }
.show-bottom-btn {
    flex: 1;
    font-family: var(--font-ui), sans-serif;
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
.show-bottom-btn--block { width: 100%; }
.show-bottom-btn:active { opacity: 0.75; }
.show-bottom-btn-primary {
    background: var(--af-color-accent-dark);
    color: #fff;
    border: 1px solid var(--af-tx-type-rec-border);
}
.show-bottom-btn-amber {
    background: var(--af-amber-tint-bg);
    color: var(--af-color-warning);
    border: 1px solid var(--af-amber-tint-border);
}
.show-bottom-btn-red {
    background: var(--af-red-tint-bg);
    color: var(--af-color-danger);
    border: 1px solid var(--af-red-tint-border);
}
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
    $statutHeroBadge = match ($statut) {
        'vert' => ['label' => 'RENTABLE', 'class' => 'show-hero-badge--vert'],
        'orange' => ['label' => 'SURVEILLER', 'class' => 'show-hero-badge--orange'],
        default => ['label' => 'DÉFICIT', 'class' => 'show-hero-badge--rouge'],
    };
    $enCours = $activite->statut === \App\Models\Activite::STATUT_EN_COURS;
@endphp

<div class="show-content-pad">

    {{-- Alerte statut clôturée/abandonnée --}}
    @if($activite->statut !== \App\Models\Activite::STATUT_EN_COURS)
        <div class="show-alert show-alert-orange show-alert--mt">
            <span class="show-alert-emoji" aria-hidden="true">ℹ️</span>
            <div>
                <p class="show-alert-title">{{ $statutLabels[$activite->statut] ?? $activite->statut }}</p>
                <p class="show-alert-sub">Les transactions ne sont plus modifiables.</p>
            </div>
        </div>
    @endif

    {{-- Alerte budget --}}
    @if($alerteBudget)
        <div class="show-alert {{ $niv === 'rouge' ? 'show-alert-rouge' : 'show-alert-orange' }}">
            <span class="show-alert-emoji" aria-hidden="true">⚠️</span>
            <p class="show-alert-msg {{ $niv === 'rouge' ? 'show-alert-msg--danger' : 'show-alert-msg--warn' }}">
                Budget consommé : {{ $alerteBudget['pourcent'] }}% — {{ $niv === 'rouge' ? 'Dépassement' : 'Attention' }}
            </p>
        </div>
    @endif

    {{-- ── Hero RNE ── --}}
    <div class="show-hero">
        <div class="show-hero-top">
            <div>
                <p class="show-hero-meta">
                    {{ ucfirst(str_replace('_', ' ', $activite->type)) }} · depuis le {{ $activite->date_debut->format('d/m/Y') }}
                </p>
            </div>
            <span class="show-hero-badge {{ $statutHeroBadge['class'] }}">
                <span class="show-hero-badge-dot"></span>
                {{ $statutHeroBadge['label'] }}
            </span>
        </div>
        <p class="show-hero-rne-label">Résultat net d'exploitation</p>
        <div class="show-hero-rne {{ $rne >= 0 ? 'show-hero-rne--pos' : 'show-hero-rne--neg' }}">
            {{ $rne >= 0 ? '+' : '−' }}{{ number_format(abs($rne), 0, ',', ' ') }}<span class="show-hero-rne-unit">FCFA</span>
        </div>

        {{-- KPI 2×4 --}}
        <div class="show-kpi-grid">
            <div class="show-kpi-cell">
                <div class="show-kpi-lbl">PB</div>
                <div class="show-kpi-val show-kpi-val--accent">{{ number_format($pb/1000,1,',', ' ') }}K</div>
            </div>
            <div class="show-kpi-cell">
                <div class="show-kpi-lbl">MB</div>
                <div class="show-kpi-val {{ $mb >= 0 ? 'show-kpi-val--accent' : 'show-kpi-val--danger' }}">{{ number_format($mb/1000,1,',',' ') }}K</div>
            </div>
            <div class="show-kpi-cell">
                <div class="show-kpi-lbl">CT</div>
                <div class="show-kpi-val show-kpi-val--danger">{{ number_format($ct/1000,1,',',' ') }}K</div>
            </div>
            <div class="show-kpi-cell">
                <div class="show-kpi-lbl">RF</div>
                <div class="show-kpi-val show-kpi-val--strong">{{ number_format($rf,1,',',' ') }}%</div>
            </div>
            <div class="show-kpi-cell">
                <div class="show-kpi-lbl">CV</div>
                <div class="show-kpi-val show-kpi-val--muted">{{ number_format($cv/1000,1,',',' ') }}K</div>
            </div>
            <div class="show-kpi-cell">
                <div class="show-kpi-lbl">CF</div>
                <div class="show-kpi-val show-kpi-val--muted">{{ number_format($cf/1000,1,',',' ') }}K</div>
            </div>
            <div class="show-kpi-cell">
                <div class="show-kpi-lbl">VAB</div>
                <div class="show-kpi-val show-kpi-val--muted">{{ number_format($vab/1000,1,',',' ') }}K</div>
            </div>
            <div class="show-kpi-cell">
                <div class="show-kpi-lbl">RNE</div>
                <div class="show-kpi-val {{ $rne >= 0 ? 'show-kpi-val--accent' : 'show-kpi-val--danger' }}">{{ number_format($rne/1000,1,',',' ') }}K</div>
            </div>
        </div>

        {{-- SR --}}
        <div class="show-sr">
            <div class="show-sr-top">
                <span class="show-sr-label">Seuil de rentabilité (SR)</span>
                <span class="show-sr-val">{{ number_format($sr, 0, ',', ' ') }} FCFA</span>
            </div>
            @if($srAtteint)
                <p class="show-sr-status show-sr-status--ok">✅ Seuil atteint</p>
            @else
                <p class="show-sr-status show-sr-status--bad">❌ Seuil non atteint</p>
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
                <form method="POST" action="{{ route('rapports.generer') }}" class="show-hero-form--grow">
                    @csrf
                    <input type="hidden" name="activite_id" value="{{ $activite->id }}">
                    <input type="hidden" name="type" value="campagne">
                    <input type="hidden" name="periode_debut" value="{{ $activite->date_debut?->format('Y-m-d') ?? now()->toDateString() }}">
                    <input type="hidden" name="periode_fin" value="{{ now()->toDateString() }}">
                    <button type="submit" class="show-hero-btn show-hero-btn-ghost show-hero-btn--block">📄 PDF</button>
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
            <span class="show-sec-count">{{ $transactions->total() }} au total</span>
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
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
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
                        <div class="show-tx-row-actions">
                            <div class="show-tx-amount {{ $isRec ? 'show-tx-amount--rec' : 'show-tx-amount--dep' }}">
                                {{ $isRec ? '+' : '−' }}{{ number_format($t->montant, 0, ',', ' ') }}
                            </div>
                            @if($enCours)
                                <a href="{{ route('transactions.edit', $t->id) }}" class="show-tx-edit" title="Modifier">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 11l6.071-6.071a2.5 2.5 0 113.536 3.536L12.5 14.5H9v-3.5z"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="show-tx-pager">{{ $transactions->links() }}</div>
        @else
            <div class="show-tx-empty">
                <p>Aucune transaction enregistrée.</p>
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
    <form method="POST" action="{{ route('activites.cloturer', $activite->id) }}" class="show-bottom-form--grow" onsubmit="return confirm('Clôturer cette campagne ?');">
        @csrf
        <button type="submit" class="show-bottom-btn show-bottom-btn-amber show-bottom-btn--block">Clôturer</button>
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
