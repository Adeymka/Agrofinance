@extends($layout)
@section('title', 'Transactions — AgroFinance+')
@section('page-title', 'Transactions')

@section('content')

@if($platform === 'mobile')

@push('styles')
<style>
.txli-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 16px;
}
.txli-title {
    font-family: var(--font-display), sans-serif;
    font-size: 22px;
    font-weight: 700;
    color: var(--af-text-high);
    letter-spacing: -0.03em;
    margin: 0;
}
.txli-btn-saisie {
    flex-shrink: 0;
    font-family: var(--font-ui), sans-serif;
    font-size: 13px;
    font-weight: 700;
    padding: 10px 14px;
    border-radius: 12px;
    background: var(--af-color-accent-dark);
    color: #fff;
    text-decoration: none;
    border: 1px solid var(--af-tx-type-rec-border);
}
.txli-card {
    background: var(--af-glass-05);
    border: 1px solid rgba(255, 255, 255, 0.09);
    border-radius: var(--af-radius-lg);
    padding: 14px 16px;
    margin-bottom: 10px;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}
.txli-card-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 8px;
}
.txli-cat {
    font-family: var(--font-ui), sans-serif;
    font-size: 14px;
    font-weight: 600;
    color: var(--af-text-body-strong);
    text-transform: capitalize;
}
.txli-amt {
    font-family: var(--font-display), sans-serif;
    font-size: 15px;
    font-weight: 700;
    letter-spacing: -0.02em;
    flex-shrink: 0;
}
.txli-amt--rec { color: var(--af-color-accent); }
.txli-amt--dep { color: var(--af-color-danger); }
.txli-meta {
    font-family: var(--font-ui), sans-serif;
    font-size: 11px;
    color: var(--af-text-muted);
    margin-bottom: 10px;
}
.txli-type-pill {
    display: inline-block;
    font-family: var(--font-ui), sans-serif;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 3px 8px;
    border-radius: 999px;
}
.txli-type-pill--rec {
    background: var(--af-filter-active-bg);
    border: 1px solid var(--af-filter-active-border);
    color: var(--af-color-accent);
}
.txli-type-pill--dep {
    background: var(--af-red-tint-bg);
    border: 1px solid var(--af-red-tint-border);
    color: var(--af-color-danger);
}
.txli-actions {
    display: flex;
    align-items: center;
    gap: 12px;
    padding-top: 8px;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
}
.txli-actions a { color: var(--af-color-accent); }
.txli-actions button { color: var(--af-color-danger); background: none; border: none; padding: 0; cursor: pointer; }
.txli-empty {
    text-align: center;
    padding: 36px 20px;
    background: var(--af-glass-06);
    border: 1px solid rgba(255, 255, 255, 0.07);
    border-radius: var(--af-radius-lg);
}
.txli-empty p {
    font-family: var(--font-ui), sans-serif;
    font-size: 14px;
    color: var(--af-text-dim);
    margin: 0 0 16px;
}
.txli-empty a {
    display: inline-block;
    font-family: var(--font-ui), sans-serif;
    font-size: 14px;
    font-weight: 600;
    color: var(--af-color-accent);
}
.txli-pager { margin-top: 8px; }
</style>
@endpush

<div class="txli-head">
    <h1 class="txli-title">Transactions</h1>
    @if($canWriteTransactions ?? true)
        <a href="{{ route('transactions.create') }}" class="txli-btn-saisie">+ Saisie</a>
    @endif
</div>

@if($isCooperative ?? false)
<form method="get" action="{{ route('transactions.index') }}" class="mb-3">
    <label for="tx-filter-mobile" class="text-xs text-white/60 mr-2">Filtre validation</label>
    <select id="tx-filter-mobile" name="statut_validation" onchange="this.form.submit()" class="input-glass text-xs py-1.5 min-w-[180px]">
        <option value="all" @selected(($statutValidation ?? 'all') === 'all')>Toutes</option>
        <option value="en_attente" @selected(($statutValidation ?? 'all') === 'en_attente')>En attente</option>
        <option value="validee" @selected(($statutValidation ?? 'all') === 'validee')>Validées</option>
    </select>
</form>
@endif

