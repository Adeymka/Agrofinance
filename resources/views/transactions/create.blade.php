@extends($layout)
@section('title', 'Nouvelle transaction — AgroFinance+')
@section('page-title', 'Nouvelle transaction')

@section('content')

@if($platform === 'mobile')

@push('styles')
<style>
/* Tokens : --af-* (app.css) */
.txm-step { display: none; }
.txm-step.active { display: block; }

.txm-progress {
    display: flex;
    justify-content: center;
    gap: 6px;
    margin-bottom: 24px;
}
.txm-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    background: var(--af-glass-12);
    transition: background 0.2s, width 0.2s;
}
.txm-dot.active {
    width: 24px;
    border-radius: 4px;
    background: var(--af-color-accent);
}

.txm-block {
    background: var(--af-glass-05);
    border: 1px solid rgba(255, 255, 255, 0.09);
    border-radius: var(--af-radius-lg);
    padding: 18px;
    margin-bottom: 14px;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}
.txm-label {
    font-family: var(--font-ui), sans-serif;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: rgba(255, 255, 255, 0.32);
    margin-bottom: 10px;
}

.txm-type-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    margin-bottom: 2px;
}
.txm-type-btn {
    padding: 14px;
    border-radius: 14px;
    border: 1px solid var(--af-border-glass-soft);
    background: var(--af-glass-06);
    font-family: var(--font-display), sans-serif;
    font-size: 14px;
    font-weight: 700;
    color: rgba(255, 255, 255, 0.4);
    cursor: pointer;
    text-align: center;
    transition: all 0.15s;
    letter-spacing: -0.01em;
}
.txm-type-btn.dep-on {
    background: var(--af-tx-type-dep-bg);
    border-color: var(--af-tx-type-dep-border);
    color: var(--af-color-danger);
}
.txm-type-btn.rec-on {
    background: var(--af-tx-type-rec-bg);
    border-color: var(--af-tx-type-rec-border);
    color: var(--af-color-accent);
}

.txm-nature-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    margin-top: 12px;
}
.txm-nature-pill {
    padding: 12px;
    border-radius: 14px;
    border: 1px solid var(--af-border-glass-soft);
    background: var(--af-glass-06);
    font-family: var(--font-ui), sans-serif;
    font-size: 13px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.4);
    cursor: pointer;
    text-align: center;
    transition: all 0.15s;
}
.txm-nature-pill.selected {
    background: var(--af-green-tint-bg);
    border-color: var(--af-green-icon-border);
    color: var(--af-color-accent);
}

.txm-select {
    width: 100%;
    background: var(--af-glass-05);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 14px;
    padding: 14px 16px;
    font-family: var(--font-ui), sans-serif;
    font-size: 14px;
    color: var(--af-text-body-strong);
    outline: none;
    appearance: none;
    -webkit-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='rgba(255,255,255,0.3)' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 14px center;
    padding-right: 40px;
}
.txm-select:focus { border-color: var(--af-chip-active-border); }

.txm-amount-input {
    width: 100%;
    text-align: center;
    font-family: var(--font-display), sans-serif;
    font-size: 48px;
    font-weight: 800;
    letter-spacing: -0.04em;
    color: var(--af-color-accent);
    background: transparent;
    border: none;
    outline: none;
    padding: 16px 0 8px;
    caret-color: var(--af-color-accent);
}
.txm-amount-input::placeholder { color: rgba(255, 255, 255, 0.15); }
.txm-amount-input.dep { color: var(--af-color-danger); caret-color: var(--af-color-danger); }
.txm-amount-unit {
    text-align: center;
    font-family: var(--font-ui), sans-serif;
    font-size: 12px;
    color: rgba(255, 255, 255, 0.25);
    letter-spacing: 0.08em;
    margin-bottom: 12px;
}
.txm-amount-divider {
    height: 1px;
    background: rgba(255, 255, 255, 0.07);
    margin-bottom: 14px;
}

.txm-input {
    width: 100%;
    background: var(--af-glass-05);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 14px;
    padding: 14px 16px;
    font-family: var(--font-ui), sans-serif;
    font-size: 14px;
    color: var(--af-text-body-strong);
    outline: none;
    box-sizing: border-box;
}
.txm-input:focus { border-color: var(--af-chip-active-border); }
.txm-input::placeholder { color: rgba(255, 255, 255, 0.22); }
.txm-input-label {
    font-family: var(--font-ui), sans-serif;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: rgba(255, 255, 255, 0.32);
    margin-bottom: 6px;
}
.txm-field { margin-bottom: 12px; }

