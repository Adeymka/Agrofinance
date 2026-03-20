@extends('layouts.app-desktop')
@section('title', 'Campagnes — AgroFinance+')
@section('page-title', 'Mes campagnes agricoles')

@section('topbar-actions')
    <a href="{{ route('activites.create') }}" class="btn-primary inline-flex items-center gap-2">
        <x-icon name="plus" class="w-4 h-4" /> Nouvelle campagne
    </a>
@endsection

@section('content')

    <div class="mb-6 flex gap-4 border-b border-gray-200">
        <button type="button" id="tab-en-cours" class="tab-btn border-b-2 border-agro-vert text-agro-vert font-semibold pb-2 px-2">
            En cours ({{ $actives->count() }})
        </button>
        <button type="button" id="tab-terminees" class="tab-btn text-gray-500 pb-2 px-2 border-b-2 border-transparent">
            Terminées ({{ $terminees->count() }})
        </button>
    </div>

    <div id="panel-en-cours" class="tab-panel">
        <div class="card overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="py-2 pr-4">Campagne</th>
                        <th class="py-2 pr-4">Type</th>
                        <th class="py-2 pr-4">Début</th>
                        <th class="py-2 pr-4 text-right">Recettes</th>
                        <th class="py-2 pr-4 text-right">Dépenses</th>
                        <th class="py-2 pr-4 text-right">Marge</th>
                        <th class="py-2 pr-4">Statut</th>
                        <th class="py-2">Actions</th>
                    </tr>
                </thead>
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
                                <form method="POST" action="{{ route('activites.cloturer', $a->id) }}" class="inline" onsubmit="return confirm('Clôturer ?');">
                                    @csrf
                                    <button type="submit" class="text-amber-700 text-xs">Clôturer</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-8 text-center text-gray-500">Aucune campagne en cours.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div id="panel-terminees" class="tab-panel hidden">
        <div class="card overflow-x-auto bg-gray-50/50">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="py-2 pr-4">Campagne</th>
                        <th class="py-2 pr-4">Type</th>
                        <th class="py-2 pr-4">Date fin</th>
                        <th class="py-2 pr-4 text-right">Marge</th>
                        <th class="py-2 pr-4 text-right">RNE</th>
                        <th class="py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($terminees as $a)
                        @php $ind = $indicateursTerminees[$a->id] ?? []; @endphp
                        <tr class="border-b border-gray-100">
                            <td class="py-3 pr-4 font-medium text-gray-700">{{ $a->nom }}</td>
                            <td class="py-3 pr-4">{{ $a->type }}</td>
                            <td class="py-3 pr-4">{{ $a->date_fin?->format('d/m/Y') ?? '—' }}</td>
                            <td class="py-3 pr-4 text-right">{{ number_format($ind['MB'] ?? 0, 0, ',', ' ') }}</td>
                            <td class="py-3 pr-4 text-right">{{ number_format($ind['RNE'] ?? 0, 0, ',', ' ') }}</td>
                            <td class="py-3"><a href="{{ route('activites.show', $a->id) }}" class="text-agro-vert font-medium">Voir</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-gray-500">Aucune campagne terminée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        (function () {
            var b1 = document.getElementById('tab-en-cours');
            var b2 = document.getElementById('tab-terminees');
            var p1 = document.getElementById('panel-en-cours');
            var p2 = document.getElementById('panel-terminees');
            function show(which) {
                if (which === 1) {
                    p1.classList.remove('hidden'); p2.classList.add('hidden');
                    b1.classList.add('border-agro-vert', 'text-agro-vert', 'font-semibold');
                    b1.classList.remove('text-gray-500', 'border-transparent');
                    b2.classList.remove('border-agro-vert', 'text-agro-vert', 'font-semibold');
                    b2.classList.add('text-gray-500', 'border-transparent');
                } else {
                    p2.classList.remove('hidden'); p1.classList.add('hidden');
                    b2.classList.add('border-agro-vert', 'text-agro-vert', 'font-semibold');
                    b2.classList.remove('text-gray-500', 'border-transparent');
                    b1.classList.remove('border-agro-vert', 'text-agro-vert', 'font-semibold');
                    b1.classList.add('text-gray-500', 'border-transparent');
                }
            }
            b1.addEventListener('click', function () { show(1); });
            b2.addEventListener('click', function () { show(2); });
        })();
    </script>
@endsection
