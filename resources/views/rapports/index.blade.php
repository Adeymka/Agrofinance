@extends('layouts.app-desktop')
@section('title', 'Rapports PDF — AgroFinance+')
@section('page-title', 'Rapports PDF')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1">
            <div class="card">
                <h2 class="text-sm font-semibold text-gray-800 mb-4">Générer un rapport</h2>

                @if ($activites->isEmpty())
                    <p class="text-sm text-gray-600">Créez une campagne en cours pour générer un PDF.</p>
                @else
                    <form method="POST" action="{{ route('rapports.generer') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Campagne</label>
                            <select name="activite_id" required class="input-field">
                                @foreach ($activites as $a)
                                    <option value="{{ $a->id }}">{{ $a->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                            <select name="type" class="input-field">
                                <option value="campagne">Campagne</option>
                                <option value="dossier_credit">Dossier crédit</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Début</label>
                                <input type="date" name="periode_debut" value="{{ old('periode_debut', now()->startOfMonth()->toDateString()) }}" class="input-field">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Fin</label>
                                <input type="date" name="periode_fin" value="{{ old('periode_fin', now()->toDateString()) }}" class="input-field">
                            </div>
                        </div>
                        <button type="submit" class="btn-primary w-full py-3">Générer le PDF</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="card overflow-x-auto">
                <h2 class="text-sm font-semibold text-gray-800 mb-4">Rapports générés</h2>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="py-2 pr-3">Date</th>
                            <th class="py-2 pr-3">Type</th>
                            <th class="py-2 pr-3">Exploitation</th>
                            <th class="py-2 pr-3">Partage</th>
                            <th class="py-2 pr-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rapports as $r)
                            @php
                                $expire = $r->lien_expire_le;
                                $valide = $expire && $expire->isFuture();
                                $heures = $valide ? max(1, (int) ceil(now()->diffInMinutes($expire) / 60)) : 0;
                            @endphp
                            <tr class="border-b border-gray-50">
                                <td class="py-3 pr-3 whitespace-nowrap">{{ $r->created_at->format('d/m/Y H:i') }}</td>
                                <td class="py-3 pr-3">{{ $r->type === 'dossier_credit' ? 'Dossier crédit' : 'Campagne' }}</td>
                                <td class="py-3 pr-3">{{ $r->exploitation->nom ?? '—' }}</td>
                                <td class="py-3 pr-3">
                                    @if ($valide && $r->lien_token)
                                        <span class="badge-vert">Valide ~{{ max(1, $heures) }} h</span>
                                        <button type="button" class="text-xs text-agro-vert underline ml-2 js-copy" data-url="{{ route('rapports.partager', $r->lien_token) }}">Copier le lien</button>
                                    @elseif($expire && ! $valide)
                                        <span class="badge-rouge">Expiré</span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="py-3 pr-3 text-right whitespace-nowrap">
                                    <a href="{{ route('rapports.telecharger', $r->id) }}" class="text-agro-vert font-medium">Télécharger</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-8 text-center text-gray-500">Aucun rapport pour le moment.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.js-copy').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var u = btn.getAttribute('data-url');
                if (navigator.clipboard && u) {
                    navigator.clipboard.writeText(u).then(function () {
                        btn.textContent = 'Copié !';
                        setTimeout(function () { btn.textContent = 'Copier le lien'; }, 2000);
                    });
                }
            });
        });
    </script>
@endsection