.txm-cat-list {
    max-height: 40vh;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
    padding-right: 2px;
}
.txm-cat-group-label {
    font-family: var(--font-ui), sans-serif;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: rgba(255, 255, 255, 0.22);
    padding: 10px 0 6px;
    display: block;
    border-top: 1px solid rgba(255, 255, 255, 0.05);
    margin-top: 4px;
}
.txm-cat-group-label:first-child {
    border-top: none;
    margin-top: 0;
    padding-top: 2px;
}
.txm-cat-group-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 2px;
}
.txm-cat-pill input[type="radio"] { display: none; }
.txm-cat-pill label {
    display: inline-block;
    font-family: var(--font-ui), sans-serif;
    font-size: 12px;
    font-weight: 500;
    padding: 7px 13px;
    border-radius: 999px;
    border: 1px solid var(--af-border-glass-soft);
    background: var(--af-glass-06);
    color: rgba(255, 255, 255, 0.5);
    cursor: pointer;
    transition: all 0.15s;
    white-space: nowrap;
}
.txm-cat-pill input[type="radio"]:checked + label {
    background: var(--af-filter-active-bg);
    border-color: var(--af-filter-active-border);
    color: var(--af-color-accent);
}
.txm-mode-row {
    display: flex;
    background: var(--af-glass-06);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 12px;
    padding: 3px;
    margin-bottom: 14px;
    gap: 3px;
}
.txm-mode-btn {
    flex: 1;
    font-family: var(--font-ui), sans-serif;
    font-size: 12px;
    font-weight: 600;
    padding: 9px;
    border-radius: 10px;
    border: none;
    cursor: pointer;
    text-align: center;
    background: transparent;
    color: rgba(255, 255, 255, 0.35);
    transition: all 0.15s;
}
.txm-mode-btn.active {
    background: var(--af-filter-active-bg);
    color: var(--af-color-accent);
}
.txm-footer {
    position: fixed;
    bottom: 70px;
    left: 0;
    right: 0;
    padding: 12px 16px;
    background: var(--af-tx-footer-scrim);
    backdrop-filter: blur(var(--af-blur-flash));
    -webkit-backdrop-filter: blur(var(--af-blur-flash));
    border-top: 1px solid rgba(255, 255, 255, 0.07);
    display: flex;
    gap: 10px;
    z-index: 30;
}
.txm-btn-back {
    flex: 0 0 48px;
    padding: 14px;
    border-radius: 14px;
    background: var(--af-glass-06);
    border: 1px solid var(--af-border-glass-soft);
    color: var(--af-text-muted);
    cursor: pointer;
    font-size: 18px;
    line-height: 1;
    transition: opacity 0.15s;
    display: flex; align-items: center; justify-content: center;
}
.txm-btn-next {
    flex: 1;
    padding: 14px;
    border-radius: 14px;
    font-family: var(--font-ui), sans-serif;
    font-size: 14px;
    font-weight: 700;
    border: none;
    cursor: pointer;
    transition: opacity 0.15s;
}
.txm-btn-next:active, .txm-btn-back:active { opacity: 0.75; }
.txm-suggestions {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 10px;
}
.txm-suggestion-pill {
    font-family: var(--font-ui), sans-serif;
    font-size: 11px;
    font-weight: 500;
    padding: 5px 12px;
    border-radius: 999px;
    border: 1px solid var(--af-green-tint-border);
    background: rgba(74, 222, 128, 0.07);
    color: rgba(74, 222, 128, 0.85);
    cursor: pointer;
    white-space: nowrap;
    transition: background 0.12s;
}
.txm-suggestion-pill:active { background: rgba(74, 222, 128, 0.15); }
.txm-content-pad { padding-bottom: 100px; }

