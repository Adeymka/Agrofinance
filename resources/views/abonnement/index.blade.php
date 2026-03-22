@extends('layouts.app-desktop')
@section('title', 'Abonnement — AgroFinance+')
@section('page-title', 'Mon abonnement')

@section('content')
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
@endsection
