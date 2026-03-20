@extends('layouts.app-desktop')
@section('title', 'Modifier transaction — AgroFinance+')
@section('page-title', 'Modifier la transaction')

@section('content')
    <form id="formTransaction" method="POST" action="{{ route('transactions.update', $transaction->id) }}" class="space-y-6">
        @csrf
        @method('PUT')
        <input type="hidden" name="type" id="inputType" value="{{ old('type', $transaction->type) }}">

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
            <div class="card space-y-5">
                <h2 class="text-sm font-semibold text-gray-800">Informations générales</h2>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Campagne</label>
                    <select name="activite_id" required class="input-field">
                        @foreach ($activites as $a)
                            <option value="{{ $a->id }}" @selected(old('activite_id', $transaction->activite_id) == $a->id)>
                                {{ $a->exploitation->nom ?? '' }} — {{ $a->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <p class="text-xs font-medium text-gray-600 mb-2">Type</p>
                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" id="btnDepense" class="type-btn rounded-xl border-2 py-3 text-sm font-semibold">Dépense</button>
                        <button type="button" id="btnRecette" class="type-btn rounded-xl border-2 py-3 text-sm font-semibold">Recette</button>
                    </div>
                </div>

                <div id="blocNature" class="space-y-2">
                    <p class="text-xs font-medium text-gray-600">Nature (dépense)</p>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="nature" value="variable" class="peer sr-only" @checked(old('nature', $transaction->nature) === 'variable' || ($transaction->type === 'depense' && $transaction->nature === 'variable'))>
                            <div class="rounded-lg border-2 border-gray-200 p-3 text-center text-sm peer-checked:border-agro-vert peer-checked:bg-green-50">Variable</div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="nature" value="fixe" class="peer sr-only" @checked(old('nature', $transaction->nature) === 'fixe')>
                            <div class="rounded-lg border-2 border-gray-200 p-3 text-center text-sm peer-checked:border-agro-vert peer-checked:bg-green-50">Fixe</div>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1 text-center">Montant (FCFA)</label>
                    <input type="number" name="montant" value="{{ old('montant', $transaction->montant) }}" min="1" step="0.01" required inputmode="numeric"
                           class="w-full text-center text-3xl font-bold rounded-xl border-2 border-gray-200 py-4 text-agro-vert focus:outline-none focus:ring-2 focus:ring-agro-vert">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Date</label>
                    <input type="date" name="date_transaction" value="{{ old('date_transaction', $transaction->date_transaction->format('Y-m-d')) }}" required class="input-field">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Note</label>
                    <textarea name="note" rows="2" maxlength="500" class="input-field">{{ old('note', $transaction->note) }}</textarea>
                </div>

                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="est_imprevue" value="1" class="rounded border-gray-300" @checked(old('est_imprevue', $transaction->est_imprevue))>
                    Dépense imprévue
                </label>
            </div>

            <div class="card space-y-4">
                <h2 class="text-sm font-semibold text-gray-800">Catégorie</h2>

                <div id="catDepenses" class="space-y-4 max-h-[70vh] overflow-y-auto pr-1">
                    @foreach ($categories['depenses'] as $groupe => $cats)
                        <details class="group border border-gray-100 rounded-xl open:bg-gray-50/50" open>
                            <summary class="cursor-pointer px-3 py-2 text-xs font-bold text-gray-500 uppercase tracking-wide list-none flex justify-between items-center">
                                {{ $groupe }} <span class="text-gray-400">▼</span>
                            </summary>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-2 pt-0">
                                @foreach ($cats as $val => $label)
                                    <label class="block">
                                        <input type="radio" name="categorie" value="{{ $val }}" class="peer sr-only tx-cat-dep"
                                            @checked(old('categorie', $transaction->categorie) === $val && old('type', $transaction->type) === 'depense')>
                                        <div class="border border-gray-200 rounded-lg p-2.5 text-sm peer-checked:border-agro-vert peer-checked:bg-agro-vert-clair">{{ $label }}</div>
                                    </label>
                                @endforeach
                            </div>
                        </details>
                    @endforeach
                </div>

                <div id="catRecettes" class="hidden space-y-4 max-h-[70vh] overflow-y-auto pr-1">
                    @foreach ($categories['recettes'] as $groupe => $cats)
                        <details class="group border border-gray-100 rounded-xl open:bg-gray-50/50" open>
                            <summary class="cursor-pointer px-3 py-2 text-xs font-bold text-gray-500 uppercase tracking-wide list-none flex justify-between items-center">
                                {{ $groupe }} <span class="text-gray-400">▼</span>
                            </summary>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-2 pt-0">
                                @foreach ($cats as $val => $label)
                                    <label class="block">
                                        <input type="radio" name="categorie_recette" value="{{ $val }}" class="peer sr-only tx-cat-rec"
                                            @checked(old('categorie', $transaction->categorie) === $val && old('type', $transaction->type) === 'recette')>
                                        <div class="border border-gray-200 rounded-lg p-2.5 text-sm peer-checked:border-agro-vert peer-checked:bg-agro-vert-clair">{{ $label }}</div>
                                    </label>
                                @endforeach
                            </div>
                        </details>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="flex flex-wrap gap-3 justify-end">
            <a href="{{ route('activites.show', $transaction->activite_id) }}" class="btn-outline">Annuler</a>
            <button type="submit" id="btnValider" class="btn-primary px-8">Enregistrer</button>
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
                    catD.querySelectorAll('input.tx-cat-dep').forEach(function (i) { i.setAttribute('name', 'categorie'); i.disabled = false; });
                    catR.querySelectorAll('input').forEach(function (i) { i.removeAttribute('name'); i.disabled = true; });
                    btnD.className = 'type-btn rounded-xl border-2 border-red-300 bg-red-50 text-red-800 py-3 text-sm font-semibold shadow-sm';
                    btnR.className = 'type-btn rounded-xl border-2 border-gray-200 text-gray-600 py-3 text-sm font-semibold';
                    btnValider.className = 'btn-primary px-8 bg-red-600 hover:bg-red-700';
                } else {
                    catD.querySelectorAll('input').forEach(function (i) { i.removeAttribute('name'); i.disabled = true; });
                    catR.querySelectorAll('input.tx-cat-rec').forEach(function (i) { i.setAttribute('name', 'categorie'); i.disabled = false; });
                    btnR.className = 'type-btn rounded-xl border-2 border-green-300 bg-green-50 text-green-800 py-3 text-sm font-semibold shadow-sm';
                    btnD.className = 'type-btn rounded-xl border-2 border-gray-200 text-gray-600 py-3 text-sm font-semibold';
                    btnValider.className = 'btn-primary px-8 bg-green-700 hover:bg-green-800';
                }
            }

            btnD.addEventListener('click', function () { setType('depense'); });
            btnR.addEventListener('click', function () { setType('recette'); });
            setType(inputType.value || 'depense');
        })();
    </script>
@endsection