.txm-empty-wrap { text-align: center; padding: 48px 20px; }
.txm-empty-msg {
    font-family: var(--font-ui), sans-serif;
    font-size: 15px;
    color: var(--af-text-dim);
    margin-bottom: 20px;
}
.txm-empty-cta {
    display: inline-block;
    background: var(--af-color-accent-dark);
    color: #fff;
    font-family: var(--font-ui), sans-serif;
    font-size: 14px;
    font-weight: 600;
    padding: 14px 28px;
    border-radius: 14px;
    text-decoration: none;
}
.txm-step-title {
    font-family: var(--font-display), sans-serif;
    font-size: 18px;
    font-weight: 700;
    color: var(--af-text-primary);
    letter-spacing: -0.02em;
    margin-bottom: 18px;
}
.txm-error { font-size: 12px; color: var(--af-color-danger); }
.txm-error--center { text-align: center; margin-bottom: 6px; }
.txm-error--block { margin-bottom: 8px; }
.txm-checkbox-row { display: flex; align-items: center; gap: 10px; padding: 8px 0; }
.txm-checkbox-accent { width: 18px; height: 18px; border-radius: 6px; accent-color: var(--af-color-accent); }
.txm-checkbox-label { font-family: var(--font-ui), sans-serif; font-size: 13px; color: var(--af-text-muted); }
.txm-suggestions-label {
    font-family: var(--font-ui), sans-serif;
    font-size: 10px;
    color: var(--af-color-accent);
    opacity: 0.72;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    margin-bottom: 6px;
}
.txm-btn-next.dep { background: var(--af-tx-btn-dep); color: #fff; }
.txm-btn-next.rec { background: var(--af-tx-btn-rec); color: #fff; }
.txm-label--nature { margin-top: 14px; margin-bottom: 6px; }
.txm-textarea-noresize { resize: none; }
.txm-wrap-suggestions { margin-bottom: 10px; }
</style>
@endpush

@if ($activites->isEmpty())
    <div class="txm-empty-wrap">
        <p class="txm-empty-msg">Aucune campagne en cours.</p>
        <a href="{{ route('activites.create') }}" class="txm-empty-cta">Créer une campagne</a>
    </div>
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

<div class="txm-content-pad">
    <form id="formTransaction" method="POST" action="{{ route('transactions.store') }}">
        @csrf
        <input type="hidden" name="type" id="inputType" value="depense">
        <input type="hidden" name="categorie_mode" id="inputCategorieMode" value="{{ $categorieModeInitial }}">

        {{-- ── ÉTAPE 1 : Type + Campagne + Nature ── --}}
        <div class="txm-step active" id="step1">

            {{-- Titre étape --}}
            <div class="txm-progress">
                <div class="txm-dot active" id="dot1"></div>
                <div class="txm-dot" id="dot2"></div>
            </div>

            <p class="txm-step-title">Nouvelle transaction</p>

            {{-- Type --}}
            <div class="txm-block">
                <div class="txm-label">Type</div>
                <div class="txm-type-row">
                    <button type="button" id="btnDep" class="txm-type-btn dep-on">Dépense</button>
                    <button type="button" id="btnRec" class="txm-type-btn">Recette</button>
                </div>
                <div id="blocNature">
                    <div class="txm-label txm-label--nature">Nature de la dépense</div>
                    <div class="txm-nature-row">
                        <div class="txm-nature-pill selected" id="pillVar" onclick="selectNature('variable')">Variable</div>
                        <div class="txm-nature-pill" id="pillFix" onclick="selectNature('fixe')">Fixe</div>
                    </div>
                    <input type="radio" name="nature" value="variable" id="natVar" checked class="hidden">
                    <input type="radio" name="nature" value="fixe" id="natFix" class="hidden">
                </div>
            </div>

            {{-- Campagne --}}
            <div class="txm-block">
                <div class="txm-label">Campagne</div>
                <select name="activite_id" id="selectActivite" required class="txm-select">
                    @foreach ($activites as $a)
                        <option value="{{ $a->id }}" data-exploitation-id="{{ $a->exploitation_id }}"
                                @selected((string) $activiteSelectionnee === (string) $a->id)>
                            {{ $a->nom }}
                        </option>
                    @endforeach
                </select>
            </div>

        </div>

        {{-- ── ÉTAPE 2 : Montant + Catégorie + Date ── --}}
        <div class="txm-step" id="step2">

            <div class="txm-progress">
                <div class="txm-dot" id="dot1b"></div>
                <div class="txm-dot active" id="dot2b"></div>
            </div>

            {{-- Montant big --}}
            <div class="txm-block">
                <input type="number" name="montant" min="1" step="1" required inputmode="numeric"
                       id="inputMontant"
                       value="{{ old('montant') }}"
                       placeholder="0"
                       class="txm-amount-input">
                <div class="txm-amount-unit">FCFA</div>
                <div class="txm-amount-divider"></div>
                @error('montant')
                    <p class="txm-error txm-error--center">{{ $message }}</p>
                @enderror
                <div class="txm-field">
                    <div class="txm-input-label">Date</div>
                    <input type="date" name="date_transaction"
                           value="{{ old('date_transaction', now()->toDateString()) }}"
                           required class="txm-input">
                </div>
                <div class="txm-field">
                    <div class="txm-input-label">Note (optionnel)</div>
                    <textarea name="note" rows="2" maxlength="500" class="txm-input txm-textarea-noresize" placeholder="Commentaire…">{{ old('note') }}</textarea>
                </div>
                <label class="txm-checkbox-row">
                    <input type="checkbox" name="est_imprevue" value="1" class="txm-checkbox-accent" @checked(old('est_imprevue'))>
                    <span class="txm-checkbox-label">Dépense imprévue</span>
                </label>
            </div>

            {{-- Catégorie --}}
            <div class="txm-block">
                <div class="txm-label">Catégorie — {{ $labelsType[$typeExploitation] ?? $typeExploitation }}</div>

                {{-- Mode toggle --}}
                <div class="txm-mode-row">
                    <button type="button" id="btnModeListe" class="txm-mode-btn active" onclick="setModeListe()">Référentiel</button>
                    <button type="button" id="btnModeLibre" class="txm-mode-btn" onclick="setModeLibre()">Saisie libre</button>
                </div>

                {{-- Mes suggestions --}}
                <div id="wrapSuggestions" class="hidden txm-wrap-suggestions">
                    <p class="txm-suggestions-label">Mes libellés</p>
                    <div id="pillsSuggestions" class="txm-suggestions"></div>
                </div>

                {{-- Mode liste : pills groupées par rubrique --}}
                <div id="zoneListe">
                    @error('categorie')
                        <p class="txm-error txm-error--block">{{ $message }}</p>
                    @enderror
                    <div id="catDepenses" class="txm-cat-list">
                        @foreach ($categories['depenses'] as $groupe => $cats)
                            <span class="txm-cat-group-label">{{ $groupe }}</span>
                            <div class="txm-cat-group-pills">
                                @foreach ($cats as $val => $label)
                                    <div class="txm-cat-pill">
                                        <input type="radio" name="categorie" value="{{ $val }}" id="dep_{{ $val }}" class="cat-radio-std">
                                        <label for="dep_{{ $val }}">{{ $label }}</label>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                    <div id="catRecettes" class="txm-cat-list hidden">
                        @foreach ($categories['recettes'] as $groupe => $cats)
                            <span class="txm-cat-group-label">{{ $groupe }}</span>
                            <div class="txm-cat-group-pills">
                                @foreach ($cats as $val => $label)
                                    <div class="txm-cat-pill">
                                        <input type="radio" name="categorie_recette" value="{{ $val }}" id="rec_{{ $val }}" class="cat-radio-std">
                                        <label for="rec_{{ $val }}">{{ $label }}</label>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Mode libre --}}
                <div id="zoneLibre" class="hidden">
                    <input type="text"
                           name="categorie_libre"
                           id="inputCategorieLibre"
                           value="{{ old('categorie_libre') }}"
                           placeholder="Ex : Location tracteur, Prime récolte…"
                           class="txm-input"
                           maxlength="100"
                           autocomplete="off"
                           disabled>
                </div>
            </div>

        </div>

    </form>