@forelse ($transactions as $t)
    @php
        $isRec = $t->type === 'recette';
    @endphp
    <div class="txli-card">
        <div class="txli-card-top">
            <span class="txli-cat">{{ str_replace('_', ' ', $t->categorie) }}</span>
            <span class="txli-amt {{ $isRec ? 'txli-amt--rec' : 'txli-amt--dep' }}">
                {{ $isRec ? '+' : '−' }}{{ number_format($t->montant, 0, ',', ' ') }}
            </span>
        </div>
        <div class="txli-meta">
            <span class="txli-type-pill {{ $isRec ? 'txli-type-pill--rec' : 'txli-type-pill--dep' }}">{{ $isRec ? 'Recette' : 'Dépense' }}</span>
            @if($isCooperative ?? false)
                ·
                <span class="txli-type-pill {{ ($t->statut_validation ?? 'validee') === 'validee' ? 'txli-type-pill--rec' : 'txli-type-pill--dep' }}">
                    @if(($t->statut_validation ?? 'validee') === 'validee')
                        Validée
                    @elseif((int) ($t->validation_niveau ?? 0) >= 1)
                        En attente N2
                    @else
                        En attente
                    @endif
                </span>
            @endif
            · {{ $t->date_transaction->format('d/m/Y') }}
            · {{ $t->activite->nom ?? '—' }}
        </div>
        <div class="txli-actions">
            @if($canWriteTransactions ?? true)
                <a href="{{ route('transactions.edit', $t->id) }}">Modifier</a>
            @endif
            @if(($isCooperative ?? false) && ($canValidateTransactions ?? false))
                @if(($t->statut_validation ?? 'validee') === 'en_attente')
                    <form method="POST" action="{{ route('transactions.valider', $t->id) }}" class="inline">
                        @csrf
                        <input type="hidden" name="statut_validation" value="{{ $statutValidation ?? 'all' }}">
                        <button type="submit" style="color:var(--af-color-accent);">
                            {{ ((int) ($t->validation_niveau ?? 0) >= 1) ? 'Valider N2' : 'Valider N1' }}
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('transactions.remettre-en-attente', $t->id) }}" class="inline">
                        @csrf
                        <input type="hidden" name="statut_validation" value="{{ $statutValidation ?? 'all' }}">
                        <button type="submit" style="color:#fcd34d;">Remettre en attente</button>
                    </form>
                @endif
            @endif
            @if($canWriteTransactions ?? true)
                <form method="POST" action="{{ route('transactions.destroy', $t->id) }}" class="inline" onsubmit="return confirm('Supprimer cette transaction ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit">Supprimer</button>
                </form>
            @endif
        </div>
    </div>
@empty
    <div class="txli-empty">
        <p>Aucune transaction enregistrée.</p>
        <a href="{{ route('transactions.create') }}">Enregistrer une transaction</a>
    </div>
@endforelse

@if($transactions->isNotEmpty())
    <div class="txli-pager">{{ $transactions->links() }}</div>
@endif

@else

