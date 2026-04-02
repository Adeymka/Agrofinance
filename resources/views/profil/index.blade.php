@extends($layout)
@section('title', 'Mon profil — AgroFinance+')
@section('page-title', 'Mon profil')

@section('content')

@if($platform === 'mobile')

@push('styles')
<style>
/* Tokens : --af-* (app.css) */
.prf-header {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px 0 24px;
}
.prf-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(74, 222, 128, 0.25), var(--af-avatar-gradient-end));
    border: 2px solid var(--af-green-icon-border);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: var(--font-display), sans-serif;
    font-size: 26px;
    font-weight: 800;
    color: var(--af-color-accent);
    letter-spacing: -0.04em;
    margin-bottom: 12px;
}
.prf-name {
    font-family: var(--font-display), sans-serif;
    font-size: 18px;
    font-weight: 700;
    color: var(--af-text-primary);
    letter-spacing: -0.02em;
    margin-bottom: 3px;
}
.prf-phone {
    font-family: var(--font-ui), sans-serif;
    font-size: 13px;
    color: var(--af-text-muted);
}
.prf-plan-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-top: 8px;
    padding: 5px 14px;
    border-radius: 999px;
    background: var(--af-green-tint-bg);
    border: 1px solid var(--af-green-icon-border);
    font-family: var(--font-ui), sans-serif;
    font-size: 11px;
    font-weight: 700;
    color: var(--af-color-accent);
    letter-spacing: 0.06em;
    text-transform: uppercase;
}
.prf-block {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: var(--af-radius-lg);
    padding: 18px;
    margin-bottom: 14px;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}
.prf-block-title {
    font-family: var(--font-display), sans-serif;
    font-size: 13px;
    font-weight: 700;
    color: var(--af-text-muted);
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 14px;
}
.prf-field { margin-bottom: 12px; }
.prf-label {
    font-family: var(--font-ui), sans-serif;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--af-text-muted);
    margin-bottom: 6px;
}
.prf-input {
    width: 100%;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 14px;
    padding: 14px 16px;
    font-family: var(--font-ui), sans-serif;
    font-size: 14px;
    color: var(--af-text-body-strong);
    outline: none;
    box-sizing: border-box;
    appearance: none;
    -webkit-appearance: none;
}
.prf-input:focus { border-color: var(--af-chip-active-border); }
.prf-input::placeholder { color: rgba(255, 255, 255, 0.38); }
.prf-input:disabled {
    background: rgba(255, 255, 255, 0.02);
    color: rgba(255, 255, 255, 0.42);
    border-color: rgba(255, 255, 255, 0.07);
    cursor: not-allowed;
}
.prf-input-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}
.prf-select {
    width: 100%;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 14px;
    padding: 14px 16px;
    font-family: var(--font-ui), sans-serif;
    font-size: 14px;
    color: var(--af-text-body-strong);
    outline: none;
    appearance: none;
    -webkit-appearance: none;
    box-sizing: border-box;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='rgba(255,255,255,0.3)' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 14px center;
    padding-right: 40px;
}
.prf-select:focus { border-color: var(--af-chip-active-border); }
.prf-pin-row { display: flex; gap: 8px; }
.prf-pin-input {
    flex: 1;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 14px;
    padding: 14px 10px;
    font-family: var(--font-display), sans-serif;
    font-size: 22px;
    font-weight: 700;
    color: var(--af-color-accent);
    text-align: center;
    letter-spacing: 0.3em;
    outline: none;
    box-sizing: border-box;
    -webkit-text-security: disc;
}
.prf-pin-input:focus { border-color: var(--af-chip-active-border); }
.prf-submit {
    width: 100%;
    background: var(--af-color-accent-dark);
    color: #fff;
    font-family: var(--font-ui), sans-serif;
    font-size: 14px;
    font-weight: 700;
    padding: 15px;
    border-radius: 14px;
    border: 1px solid var(--af-tx-type-rec-border);
    cursor: pointer;
    margin-top: 6px;
    transition: opacity 0.15s;
}
.prf-submit:active { opacity: 0.8; }
.prf-submit--secondary {
    background: var(--af-glass-06);
    border-color: var(--af-border-glass-soft);
    color: var(--af-text-muted);
}
.prf-abo-card {
    background: rgba(74, 222, 128, 0.06);
    border: 1px solid rgba(74, 222, 128, 0.16);
    border-radius: 16px;
    padding: 16px;
    margin-bottom: 12px;
}
.prf-abo-plan-label {
    font-family: var(--font-ui), sans-serif;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--af-color-accent);
    opacity: 0.72;
    margin-bottom: 4px;
}
.prf-abo-plan-name {
    font-family: var(--font-display), sans-serif;
    font-size: 20px;
    font-weight: 800;
    color: var(--af-text-primary);
    letter-spacing: -0.03em;
    margin-bottom: 4px;
}
.prf-abo-expiry {
    font-family: var(--font-ui), sans-serif;
    font-size: 12px;
    color: var(--af-text-subtle);
}
.prf-abo-empty {
    font-family: var(--font-ui), sans-serif;
    font-size: 13px;
    color: var(--af-text-muted);
    margin-bottom: 12px;
}
.prf-manage-link {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 16px;
    background: var(--af-glass-06);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 14px;
    text-decoration: none;
    transition: background 0.15s;
}
.prf-manage-link:active { background: rgba(255, 255, 255, 0.08); }
.prf-manage-link-text {
    font-family: var(--font-ui), sans-serif;
    font-size: 14px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.7);
}
.prf-manage-link-arrow {
    color: rgba(255, 255, 255, 0.3);
    font-size: 18px;
}
.prf-error {
    font-family: var(--font-ui), sans-serif;
    font-size: 11px;
    color: var(--af-color-danger);
    margin-top: 5px;
}
.prf-pin-hint {
    font-family: var(--font-ui), sans-serif;
    font-size: 12px;
    color: var(--af-text-muted);
    margin-bottom: 14px;
}
.prf-logout-form { margin-bottom: 16px; }
.prf-logout-btn {
    width: 100%;
    background: var(--af-red-tint-bg);
    border: 1px solid var(--af-red-tint-border);
    color: var(--af-color-danger);
    font-family: var(--font-ui), sans-serif;
    font-size: 14px;
    font-weight: 600;
    padding: 15px;
    border-radius: 14px;
    cursor: pointer;
    transition: opacity 0.15s;
}
.prf-logout-btn:active { opacity: 0.85; }
.prf-pad { padding-bottom: 32px; }
.prf-outdoor-row {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    cursor: pointer;
    margin: 0;
}
.prf-outdoor-cb {
    width: 20px;
    height: 20px;
    margin-top: 2px;
    accent-color: var(--af-color-accent);
    flex-shrink: 0;
}
.prf-outdoor-row-text { display: flex; flex-direction: column; gap: 4px; }
.prf-outdoor-title {
    font-family: var(--font-ui), sans-serif;
    font-size: 14px;
    font-weight: 600;
    color: var(--af-text-body-strong);
}
.prf-outdoor-sub {
    font-family: var(--font-ui), sans-serif;
    font-size: 12px;
    line-height: 1.35;
    color: var(--af-text-muted);
}