</div>

{{-- ── Footer fixe ── --}}
<div class="txm-footer">
    <button type="button" class="txm-btn-back hidden" id="btnBack" onclick="goStep(1)">←</button>
    <button type="button" class="txm-btn-next dep" id="btnNext" onclick="handleNext()">
        Suivant →
    </button>
</div>

<script>
(function () {
    var suggestionsPayload = @json($suggestionsByExploitation);
    var activiteVersExploitation = @json($activiteVersExploitation);

    var currentStep = 1;
    var currentType = 'depense';
    var inputType = document.getElementById('inputType');
    var inputCategorieMode = document.getElementById('inputCategorieMode');
    var inputCategorieLibre = document.getElementById('inputCategorieLibre');
    var selectActivite = document.getElementById('selectActivite');
    var catD = document.getElementById('catDepenses');
    var catR = document.getElementById('catRecettes');
    var inputMontant = document.getElementById('inputMontant');
    var btnNext = document.getElementById('btnNext');
    var btnBack = document.getElementById('btnBack');

    function currentExploitationId() {
        var opt = selectActivite.options[selectActivite.selectedIndex];
        return opt ? String(opt.getAttribute('data-exploitation-id')) : null;
    }

    function renderSuggestions() {
        var eid = currentExploitationId();
        var t = currentType === 'depense' ? 'depense' : 'recette';
        var wrap = document.getElementById('wrapSuggestions');
        var pills = document.getElementById('pillsSuggestions');
        pills.innerHTML = '';
        if (!eid || !suggestionsPayload[eid] || !suggestionsPayload[eid][t] || !suggestionsPayload[eid][t].length) {
            wrap.classList.add('hidden');
            return;
        }
        wrap.classList.remove('hidden');
        suggestionsPayload[eid][t].forEach(function (item) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'txm-suggestion-pill';
            b.textContent = item.label;
            b.addEventListener('click', function () {
                setModeLibre();
                inputCategorieLibre.value = item.label;
                clearFsaRadios();
            });
            pills.appendChild(b);
        });
    }

    function clearFsaRadios() {
        document.querySelectorAll('.cat-radio-std').forEach(function (i) { i.checked = false; });
    }

    window.setModeListe = function () {
        inputCategorieMode.value = 'liste';
        document.getElementById('zoneListe').classList.remove('hidden');
        document.getElementById('zoneLibre').classList.add('hidden');
        inputCategorieLibre.disabled = true;
        inputCategorieLibre.value = '';
        document.getElementById('btnModeListe').classList.add('active');
        document.getElementById('btnModeLibre').classList.remove('active');
        if (currentType === 'depense') {
            catD.querySelectorAll('input').forEach(function (i) { i.name = 'categorie'; i.disabled = false; });
            catR.querySelectorAll('input').forEach(function (i) { i.removeAttribute('name'); i.disabled = true; });
        } else {
            catR.querySelectorAll('input').forEach(function (i) { i.name = 'categorie'; i.disabled = false; });
            catD.querySelectorAll('input').forEach(function (i) { i.removeAttribute('name'); i.disabled = true; });
        }
    };

    window.setModeLibre = function () {
        inputCategorieMode.value = 'libre';
        document.getElementById('zoneListe').classList.add('hidden');
        document.getElementById('zoneLibre').classList.remove('hidden');
        inputCategorieLibre.disabled = false;
        clearFsaRadios();
        catD.querySelectorAll('input').forEach(function (i) { i.removeAttribute('name'); i.disabled = true; });
        catR.querySelectorAll('input').forEach(function (i) { i.removeAttribute('name'); i.disabled = true; });
        document.getElementById('btnModeLibre').classList.add('active');
        document.getElementById('btnModeListe').classList.remove('active');
    };

    function setType(type) {
        currentType = type;
        inputType.value = type;
        var dep = type === 'depense';
        catD.classList.toggle('hidden', !dep);
        catR.classList.toggle('hidden', dep);
        document.getElementById('blocNature').style.display = dep ? '' : 'none';
        document.querySelectorAll('input[name="nature"]').forEach(function (i) { i.disabled = !dep; });

        btnNext.classList.remove('dep', 'rec');
        btnNext.classList.add(dep ? 'dep' : 'rec');
        inputMontant.className = 'txm-amount-input' + (dep ? ' dep' : '');

        document.getElementById('btnDep').className = 'txm-type-btn' + (dep ? ' dep-on' : '');
        document.getElementById('btnRec').className = 'txm-type-btn' + (!dep ? ' rec-on' : '');

        if (inputCategorieMode.value === 'liste') {
            setModeListe();
        }
        renderSuggestions();
    }

    window.selectNature = function (val) {
        document.getElementById('pillVar').classList.toggle('selected', val === 'variable');
        document.getElementById('pillFix').classList.toggle('selected', val === 'fixe');
        document.getElementById('natVar').checked = val === 'variable';
        document.getElementById('natFix').checked = val === 'fixe';
    };

    window.goStep = function (step) {
        currentStep = step;
        document.getElementById('step1').classList.toggle('active', step === 1);
        document.getElementById('step2').classList.toggle('active', step === 2);
        document.getElementById('dot1').classList.toggle('active', step === 1);
        document.getElementById('dot2').classList.toggle('active', step === 2);
        if (document.getElementById('dot1b')) {
            document.getElementById('dot1b').classList.toggle('active', step === 1);
            document.getElementById('dot2b').classList.toggle('active', step === 2);
        }
        btnBack.classList.toggle('hidden', step !== 2);
        if (step === 2) {
            btnNext.textContent = 'Enregistrer';
            renderSuggestions();
            if (inputCategorieMode.value === 'liste') { setModeListe(); } else { setModeLibre(); }
        } else {
            btnNext.textContent = 'Suivant →';
        }
    };

    window.__AF_buildMobileTxPayload = function () {
        var type = document.getElementById('inputType').value;
        var activite_id = parseInt(document.getElementById('selectActivite').value, 10);
        var montant = parseFloat(String(document.getElementById('inputMontant').value), 10);
        var dateEl = document.querySelector('#formTransaction input[name="date_transaction"]');
        var date_transaction = dateEl ? dateEl.value : '';
        var noteEl = document.querySelector('#formTransaction textarea[name="note"]');
        var note = noteEl ? noteEl.value.trim() : '';
        var cb = document.querySelector('#formTransaction input[name="est_imprevue"]');
        var est_imprevue = cb ? cb.checked : false;

        var nature = null;
        if (type === 'depense') {
            var nr = document.querySelector('#formTransaction input[name="nature"]:checked');
            nature = nr ? nr.value : 'variable';
        }

        var categorie = '';
        var mode = document.getElementById('inputCategorieMode').value;
        if (mode === 'libre') {
            categorie = (document.getElementById('inputCategorieLibre') && document.getElementById('inputCategorieLibre').value || '').trim();
        } else {
            var cr = document.querySelector('#formTransaction input[name="categorie"]:checked');
            categorie = cr ? cr.value : '';
        }

        if (!activite_id || !date_transaction) {
            alert('Vérifiez la campagne et la date.');
            return null;
        }
        if (!montant || montant < 1 || isNaN(montant)) {
            alert('Indiquez un montant valide (minimum 1 FCFA).');
            return null;
        }
        if (!categorie) {
            alert('Choisissez une catégorie ou saisissez-la en libre.');
            return null;
        }

        return {
            activite_id: activite_id,
            type: type,
            nature: nature,
            categorie: categorie,
            montant: montant,
            date_transaction: date_transaction,
            note: note || null,
            est_imprevue: !!est_imprevue,
        };
    };

    window.handleNext = function () {
        if (currentStep === 1) {
            goStep(2);
        } else {
            if (!navigator.onLine && typeof window.__AF_enqueueOfflineTransaction === 'function') {
                var payload = window.__AF_buildMobileTxPayload();
                if (!payload) {
                    return;
                }
                window.__AF_enqueueOfflineTransaction(payload).then(function () {
                    alert('Enregistré hors ligne. Synchronisation à la reconnexion.');
                    window.location.href = @json(route('dashboard'));
                }).catch(function (err) {
                    var msg = (err && err.message) ? err.message : 'Erreur inconnue';
                    alert('Impossible d\'enregistrer hors ligne : ' + msg);
                    if (window.console && console.error) {
                        console.error(err);
                    }
                });
                return;
            }
            document.getElementById('formTransaction').submit();
        }
    };

    document.getElementById('btnDep').addEventListener('click', function () { setType('depense'); });
    document.getElementById('btnRec').addEventListener('click', function () { setType('recette'); });

    setType('depense');
    goStep(1);
    @if($categorieModeInitial === 'libre')
        setModeLibre();
    @endif
})();
</script>

