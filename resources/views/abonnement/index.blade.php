@extends($layout)
@section('title', 'Abonnement — AgroFinance+')
@section('page-title', 'Mon abonnement')

@section('content')

@if($platform === 'mobile')

@push('styles')
<style>
/* Abonnement mobile — tokens --af-* (app.css) */
.abo-m-current {
    background: var(--af-glass-05);
    border: 1px solid var(--af-green-icon-border);
    border-radius: var(--af-radius-lg);
    padding: 18px;
    margin-bottom: 20px;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}
.abo-m-current-label {
    font-family: var(--font-ui), sans-serif;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--af-color-accent);
    opacity: 0.85;
    margin-bottom: 6px;
}
.abo-m-current-plan {
    font-family: var(--font-display), sans-serif;
    font-size: 22px;
    font-weight: 800;
    color: var(--af-text-primary);
    letter-spacing: -0.03em;
    margin-bottom: 6px;
}
.abo-m-current-date {
    font-family: var(--font-ui), sans-serif;
    font-size: 13px;
    color: var(--af-text-muted);
}
.abo-m-empty {
    font-family: var(--font-ui), sans-serif;
    font-size: 14px;
    color: var(--af-text-dim);
    margin-bottom: 20px;
}
.abo-m-h2 {
    font-family: var(--font-display), sans-serif;
    font-size: 15px;
    font-weight: 700;
    color: var(--af-text-heading-soft);
    letter-spacing: -0.02em;
    margin-bottom: 14px;
}
.abo-m-card {
    position: relative;
    background: var(--af-glass-05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--af-radius-lg);
    padding: 18px;
    margin-bottom: 12px;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}
.abo-m-card--featured {
    border-color: var(--af-filter-active-border);
    box-shadow: 0 0 0 1px rgba(74, 222, 128, 0.12);
}
.abo-m-popular {
    position: absolute;
    top: -9px;
    right: 12px;
    font-family: var(--font-ui), sans-serif;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    padding: 4px 10px;
    border-radius: 999px;
    background: var(--af-filter-active-bg);
    border: 1px solid var(--af-filter-active-border);
    color: var(--af-color-accent);
}
.abo-m-card--pro {
    border-color: var(--af-amber-tint-border);
    background: var(--af-amber-tint-bg);
}
.abo-m-card--coop {
    border-color: rgba(167, 139, 250, 0.35);
    background: rgba(139, 92, 246, 0.08);
}
.abo-m-badge {
    display: inline-block;
    font-family: var(--font-ui), sans-serif;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    padding: 4px 12px;
    border-radius: 999px;
    margin-bottom: 10px;
}
.abo-m-badge--muted {
    background: var(--af-glass-08);
    border: 1px solid rgba(255, 255, 255, 0.12);
    color: var(--af-text-muted);
}
.abo-m-badge--accent {
    background: var(--af-green-tint-bg);
    border: 1px solid var(--af-green-icon-border);
    color: var(--af-color-accent);
}
.abo-m-badge--amber {
    background: var(--af-amber-alert-bg);
    border: 1px solid var(--af-amber-alert-border);
    color: var(--af-color-warning);
}
.abo-m-badge--violet {
    background: rgba(139, 92, 246, 0.15);
    border: 1px solid rgba(167, 139, 250, 0.35);
    color: #c4b5fd;
}
.abo-m-price {
    font-family: var(--font-display), sans-serif;
    font-size: 26px;
    font-weight: 800;
    color: var(--af-text-primary);
    letter-spacing: -0.03em;
    margin-bottom: 2px;
}
.abo-m-price span {
    font-size: 13px;
    font-weight: 600;
    color: var(--af-text-muted);
}
.abo-m-list {
    list-style: none;
    margin: 14px 0 0;
    padding: 0;
}
.abo-m-list li {
    font-family: var(--font-ui), sans-serif;
    font-size: 13px;
    color: var(--af-text-secondary);
    padding: 6px 0;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
}
.abo-m-list li:first-child { border-top: none; padding-top: 0; }
.abo-m-note {
    font-family: var(--font-ui), sans-serif;
    font-size: 11px;
    color: var(--af-text-caption);
    margin-top: 12px;
}
.abo-m-btn {
    width: 100%;
    margin-top: 16px;
    padding: 14px;
    border-radius: 14px;
    font-family: var(--font-ui), sans-serif;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    border: 1px solid transparent;
    transition: opacity 0.15s;
}
.abo-m-btn:active { opacity: 0.88; }
.abo-m-btn--primary {
    background: var(--af-color-accent-dark);
    color: #fff;
    border-color: var(--af-tx-type-rec-border);
}
.abo-m-btn--amber {
    background: transparent;
    color: var(--af-color-warning);
    border-color: var(--af-amber-alert-border);
}
.abo-m-btn--violet {
    background: rgba(139, 92, 246, 0.12);
    color: #ddd6fe;
    border-color: rgba(167, 139, 250, 0.45);
}
.abo-m-fedapay {
    margin-top: 8px;
    padding: 16px;
    background: var(--af-glass-06);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: var(--af-radius-lg);
}
.abo-m-fedapay-title {
    font-family: var(--font-ui), sans-serif;
    font-size: 13px;
    font-weight: 700;
    color: var(--af-text-body-strong);
    margin-bottom: 6px;
}
.abo-m-fedapay-text {
    font-family: var(--font-ui), sans-serif;
    font-size: 12px;
    color: var(--af-text-muted);
    line-height: 1.5;
    margin-bottom: 0;
}
.abo-m-mock {
    margin-top: 14px;
    padding-top: 14px;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
}
.abo-m-mock-hint {
    font-family: var(--font-ui), sans-serif;
    font-size: 11px;
    color: var(--af-color-warning);
    margin-bottom: 10px;
    opacity: 0.95;
}
.abo-m-btn--mock {
    background: var(--af-amber-tint-bg);
    color: var(--af-color-warning);
    border-color: var(--af-amber-tint-border);
    margin-top: 0;
}
</style>
@endpush

