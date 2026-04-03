<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AbonnementService;
use App\Services\CooperativeInvitationService;
use App\Services\CooperativeService;
use Illuminate\Http\Request;

/**
 * Parité mobile / API avec le module coopérative web (membres, invitation).
 */
class CooperativeController extends Controller
{
    public function __construct(
        private AbonnementService $abonnementService,
        private CooperativeService $cooperativeService,
        private CooperativeInvitationService $cooperativeInvitationService
    ) {}

    public function membres(Request $request)
    {
        $actor = auth()->user();
        $owner = $this->cooperativeService->resolveOwner($actor);

        if (! $this->abonnementService->estPlanCooperatif($owner)) {
            return response()->json([
                'succes' => false,
                'message' => 'Fonction réservée au plan Coopérative.',
            ], 403);
        }

        $coop = $this->cooperativeService->ensureOwnedCooperative($owner);
        $members = $coop->members()
            ->with('user:id,nom,prenom,telephone')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'succes' => true,
            'data' => [
                'cooperative_id' => $coop->id,
                'double_validation_threshold' => $coop->double_validation_threshold,
                'members' => $members,
                'my_role' => $this->cooperativeService->roleFor($actor),
                'can_manage_members' => $this->cooperativeService->canManageMembers($actor),
                'can_manage_settings' => $this->cooperativeService->canManageSettings($actor),
            ],
        ]);
    }

    public function inviter(Request $request)
    {
        $actor = auth()->user();
        $owner = $this->cooperativeService->resolveOwner($actor);

        if (! $this->abonnementService->estPlanCooperatif($owner) || ! $this->cooperativeService->canManageMembers($actor)) {
            return response()->json([
                'succes' => false,
                'message' => 'Action non autorisée.',
            ], 403);
        }

        $request->validate([
            'telephone' => 'required|string|max:20',
            'role' => 'required|in:admin,validateur,saisie,lecture',
        ]);

        $phone = trim((string) $request->input('telephone'));
        $role = (string) $request->input('role');

        $member = $this->cooperativeService->inviteMember($owner, $actor, $phone, $role);
        $result = $this->cooperativeInvitationService->sendInvitation($member->fresh(['user', 'cooperative.owner']));
        $acceptPath = route('cooperative.invitation.show', ['token' => $member->invitation_token], absolute: true);

        return response()->json([
            'succes' => true,
            'message' => $result['sent']
                ? 'Invitation envoyée via '.$result['channel'].'.'
                : 'Invitation créée ; envoi automatique non disponible.',
            'data' => [
                'member' => $member->fresh(['user']),
                'invitation_sent' => $result['sent'],
                'channel' => $result['channel'] ?? null,
                'accept_url' => $acceptPath,
            ],
        ], 201);
    }
}