@endif

@else
{{-- ════ DESKTOP (original) ════ --}}
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
                        <button type="button" id="btnDepense" class="txn-type-btn txn-type-btn--depense-on">Dépense</button>
                        <button type="button" id="btnRecette" class="txn-type-btn txn-type-btn--inactive">Recette</button>
                    </div>
                </div>
                <div id="blocNature" class="space-y-2">
                    <p class="text-xs font-medium text-gray-600">Nature (dépense)</p>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="cursor-pointer"><input type="radio" name="nature" value="variable" class="peer sr-only" checked><div class="txn-nature-pill p-3 text-center text-sm">Variable</div></label>
                        <label class="cursor-pointer"><input type="radio" name="nature" value="fixe" class="peer sr-only"><div class="txn-nature-pill p-3 text-center text-sm">Fixe</div></label>
                    </div>
                </div>
            </div>

            <div class="card space-y-4">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <h2 class="text-sm font-semibold text-gray-800 font-display">Catégorie</h2>
                    <p class="text-xs text-gray-500 max-w-sm">Référentiel — {{ $labelsType[$typeExploitation] ?? $typeExploitation }}</p>
                </div>
                <div class="flex rounded-xl border border-white/15 p-1 gap-1 bg-white/[0.03]">
                    <button type="button" id="btnModeListe" class="txn-cat-mode-btn txn-cat-mode-btn--active flex-1 py-2.5 text-sm font-semibold rounded-lg transition-colors">Liste (standard + mes libellés)</button>
                    <button type="button" id="btnModeLibre" class="txn-cat-mode-btn flex-1 py-2.5 text-sm font-semibold rounded-lg transition-colors text-white/60">Saisie libre</button>
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
                                <summary class="cursor-pointer px-3 py-2 text-xs font-bold text-gray-500 uppercase tracking-wide list-none flex justify-between items-center">{{ $groupe }}<span class="text-gray-400 group-open:rotate-180 transition">▼</span></summary>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-2 pt-0">
                                    @foreach ($cats as $val => $label)
                                        <label class="block"><input type="radio" name="categorie" value="{{ $val }}" class="peer sr-only cat-radio-std"><div class="txn-cat-pill p-2.5 text-sm">{{ $label }}</div></label>
                                    @endforeach
                                </div>
                            </details>
                        @endforeach
                    </div>
                    <div id="catRecettes" class="hidden space-y-4 max-h-[55vh] overflow-y-auto pr-1">
                        @foreach ($categories['recettes'] as $groupe => $cats)
                            <details class="group border border-white/10 rounded-xl bg-white/[0.03] open:bg-white/[0.06]" open>
                                <summary class="cursor-pointer px-3 py-2 text-xs font-bold text-gray-500 uppercase tracking-wide list-none flex justify-between items-center">{{ $groupe }}<span class="text-gray-400">▼</span></summary>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-2 pt-0">
                                    @foreach ($cats as $val => $label)
                                        <label class="block"><input type="radio" name="categorie_recette" value="{{ $val }}" class="peer sr-only cat-radio-std"><div class="txn-cat-pill p-2.5 text-sm">{{ $label }}</div></label>
                                    @endforeach
                                </div>
                            </details>
                        @endforeach
                    </div>
                    <p class="text-[11px] text-white/40">En « Liste », choisissez un libellé du référentiel ci-dessus ou un de vos libellés en haut. Passez en « Saisie libre » pour tout taper.</p>
                </div>
                <div id="zoneLibre" class="hidden space-y-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Libellé de catégorie</label>
                    <input type="text" name="categorie_libre" id="inputCategorieLibre" value="{{ old('categorie_libre') }}" placeholder="Ex : Location tracteur, Prime récolte, Tontine…" class="input-field" maxlength="100" autocomplete="off" @if($categorieModeInitial === 'liste') disabled @endif>
                </div>
                @error('categorie')<p class="text-sm text-red-400">{{ $message }}</p>@enderror
            </div>

            <div class="card space-y-5">
                <h2 class="text-sm font-semibold text-gray-800 font-display sr-only">Montant et détails</h2>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1 text-center">Montant (FCFA)</label>
                    <input type="number" name="montant" min="1" step="1" required inputmode="numeric" value="{{ old('montant') }}" class="w-full text-center text-3xl font-bold rounded-xl border-2 border-white/15 bg-white/[0.05] py-4 text-emerald-300 placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500/40">
                </div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Date</label><input type="date" name="date_transaction" value="{{ old('date_transaction', now()->toDateString()) }}" required class="input-field"></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Note (optionnel)</label><textarea name="note" rows="2" maxlength="500" class="input-field" placeholder="Commentaire…">{{ old('note') }}</textarea></div>
                <label class="flex items-center gap-2 text-sm text-gray-700"><input type="checkbox" name="est_imprevue" value="1" class="rounded border-white/30 bg-white/[0.06] text-emerald-500 focus:ring-emerald-500/40" @checked(old('est_imprevue'))>Dépense imprévue</label>
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
                function currentExploitationId() { var opt = selectActivite.options[selectActivite.selectedIndex]; return opt ? String(opt.getAttribute('data-exploitation-id')) : null; }
                function renderMesCategories() {
                    var eid = currentExploitationId(); var t = inputType.value === 'depense' ? 'depense' : 'recette'; mesPills.innerHTML = '';
                    if (!eid || !suggestionsPayload[eid] || !suggestionsPayload[eid][t] || !suggestionsPayload[eid][t].length) { wrapMes.classList.add('hidden'); return; }
                    wrapMes.classList.remove('hidden');
                    suggestionsPayload[eid][t].forEach(function (item) { var b = document.createElement('button'); b.type='button'; b.className='rounded-lg border border-emerald-500/35 bg-emerald-500/10 px-3 py-1.5 text-sm text-emerald-200 hover:bg-emerald-500/20 transition-colors'; b.textContent=item.label; b.setAttribute('data-label',item.label); b.addEventListener('click',function(){setModeLibre();inputCategorieLibre.value=item.label;inputCategorieLibre.focus();clearFsaRadios();}); mesPills.appendChild(b); });
                }
                function clearFsaRadios() { document.querySelectorAll('.cat-radio-std').forEach(function(i){i.checked=false;}); }
                function clearLibre() { inputCategorieLibre.value=''; }
                function setModeListe() { inputCategorieMode.value='liste'; zoneListe.classList.remove('hidden'); zoneLibre.classList.add('hidden'); inputCategorieLibre.disabled=true; clearLibre(); btnModeListe.classList.add('txn-cat-mode-btn--active'); btnModeListe.classList.remove('text-white/60'); btnModeLibre.classList.remove('txn-cat-mode-btn--active'); btnModeLibre.classList.add('text-white/60'); }
                function setModeLibre() { inputCategorieMode.value='libre'; zoneListe.classList.add('hidden'); zoneLibre.classList.remove('hidden'); inputCategorieLibre.disabled=false; clearFsaRadios(); btnModeLibre.classList.add('txn-cat-mode-btn--active'); btnModeLibre.classList.remove('text-white/60'); btnModeListe.classList.remove('txn-cat-mode-btn--active'); btnModeListe.classList.add('text-white/60'); catD.querySelectorAll('input[type="radio"]').forEach(function(i){i.removeAttribute('name');i.disabled=true;}); catR.querySelectorAll('input[type="radio"]').forEach(function(i){i.removeAttribute('name');i.disabled=true;}); }
                function setType(type) {
                    inputType.value=type; var dep=type==='depense'; catD.classList.toggle('hidden',!dep); catR.classList.toggle('hidden',dep); blocNat.classList.toggle('hidden',!dep); blocNat.querySelectorAll('input[name="nature"]').forEach(function(i){i.disabled=!dep;});
                    if(inputCategorieMode.value==='libre'){catD.querySelectorAll('input[type="radio"]').forEach(function(i){i.removeAttribute('name');i.disabled=true;}); catR.querySelectorAll('input[type="radio"]').forEach(function(i){i.removeAttribute('name');i.disabled=true;}); btnValider.className='btn-primary px-8 py-3 w-full sm:w-auto '+(dep?'bg-red-600 hover:bg-red-700':'bg-green-700 hover:bg-green-800'); renderMesCategories(); return;}
                    if(dep){catD.querySelectorAll('input[type="radio"]').forEach(function(i){i.setAttribute('name','categorie');i.disabled=false;}); catR.querySelectorAll('input').forEach(function(i){i.removeAttribute('name');i.disabled=true;}); btnD.className='txn-type-btn txn-type-btn--depense-on'; btnR.className='txn-type-btn txn-type-btn--inactive'; btnValider.className='btn-primary px-8 py-3 w-full sm:w-auto bg-red-600 hover:bg-red-700';}
                    else{catD.querySelectorAll('input[type="radio"]').forEach(function(i){i.removeAttribute('name');i.disabled=true;}); catR.querySelectorAll('input[type="radio"]').forEach(function(i){i.setAttribute('name','categorie');i.disabled=false;}); btnR.className='txn-type-btn txn-type-btn--recette-on'; btnD.className='txn-type-btn txn-type-btn--inactive'; btnValider.className='btn-primary px-8 py-3 w-full sm:w-auto bg-green-700 hover:bg-green-800';}
                    renderMesCategories();
                }
                zoneListe.addEventListener('change',function(e){if(e.target&&e.target.classList.contains('cat-radio-std')&&e.target.checked){clearLibre();}});
                document.getElementById('btnDepense').addEventListener('click',function(){setType('depense');});
                document.getElementById('btnRecette').addEventListener('click',function(){setType('recette');});
                btnModeListe.addEventListener('click',function(){setModeListe();setType(inputType.value);});
                btnModeLibre.addEventListener('click',function(){setModeLibre();setType(inputType.value);});
                selectActivite.addEventListener('change',function(){var url=new URL(window.location.href);url.searchParams.set('activite_id',selectActivite.value);window.location.href=url.toString();});
                if(inputCategorieMode.value==='libre'){setModeLibre();}else{setModeListe();}
                setType(inputType.value);
            })();
        </script>
    @endif
@endif

@endsection
