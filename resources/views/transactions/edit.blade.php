@extends($layout)
@section('title', 'Modifier transaction — AgroFinance+')
@section('page-title', 'Modifier la transaction')

@section('content')
    @php
        $labelsType = [
            'cultures_vivrieres' => 'Cultures vivrières',
            'elevage' => 'Élevage',
            'maraichage' => 'Maraîchage',
            'transformation' => 'Transformation',
            'mixte' => 'Mixte',
        ];
        $categorieModeInitial = old('categorie_mode', trim((string) $categorieLibre) !== '' ? 'libre' : 'liste');
    @endphp

    <form id="formTransaction" method="POST" action="{{ route('transactions.update', $transaction->id) }}" enctype="multipart/form-data" class="max-w-2xl mx-auto space-y-6">
        @csrf
        @method('PUT')
        <input type="hidden" name="type" id="inputType" value="{{ old('type', $transaction->type) }}">
        <input type="hidden" name="categorie_mode" id="inputCategorieMode" value="{{ $categorieModeInitial }}">

        <div class="card space-y-5">
            <h2 class="text-sm font-semibold text-gray-800 font-display">Informations générales</h2>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Campagne</label>
                <select name="activite_id" id="selectActivite" required class="input-field">
                    @foreach ($activites as $a)
                        <option value="{{ $a->id }}" data-exploitation-id="{{ $a->exploitation_id }}"
                                @selected(old('activite_id', $transaction->activite_id) == $a->id)>
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
        </div>

        <div class="card space-y-4">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <h2 class="text-sm font-semibold text-gray-800 font-display">Catégorie</h2>
                <p class="text-xs text-gray-500 max-w-sm">
                    Référentiel — {{ $labelsType[$typeExploitation] ?? $typeExploitation }}
                </p>
            </div>

            <div class="flex rounded-xl border border-gray-200 p-1 gap-1 bg-gray-50/80">
                <button type="button" id="btnModeListe" class="txn-cat-mode-btn txn-cat-mode-btn--active flex-1 py-2.5 text-sm font-semibold rounded-lg transition-colors">
                    Liste (standard + mes libellés)
                </button>
                <button type="button" id="btnModeLibre" class="txn-cat-mode-btn flex-1 py-2.5 text-sm font-semibold rounded-lg transition-colors text-gray-500">
                    Saisie libre
                </button>
            </div>

            <div id="zoneListe" class="space-y-4">
                <div id="wrapMesCategories" class="hidden rounded-xl border border-agro-vert/30 bg-agro-vert-clair/40 p-3 space-y-2">
                    <p class="text-xs font-semibold text-agro-vert uppercase tracking-wide">Mes catégories (cette exploitation)</p>
                    <p class="text-[11px] text-gray-500">Libellés déjà utilisés — clic pour remplir la saisie libre.</p>
                    <div id="mesCategoriesPills" class="flex flex-wrap gap-2"></div>
                </div>

                <div id="catDepenses" class="space-y-4 max-h-[55vh] overflow-y-auto pr-1">
                    @foreach ($categories['depenses'] as $groupe => $cats)
                        <details class="group border border-gray-100 rounded-xl open:bg-gray-50/50" open>
                            <summary class="cursor-pointer px-3 py-2 text-xs font-bold text-gray-500 uppercase tracking-wide list-none flex justify-between items-center">
                                {{ $groupe }} <span class="text-gray-400">▼</span>
                            </summary>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-2 pt-0">
                                @foreach ($cats as $val => $label)
                                    <label class="block">
                                        <input type="radio" name="categorie" value="{{ $val }}" class="peer sr-only tx-cat-dep cat-radio-std"
                                            @checked($categorieSelectionnee === $val && old('type', $transaction->type) === 'depense')>
                                        <div class="border border-gray-200 rounded-lg p-2.5 text-sm peer-checked:border-agro-vert peer-checked:bg-agro-vert-clair">{{ $label }}</div>
                                    </label>
                                @endforeach
                            </div>
                        </details>
                    @endforeach
                </div>

                <div id="catRecettes" class="hidden space-y-4 max-h-[55vh] overflow-y-auto pr-1">
                    @foreach ($categories['recettes'] as $groupe => $cats)
                        <details class="group border border-gray-100 rounded-xl open:bg-gray-50/50" open>
                            <summary class="cursor-pointer px-3 py-2 text-xs font-bold text-gray-500 uppercase tracking-wide list-none flex justify-between items-center">
                                {{ $groupe }} <span class="text-gray-400">▼</span>
                            </summary>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-2 pt-0">
                                @foreach ($cats as $val => $label)
                                    <label class="block">
                                        <input type="radio" name="categorie_recette" value="{{ $val }}" class="peer sr-only tx-cat-rec cat-radio-std"
                                            @checked($categorieSelectionnee === $val && old('type', $transaction->type) === 'recette')>
                                        <div class="border border-gray-200 rounded-lg p-2.5 text-sm peer-checked:border-agro-vert peer-checked:bg-agro-vert-clair">{{ $label }}</div>
                                    </label>
                                @endforeach
                            </div>
                        </details>
                    @endforeach
                </div>

                <p class="text-[11px] text-gray-400">En « Liste », choisissez un libellé du référentiel ci-dessus ou un de vos libellés en haut.</p>
            </div>

            <div id="zoneLibre" class="hidden space-y-2">
                <label class="block text-xs font-medium text-gray-600 mb-1">Libellé de catégorie</label>
                <input type="text"
                       name="categorie_libre"
                       id="inputCategorieLibre"
                       value="{{ $categorieLibre }}"
                       placeholder="Ex : Location tracteur, Prime récolte…"
                       class="input-field"
                       maxlength="100"
                       autocomplete="off"
                       @if($categorieModeInitial === 'liste') disabled @endif>
            </div>

            @error('categorie')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="card space-y-5">
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

        <div class="card space-y-3">
            <h2 class="text-sm font-semibold text-gray-800 font-display">Justificatif</h2>
            @if($transaction->has_justificatif)
                <p class="text-xs text-gray-600">Un fichier est déjà enregistré.</p>
                <a href="{{ route('transactions.justificatif', $transaction->id) }}" class="text-sm text-agro-vert font-medium underline">Télécharger le justificatif</a>
                <label class="flex items-center gap-2 text-sm text-gray-700 mt-2">
                    <input type="checkbox" name="supprimer_justificatif" value="1" class="rounded border-gray-300" @checked(old('supprimer_justificatif'))>
                    Supprimer le justificatif
                </label>
            @endif
            <p class="text-xs text-gray-500">Remplacer ou ajouter : photo ou PDF, max. 5 Mo.</p>
            <input type="file" name="justificatif" accept="image/jpeg,image/png,image/webp,application/pdf" class="input-field text-sm">
            @error('justificatif')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="flex flex-wrap gap-3 justify-end">
            <a href="{{ route('activites.show', $transaction->activite_id) }}" class="btn-outline">Annuler</a>
            <button type="submit" id="btnValider" class="btn-primary px-8">Enregistrer</button>
        </div>
    </form>

    <script>
        (function () {
            var suggestionsPayload = @json($suggestionsByExploitation);
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
                    b.className = 'rounded-lg border border-agro-vert/40 bg-white px-3 py-1.5 text-sm text-agro-vert hover:bg-agro-vert-clair transition-colors';
                    b.textContent = item.label;
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
                document.querySelectorAll('.cat-radio-std').forEach(function (i) { i.checked = false; });
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
                btnModeListe.classList.remove('text-gray-500');
                btnModeLibre.classList.remove('txn-cat-mode-btn--active');
                btnModeLibre.classList.add('text-gray-500');
            }

            function setModeLibre() {
                inputCategorieMode.value = 'libre';
                zoneListe.classList.add('hidden');
                zoneLibre.classList.remove('hidden');
                inputCategorieLibre.disabled = false;
                clearFsaRadios();
                btnModeLibre.classList.add('txn-cat-mode-btn--active');
                btnModeLibre.classList.remove('text-gray-500');
                btnModeListe.classList.remove('txn-cat-mode-btn--active');
                btnModeListe.classList.add('text-gray-500');
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
                    catD.querySelectorAll('input[type="radio"]').forEach(function (i) { i.removeAttribute('name'); i.disabled = true; });
                    catR.querySelectorAll('input[type="radio"]').forEach(function (i) { i.removeAttribute('name'); i.disabled = true; });
                    btnValider.className = 'btn-primary px-8 ' + (dep ? 'bg-red-600 hover:bg-red-700' : 'bg-green-700 hover:bg-green-800');
                    renderMesCategories();
                    return;
                }

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
                renderMesCategories();
            }

            zoneListe.addEventListener('change', function (e) {
                if (e.target && e.target.classList.contains('cat-radio-std') && e.target.checked) {
                    clearLibre();
                }
            });

            btnD.addEventListener('click', function () { setType('depense'); });
            btnR.addEventListener('click', function () { setType('recette'); });

            btnModeListe.addEventListener('click', function () { setModeListe(); setType(inputType.value || 'depense'); });
            btnModeLibre.addEventListener('click', function () { setModeLibre(); setType(inputType.value || 'depense'); });
            selectActivite.addEventListener('change', renderMesCategories);

            if (inputCategorieMode.value === 'libre') {
                setModeLibre();
            } else {
                setModeListe();
            }
            setType(inputType.value || 'depense');
        })();
    </script>
@endsection
