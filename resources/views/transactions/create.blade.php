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
    color: var(--af-text-muted);
    margin-bottom: 10px;
}

.txm-type-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    margin-bottom: 2px;
}
.txm-type-btn {
    min-height: 44px;
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
    min-height: 44px;
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
    background: var(--af-color-accent);
    border-color: var(--af-color-accent);
    color: #000;
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
    color: var(--af-text-dim);
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
.txm-input::placeholder { color: rgba(255, 255, 255, 0.38); }
.txm-input-label {
    font-family: var(--font-ui), sans-serif;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--af-text-muted);
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
    color: rgba(255, 255, 255, 0.38);
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
    min-height: 44px;
    font-family: var(--font-ui), sans-serif;
    font-size: 12px;
    font-weight: 600;
    padding: 9px;
    border-radius: 10px;
    border: none;
    cursor: pointer;
    text-align: center;
    background: transparent;
    color: var(--af-text-muted);
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
    min-height: 44px;
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
.txm-error {
    font-size: 12px;
    color: #fecaca;
    border-left: 3px solid var(--af-color-danger);
    padding: 4px 0 4px 10px;
    margin-top: 4px;
}
.txm-error--center { text-align: center; margin-bottom: 6px; border-left: none; padding-left: 0; }
.txm-error--block { margin-bottom: 8px; }
.txm-error-summary {
    background: var(--af-red-alert-bg);
    border: 1px solid var(--af-red-alert-border);
    border-radius: var(--af-radius-md);
    padding: 12px 14px;
    margin-bottom: 16px;
    font-family: var(--font-ui), sans-serif;
    font-size: 12px;
    color: #fecaca;
    line-height: 1.45;
}
.txm-error-summary strong { display: block; margin-bottom: 6px; font-weight: 700; color: #fff; }
.txm-error-summary ul { margin: 0; padding-left: 1.1rem; }
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
.txm-textarea-noresize { resize: none; }
.txm-wrap-suggestions { margin-bottom: 10px; }
.txm-combo-wrap { position: relative; }
.txm-combo-input {
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
.txm-combo-input:focus { border-color: var(--af-chip-active-border); }
/* Liste déroulante : fond opaque (évite glass sur glass = texte illisible) */
.txm-combo-dd {
    display: none;
    position: absolute;
    left: 0; right: 0;
    top: calc(100% + 6px);
    max-height: min(45vh, 280px);
    overflow-y: auto;
    z-index: 300;
    isolation: isolate;
    background:
        linear-gradient(180deg, rgba(255, 255, 255, 0.05) 0%, transparent 32%),
        rgba(13, 31, 13, 0.97);
    border: 1px solid rgba(74, 222, 128, 0.2);
    border-radius: var(--af-radius-md);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    box-shadow:
        0 16px 48px rgba(0, 0, 0, 0.55),
        0 0 0 1px rgba(255, 255, 255, 0.06);
    -webkit-overflow-scrolling: touch;
}
.txm-combo-dd.open { display: block; }
.txm-combo-item {
    padding: 12px 14px;
    font-family: var(--font-ui), sans-serif;
    font-size: 13px;
    font-weight: 500;
    color: var(--af-text-body-strong);
    cursor: pointer;
    border: none;
    border-bottom: 1px solid rgba(255, 255, 255, 0.07);
    text-align: left;
    width: 100%;
    background: transparent;
    transition: background 0.12s ease;
}
.txm-combo-item:last-child { border-bottom: none; }
.txm-combo-item:active, .txm-combo-item:hover {
    background: var(--af-green-tint-bg);
    color: var(--af-text-heading-soft);
}
.txm-combo-groupe {
    font-family: var(--font-ui), sans-serif;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--af-color-accent);
    opacity: 0.85;
    padding: 10px 14px 6px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    background: rgba(0, 0, 0, 0.15);
}
.txm-modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 200;
    align-items: center;
    justify-content: center;
    padding: 16px;
    background: rgba(13, 31, 13, 0.72);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}
.txm-modal-overlay.open { display: flex; }
.txm-modal {
    width: 100%;
    max-width: 380px;
    background: var(--af-mobile-surface-hero);
    border: 1px solid var(--af-mobile-border-strong);
    border-radius: var(--af-radius-lg);
    padding: 22px 20px 20px;
    backdrop-filter: blur(var(--af-blur-hero-mobile));
    -webkit-backdrop-filter: blur(var(--af-blur-hero-mobile));
    box-shadow: var(--af-shadow-hero-mobile);
}
.txm-modal h3 {
    font-family: var(--font-display), sans-serif;
    font-size: 18px;
    font-weight: 700;
    letter-spacing: -0.02em;
    color: var(--af-text-high);
    margin-bottom: 8px;
}
.txm-modal p.hint { font-family: var(--font-ui), sans-serif; font-size: 12px; color: var(--af-text-muted); line-height: 1.5; margin-bottom: 16px; }
.txm-modal-actions { display: flex; gap: 10px; margin-top: 18px; }
.txm-modal-actions button {
    flex: 1;
    min-height: var(--af-touch-min);
    border-radius: var(--af-radius-sm);
    font-weight: 600;
    font-size: 14px;
    font-family: var(--font-ui), sans-serif;
    cursor: pointer;
    border: 1px solid var(--af-border-glass-soft);
    background: var(--af-glass-06);
    color: var(--af-text-secondary);
    transition: background 0.15s ease, border-color 0.15s ease;
}
.txm-modal-actions button:first-child:hover {
    background: var(--af-glass-10);
    border-color: var(--af-border-glass-mid);
}
.txm-modal-actions .txm-modal-ok {
    background: linear-gradient(180deg, var(--af-color-accent-mid) 0%, var(--af-color-accent-dark) 100%);
    border: 1px solid rgba(74, 222, 128, 0.45);
    color: #fff;
    box-shadow: 0 4px 16px rgba(22, 163, 74, 0.35);
}
.txm-modal-actions .txm-modal-ok:active {
    opacity: 0.92;
}
</style>
@endpush

@if ($activites->isEmpty())
    <div class="txm-empty-wrap">
        <p class="txm-empty-msg">Aucune campagne en cours.</p>
        <a href="{{ route('activites.create', array_filter(['exploitation_id' => $exploitationIdPourCampagne ?? null])) }}" class="txm-empty-cta">Créer une campagne</a>
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
@endphp

<div class="txm-content-pad">
    <div class="mb-3 flex justify-end">
        <a href="{{ route('transactions.index') }}" class="inline-flex items-center rounded-xl border border-white/15 bg-white/5 px-3 py-2 text-xs font-semibold text-white/80">
            Voir la liste des transactions
        </a>
    </div>
    <form id="formTransaction" method="POST" action="{{ route('transactions.store') }}" enctype="multipart/form-data">
        @csrf
        @if ($errors->any())
            <div class="txm-error-summary" role="alert" aria-live="polite">
                <strong>⚠ Vérifiez les champs ci-dessous</strong>
                <ul>
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <input type="hidden" name="type" id="inputType" value="depense">
        {{-- ── ÉTAPE 1 : Type + Campagne ── --}}
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
            </div>

            {{-- Campagne --}}
            <div class="txm-block">
                <div class="txm-label">Campagne</div>
                <select name="activite_id" id="selectActivite" required class="txm-select">
                    @foreach ($activites as $a)
                        <option value="{{ $a->id }}" data-exploitation-id="{{ $a->id }}"
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
                <div class="txm-field" style="margin-top:14px;">
                    <div class="txm-input-label">Photo ou PDF justificatif (optionnel)</div>
                    <input type="file" name="justificatif" accept="image/jpeg,image/png,image/webp,application/pdf" class="txm-input" style="font-size:13px;">
                    <p class="text-[11px] text-white/48 mt-1">Max. 5 Mo — uniquement en ligne. Formats : JPG, PNG, WEBP, PDF.</p>
                    @error('justificatif')<p class="txm-error">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Catégorie (combobox) --}}
            <div class="txm-block">
                <div class="txm-label">Catégorie — {{ $labelsType[$typeExploitation] ?? $typeExploitation }}</div>
                <p class="text-[11px] text-white/48 mb-3 leading-relaxed">Tapez pour filtrer ou choisissez dans la liste. Si votre libellé n’existe pas, complétez la saisie : nous vous demanderons deux précisions.</p>

                <input type="hidden" name="categorie" id="mob_cat_slug" value="{{ old('categorie') }}" disabled>
                <input type="hidden" name="categorie_libre" id="mob_cat_libre" value="{{ old('categorie_libre') }}" disabled>
                <input type="hidden" name="nature" id="mob_nature" value="{{ old('nature', 'variable') }}" disabled>
                <input type="hidden" name="intrant_production" id="mob_intrant" value="1" disabled>

                <div id="wrapSuggestions" class="hidden txm-wrap-suggestions">
                    <p class="txm-suggestions-label">Mes libellés récents</p>
                    <div id="pillsSuggestions" class="txm-suggestions"></div>
                </div>

                <div class="txm-combo-wrap">
                    @error('categorie')
                        <p class="txm-error txm-error--block">{{ $message }}</p>
                    @enderror
                    @error('nature')<p class="txm-error txm-error--block">{{ $message }}</p>@enderror
                    @error('intrant_production')<p class="txm-error txm-error--block">{{ $message }}</p>@enderror
                    <input type="text" id="catComboMob" class="txm-combo-input" placeholder="Rechercher ou saisir…" maxlength="100" autocomplete="off" value="{{ old('categorie_libre') ?: '' }}">
                    <div id="catDropdownMob" class="txm-combo-dd" role="listbox" aria-label="Suggestions"></div>
                </div>
            </div>

        </div>

    </form>
