@extends($layout)
@section('title', 'Membres coopérative — AgroFinance+')
@section('page-title', 'Membres coopérative')
@section('page-subtitle', $cooperative->nom ?? ('Coop #'.$cooperative->id))

@section('content')
<div class="space-y-6">
    <div class="card p-4">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
            <h2 class="text-sm font-semibold text-gray-800">Paramètres de validation</h2>
            <span class="text-xs text-gray-500">Rôle actuel : {{ $myRole ?? '—' }}</span>
        </div>
        <p class="text-sm text-gray-600">
            Double validation active à partir de
            <strong>{{ number_format((float) $cooperative->double_validation_threshold, 0, ',', ' ') }} FCFA</strong>.
        </p>
    </div>

    @if($canManageMembers)
        <div class="card p-4">
            <h2 class="text-sm font-semibold text-gray-800 mb-3">Inviter un membre</h2>
            <form method="POST" action="{{ route('cooperative.members.invite') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                @csrf
                <input type="text" name="telephone" class="input-field" placeholder="Téléphone (ex. +229XXXXXXXX)" required>
                <select name="role" class="input-field" required>
                    @foreach($roles as $role)
                        <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary">Inviter</button>
            </form>
        </div>
    @endif

    <div class="card p-4">
        <h2 class="text-sm font-semibold text-gray-800 mb-3">Liste des membres</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-left px-2 py-2">Membre</th>
                        <th class="text-left px-2 py-2">Téléphone</th>
                        <th class="text-left px-2 py-2">Rôle</th>
                        <th class="text-left px-2 py-2">Statut</th>
                        <th class="text-right px-2 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($members as $m)
                        <tr class="border-b border-gray-100">
                            <td class="px-2 py-2">{{ $m->user ? ($m->user->prenom.' '.$m->user->nom) : 'Invitation en attente' }}</td>
                            <td class="px-2 py-2">{{ $m->user?->telephone ?? $m->invited_phone ?? '—' }}</td>
                            <td class="px-2 py-2">{{ ucfirst($m->role) }}</td>
                            <td class="px-2 py-2">{{ $m->statut }}</td>
                            <td class="px-2 py-2 text-right">
                                @if($canManageMembers)
                                    <form method="POST" action="{{ route('cooperative.members.role', $m->id) }}" class="inline-flex gap-2 items-center">
                                        @csrf
                                        <select name="role" class="input-field py-1 text-xs min-w-[120px]">
                                            @foreach($roles as $role)
                                                <option value="{{ $role }}" @selected($m->role === $role)>{{ ucfirst($role) }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn-outline text-xs px-2 py-1">Rôle</button>
                                    </form>
                                    <form method="POST" action="{{ route('cooperative.members.status', $m->id) }}" class="inline-flex ml-2">
                                        @csrf
                                        <input type="hidden" name="statut" value="{{ $m->statut === 'active' ? 'inactive' : 'active' }}">
                                        <button type="submit" class="btn-outline text-xs px-2 py-1">
                                            {{ $m->statut === 'active' ? 'Désactiver' : 'Activer' }}
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-500">Lecture seule</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-2 py-6 text-center text-gray-500">Aucun membre pour le moment.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card p-4">
        <h2 class="text-sm font-semibold text-gray-800 mb-3">Audit coopérative (50 derniers événements)</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-left px-2 py-2">Date</th>
                        <th class="text-left px-2 py-2">Action</th>
                        <th class="text-left px-2 py-2">Acteur</th>
                        <th class="text-left px-2 py-2">Détails</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($audits as $a)
                        <tr class="border-b border-gray-100">
                            <td class="px-2 py-2 whitespace-nowrap">{{ $a->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="px-2 py-2">{{ $a->action }}</td>
                            <td class="px-2 py-2">{{ $a->actor ? ($a->actor->prenom.' '.$a->actor->nom) : 'Système' }}</td>
                            <td class="px-2 py-2 text-xs">{{ json_encode($a->meta ?? [], JSON_UNESCAPED_UNICODE) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-2 py-6 text-center text-gray-500">Aucun événement d’audit.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
