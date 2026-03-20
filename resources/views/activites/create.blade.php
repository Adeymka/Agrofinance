@extends('layouts.app-desktop')
@section('title', 'Nouvelle campagne — AgroFinance+')
@section('page-title', 'Nouvelle campagne')
@section('page-subtitle', $exploitation->nom)

@section('content')
    <div class="max-w-2xl">
        <form method="POST" action="{{ route('activites.store') }}" class="card space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Nom</label>
                <input name="nom" value="{{ old('nom') }}" required placeholder="Ex : Maïs grande saison 2025" class="input-field">
            </div>
            <div>
                <p class="text-xs font-medium text-gray-600 mb-2">Type</p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                    @foreach ([
                        'culture' => 'Culture',
                        'elevage' => 'Élevage',
                        'transformation' => 'Transformation',
                    ] as $v => $label)
                        <label class="flex items-center gap-2 cursor-pointer rounded-xl border border-gray-200 p-3 has-[:checked]:border-agro-vert has-[:checked]:bg-agro-vert-clair">
                            <input type="radio" name="type" value="{{ $v }}" class="text-agro-vert" @checked(old('type') === $v) required>
                            <span class="text-sm font-medium">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Date début</label>
                    <input type="date" name="date_debut" value="{{ old('date_debut', now()->toDateString()) }}" required class="input-field">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Date fin (optionnel)</label>
                    <input type="date" name="date_fin" value="{{ old('date_fin') }}" class="input-field">
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Budget prévisionnel (FCFA)</label>
                <input type="number" step="0.01" min="0" name="budget_previsionnel" value="{{ old('budget_previsionnel') }}" class="input-field">
                <p class="text-[11px] text-gray-500 mt-1">Active les alertes à 70%, 90% et 100% du budget consommé</p>
            </div>
            <button type="submit" class="btn-primary">Démarrer la campagne</button>
        </form>
    </div>
@endsection