</div>

<div id="txmModalCustom" class="txm-modal-overlay" aria-hidden="true">
    <div class="txm-modal" role="dialog" aria-modal="true" aria-labelledby="txmModalTitle">
        <h3 id="txmModalTitle">Précisez cette dépense</h3>
        <p class="hint">Vous avez saisi un libellé personnalisé. Pour calculer correctement vos indicateurs, répondez aux deux questions ci-dessous.</p>
        <div class="txm-label" style="margin-bottom:6px;">1. Cette charge varie selon ce que vous produisez ou vendez ?</div>
        <div class="txm-nature-row" style="margin-bottom:14px;">
            <button type="button" class="txm-nature-pill selected" id="modalMobNatVar" data-val="variable">Oui → variable</button>
            <button type="button" class="txm-nature-pill" id="modalMobNatFix" data-val="fixe">Non → fixe</button>
        </div>
        <p class="text-[10px] text-white/45 mb-3 px-0.5">Ex. : les semences suivent le volume cultivé (variable) ; le loyer du terrain reste le même (fixe).</p>
        <div class="txm-label" style="margin-bottom:6px;">2. Cet achat sert directement la production de cette campagne ?</div>
        <div class="txm-nature-row">
            <button type="button" class="txm-nature-pill selected" id="modalMobIntOui" data-val="1">Oui</button>
            <button type="button" class="txm-nature-pill" id="modalMobIntNon" data-val="0">Non</button>
        </div>
        <div class="txm-modal-actions">
            <button type="button" id="modalMobCancel">Annuler</button>
            <button type="button" class="txm-modal-ok" id="modalMobOk">Valider</button>
        </div>
    </div>
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
    var CI_SLUGS_MOB = @json($slugsCi ?? \App\Helpers\TransactionCategories::slugsChargesIntermediaires());
    var TX_CAT = @json($txCatMeta ?? ['depenses' => [], 'recettes' => []]);
    var suggestionsPayload = @json($suggestionsByExploitation);
    var activiteVersExploitation = @json($activiteVersExploitation);

    var currentStep = 1;
    var currentType = 'depense';
    var selectedSlug = null;
    var selectedLabel = '';
    var inputType = document.getElementById('inputType');
    var selectActivite = document.getElementById('selectActivite');
    var catCombo = document.getElementById('catComboMob');
    var catDd = document.getElementById('catDropdownMob');
    var mobSlug = document.getElementById('mob_cat_slug');
    var mobLibre = document.getElementById('mob_cat_libre');
    var mobNat = document.getElementById('mob_nature');
    var mobInt = document.getElementById('mob_intrant');
    var inputMontant = document.getElementById('inputMontant');
    var btnNext = document.getElementById('btnNext');
    var btnBack = document.getElementById('btnBack');
    var modal = document.getElementById('txmModalCustom');
    var pendingLibre = null;
    var modalForOffline = false;

    function currentList() {
        return currentType === 'depense' ? TX_CAT.depenses : TX_CAT.recettes;
    }

    function norm(s) {
        return String(s || '').trim().toLowerCase();
    }

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
                selectedSlug = null;
                selectedLabel = '';
                catCombo.value = item.label;
            });
            pills.appendChild(b);
        });
    }

    function renderDropdown(filter) {
        var q = norm(filter);
        var list = currentList();
        var groups = {};
        list.forEach(function (row) {
            if (q && row.label_search.indexOf(q) === -1 && row.slug.indexOf(q) === -1) {
                return;
            }
            if (!groups[row.groupe]) {
                groups[row.groupe] = [];
            }
            groups[row.groupe].push(row);
        });
        catDd.innerHTML = '';
        Object.keys(groups).forEach(function (g) {
            var h = document.createElement('div');
            h.className = 'txm-combo-groupe';
            h.textContent = g;
            catDd.appendChild(h);
            groups[g].forEach(function (row) {
                var el = document.createElement('button');
                el.type = 'button';
                el.className = 'txm-combo-item';
                el.textContent = row.label;
                el.addEventListener('click', function () {
                    selectedSlug = row.slug;
                    selectedLabel = row.label;
                    catCombo.value = row.label;
                    catDd.classList.remove('open');
                });
                catDd.appendChild(el);
            });
        });
    }

    catCombo.addEventListener('focus', function () {
        renderDropdown(catCombo.value);
        catDd.classList.add('open');
    });
    catCombo.addEventListener('input', function () {
        if (selectedLabel && catCombo.value !== selectedLabel) {
            selectedSlug = null;
            selectedLabel = '';
        }
        renderDropdown(catCombo.value);
        catDd.classList.add('open');
    });
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.txm-combo-wrap')) {
            catDd.classList.remove('open');
        }
    });

    function resolveCategory() {
        var txt = (catCombo.value || '').trim();
        if (!txt) {
            return { err: 'Indiquez une catégorie ou choisissez dans la liste.' };
        }
        var list = currentList();
        var slug = null;
        if (selectedSlug && txt === selectedLabel) {
            slug = selectedSlug;
        } else {
            var t = txt.toLowerCase();
            for (var i = 0; i < list.length; i++) {
                if (list[i].slug === t) {
                    slug = list[i].slug;
                    break;
                }
            }
            if (!slug) {
                for (var j = 0; j < list.length; j++) {
                    if (list[j].label_search.indexOf(norm(txt)) !== -1 || norm(list[j].label) === norm(txt)) {
                        slug = list[j].slug;
                        break;
                    }
                }
            }
        }
        if (slug) {
            return { slug: slug, custom: false };
        }
        if (currentType === 'recette') {
            return { custom: true, libre: txt, needModal: false };
        }
        if (CI_SLUGS_MOB.indexOf(txt) !== -1) {
            return { slug: txt, custom: false };
        }
        return { custom: true, libre: txt, needModal: true };
    }

    function applyHidden(r) {
        mobSlug.disabled = true;
        mobLibre.disabled = true;
        mobNat.disabled = true;
        mobInt.disabled = true;
        mobSlug.value = '';
        mobLibre.value = '';
        mobNat.value = 'variable';
        mobInt.value = '1';
        if (r.slug) {
            mobSlug.disabled = false;
            mobSlug.value = r.slug;
            return;
        }
        mobLibre.disabled = false;
        mobLibre.value = r.libre;
        if (currentType !== 'depense') {
            return;
        }
        mobNat.disabled = false;
        mobNat.value = r.nature || 'variable';
        if (CI_SLUGS_MOB.indexOf(r.libre) === -1) {
            mobInt.disabled = false;
            mobInt.value = (r.intrant === false || r.intrant === '0') ? '0' : '1';
        }
    }

    var modalNat = 'variable';
    var modalInt = true;
    function syncModalUi() {
        document.getElementById('modalMobNatVar').classList.toggle('selected', modalNat === 'variable');
        document.getElementById('modalMobNatFix').classList.toggle('selected', modalNat === 'fixe');
        document.getElementById('modalMobIntOui').classList.toggle('selected', modalInt === true);
        document.getElementById('modalMobIntNon').classList.toggle('selected', modalInt === false);
    }
    document.getElementById('modalMobNatVar').addEventListener('click', function () { modalNat = 'variable'; syncModalUi(); });
    document.getElementById('modalMobNatFix').addEventListener('click', function () { modalNat = 'fixe'; syncModalUi(); });
    document.getElementById('modalMobIntOui').addEventListener('click', function () { modalInt = true; syncModalUi(); });
    document.getElementById('modalMobIntNon').addEventListener('click', function () { modalInt = false; syncModalUi(); });
    document.getElementById('modalMobCancel').addEventListener('click', function () {
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
        pendingLibre = null;
        modalForOffline = false;
    });
    document.getElementById('modalMobOk').addEventListener('click', function () {
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
        if (pendingLibre === null) {
            return;
        }
        applyHidden({
            custom: true,
            libre: pendingLibre,
            nature: modalNat,
            intrant: modalInt,
        });
        pendingLibre = null;
        if (modalForOffline) {
            modalForOffline = false;
            var p = window.__AF_buildMobileTxPayloadAfterHidden();
            if (!p) {
                return;
            }
            window.__AF_enqueueOfflineTransaction(p).then(function () {
                alert('Enregistré hors ligne. Synchronisation à la reconnexion.');
                window.location.href = @json(route('dashboard'));
            }).catch(function (err) {
                alert('Erreur : ' + ((err && err.message) ? err.message : 'inconnue'));
            });
            return;
        }
        document.getElementById('formTransaction').submit();
    });

    function setType(type) {
        currentType = type;
        inputType.value = type;
        btnNext.classList.remove('dep', 'rec');
        btnNext.classList.add(type === 'depense' ? 'dep' : 'rec');
        inputMontant.className = 'txm-amount-input' + (type === 'depense' ? ' dep' : '');
        document.getElementById('btnDep').className = 'txm-type-btn' + (type === 'depense' ? ' dep-on' : '');
        document.getElementById('btnRec').className = 'txm-type-btn' + (type === 'recette' ? ' rec-on' : '');
        catCombo.value = '';
        selectedSlug = null;
        selectedLabel = '';
        renderSuggestions();
        renderDropdown('');
    }

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
        } else {
            btnNext.textContent = 'Suivant →';
        }
    };

    function trySubmitOnline() {
        var r = resolveCategory();
        if (r.err) {
            alert(r.err);
            return;
        }
        if (r.custom && r.needModal) {
            pendingLibre = r.libre;
            modalNat = 'variable';
            modalInt = true;
            syncModalUi();
            modal.classList.add('open');
            modal.setAttribute('aria-hidden', 'false');
            return;
        }
        if (r.custom && !r.needModal) {
            applyHidden({ custom: true, libre: r.libre });
        } else {
            applyHidden({ slug: r.slug });
        }
        document.getElementById('formTransaction').submit();
    }

    window.__AF_buildMobileTxPayloadAfterHidden = function () {
        var type = document.getElementById('inputType').value;
        var activite_id = parseInt(document.getElementById('selectActivite').value, 10);
        var montant = parseFloat(String(document.getElementById('inputMontant').value), 10);
        var dateEl = document.querySelector('#formTransaction input[name="date_transaction"]');
        var date_transaction = dateEl ? dateEl.value : '';
        var noteEl = document.querySelector('#formTransaction textarea[name="note"]');
        var note = noteEl ? noteEl.value.trim() : '';
        var cb = document.querySelector('#formTransaction input[name="est_imprevue"]');
        var est_imprevue = cb ? cb.checked : false;
        if (!activite_id || !date_transaction) {
            alert('Vérifiez la campagne et la date.');
            return null;
        }
        if (!montant || montant < 1 || isNaN(montant)) {
            alert('Indiquez un montant valide (minimum 1 FCFA).');
            return null;
        }
        var categorie = !mobSlug.disabled && mobSlug.value ? mobSlug.value : mobLibre.value;
        if (!categorie) {
            alert('Choisissez une catégorie.');
            return null;
        }
        var out = {
            activite_id: activite_id,
            type: type,
            categorie: categorie,
            montant: montant,
            date_transaction: date_transaction,
            note: note || null,
            est_imprevue: !!est_imprevue,
        };
        if (type === 'depense') {
            if (!mobLibre.disabled && mobLibre.value) {
                out.nature = mobNat.value;
                if (CI_SLUGS_MOB.indexOf(categorie) === -1) {
                    out.intrant_production = mobInt.value === '1';
                }
            }
        }
        return out;
    };

    window.__AF_buildMobileTxPayload = function () {
        var r = resolveCategory();
        if (r.err) {
            alert(r.err);
            return null;
        }
        if (r.custom && r.needModal) {
            pendingLibre = r.libre;
            modalNat = 'variable';
            modalInt = true;
            syncModalUi();
            modalForOffline = true;
            modal.classList.add('open');
            modal.setAttribute('aria-hidden', 'false');
            return null;
        }
        if (r.custom && !r.needModal) {
            applyHidden({ custom: true, libre: r.libre });
        } else {
            applyHidden({ slug: r.slug });
        }
        return window.__AF_buildMobileTxPayloadAfterHidden();
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
            trySubmitOnline();
        }
    };

    document.getElementById('btnDep').addEventListener('click', function () { setType('depense'); });
    document.getElementById('btnRec').addEventListener('click', function () { setType('recette'); });

    setType('depense');
    goStep(1);
    syncModalUi();

    @if(old('categorie') && !old('categorie_libre'))
        (function () {
            var os = @json(old('categorie'));
            var list = TX_CAT.depenses.concat(TX_CAT.recettes);
            for (var i = 0; i < list.length; i++) {
                if (list[i].slug === os) {
                    selectedSlug = list[i].slug;
                    selectedLabel = list[i].label;
                    catCombo.value = list[i].label;
                    currentType = document.getElementById('inputType').value;
                    break;
                }
            }
        })();
    @endif
})();
</script>