<div>

    @if ($abonnement)
        <div class="abo-m-current">
            <p class="abo-m-current-label">Votre plan</p>
            <p class="abo-m-current-plan">{{ ucfirst($abonnement->plan) }}</p>
            <p class="abo-m-current-date">Valide jusqu’au <strong>{{ $abonnement->date_fin?->format('d/m/Y') ?? '—' }}</strong></p>
        </div>
    @else
        <p class="abo-m-empty">Aucun abonnement actif.</p>
    @endif

    <h2 class="abo-m-h2">Offres</h2>

    {{-- Gratuit --}}
    <div class="abo-m-card">
        <span class="abo-m-badge abo-m-badge--muted">Gratuit</span>
        <p class="abo-m-price">0 <span>FCFA / mois</span></p>
        <ul class="abo-m-list">
            <li>• Tableau de bord</li>
            <li>• Saisie des transactions</li>
            <li>• Historique limité (6 mois)</li>
        </ul>
        <p class="abo-m-note">Plan par défaut à l’inscription (essai / gratuit).</p>
    </div>

    {{-- Essentielle — populaire --}}
    <div class="abo-m-card abo-m-card--featured">
        <span class="abo-m-popular">Populaire</span>
        <span class="abo-m-badge abo-m-badge--accent">Essentielle</span>
        <p class="abo-m-price">1 500 <span>FCFA / mois</span></p>
        <ul class="abo-m-list">
            <li>• Tout le gratuit</li>
            <li>• Rapports PDF avancés</li>
            <li>• 1 exploitation</li>
        </ul>
        <form method="POST" action="{{ route('abonnement.initier') }}">
            @csrf
            <input type="hidden" name="plan" value="mensuel">
            <input type="hidden" name="telephone" value="{{ $user->telephone }}">
            <button type="submit" class="abo-m-btn abo-m-btn--primary">Choisir Essentielle</button>
        </form>
    </div>

    {{-- Pro --}}
    <div class="abo-m-card abo-m-card--pro">
        <span class="abo-m-badge abo-m-badge--amber">Pro</span>
        <p class="abo-m-price">5 000 <span>FCFA / mois</span></p>
        <ul class="abo-m-list">
            <li>• Tout Essentielle</li>
            <li>• Jusqu’à 5 exploitations</li>
            <li>• Export dossier crédit</li>
        </ul>
        <form method="POST" action="{{ route('abonnement.initier') }}">
            @csrf
            <input type="hidden" name="plan" value="annuel">
            <input type="hidden" name="telephone" value="{{ $user->telephone }}">
            <button type="submit" class="abo-m-btn abo-m-btn--amber">Choisir Pro</button>
        </form>
    </div>

    {{-- Coopérative --}}
    <div class="abo-m-card abo-m-card--coop">
        <span class="abo-m-badge abo-m-badge--violet">Coopérative</span>
        <p class="abo-m-price">8 000 <span>FCFA / mois</span></p>
        <ul class="abo-m-list">
            <li>• Tout Pro</li>
            <li>• Exploitations illimitées</li>
            <li>• Idéal groupes &amp; coopératives</li>
        </ul>
        <form method="POST" action="{{ route('abonnement.initier') }}">
            @csrf
            <input type="hidden" name="plan" value="cooperative">
            <input type="hidden" name="telephone" value="{{ $user->telephone }}">
            <button type="submit" class="abo-m-btn abo-m-btn--violet">Choisir Coopérative</button>
        </form>
    </div>

    <div class="abo-m-fedapay">
        <p class="abo-m-fedapay-title">Paiement FedaPay</p>
        <p class="abo-m-fedapay-text">Le numéro utilisé est pré-rempli avec votre compte. Vous pouvez le modifier avant de lancer le paiement depuis les boutons ci-dessus.</p>

        @if (config('services.fedapay.mock'))
            <div class="abo-m-mock">
                <p class="abo-m-mock-hint">Mode simulation : après « Choisir… », confirmez ici.</p>
                <form method="POST" action="{{ route('abonnement.finaliser-mock') }}">
                    @csrf
                    <button type="submit" class="abo-m-btn abo-m-btn--mock">Confirmer la simulation (mock)</button>
                </form>
            </div>
        @endif
    </div>
