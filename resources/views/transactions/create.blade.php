@extends('layouts.app-desktop')
@section('title', 'Nouvelle transaction — AgroFinance+')
@section('page-title', 'Nouvelle transaction')

@section('content')
    @if ($activites->isEmpty())
        <p class="text-sm text-gray-600">Aucune campagne en cours. <a href="{{ route('activites.create') }}" class="text-agro-vert font-medium underline">Créer une campagne</a>.</p>
    @else
        @php
            $labelsType = [
                'cultures_vivrieres' => 'Cultures vivrières',
                'elevage' => 'Élevage',
                'maraichage' => 'Maraîchage',
                'transformation' => 'Transformation',
                'mixte' => 'Mixte',
            ];
            $categorieModeInitial = old('categorie_mode', 'liste');
        @endphp

        <form id="formTransaction" method="POST" action="{{ route('transactions.store') }}" class="max-w-2xl mx-auto space-y-6">
            @csrf
            <input type="hidden" name="type" id="inputType" value="depense">
            <input type="hidden" name="categorie_mode" id="inputCategorieMode" value="{{ $categorieModeInitial }}">

            <div class="card space-y-5">
                <h2 class="text-sm font-semibold text-gray-800 font-display">Informations générales</h2>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Campagne concernée</label>
                    <select name="activite_id" id="selectActivite" required class="input-field">
                        @foreach ($activites as $a)
                            <option value="{{ $a->id }}" data-exploitation-id="{{ $a->exploitation_id }}"
                                    @selected((string) $activiteSelectionnee === (string) $a->id)>
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
            </div>

            {{-- Catégorie : Liste FSA / Saisie libre + fusion suggestions --}}
            <div class="card space-y-4">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <h2 class="text-sm font-semibold text-gray-800 font-display">Catégorie</h2>
                    <p class="text-xs text-gray-500 max-w-sm">
                        FSA — {{ $labelsType[$typeExploitation] ?? $typeExploitation }}
                    </p>
                </div>

                <div class="flex rounded-xl border border-white/15 p-1 gap-1 bg-white/[0.03]">
                    <button type="button" id="btnModeListe" class="txn-cat-mode-btn txn-cat-mode-btn--active flex-1 py-2.5 text-sm font-semibold rounded-lg transition-colors">
                        Liste (FSA + mes libellés)
                    </button>
                    <button type="button" id="btnModeLibre" class="txn-cat-mode-btn flex-1 py-2.5 text-sm font-semibold rounded-lg transition-colors text-white/60">
                        Saisie libre
                    </button>
                </div>

                <div id="zoneListe" class="space-y-4">
                    <div id="wrapMesCategories" class="hidden rounded-xl border border-emerald-500/25 bg-emerald-500/[0.06] p-3 space-y-2">
                        <p class="text-xs font-semibold text-emerald-300/90 uppercase tracking-wide">Mes catégories (cette exploitation)</p>
                        <p class="text-[11px] text-white/45">Libellés déjà utilisés — clic pour remplir la saisie libre.</p>
                        <div id="mesCategoriesPills" class="flex flex-wrap gap-2"></div>
                    </div>

                    <div id="catDepenses" class="space-y-4 max-h-[55vh] overflow-y-auto pr-1">
                        @foreach ($categories['depenses'] as $groupe => $cats)
                            <details class="group border border-white/10 rounded-xl bg-white/[0.03] open:bg-white/[0.06]" open>
                                <summary class="cursor-pointer px-3 py-2 text-xs font-bold text-gray-500 uppercase tracking-wide list-none flex justify-between items-center">
                                    {{ $groupe }}
                                    <span class="text-gray-400 group-open:rotate-180 transition">▼</span>
                                </summary>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-2 pt-0">
                                    @foreach ($cats as $val => $label)
                                        <label class="block">
                                            <input type="radio" name="categorie" value="{{ $val }}" class="peer sr-only cat-radio-fsa">
                                            <div class="txn-cat-pill p-2.5 text-sm">{{ $label }}</div>
                                        </label>
                                    @endforeach
                                </div>
                            </details>
                        @endforeach
                    </div>

                    <div id="catRecettes" class="hidden space-y-4 max-h-[55vh] overflow-y-auto pr-1">
                        @foreach ($categories['recettes'] as $groupe => $cats)
                            <details class="group border border-white/10 rounded-xl bg-white/[0.03] open:bg-white/[0.06]" open>
                                <summary class="cursor-pointer px-3 py-2 text-xs font-bold text-gray-500 uppercase tracking-wide list-none flex justify-between items-center">
                                    {{ $groupe }}
                                    <span class="text-gray-400">▼</span>
                                </summary>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-2 pt-0">
                                    @foreach ($cats as $val => $label)
                                        <label class="block">
                                            <input type="radio" name="categorie_recette" value="{{ $val }}" class="peer sr-only cat-radio-fsa">
                                            <div class="txn-cat-pill p-2.5 text-sm">{{ $label }}</div>
                                        </label>
                                    @endforeach
                                </div>
                            </details>
                        @endforeach
                    </div>

                    <p class="text-[11px] text-white/40">En « Liste », choisissez un libellé FSA ci-dessus ou un de vos libellés en haut. Passez en « Saisie libre » pour tout taper.</p>
                </div>

                <div id="zoneLibre" class="hidden space-y-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Libellé de catégorie</label>
                    <input type="text"
                           name="categorie_libre"
                           id="inputCategorieLibre"
                           value="{{ old('categorie_libre') }}"
                           placeholder="Ex : Location tracteur, Prime récolte, Tontine…"
                           class="input-field"
                           maxlength="100"
                           autocomplete="off"
                           @if($categorieModeInitial === 'liste') disabled @endif>
                </div>

                @error('categorie')
                    <p class="text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="card space-y-5">
                <h2 class="text-sm font-semibold text-gray-800 font-display sr-only">Montant et détails</h2>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1 text-center">Montant (FCFA)</label>
                    <input type="number" name="montant" min="1" step="1" required inputmode="numeric"
                           value="{{ old('montant') }}"
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
                    <input type="checkbox" name="est_imprevue" value="1" class="rounded border-white/30 bg-white/[0.06] text-emerald-500 focus:ring-emerald-500/40" @checked(old('est_imprevue'))>
                    Dépense imprévue
                </label>
            </div>

            <div class="flex justify-end">
                <button type="submit" id="btnValider" class="btn-primary px-8 py-3 w-full sm:w-auto">Enregistrer la transaction</button>
            </div>
        </form>

        <script>
            (function () {
                var suggestionsPayload = @json($suggestionsByExploitation);
                var activiteVersExploitation = @json($activiteVersExploitation);
                var inputType = document.getElementById('inputType');
                var catD = document.getElementById('catDepenses');
                var catR = document.getElementById('catRecettes');
                var blocNat = document.getElementById('blocNature');
                var btnD = document.getElementById('btnDepense');
                var btnR = document.getElementById('btnRecette');
                var btnValider = document.getElementById('btnValider');
                var btnModeListe = document.getElementById('btnModeListe');
                var btnModeLibre = document.getElementById('btnModeLibre');
                var zoneListe = document.getElementById('zoneListe');
                var zoneLibre = document.getElementById('zoneLibre');
                var inputCategorieMode = document.getElementById('inputCategorieMode');
                var inputCategorieLibre = document.getElementById('inputCategorieLibre');
                var selectActivite = document.getElementById('selectActivite');
                var wrapMes = document.getElementById('wrapMesCategories');
                var mesPills = document.getElementById('mesCategoriesPills');

                function currentExploitationId() {
                    var opt = selectActivite.options[selectActivite.selectedIndex];
                    return opt ? String(opt.getAttribute('data-exploitation-id')) : null;
                }

                function renderMesCategories() {
                    var eid = currentExploitationId();
                    var t = inputType.value === 'depense' ? 'depense' : 'recette';
                    mesPills.innerHTML = '';
                    if (!eid || !suggestionsPayload[eid] || !suggestionsPayload[eid][t]) {
                        wrapMes.classList.add('hidden');
                        return;
                    }
                    var list = suggestionsPayload[eid][t];
                    if (!list.length) {
                        wrapMes.classList.add('hidden');
                        return;
                    }
                    wrapMes.classList.remove('hidden');
                    list.forEach(function (item) {
                        var b = document.createElement('button');
                        b.type = 'button';
                        b.className = 'rounded-lg border border-emerald-500/35 bg-emerald-500/10 px-3 py-1.5 text-sm text-emerald-200 hover:bg-emerald-500/20 transition-colors';
                        b.textContent = item.label;
                        b.setAttribute('data-label', item.label);
                        b.addEventListener('click', function () {
                            setModeLibre();
                            inputCategorieLibre.value = item.label;
                            inputCategorieLibre.focus();
                            clearFsaRadios();
                        });
                        mesPills.appendChild(b);
                    });
                }

                function clearFsaRadios() {
                    document.querySelectorAll('.cat-radio-fsa').forEach(function (i) { i.checked = false; });
                }

                function clearLibre() {
                    inputCategorieLibre.value = '';
                }

                function setModeListe() {
                    inputCategorieMode.value = 'liste';
                    zoneListe.classList.remove('hidden');
                    zoneLibre.classList.add('hidden');
                    inputCategorieLibre.disabled = true;
                    clearLibre();
                    btnModeListe.classList.add('txn-cat-mode-btn--active');
                    btnModeListe.classList.remove('text-white/60');
                    btnModeLibre.classList.remove('txn-cat-mode-btn--active');
                    btnModeLibre.classList.add('text-white/60');
                }

                function setModeLibre() {
                    inputCategorieMode.value = 'libre';
                    zoneListe.classList.add('hidden');
                    zoneLibre.classList.remove('hidden');
                    inputCategorieLibre.disabled = false;
                    clearFsaRadios();
                    btnModeLibre.classList.add('txn-cat-mode-btn--active');
                    btnModeLibre.classList.remove('text-white/60');
                    btnModeListe.classList.remove('txn-cat-mode-btn--active');
                    btnModeListe.classList.add('text-white/60');
                    catD.querySelectorAll('input[type="radio"]').forEach(function (i) { i.removeAttribute('name'); i.disabled = true; });
                    catR.querySelectorAll('input[type="radio"]').forEach(function (i) { i.removeAttribute('name'); i.disabled = true; });
                }

                function setType(type) {
                    inputType.value = type;
                    var dep = type === 'depense';
                    catD.classList.toggle('hidden', !dep);
                    catR.classList.toggle('hidden', dep);
                    blocNat.classList.toggle('hidden', !dep);
                    blocNat.querySelectorAll('input[name="nature"]').forEach(function (i) { i.disabled = !dep; });

                    if (inputCategorieMode.value === 'libre') {
                        if (dep) {
                            catD.querySelectorAll('input[type="radio"]').forEach(function (i) { i.removeAttribute('name'); i.disabled = true; });
                            catR.querySelectorAll('input[type="radio"]').forEach(function (i) { i.removeAttribute('name'); i.disabled = true; });
                        } else {
                            catD.querySelectorAll('input[type="radio"]').forEach(function (i) { i.removeAttribute('name'); i.disabled = true; });
                            catR.querySelectorAll('input[type="radio"]').forEach(function (i) { i.removeAttribute('name'); i.disabled = true; });
                        }
                        btnValider.className = 'btn-primary px-8 py-3 w-full sm:w-auto ' + (dep ? 'bg-red-600 hover:bg-red-700' : 'bg-green-700 hover:bg-green-800');
                        renderMesCategories();
                        return;
                    }

                    if (dep) {
                        catD.querySelectorAll('input[type="radio"]').forEach(function (i) { i.setAttribute('name', 'categorie'); i.disabled = false; });
                        catR.querySelectorAll('input').forEach(function (i) { i.removeAttribute('name'); i.disabled = true; });
                        btnD.className = 'txn-type-btn txn-type-btn--depense-on';
                        btnR.className = 'txn-type-btn txn-type-btn--inactive';
                        btnValider.className = 'btn-primary px-8 py-3 w-full sm:w-auto bg-red-600 hover:bg-red-700';
                    } else {
                        catD.querySelectorAll('input[type="radio"]').forEach(function (i) { i.removeAttribute('name'); i.disabled = true; });
                        catR.querySelectorAll('input[type="radio"]').forEach(function (i) { i.setAttribute('name', 'categorie'); i.disabled = false; });
                        btnR.className = 'txn-type-btn txn-type-btn--recette-on';
                        btnD.className = 'txn-type-btn txn-type-btn--inactive';
                        btnValider.className = 'btn-primary px-8 py-3 w-full sm:w-auto bg-green-700 hover:bg-green-800';
                    }
                    renderMesCategories();
                }

                zoneListe.addEventListener('change', function (e) {
                    if (e.target && e.target.classList.contains('cat-radio-fsa') && e.target.checked) {
                        clearLibre();
                    }
                });

                document.getElementById('btnDepense').addEventListener('click', function () { setType('depense'); });
                document.getElementById('btnRecette').addEventListener('click', function () { setType('recette'); });

                btnModeListe.addEventListener('click', function () { setModeListe(); setType(inputType.value); });
                btnModeLibre.addEventListener('click', function () { setModeLibre(); setType(inputType.value); });
                selectActivite.addEventListener('change', function () {
                    var url = new URL(window.location.href);
                    url.searchParams.set('activite_id', selectActivite.value);
                    window.location.href = url.toString();
                });

                if (inputCategorieMode.value === 'libre') {
                    setModeLibre();
                } else {
                    setModeListe();
                }
                setType(inputType.value);
            })();
        </script>
    @endif
@endsection
