@extends('layouts.app-desktop')
@section('title', 'Mon profil — AgroFinance+')
@section('page-title', 'Mon profil')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="card space-y-6">
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
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Prénom</label>
                        <input name="prenom" value="{{ old('prenom', $user->prenom) }}" required class="input-field">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nom</label>
                        <input name="nom" value="{{ old('nom', $user->nom) }}" required class="input-field">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Téléphone</label>
                    <input value="{{ $user->telephone }}" disabled class="input-field bg-gray-50 text-gray-500">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Type d’exploitation</label>
                    <select name="type_exploitation" class="input-field">
                        @foreach (['cultures_vivrieres' => 'Cultures vivrières', 'elevage' => 'Élevage', 'maraichage' => 'Maraîchage', 'transformation' => 'Transformation', 'mixte' => 'Mixte'] as $val => $lab)
                            <option value="{{ $val }}" @selected(old('type_exploitation', $user->type_exploitation) === $val)>{{ $lab }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Département</label>
                        <input name="departement" value="{{ old('departement', $user->departement) }}" class="input-field">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Commune</label>
                        <input name="commune" value="{{ old('commune', $user->commune) }}" class="input-field">
                    </div>
                </div>

                <hr class="border-gray-100">

                <div>
                    <p class="text-sm font-semibold text-gray-800 mb-3">Changer le PIN</p>
                    <p class="text-xs text-gray-500 mb-3">Laissez vide pour ne pas modifier. 4 chiffres.</p>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">PIN actuel</label>
                            <input type="password" name="pin_actuel" maxlength="4" inputmode="numeric" autocomplete="current-password" class="input-field" placeholder="••••">
                            @error('pin_actuel') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Nouveau PIN</label>
                            <input type="password" name="pin" maxlength="4" inputmode="numeric" class="input-field" placeholder="••••">
                            @error('pin') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Confirmer le PIN</label>
                            <input type="password" name="pin_confirmation" maxlength="4" inputmode="numeric" class="input-field" placeholder="••••">
                        </div>
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
                    <p class="text-sm text-gray-700 mt-2">Jusqu’au {{ $abonnement->date_fin?->format('d/m/Y') ?? '—' }}</p>
                </div>
            @else
                <p class="text-sm text-gray-600">Aucun abonnement actif listé.</p>
            @endif

            <a href="{{ route('abonnement') }}" class="btn-outline w-full inline-block text-center py-3">Gérer mon abonnement →</a>
        </div>
    </div>
@endsection