</div>

@else

{{-- ════ Desktop (inchangé) ════ --}}
    @if ($abonnement)
        <div class="card mb-8 border-2 border-agro-vert/30 bg-gradient-to-br from-green-50 to-white">
            <p class="text-xs font-semibold text-agro-vert uppercase tracking-wide">Votre plan</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ ucfirst($abonnement->plan) }}</p>
            <p class="text-sm text-gray-600 mt-2">Valide jusqu’au <strong>{{ $abonnement->date_fin?->format('d/m/Y') ?? '—' }}</strong></p>
        </div>
    @else
        <p class="text-sm text-gray-600 mb-6">Aucun abonnement actif.</p>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 mb-10">
        <div class="card border-2 border-gray-200">
            <p class="badge-gris mb-2">Gratuit</p>
            <p class="text-2xl font-bold text-gray-900">0 FCFA</p>
            <ul class="mt-4 space-y-2 text-sm text-gray-600">
                <li>• Tableau de bord</li>
                <li>• Saisie des transactions</li>
                <li>• Historique limité (6 mois)</li>
            </ul>
            <p class="text-xs text-gray-400 mt-4">Plan par défaut à l’inscription (essai / gratuit).</p>
        </div>

        <div class="card border-2 border-agro-vert ring-2 ring-agro-vert/20 relative">
            <span class="absolute -top-2 right-3 badge-vert text-[10px]">Populaire</span>
            <p class="badge-vert mb-2">Essentielle</p>
            <p class="text-2xl font-bold text-gray-900">1 500 <span class="text-sm font-normal">FCFA / mois</span></p>
            <ul class="mt-4 space-y-2 text-sm text-gray-600">
                <li>• Tout le gratuit</li>
                <li>• Rapports PDF avancés</li>
                <li>• 1 exploitation</li>
            </ul>
            <form method="POST" action="{{ route('abonnement.initier') }}" class="mt-6">
                @csrf
                <input type="hidden" name="plan" value="mensuel">
                <input type="hidden" name="telephone" value="{{ $user->telephone }}">
                <button type="submit" class="btn-primary w-full py-3">Choisir ce plan</button>
            </form>
        </div>

        <div class="card border-2 border-amber-200 bg-amber-50/30">
            <p class="badge-orange mb-2">Pro</p>
            <p class="text-2xl font-bold text-gray-900">5 000 <span class="text-sm font-normal">FCFA / mois</span></p>
            <ul class="mt-4 space-y-2 text-sm text-gray-600">
                <li>• Tout Essentielle</li>
                <li>• Jusqu’à 5 exploitations</li>
                <li>• Export dossier crédit</li>
            </ul>
            <form method="POST" action="{{ route('abonnement.initier') }}" class="mt-6">
                @csrf
                <input type="hidden" name="plan" value="annuel">
                <input type="hidden" name="telephone" value="{{ $user->telephone }}">
                <button type="submit" class="btn-outline w-full py-3 border-amber-400 bg-white hover:bg-amber-50">Choisir ce plan</button>
            </form>
        </div>

        <div class="card border-2 border-violet-200 bg-violet-50/40">
            <p class="mb-2 inline-block rounded-full bg-violet-100 px-2 py-0.5 text-xs font-semibold text-violet-800">Coopérative</p>
            <p class="text-2xl font-bold text-gray-900">8 000 <span class="text-sm font-normal">FCFA / mois</span></p>
            <ul class="mt-4 space-y-2 text-sm text-gray-600">
                <li>• Tout Pro</li>
                <li>• Exploitations illimitées</li>
                <li>• Idéal groupes &amp; coopératives</li>
            </ul>
            <form method="POST" action="{{ route('abonnement.initier') }}" class="mt-6">
                @csrf
                <input type="hidden" name="plan" value="cooperative">
                <input type="hidden" name="telephone" value="{{ $user->telephone }}">
                <button type="submit" class="w-full rounded-xl border-2 border-violet-400 bg-white py-3 text-sm font-semibold text-violet-900 hover:bg-violet-50">
                    Choisir ce plan
                </button>
            </form>
        </div>
    </div>

    <div class="card max-w-xl">
        <p class="text-sm font-semibold text-gray-800 mb-2">Paiement FedaPay</p>
        <p class="text-xs text-gray-500 mb-4">Le numéro utilisé est pré-rempli avec votre compte. Vous pouvez le modifier avant de lancer le paiement depuis les boutons ci-dessus.</p>

        @if (config('services.fedapay.mock'))
            <form method="POST" action="{{ route('abonnement.finaliser-mock') }}" class="pt-4 border-t border-gray-100">
                @csrf
                <p class="text-xs text-amber-800 mb-3">Mode simulation : après « Choisir ce plan », confirmez ici.</p>
                <button type="submit" class="w-full rounded-xl border border-amber-400 text-amber-900 py-2 text-sm font-medium">
                    Confirmer la simulation (mock)
                </button>
            </form>
        @endif
    </div>

@endif

@endsection