/* En mode plein soleil, maintenir le fond du bloc affichage sombre */
html.af-outdoor .prf-block {
    background: rgba(0, 0, 0, 0.35);
    border-color: rgba(255, 255, 255, 0.15);
}
</style>
@endpush

<div class="prf-pad">

    {{-- ── Avatar + nom ── --}}
    <div class="prf-header">
        <div class="prf-avatar">
            {{ strtoupper(mb_substr((string) $user->prenom, 0, 1)) }}{{ strtoupper(mb_substr((string) $user->nom, 0, 1)) }}
        </div>
        <div class="prf-name">{{ $user->prenom }} {{ $user->nom }}</div>
        <div class="prf-phone">{{ $user->telephone }}</div>
        @if($abonnement)
            <div class="prf-plan-badge">
                <span>●</span>
                Plan {{ ucfirst($abonnement->plan) }}
            </div>
        @endif
    </div>

    {{-- ── Affichage (plein soleil) ── --}}
    <div class="prf-block">
        <div class="prf-block-title">Affichage</div>
        <label class="prf-outdoor-row">
            <input type="checkbox" class="prf-outdoor-cb js-af-outdoor-toggle" autocomplete="off">
            <span class="prf-outdoor-row-text">
                <span class="prf-outdoor-title">Lecture plein soleil</span>
                <span class="prf-outdoor-sub">Texte et cartes plus lisibles quand la lumière est forte (réglage enregistré sur cet appareil).</span>
            </span>
        </label>
    </div>

    {{-- ── Infos personnelles ── --}}
    <div class="prf-block">
        <div class="prf-block-title">Informations</div>
        <form method="POST" action="{{ route('profil.update') }}">
            @csrf @method('PUT')

            <div class="prf-input-grid prf-field">
                <div>
                    <div class="prf-label">Prénom</div>
                    <input name="prenom" value="{{ old('prenom', $user->prenom) }}" required class="prf-input">
                </div>
                <div>
                    <div class="prf-label">Nom</div>
                    <input name="nom" value="{{ old('nom', $user->nom) }}" required class="prf-input">
                </div>
            </div>

            <div class="prf-field">
                <div class="prf-label">Téléphone</div>
                <input value="{{ $user->telephone }}" disabled class="prf-input">
            </div>

            <div class="prf-input-grid prf-field">
                <div>
                    <div class="prf-label">Département</div>
                    <input name="departement" value="{{ old('departement', $user->departement) }}" class="prf-input" placeholder="Atlantique…">
                </div>
                <div>
                    <div class="prf-label">Commune</div>
                    <input name="commune" value="{{ old('commune', $user->commune) }}" class="prf-input" placeholder="Cotonou…">
                </div>
            </div>

            <button type="submit" class="prf-submit">Enregistrer</button>
        </form>
    </div>

    {{-- ── Changer le PIN ── --}}
    <div class="prf-block">
        <div class="prf-block-title">Changer le PIN</div>
        <form method="POST" action="{{ route('profil.update') }}">
            @csrf @method('PUT')
            <p class="prf-pin-hint">Laissez vide pour ne pas modifier. 4 chiffres uniquement.</p>

            <div class="prf-field">
                <div class="prf-label">PIN actuel</div>
                <input type="password" name="pin_actuel" maxlength="4" inputmode="numeric"
                       autocomplete="current-password" class="prf-input" placeholder="••••">
                @error('pin_actuel') <p class="prf-error">{{ $message }}</p> @enderror
            </div>

            <div class="prf-input-grid prf-field">
                <div>
                    <div class="prf-label">Nouveau PIN</div>
                    <input type="password" name="pin" maxlength="4" inputmode="numeric"
                           class="prf-input" placeholder="••••">
                    @error('pin') <p class="prf-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <div class="prf-label">Confirmer</div>
                    <input type="password" name="pin_confirmation" maxlength="4" inputmode="numeric"
                           class="prf-input" placeholder="••••">
                </div>
            </div>

            <button type="submit" class="prf-submit prf-submit--secondary">
                Changer le PIN
            </button>
        </form>
    </div>

    {{-- ── Abonnement ── --}}
    <div class="prf-block">
        <div class="prf-block-title">Abonnement</div>
        @if ($abonnement)
            <div class="prf-abo-card">
                <div class="prf-abo-plan-label">Plan actuel</div>
                <div class="prf-abo-plan-name">{{ ucfirst($abonnement->plan) }}</div>
                <div class="prf-abo-expiry">Jusqu'au {{ $abonnement->date_fin?->format('d/m/Y') ?? '—' }}</div>
            </div>
        @else
            <p class="prf-abo-empty">Aucun abonnement actif.</p>
        @endif
        <a href="{{ route('abonnement') }}" class="prf-manage-link">
            <span class="prf-manage-link-text">Gérer mon abonnement</span>
            <span class="prf-manage-link-arrow">›</span>
        </a>
    </div>

    {{-- ── Déconnexion ── --}}
    <form method="POST" action="{{ route('deconnexion') }}" class="prf-logout-form">
        @csrf
        <button type="submit" class="prf-logout-btn" onclick="return confirm('Se déconnecter ?')">
            Se déconnecter
        </button>
    </form>