{{-- Desktop --}}
    <style>
        /* Table desktop lisible et cohérente avec le thème dark glass. */
        .txd-table-solid,
        .txd-table-solid th,
        .txd-table-solid td {
            color: rgba(236, 253, 245, 0.92) !important;
        }
        .txd-table-solid thead th {
            color: rgba(167, 243, 208, 0.9) !important;
            background: rgba(16, 38, 20, 0.72);
        }
        .txd-table-solid tbody tr:hover {
            background: rgba(22, 101, 52, 0.22);
        }
        .txd-table-solid tbody td.txd-cell-main { color: rgba(236, 253, 245, 0.96) !important; }
        .txd-table-solid tbody td.txd-cell-muted { color: rgba(209, 250, 229, 0.84) !important; }
        .txd-table-solid tbody td.txd-cell-amount {
            color: rgba(236, 253, 245, 0.98) !important;
            font-weight: 600;
        }
    </style>
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-lg font-bold text-agro-vert">Transactions</h1>
        @if($canWriteTransactions ?? true)
            <a href="{{ route('transactions.create') }}" class="text-sm font-semibold text-white bg-agro-vert px-3 py-2 rounded-lg">+ Saisie</a>
        @endif
    </div>

    @if($isCooperative ?? false)
    <form method="get" action="{{ route('transactions.index') }}" class="mb-3 inline-flex items-center gap-2">
        <label for="tx-filter-desktop" class="text-xs text-gray-500">Filtre validation</label>
        <select id="tx-filter-desktop" name="statut_validation" onchange="this.form.submit()" class="input-field py-1.5 text-sm min-w-[200px]">
            <option value="all" @selected(($statutValidation ?? 'all') === 'all')>Toutes</option>
            <option value="en_attente" @selected(($statutValidation ?? 'all') === 'en_attente')>En attente</option>
            <option value="validee" @selected(($statutValidation ?? 'all') === 'validee')>Validées</option>
        </select>
    </form>
    @endif

    <div class="overflow-x-auto rounded-xl border border-white/15 bg-white/[0.06] backdrop-blur-xl shadow-sm">
        <table class="w-full text-sm txd-table-solid">
            <thead class="text-left text-xs uppercase border-b border-white/10">
                <tr>
                    <th class="px-3 py-2">Date</th>
                    <th class="px-3 py-2">Campagne</th>
                    <th class="px-3 py-2">Type</th>
                    @if($isCooperative ?? false)
                    <th class="px-3 py-2">Validation</th>
                    @endif
                    <th class="px-3 py-2">Catégorie</th>
                    <th class="px-3 py-2 text-right">Montant</th>
                    <th class="px-3 py-2"></th>
                </tr>
            </thead>
            <tbody class="text-gray-800">
                @forelse ($transactions as $t)
                    <tr class="border-t border-white/10">
                        <td class="px-3 py-2 whitespace-nowrap txd-cell-muted">{{ $t->date_transaction->format('d/m/Y') }}</td>
                        <td class="px-3 py-2 txd-cell-main">{{ $t->activite->nom ?? '—' }}</td>
                        <td class="px-3 py-2 txd-cell-muted">{{ $t->type }}</td>
                        @if($isCooperative ?? false)
                        <td class="px-3 py-2">
                            @if(($t->statut_validation ?? 'validee') === 'validee')
                                <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-xs text-emerald-700">Validée</span>
                            @else
                                <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-2 py-0.5 text-xs text-amber-700">
                                    {{ ((int) ($t->validation_niveau ?? 0) >= 1) ? 'En attente N2' : 'En attente' }}
                                </span>
                            @endif
                        </td>
                        @endif
                        <td class="px-3 py-2 txd-cell-main">{{ str_replace('_', ' ', $t->categorie) }}</td>
                        <td class="px-3 py-2 text-right txd-cell-amount">{{ number_format($t->montant, 0, ',', ' ') }}</td>
                        <td class="px-3 py-2 text-right whitespace-nowrap">
                            @if($canWriteTransactions ?? true)
                                <a href="{{ route('transactions.edit', $t->id) }}" class="text-agro-vert inline-flex mr-2" title="Modifier"><x-icon name="pencil-square" class="w-4 h-4" /></a>
                            @endif
                            @if(($isCooperative ?? false) && ($canValidateTransactions ?? false))
                                @if(($t->statut_validation ?? 'validee') === 'en_attente')
                                    <form method="POST" action="{{ route('transactions.valider', $t->id) }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="statut_validation" value="{{ $statutValidation ?? 'all' }}">
                                        <button type="submit" class="text-emerald-600 inline-flex p-0.5 mr-2" title="{{ ((int) ($t->validation_niveau ?? 0) >= 1) ? 'Valider niveau 2' : 'Valider niveau 1' }}"><x-icon name="check-circle" class="w-4 h-4" /></button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('transactions.remettre-en-attente', $t->id) }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="statut_validation" value="{{ $statutValidation ?? 'all' }}">
                                        <button type="submit" class="text-amber-600 inline-flex p-0.5 mr-2" title="Remettre en attente"><x-icon name="arrow-path" class="w-4 h-4" /></button>
                                    </form>
                                @endif
                            @endif
                            @if($canWriteTransactions ?? true)
                                <form method="POST" action="{{ route('transactions.destroy', $t->id) }}" class="inline" onsubmit="return confirm('Supprimer ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 inline-flex p-0.5" title="Supprimer"><x-icon name="trash" class="w-4 h-4" /></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ ($isCooperative ?? false) ? 7 : 6 }}" class="px-4 py-8 text-center text-gray-500">Aucune transaction.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 px-1">
        {{ $transactions->links() }}
    </div>

@endif

@endsection
