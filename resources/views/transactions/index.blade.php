@extends('layouts.app-desktop')
@section('title', 'Transactions — AgroFinance+')

@section('content')
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
@endsection
