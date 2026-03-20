@extends('layouts.app-desktop')
@section('title', 'Nouvelle transaction — AgroFinance+')
@section('page-title', 'Nouvelle transaction')

@section('content')
    @if ($activites->isEmpty())
        <p class="text-sm text-gray-600">Aucune campagne en cours. <a href="{{ route('activites.create') }}" class="text-agro-vert font-medium underline">Créer une campagne</a>.</p>
    @else
        <form id="formTransaction" method="POST" action="{{ route('transactions.store') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="type" id="inputType" value="depense">

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                {{-- Colonne gauche — Informations générales --}}
                <div class="card space-y-5">
                    <h2 class="text-sm font-semibold text-gray-800">Informations générales</h2>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Campagne concernée</label>
                        <select name="activite_id" required class="input-field">
                            @foreach ($activites as $a)
                                <option value="{{ $a->id }}" @selected((string) $activiteSelectionnee === (string) $a->id)>
                                    {{ $a->exploitation->nom ?? '' }} — {{ $a->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <p class="text-xs font-medium text-gray-600 mb-2">Type</p>
                        <div class="grid grid-cols-2 gap-3">
                            <button type="button" id="btnDepense" class="txn-type-btn txn-type-btn--depense-on">
                                Dépense
                            </button>
                            <button type="button" id="btnRecette" class="txn-type-btn txn-type-btn--inactive">
                                Recette
                            </button>
                        </div>
                    </div>

                    <div id="blocNature" class="space-y-2">
                        <p class="text-xs font-medium text-gray-600">Nature (dépense)</p>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="cursor-pointer">
                                <input type="radio" name="nature" value="variable" class="peer sr-only" checked>
                                <div class="txn-nature-pill p-3 text-center text-sm">Variable</div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="nature" value="fixe" class="peer sr-only">
                                <div class="txn-nature-pill p-3 text-center text-sm">Fixe</div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1 text-center">Montant (FCFA)</label>
                        <input type="number" name="montant" min="1" step="1" required inputmode="numeric"
                               class="w-full text-center text-3xl font-bold rounded-xl border-2 border-white/15 bg-white/[0.05] py-4 text-emerald-300 placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500/40">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Date</label>
                        <input type="date" name="date_transaction" value="{{ old('date_transaction', now()->toDateString()) }}" required class="input-field">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Note (optionnel)</label>
                        <textarea name="note" rows="2" maxlength="500" class="input-field" placeholder="Commentaire…">{{ old('note') }}</textarea>
                    </div>

                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="est_imprevue" value="1" class="rounded border-white/30 bg-white/[0.06] text-emerald-500 focus:ring-emerald-500/40">
                        Dépense imprévue
                    </label>
                </div>

                {{-- Colonne droite — Catégories --}}
                <div class="card space-y-4">
                    <h2 class="text-sm font-semibold text-gray-800">Choisissez une catégorie</h2>

                    <div id="catDepenses" class="space-y-4 max-h-[70vh] overflow-y-auto pr-1">
                        @foreach ($categories['depenses'] as $groupe => $cats)
                            <details class="group border border-white/10 rounded-xl bg-white/[0.03] open:bg-white/[0.06]" open>
                                <summary class="cursor-pointer px-3 py-2 text-xs font-bold text-gray-500 uppercase tracking-wide list-none flex justify-between items-center">
                                    {{ $groupe }}
                                    <span class="text-gray-400 group-open:rotate-180 transition">▼</span>
                                </summary>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-2 pt-0">
                                    @foreach ($cats as $val => $label)
                                        <label class="block">
                                            <input type="radio" name="categorie" value="{{ $val }}" class="peer sr-only">
                                            <div class="txn-cat-pill p-2.5 text-sm">{{ $label }}</div>
                                        </label>
                                    @endforeach
                                </div>
                            </details>
                        @endforeach
                    </div>

                    <div id="catRecettes" class="hidden space-y-4 max-h-[70vh] overflow-y-auto pr-1">
                        @foreach ($categories['recettes'] as $groupe => $cats)
                            <details class="group border border-white/10 rounded-xl bg-white/[0.03] open:bg-white/[0.06]" open>
                                <summary class="cursor-pointer px-3 py-2 text-xs font-bold text-gray-500 uppercase tracking-wide list-none flex justify-between items-center">
                                    {{ $groupe }}
                                    <span class="text-gray-400">▼</span>
                                </summary>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-2 pt-0">
                                    @foreach ($cats as $val => $label)
                                        <label class="block">
                                            <input type="radio" name="categorie_recette" value="{{ $val }}" class="peer sr-only">
                                            <div class="txn-cat-pill p-2.5 text-sm">{{ $label }}</div>
                                        </label>
                                    @endforeach
                                </div>
                            </details>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" id="btnValider" class="btn-primary px-8 py-3 w-full lg:w-auto">Enregistrer la transaction</button>
            </div>
        </form>

        <script>
            (function () {
                var inputType = document.getElementById('inputType');
                var catD = document.getElementById('catDepenses');
                var catR = document.getElementById('catRecettes');
                var blocNat = document.getElementById('blocNature');
                var btnD = document.getElementById('btnDepense');
                var btnR = document.getElementById('btnRecette');
                var btnValider = document.getElementById('btnValider');

                function setType(type) {
                    inputType.value = type;
                    var dep = type === 'depense';
                    catD.classList.toggle('hidden', !dep);
                    catR.classList.toggle('hidden', dep);
                    blocNat.classList.toggle('hidden', !dep);
                    blocNat.querySelectorAll('input[name="nature"]').forEach(function (i) { i.disabled = !dep; });

                    if (dep) {
                        catD.querySelectorAll('input[type="radio"]').forEach(function (i) { i.setAttribute('name', 'categorie'); i.disabled = false; });
                        catR.querySelectorAll('input').forEach(function (i) { i.removeAttribute('name'); i.disabled = true; });
                        btnD.className = 'txn-type-btn txn-type-btn--depense-on';
                        btnR.className = 'txn-type-btn txn-type-btn--inactive';
                        btnValider.className = 'btn-primary px-8 py-3 w-full lg:w-auto bg-red-600 hover:bg-red-700';
                    } else {
                        catD.querySelectorAll('input[type="radio"]').forEach(function (i) { i.removeAttribute('name'); i.disabled = true; });
                        catR.querySelectorAll('input[type="radio"]').forEach(function (i) { i.setAttribute('name', 'categorie'); i.disabled = false; });
                        btnR.className = 'txn-type-btn txn-type-btn--recette-on';
                        btnD.className = 'txn-type-btn txn-type-btn--inactive';
                        btnValider.className = 'btn-primary px-8 py-3 w-full lg:w-auto bg-green-700 hover:bg-green-800';
                    }
                }

                document.getElementById('btnDepense').addEventListener('click', function () { setType('depense'); });
                document.getElementById('btnRecette').addEventListener('click', function () { setType('recette'); });
                setType('depense');
            })();
        </script>
    @endif
@endsection
