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
        @if($canManageSettings ?? false)
            <form method="POST" action="{{ route('cooperative.threshold.update') }}" class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-3">
                @csrf
                <input type="number" step="1" min="1" max="1000000000" name="double_validation_threshold" class="input-field" value="{{ (int) $cooperative->double_validation_threshold }}" required>
                <input
                    type="text"
                    name="categories_always_double"
                    class="input-field md:col-span-2"
                    placeholder="Catégories toujours en double validation (ex: engrais,main_oeuvre)"
                    value="{{ implode(',', (array) (($cooperative->validation_rules['categories_always_double'] ?? []))) }}"
                >
                <select name="period_rule" class="input-field">
                    @php($periodRule = (string) (($cooperative->validation_rules['period_rule'] ?? 'none')))
                    <option value="none" @selected($periodRule === 'none')>Période: aucune</option>
                    <option value="month_start" @selected($periodRule === 'month_start')>Période: début du mois (1-5)</option>
                    <option value="month_end" @selected($periodRule === 'month_end')>Période: fin du mois (5 derniers jours)</option>
                    <option value="month_start_end" @selected($periodRule === 'month_start_end')>Période: début + fin du mois</option>
                    <option value="weekend" @selected($periodRule === 'weekend')>Période: week-end</option>
                </select>
                <div class="md:col-span-2 text-xs text-gray-500">
                    Saisir les catégories avec des virgules. Si une règle correspond (montant, catégorie ou période), la double validation est exigée.
                </div>
                <div class="md:col-span-3">
                    <button type="submit" class="btn-outline">Mettre à jour les règles</button>
                </div>
            </form>
        @endif
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
                                    @if($m->statut === 'invited')
                                        <form method="POST" action="{{ route('cooperative.members.invitation.rotate', $m->id) }}" class="inline-flex ml-2">
                                            @csrf
                                            <button type="submit" class="btn-outline text-xs px-2 py-1">
                                                Régénérer le lien
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('cooperative.members.invitation.revoke', $m->id) }}" class="inline-flex ml-2">
                                            @csrf
                                            <button type="submit" class="btn-outline text-xs px-2 py-1">
                                                Révoquer
                                            </button>
                                        </form>
                                    @endif
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

    @if($canViewAudit ?? false)
        <div class="card p-4">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                <h2 class="text-sm font-semibold text-gray-800">Audit coopérative (paginé)</h2>
                <a
                    href="{{ route('cooperative.audit.export.csv', ['action' => $auditFilters['action'] ?? '', 'member_user_id' => $auditFilters['member_user_id'] ?? 0, 'date_debut' => $auditFilters['date_debut'] ?? '', 'date_fin' => $auditFilters['date_fin'] ?? '']) }}"
                    class="btn-outline text-xs px-3 py-1.5"
                >Exporter CSV</a>
            </div>

            <form method="GET" action="{{ route('cooperative.members') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3 mb-4">
                <select name="action" class="input-field">
                    <option value="">Toutes les actions</option>
                    @foreach(($auditActions ?? collect()) as $act)
                        <option value="{{ $act }}" @selected(($auditFilters['action'] ?? '') === $act)>{{ $act }}</option>
                    @endforeach
                </select>
                <select name="member_user_id" class="input-field">
                    <option value="0">Tous les membres</option>
                    @foreach($members as $m)
                        @if($m->user)
                            <option value="{{ $m->user->id }}" @selected((int) ($auditFilters['member_user_id'] ?? 0) === (int) $m->user->id)>
                                {{ $m->user->prenom }} {{ $m->user->nom }}
                            </option>
                        @endif
                    @endforeach
                </select>
                <input type="date" name="date_debut" class="input-field" value="{{ $auditFilters['date_debut'] ?? '' }}">
                <input type="date" name="date_fin" class="input-field" value="{{ $auditFilters['date_fin'] ?? '' }}">
                <button type="submit" class="btn-outline">Filtrer</button>
            </form>

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
            @if(is_object($audits) && method_exists($audits, 'links'))
                <div class="mt-4">
                    {{ $audits->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