@endif

@else
{{-- ════ DESKTOP (original) ════ --}}
    @if ($activites->isEmpty())
        <p class="text-sm text-gray-600">Aucune campagne en cours. <a href="{{ route('activites.create', array_filter(['exploitation_id' => $exploitationIdPourCampagne ?? null])) }}" class="text-agro-vert font-medium underline">Créer une campagne</a>.</p>
    @else
        @php
            $labelsType = [
                'cultures_vivrieres' => 'Cultures vivrières',
                'elevage' => 'Élevage',
                'maraichage' => 'Maraîchage',
                'transformation' => 'Transformation',
                'mixte' => 'Mixte',
            ];
        @endphp

        <style>
            /* Même principe que mobile : panneau lisible, pas de superposition glass / glass */
            .txn-desk-combo-dd {
                display: none;
                position: absolute;
                left: 0; right: 0;
                top: calc(100% + 6px);
                max-height: min(50vh, 320px);
                overflow-y: auto;
                z-index: 300;
                isolation: isolate;
                background:
                    linear-gradient(180deg, rgba(255, 255, 255, 0.06) 0%, transparent 30%),
                    rgba(13, 31, 13, 0.97);
                border: 1px solid rgba(74, 222, 128, 0.22);
                border-radius: var(--af-radius-md);
                backdrop-filter: blur(16px);
                -webkit-backdrop-filter: blur(16px);
                box-shadow:
                    0 16px 48px rgba(0, 0, 0, 0.5),
                    0 0 0 1px rgba(255, 255, 255, 0.07);
                -webkit-overflow-scrolling: touch;
            }
            .txn-desk-combo-dd.open { display: block; }
            .txn-desk-combo-item {
                display: block;
                width: 100%;
                text-align: left;
                padding: 11px 14px;
                font-family: var(--font-ui), sans-serif;
                font-size: 13px;
                font-weight: 500;
                color: var(--af-text-body-strong);
                border: none;
                border-bottom: 1px solid rgba(255, 255, 255, 0.07);
                background: transparent;
                cursor: pointer;
                transition: background 0.12s ease;
            }
            .txn-desk-combo-item:hover {
                background: var(--af-green-tint-bg);
                color: var(--af-text-heading-soft);
            }
            .txn-desk-combo-groupe {
                font-family: var(--font-ui), sans-serif;
                font-size: 10px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.08em;
                color: var(--af-color-accent);
                opacity: 0.88;
                padding: 10px 14px 6px;
                border-bottom: 1px solid rgba(255, 255, 255, 0.08);
                background: rgba(0, 0, 0, 0.12);
            }
            .txn-desk-modal {
                display: none;
                position: fixed;
                inset: 0;
                z-index: 200;
                align-items: center;
                justify-content: center;
                padding: 16px;
                background: rgba(13, 31, 13, 0.72);
                backdrop-filter: blur(8px);
                -webkit-backdrop-filter: blur(8px);
            }
            .txn-desk-modal.open { display: flex; }
            .txn-desk-modal-card {
                width: 100%;
                max-width: 400px;
                background: var(--af-glass-05);
                border: 1px solid var(--af-border-glass-mid);
                border-radius: var(--af-radius-lg);
                padding: 22px 22px 20px;
                backdrop-filter: blur(20px);
                -webkit-backdrop-filter: blur(20px);
                box-shadow: var(--af-shadow-card-lg);
            }
            .txn-desk-modal-card h3 {
                font-family: var(--font-display), sans-serif;
                font-size: 1.125rem;
                font-weight: 700;
                letter-spacing: -0.02em;
                color: var(--af-text-high);
                margin-bottom: 0.5rem;
            }
            .txn-desk-modal-card .txn-desk-modal-hint {
                font-family: var(--font-ui), sans-serif;
                font-size: 12px;
                color: var(--af-text-muted);
                line-height: 1.5;
                margin-bottom: 1rem;
            }
            .txn-desk-modal-card .txn-desk-q {
                font-family: var(--font-ui), sans-serif;
                font-size: 11px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.06em;
                color: var(--af-text-label);
                margin-bottom: 0.5rem;
            }
            .af-desk-pill {
                font-family: var(--font-ui), sans-serif;
                min-height: var(--af-touch-min);
                border: 1px solid var(--af-border-glass-soft);
                background: var(--af-glass-06);
                color: var(--af-text-secondary);
                border-radius: var(--af-radius-sm);
                transition: border-color 0.15s ease, background 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
            }
            .af-desk-pill:hover {
                border-color: var(--af-border-glass-mid);
                background: var(--af-glass-08);
            }
            .af-desk-pill.af-desk-pill--active {
                border-color: var(--af-chip-active-border);
                background: var(--af-chip-active-bg);
                color: var(--af-color-accent);
                box-shadow: 0 0 0 1px rgba(74, 222, 128, 0.2);
            }
            .txn-desk-modal-card .txn-desk-modal-micro {
                font-family: var(--font-ui), sans-serif;
                font-size: 11px;
                line-height: 1.45;
                color: var(--af-text-caption);
                margin-bottom: 0.75rem;
            }
        </style>

        <div class="max-w-2xl mx-auto mb-3 flex justify-end">
            <a href="{{ route('transactions.index') }}" class="btn-outline text-sm px-3 py-2">Voir la liste des transactions</a>
        </div>
        <form id="formTransaction" method="POST" action="{{ route('transactions.store') }}" enctype="multipart/form-data" class="max-w-2xl mx-auto space-y-6">
            @csrf
            <input type="hidden" name="type" id="inputType" value="depense">

            <div class="card space-y-5">
                <h2 class="text-sm font-semibold text-gray-800 font-display">Informations générales</h2>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Campagne concernée</label>
                    <select name="activite_id" id="selectActivite" required class="input-field">
                        @foreach ($activites as $a)
                            <option value="{{ $a->id }}" data-exploitation-id="{{ $a->id }}"
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
            </div>

            <div class="card space-y-4 relative z-20 overflow-visible">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <h2 class="text-sm font-semibold text-gray-800 font-display">Catégorie</h2>
                    <p class="text-xs text-gray-500 max-w-sm">{{ $labelsType[$typeExploitation] ?? $typeExploitation }} — recherchez ou saisissez un libellé.</p>
                </div>
                <p class="text-[11px] text-white/50">Pour un libellé du référentiel, la nature (fixe / variable) et l’intrant sont appliqués automatiquement. Sinon, une fenêtre vous posera deux questions.</p>

                <input type="hidden" name="categorie" id="desk_cat_slug" value="{{ old('categorie') }}" disabled>
                <input type="hidden" name="categorie_libre" id="desk_cat_libre" value="{{ old('categorie_libre') }}" disabled>
                <input type="hidden" name="nature" id="desk_nature" value="{{ old('nature', 'variable') }}" disabled>
                <input type="hidden" name="intrant_production" id="desk_intrant" value="1" disabled>

                <div id="wrapMesCategories" class="hidden rounded-xl border border-emerald-500/25 bg-emerald-500/[0.06] p-3 space-y-2">
                    <p class="text-xs font-semibold text-emerald-300/90 uppercase tracking-wide">Mes libellés récents</p>
                    <div id="mesCategoriesPills" class="flex flex-wrap gap-2"></div>
                </div>

                <div class="relative">
                    @error('categorie')<p class="text-sm text-red-400 mb-2">{{ $message }}</p>@enderror
                    @error('nature')<p class="text-sm text-red-400 mb-2">{{ $message }}</p>@enderror
                    @error('intrant_production')<p class="text-sm text-red-400 mb-2">{{ $message }}</p>@enderror
                    <label class="block text-xs font-medium text-gray-600 mb-1">Catégorie</label>
                    <input type="text" id="catComboDesk" class="input-field" placeholder="Rechercher ou saisir…" maxlength="100" autocomplete="off" value="{{ old('categorie_libre') ?: '' }}">
                    <div id="catDropdownDesk" class="txn-desk-combo-dd" role="listbox"></div>
                </div>
            </div>

            <div class="card space-y-5 relative z-10">
                <h2 class="text-sm font-semibold text-gray-800 font-display sr-only">Montant et détails</h2>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1 text-center">Montant (FCFA)</label>
                    <input type="number" name="montant" min="1" step="1" required inputmode="numeric" value="{{ old('montant') }}" class="w-full text-center text-3xl font-bold rounded-xl border-2 border-white/15 bg-white/[0.05] py-4 text-emerald-300 placeholder:text-white/45 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500/40">
                </div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Date</label><input type="date" name="date_transaction" value="{{ old('date_transaction', now()->toDateString()) }}" required class="input-field"></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Note (optionnel)</label><textarea name="note" rows="2" maxlength="500" class="input-field" placeholder="Commentaire…">{{ old('note') }}</textarea></div>
                <label class="flex items-center gap-2 text-sm text-gray-700"><input type="checkbox" name="est_imprevue" value="1" class="rounded border-white/30 bg-white/[0.06] text-emerald-500 focus:ring-emerald-500/40" @checked(old('est_imprevue'))>Dépense imprévue</label>
            </div>
            <div class="card space-y-3">
                <h2 class="text-sm font-semibold text-gray-800 font-display">Justificatif (optionnel)</h2>
                <p class="text-xs text-gray-500">Photo ou PDF, max. 5 Mo (JPG, PNG, WEBP, PDF). Envoyé uniquement avec une connexion internet.</p>
                <input type="file" name="justificatif" accept="image/jpeg,image/png,image/webp,application/pdf" class="input-field text-sm">
                @error('justificatif')<p class="text-sm text-red-400">{{ $message }}</p>@enderror
            </div>
            <div class="flex justify-end">
                <button type="button" id="btnValider" class="btn-primary px-8 py-3 w-full sm:w-auto">Enregistrer la transaction</button>
            </div>
        </form>

        <div id="deskModalCustom" class="txn-desk-modal" aria-hidden="true">
            <div class="txn-desk-modal-card" role="dialog" aria-modal="true" aria-labelledby="deskModalTitle">
                <h3 id="deskModalTitle">Précisez cette dépense</h3>
                <p class="txn-desk-modal-hint">Libellé personnalisé : deux questions pour calculer correctement vos indicateurs.</p>
                <p class="txn-desk-q">1. Charge variable ou fixe ?</p>
                <div class="grid grid-cols-2 gap-2 mb-3">
                    <button type="button" class="af-desk-pill desk-mod-nat af-desk-pill--active px-3 py-2.5 text-sm" data-nat="variable">Oui → variable</button>
                    <button type="button" class="af-desk-pill desk-mod-nat px-3 py-2.5 text-sm" data-nat="fixe">Non → fixe</button>
                </div>
                <p class="txn-desk-modal-micro">Variable : liée au volume produit ou vendu. Fixe : montant qui reste le même (loyer, abonnement…).</p>
                <p class="txn-desk-q">2. Intrant de production ?</p>
                <div class="grid grid-cols-2 gap-2 mb-2">
                    <button type="button" class="af-desk-pill desk-mod-int af-desk-pill--active px-3 py-2.5 text-sm" data-int="1">Oui</button>
                    <button type="button" class="af-desk-pill desk-mod-int px-3 py-2.5 text-sm" data-int="0">Non</button>
                </div>
                <div class="flex gap-2 pt-2">
                    <button type="button" id="deskModalCancel" class="btn-outline flex-1 justify-center">Annuler</button>
                    <button type="button" id="deskModalOk" class="btn-primary flex-1 justify-center">Valider</button>
                </div>
            </div>
        </div>

        <script>
            (function () {
                var CI_SLUGS = @json($slugsCi ?? \App\Helpers\TransactionCategories::slugsChargesIntermediaires());
                var TX_CAT = @json($txCatMeta ?? ['depenses' => [], 'recettes' => []]);
                var suggestionsPayload = @json($suggestionsByExploitation);
                var inputType = document.getElementById('inputType');
                var catCombo = document.getElementById('catComboDesk');
                var catDd = document.getElementById('catDropdownDesk');
                var deskSlug = document.getElementById('desk_cat_slug');
                var deskLibre = document.getElementById('desk_cat_libre');
                var deskNat = document.getElementById('desk_nature');
                var deskInt = document.getElementById('desk_intrant');
                var selectActivite = document.getElementById('selectActivite');
                var wrapMes = document.getElementById('wrapMesCategories');
                var mesPills = document.getElementById('mesCategoriesPills');
                var btnValider = document.getElementById('btnValider');
                var btnD = document.getElementById('btnDepense');
                var btnR = document.getElementById('btnRecette');
                var currentTypeDesk = 'depense';
                var selSlug = null;
                var selLabel = '';
                var pendingLibreDesk = null;
                var modalNatDesk = 'variable';
                var modalIntDesk = true;

                function norm(s) { return String(s || '').trim().toLowerCase(); }
                function currentListDesk() {
                    return currentTypeDesk === 'depense' ? TX_CAT.depenses : TX_CAT.recettes;
                }
                function currentExploitationId() {
                    var opt = selectActivite.options[selectActivite.selectedIndex];
                    return opt ? String(opt.getAttribute('data-exploitation-id')) : null;
                }
                function renderMesCategories() {
                    var eid = currentExploitationId();
                    var t = currentTypeDesk === 'depense' ? 'depense' : 'recette';
                    mesPills.innerHTML = '';
                    if (!eid || !suggestionsPayload[eid] || !suggestionsPayload[eid][t] || !suggestionsPayload[eid][t].length) {
                        wrapMes.classList.add('hidden');
                        return;
                    }
                    wrapMes.classList.remove('hidden');
                    suggestionsPayload[eid][t].forEach(function (item) {
                        var b = document.createElement('button');
                        b.type = 'button';
                        b.className = 'rounded-lg border border-emerald-500/35 bg-emerald-500/10 px-3 py-1.5 text-sm text-emerald-200';
                        b.textContent = item.label;
                        b.addEventListener('click', function () {
                            selSlug = null;
                            selLabel = '';
                            catCombo.value = item.label;
                        });
                        mesPills.appendChild(b);
                    });
                }
                function renderDdDesk(filter) {
                    var q = norm(filter);
                    var list = currentListDesk();
                    var groups = {};
                    list.forEach(function (row) {
                        if (q && row.label_search.indexOf(q) === -1 && row.slug.indexOf(q) === -1) {
                            return;
                        }
                        if (!groups[row.groupe]) {
                            groups[row.groupe] = [];
                        }
                        groups[row.groupe].push(row);
                    });
                    catDd.innerHTML = '';
                    Object.keys(groups).forEach(function (g) {
                        var h = document.createElement('div');
                        h.className = 'txn-desk-combo-groupe';
                        h.textContent = g;
                        catDd.appendChild(h);
                        groups[g].forEach(function (row) {
                            var el = document.createElement('button');
                            el.type = 'button';
                            el.className = 'txn-desk-combo-item';
                            el.textContent = row.label;
                            el.addEventListener('click', function () {
                                selSlug = row.slug;
                                selLabel = row.label;
                                catCombo.value = row.label;
                                catDd.classList.remove('open');
                            });
                            catDd.appendChild(el);
                        });
                    });
                }
                function resolveDesk() {
                    var txt = (catCombo.value || '').trim();
                    if (!txt) {
                        return { err: 'Indiquez une catégorie.' };
                    }
                    var list = currentListDesk();
                    var slug = null;
                    if (selSlug && txt === selLabel) {
                        slug = selSlug;
                    } else {
                        var t = txt.toLowerCase();
                        for (var i = 0; i < list.length; i++) {
                            if (list[i].slug === t) {
                                slug = list[i].slug;
                                break;
                            }
                        }
                        if (!slug) {
                            for (var j = 0; j < list.length; j++) {
                                if (list[j].label_search.indexOf(norm(txt)) !== -1 || norm(list[j].label) === norm(txt)) {
                                    slug = list[j].slug;
                                    break;
                                }
                            }
                        }
                    }
                    if (slug) {
                        return { slug: slug };
                    }
                    if (currentTypeDesk === 'recette') {
                        return { custom: true, libre: txt, needModal: false };
                    }
                    if (CI_SLUGS.indexOf(txt) !== -1) {
                        return { slug: txt };
                    }
                    return { custom: true, libre: txt, needModal: true };
                }
                function applyDesk(r) {
                    deskSlug.disabled = true;
                    deskLibre.disabled = true;
                    deskNat.disabled = true;
                    deskInt.disabled = true;
                    deskSlug.value = '';
                    deskLibre.value = '';
                    deskNat.value = 'variable';
                    deskInt.value = '1';
                    if (r.slug) {
                        deskSlug.disabled = false;
                        deskSlug.value = r.slug;
                        return;
                    }
                    deskLibre.disabled = false;
                    deskLibre.value = r.libre;
                    if (currentTypeDesk !== 'depense') {
                        return;
                    }
                    deskNat.disabled = false;
                    deskNat.value = r.nature || 'variable';
                    if (CI_SLUGS.indexOf(r.libre) === -1) {
                        deskInt.disabled = false;
                        deskInt.value = (r.intrant === false) ? '0' : '1';
                    }
                }
                function syncDeskModal() {
                    document.querySelectorAll('.desk-mod-nat').forEach(function (b) {
                        b.classList.toggle('af-desk-pill--active', b.getAttribute('data-nat') === modalNatDesk);
                    });
                    document.querySelectorAll('.desk-mod-int').forEach(function (b) {
                        var isOui = b.getAttribute('data-int') === '1';
                        b.classList.toggle('af-desk-pill--active', isOui === modalIntDesk);
                    });
                }
                document.querySelectorAll('.desk-mod-nat').forEach(function (b) {
                    b.addEventListener('click', function () {
                        modalNatDesk = b.getAttribute('data-nat');
                        syncDeskModal();
                    });
                });
                document.querySelectorAll('.desk-mod-int').forEach(function (b) {
                    b.addEventListener('click', function () {
                        modalIntDesk = b.getAttribute('data-int') === '1';
                        syncDeskModal();
                    });
                });
                document.getElementById('deskModalCancel').addEventListener('click', function () {
                    document.getElementById('deskModalCustom').classList.remove('open');
                    pendingLibreDesk = null;
                });
                document.getElementById('deskModalOk').addEventListener('click', function () {
                    document.getElementById('deskModalCustom').classList.remove('open');
                    if (pendingLibreDesk === null) {
                        return;
                    }
                    applyDesk({ custom: true, libre: pendingLibreDesk, nature: modalNatDesk, intrant: modalIntDesk });
                    pendingLibreDesk = null;
                    document.getElementById('formTransaction').submit();
                });
                catCombo.addEventListener('focus', function () {
                    renderDdDesk(catCombo.value);
                    catDd.classList.add('open');
                });
                catCombo.addEventListener('input', function () {
                    if (selLabel && catCombo.value !== selLabel) {
                        selSlug = null;
                        selLabel = '';
                    }
                    renderDdDesk(catCombo.value);
                    catDd.classList.add('open');
                });
                document.addEventListener('click', function (e) {
                    if (!e.target.closest('.relative')) {
                        catDd.classList.remove('open');
                    }
                });
                function setTypeDesk(type) {
                    currentTypeDesk = type;
                    inputType.value = type;
                    catCombo.value = '';
                    selSlug = null;
                    selLabel = '';
                    btnD.className = 'txn-type-btn ' + (type === 'depense' ? 'txn-type-btn--depense-on' : 'txn-type-btn--inactive');
                    btnR.className = 'txn-type-btn ' + (type === 'recette' ? 'txn-type-btn--recette-on' : 'txn-type-btn--inactive');
                    btnValider.className = 'btn-primary px-8 py-3 w-full sm:w-auto ' + (type === 'depense' ? 'bg-red-600 hover:bg-red-700' : 'bg-green-700 hover:bg-green-800');
                    renderMesCategories();
                    renderDdDesk('');
                }
                btnD.addEventListener('click', function () { setTypeDesk('depense'); });
                btnR.addEventListener('click', function () { setTypeDesk('recette'); });
                selectActivite.addEventListener('change', function () {
                    var url = new URL(window.location.href);
                    url.searchParams.set('activite_id', selectActivite.value);
                    window.location.href = url.toString();
                });
                btnValider.addEventListener('click', function () {
                    var r = resolveDesk();
                    if (r.err) {
                        alert(r.err);
                        return;
                    }
                    if (r.custom && r.needModal) {
                        pendingLibreDesk = r.libre;
                        modalNatDesk = 'variable';
                        modalIntDesk = true;
                        syncDeskModal();
                        document.getElementById('deskModalCustom').classList.add('open');
                        return;
                    }
                    if (r.custom && !r.needModal) {
                        applyDesk({ custom: true, libre: r.libre });
                    } else {
                        applyDesk({ slug: r.slug });
                    }
                    document.getElementById('formTransaction').submit();
                });
                setTypeDesk('depense');
                syncDeskModal();
                @if(old('categorie') && !old('categorie_libre'))
                    (function () {
                        var os = @json(old('categorie'));
                        var list = TX_CAT.depenses.concat(TX_CAT.recettes);
                        for (var i = 0; i < list.length; i++) {
                            if (list[i].slug === os) {
                                selSlug = list[i].slug;
                                selLabel = list[i].label;
                                catCombo.value = list[i].label;
                                break;
                            }
                        }
                    })();
                @endif
            })();
        </script>
    @endif
@endif

@endsection
