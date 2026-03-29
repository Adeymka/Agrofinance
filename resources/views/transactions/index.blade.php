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
    <a href="{{ route('transactions.create') }}" class="txli-btn-saisie">+ Saisie</a>
</div>

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
            · {{ $t->date_transaction->format('d/m/Y') }}
            · {{ $t->activite->nom ?? '—' }}
        </div>
        <div class="txli-actions">
            <a href="{{ route('transactions.edit', $t->id) }}">Modifier</a>
            <form method="POST" action="{{ route('transactions.destroy', $t->id) }}" class="inline" onsubmit="return confirm('Supprimer cette transaction ?');">
                @csrf
                @method('DELETE')
                <button type="submit">Supprimer</button>
            </form>
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
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-lg font-bold text-agro-vert">Transactions</h1>
        <a href="{{ route('transactions.create') }}" class="text-sm font-semibold text-white bg-agro-vert px-3 py-2 rounded-lg">+ Saisie</a>
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-100 bg-white shadow-sm">
        <table class="w-full text-sm">
            <thead class="text-left text-gray-500 text-xs uppercase border-b border-gray-100">
                <tr>
                    <th class="px-3 py-2">Date</th>
                    <th class="px-3 py-2">Campagne</th>
                    <th class="px-3 py-2">Type</th>
                    <th class="px-3 py-2">Catégorie</th>
                    <th class="px-3 py-2 text-right">Montant</th>
                    <th class="px-3 py-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transactions as $t)
                    <tr class="border-t border-gray-100">
                        <td class="px-3 py-2 whitespace-nowrap">{{ $t->date_transaction->format('d/m/Y') }}</td>
                        <td class="px-3 py-2">{{ $t->activite->nom ?? '—' }}</td>
                        <td class="px-3 py-2">{{ $t->type }}</td>
                        <td class="px-3 py-2">{{ str_replace('_', ' ', $t->categorie) }}</td>
                        <td class="px-3 py-2 text-right font-medium">{{ number_format($t->montant, 0, ',', ' ') }}</td>
                        <td class="px-3 py-2 text-right whitespace-nowrap">
                            <a href="{{ route('transactions.edit', $t->id) }}" class="text-agro-vert inline-flex mr-2" title="Modifier"><x-icon name="pencil-square" class="w-4 h-4" /></a>
                            <form method="POST" action="{{ route('transactions.destroy', $t->id) }}" class="inline" onsubmit="return confirm('Supprimer ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 inline-flex p-0.5" title="Supprimer"><x-icon name="trash" class="w-4 h-4" /></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">Aucune transaction.</td>
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
