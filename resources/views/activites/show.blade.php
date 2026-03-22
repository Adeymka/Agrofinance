@extends('layouts.app-desktop')
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
            \App\Models\Activite::STATUT_TERMINE => 'Terminée',
            \App\Models\Activite::STATUT_ABANDONNE => 'Abandonnée',
        ];
    @endphp

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
                    <th class="py-2 pr-3">Date</th>
                    <th class="py-2 pr-3">Type</th>
                    <th class="py-2 pr-3">Catégorie</th>
                    <th class="py-2 pr-3">Nature</th>
                    <th class="py-2 pr-3 text-right">Montant</th>
                    <th class="py-2 pr-3">Note</th>
                    <th class="py-2 pr-3">Actions</th>
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
                                    @csrf
                                    @method('DELETE')
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
            <button
                type="button"
                class="btn-outline text-gray-600 border-gray-200"
                data-open-abandon-modal
                data-abandon-url="{{ route('activites.abandonner', $activite->id) }}"
            >
                Marquer comme abandonnée
            </button>
        </div>
    @endif

    <x-confirm-abandon-modal />
@endsection
