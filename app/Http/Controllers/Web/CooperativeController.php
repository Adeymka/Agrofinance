<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CooperativeMember;
use App\Models\User;
use App\Services\AbonnementService;
use App\Services\CooperativeService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CooperativeController extends Controller
{
    public function __construct(
        private AbonnementService $abonnementService,
        private CooperativeService $cooperativeService
    ) {
    }

    public function members()
    {
        $actor = auth()->user();
        $owner = $this->cooperativeService->resolveOwner($actor);

        if (! $this->abonnementService->estPlanCooperatif($owner)) {
            abort(403, 'Fonction réservée au plan Coopérative.');
        }

        $coop = $this->cooperativeService->ensureOwnedCooperative($owner);
        $members = $coop->members()
            ->with('user:id,nom,prenom,telephone')
            ->orderByDesc('id')
            ->get();
        $canViewAudit = $this->cooperativeService->canViewAudit($actor);
        $audits = $canViewAudit
            ? $coop->audits()
                ->with(['actor:id,nom,prenom,telephone', 'member:id,nom,prenom,telephone', 'transaction:id,categorie,montant'])
                ->latest('id')
                ->limit(50)
                ->get()
            : new Collection();

        return view('cooperative.members', [
            'nav' => 'cooperative',
            'owner' => $owner,
            'cooperative' => $coop,
            'members' => $members,
            'audits' => $audits,
            'canManageMembers' => $this->cooperativeService->canManageMembers($actor),
            'canManageSettings' => $this->cooperativeService->canManageSettings($actor),
            'canViewAudit' => $canViewAudit,
            'myRole' => $this->cooperativeService->roleFor($actor),
            'roles' => [
                CooperativeMember::ROLE_ADMIN,
                CooperativeMember::ROLE_VALIDATEUR,
                CooperativeMember::ROLE_SAISIE,
                CooperativeMember::ROLE_LECTURE,
            ],
        ]);
    }

    public function invite(Request $request)
    {
        $actor = auth()->user();
        $owner = $this->cooperativeService->resolveOwner($actor);

        if (! $this->abonnementService->estPlanCooperatif($owner) || ! $this->cooperativeService->canManageMembers($actor)) {
            abort(403);
        }

        $request->validate([
            'telephone' => 'required|string|max:20',
            'role' => 'required|in:admin,validateur,saisie,lecture',
        ]);

        $phone = trim((string) $request->input('telephone'));
        $role = (string) $request->input('role');

        $this->cooperativeService->inviteMember($owner, $actor, $phone, $role);

        return redirect()->route('cooperative.members')
            ->with('success', 'Invitation enregistrée.');
    }

    public function updateRole(Request $request, int $id)
    {
        $actor = auth()->user();
        if (! $this->cooperativeService->canManageMembers($actor)) {
            abort(403);
        }

        $owner = $this->cooperativeService->resolveOwner($actor);
        $coop = $this->cooperativeService->ensureOwnedCooperative($owner);
        $member = $coop->members()->findOrFail($id);

        $request->validate([
            'role' => 'required|in:admin,validateur,saisie,lecture',
        ]);

        $member->update(['role' => (string) $request->input('role')]);

        $this->cooperativeService->log(
            $coop,
            $actor,
            'member.role_updated',
            ['role' => $member->role, 'member_id' => $member->id],
            $member->user_id
        );

        return redirect()->route('cooperative.members')->with('success', 'Rôle mis à jour.');
    }

    public function toggleStatus(Request $request, int $id)
    {
        $actor = auth()->user();
        if (! $this->cooperativeService->canManageMembers($actor)) {
            abort(403);
        }

        $owner = $this->cooperativeService->resolveOwner($actor);
        $coop = $this->cooperativeService->ensureOwnedCooperative($owner);
        $member = $coop->members()->findOrFail($id);

        $request->validate([
            'statut' => 'required|in:active,inactive',
        ]);
        $statut = (string) $request->input('statut');

        $member->update([
            'statut' => $statut,
            'joined_at' => $statut === CooperativeMember::STATUT_ACTIVE ? ($member->joined_at ?? now()) : $member->joined_at,
        ]);

        $this->cooperativeService->log(
            $coop,
            $actor,
            'member.status_updated',
            ['statut' => $statut, 'member_id' => $member->id],
            $member->user_id
        );

        return redirect()->route('cooperative.members')->with('success', 'Statut mis à jour.');
    }

    public function updateThreshold(Request $request)
    {
        $actor = auth()->user();
        if (! $this->cooperativeService->canManageSettings($actor)) {
            abort(403);
        }

        $owner = $this->cooperativeService->resolveOwner($actor);
        $coop = $this->cooperativeService->ensureOwnedCooperative($owner);

        $request->validate([
            'double_validation_threshold' => 'required|numeric|min:1|max:1000000000',
        ]);

        $threshold = (float) $request->input('double_validation_threshold');
        $coop->update(['double_validation_threshold' => $threshold]);

        $this->cooperativeService->log(
            $coop,
            $actor,
            'cooperative.threshold_updated',
            ['double_validation_threshold' => $threshold]
        );

        return redirect()->route('cooperative.members')->with('success', 'Seuil de double validation mis à jour.');
    }
}