</div>

@else
{{-- ════ DESKTOP (original) ════ --}}
<style>
    /* Desktop: Affichage bloc en dark glassmorphe comme mobile */
    .desktop-affichage-block {
        background: rgba(0, 0, 0, 0.35) !important;
        border: 1px solid rgba(255, 255, 255, 0.15) !important;
        color: rgba(255, 255, 255, 0.9) !important;
    }
    .desktop-affichage-block .text-gray-700,
    .desktop-affichage-block .text-gray-900,
    .desktop-affichage-block .text-gray-500 {
        color: rgba(255, 255, 255, 0.8) !important;
    }
    .desktop-affichage-block .border-gray-300 {
        border-color: rgba(255, 255, 255, 0.3) !important;
    }
    .desktop-affichage-block input[type="checkbox"] {
        accent-color: var(--af-color-accent);
    }
</style>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="card space-y-6">
            <div class="rounded-xl border border-gray-200 bg-gray-50/80 p-4 desktop-affichage-block">
                <p class="text-xs font-semibold text-gray-700 uppercase tracking-wide mb-2">Affichage</p>
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" class="js-af-outdoor-toggle mt-1 h-4 w-4 rounded border-gray-300 text-agro-vert focus:ring-agro-vert" autocomplete="off">
                    <span>
                        <span class="block text-sm font-medium text-gray-900">Lecture plein soleil</span>
                        <span class="block text-xs text-gray-500 mt-1">Texte et cartes plus lisibles en pleine lumière (réglage enregistré sur cet appareil).</span>
                    </span>
                </label>
            </div>
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-full bg-agro-vert text-white flex items-center justify-center text-2xl font-bold">
                    {{ strtoupper(mb_substr((string) $user->prenom, 0, 1)) }}{{ strtoupper(mb_substr((string) $user->nom, 0, 1)) }}
                </div>
                <div>
                    <p class="font-semibold text-gray-900">{{ $user->prenom }} {{ $user->nom }}</p>
                    <p class="text-sm text-gray-500">{{ $user->telephone }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('profil.update') }}" class="space-y-4">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Prénom</label><input name="prenom" value="{{ old('prenom', $user->prenom) }}" required class="input-field"></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Nom</label><input name="nom" value="{{ old('nom', $user->nom) }}" required class="input-field"></div>
                </div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Téléphone</label><input value="{{ $user->telephone }}" disabled class="input-field bg-gray-50 text-gray-500"></div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Département</label><input name="departement" value="{{ old('departement', $user->departement) }}" class="input-field"></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Commune</label><input name="commune" value="{{ old('commune', $user->commune) }}" class="input-field"></div>
                </div>
                <hr class="border-gray-100">
                <div>
                    <p class="text-sm font-semibold text-gray-800 mb-3">Changer le PIN</p>
                    <p class="text-xs text-gray-500 mb-3">Laissez vide pour ne pas modifier. 4 chiffres.</p>
                    <div class="space-y-3">
                        <div><label class="block text-xs font-medium text-gray-600 mb-1">PIN actuel</label><input type="password" name="pin_actuel" maxlength="4" inputmode="numeric" autocomplete="current-password" class="input-field" placeholder="••••">@error('pin_actuel') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror</div>
                        <div><label class="block text-xs font-medium text-gray-600 mb-1">Nouveau PIN</label><input type="password" name="pin" maxlength="4" inputmode="numeric" class="input-field" placeholder="••••">@error('pin') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror</div>
                        <div><label class="block text-xs font-medium text-gray-600 mb-1">Confirmer le PIN</label><input type="password" name="pin_confirmation" maxlength="4" inputmode="numeric" class="input-field" placeholder="••••"></div>
                    </div>
                </div>
                <button type="submit" class="btn-primary w-full py-3">Enregistrer</button>
            </form>
        </div>
        <div class="card space-y-4">
            <h2 class="text-sm font-semibold text-gray-800">Abonnement</h2>
            @if ($abonnement)
                <div class="rounded-xl border border-green-200 bg-green-50 p-4">
                    <p class="text-xs text-green-800 uppercase font-semibold">Plan actuel</p>
                    <p class="text-lg font-bold text-green-900 mt-1">{{ ucfirst($abonnement->plan) }}</p>
                    <p class="text-sm text-gray-700 mt-2">Jusqu'au {{ $abonnement->date_fin?->format('d/m/Y') ?? '—' }}</p>
                </div>
            @else
                <p class="text-sm text-gray-600">Aucun abonnement actif listé.</p>
            @endif
            <a href="{{ route('abonnement') }}" class="btn-outline w-full inline-block text-center py-3">Gérer mon abonnement →</a>
        </div>
    </div>
@endif

@push('scripts')
<script>
(function () {
    var KEY = 'af_outdoor_boost';
    function apply(v) {
        document.documentElement.classList.toggle('af-outdoor', v);
    }
    var boxes = document.querySelectorAll('.js-af-outdoor-toggle');
    var stored = localStorage.getItem(KEY) === '1';
    apply(stored);
    boxes.forEach(function (cb) {
        cb.checked = stored;
        cb.addEventListener('change', function () {
            var on = cb.checked;
            if (on) {
                localStorage.setItem(KEY, '1');
            } else {
                localStorage.removeItem(KEY);
            }
            apply(on);
            boxes.forEach(function (x) {
                if (x !== cb) {
                    x.checked = on;
                }
            });
        });
    });
})();
</script>
@endpush

@endsection
