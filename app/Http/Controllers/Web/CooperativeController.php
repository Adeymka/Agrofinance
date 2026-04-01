<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CooperativeMember;
use App\Models\User;
use App\Services\AbonnementService;
use App\Services\CooperativeService;
use App\Services\CooperativeInvitationService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CooperativeController extends Controller
{
    public function __construct(
        private AbonnementService $abonnementService,
        private CooperativeService $cooperativeService,
        private CooperativeInvitationService $cooperativeInvitationService
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
        $action = (string) request()->query('action', '');
        $memberUserId = (int) request()->query('member_user_id', 0);
        $dateDebut = (string) request()->query('date_debut', '');
        $dateFin = (string) request()->query('date_fin', '');

        $members = $coop->members()
            ->with('user:id,nom,prenom,telephone')
            ->orderByDesc('id')
            ->get();
        $canViewAudit = $this->cooperativeService->canViewAudit($actor);
        $audits = $canViewAudit
            ? $coop->audits()
                ->with(['actor:id,nom,prenom,telephone', 'member:id,nom,prenom,telephone', 'transaction:id,categorie,montant'])
                ->when($action !== '', fn ($q) => $q->where('action', $action))
                ->when($memberUserId > 0, fn ($q) => $q->where('member_user_id', $memberUserId))
                ->when($dateDebut !== '', fn ($q) => $q->whereDate('created_at', '>=', $dateDebut))
                ->when($dateFin !== '', fn ($q) => $q->whereDate('created_at', '<=', $dateFin))
                ->latest('id')
                ->paginate(25)
                ->withQueryString()
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
            'auditFilters' => [
                'action' => $action,
                'member_user_id' => $memberUserId,
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
            ],
            'auditActions' => $canViewAudit
                ? $coop->audits()->select('action')->distinct()->orderBy('action')->pluck('action')->values()
                : collect(),
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

        $member = $this->cooperativeService->inviteMember($owner, $actor, $phone, $role);
        $result = $this->cooperativeInvitationService->sendInvitation($member->fresh(['user', 'cooperative.owner']));
        $acceptUrl = route('cooperative.invitation.show', ['token' => $member->invitation_token]);

        $feedback = $result['sent']
            ? 'Invitation envoyée via '.$result['channel'].'.'
            : 'Invitation créée, envoi automatique non disponible.';

        return redirect()->route('cooperative.members')
            ->with('success', $feedback.' Lien d’acceptation : '.$acceptUrl);
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

    public function rotateInvitation(int $id)
    {
        $actor = auth()->user();
        if (! $this->cooperativeService->canManageMembers($actor)) {
            abort(403);
        }

        $owner = $this->cooperativeService->resolveOwner($actor);
        $coop = $this->cooperativeService->ensureOwnedCooperative($owner);
        $member = $coop->members()->findOrFail($id);

        $member = $this->cooperativeService->rotateInvitationToken($owner, $actor, $member);
        $result = $this->cooperativeInvitationService->sendInvitation($member->fresh(['user', 'cooperative.owner']));

        $feedback = $result['sent']
            ? 'Token régénéré et invitation renvoyée via '.$result['channel'].'.'
            : 'Token régénéré, mais envoi automatique non disponible.';

        return redirect()->route('cooperative.members')->with('success', $feedback);
    }

    public function revokeInvitation(int $id)
    {
        $actor = auth()->user();
        if (! $this->cooperativeService->canManageMembers($actor)) {
            abort(403);
        }

        $owner = $this->cooperativeService->resolveOwner($actor);
        $coop = $this->cooperativeService->ensureOwnedCooperative($owner);
        $member = $coop->members()->findOrFail($id);

        $this->cooperativeService->revokeInvitationToken($owner, $actor, $member);

        return redirect()->route('cooperative.members')->with('success', 'Invitation révoquée. Le lien précédent est invalide.');
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
            'categories_always_double' => 'nullable|string|max:2000',
            'period_rule' => 'nullable|in:none,month_start,month_end,month_start_end,weekend',
        ]);

        $threshold = (float) $request->input('double_validation_threshold');
        $categoriesRaw = (string) $request->input('categories_always_double', '');
        $categories = array_values(array_filter(array_map('trim', explode(',', $categoriesRaw)), fn ($v) => $v !== ''));
        $periodRule = (string) $request->input('period_rule', 'none');

        $this->cooperativeService->updateAdvancedValidationRules(
            $owner,
            $actor,
            $threshold,
            $categories,
            $periodRule
        );

        return redirect()->route('cooperative.members')->with('success', 'Règles de validation mises à jour.');
    }

    public function showInvitation(string $token)
    {
        $actor = auth()->user();
        $invitation = $this->cooperativeService->findInvitationByToken($token);
        if (! $invitation) {
            abort(404);
        }

        $owner = $invitation->cooperative?->owner;
        if (! $owner || ! $this->abonnementService->estPlanCooperatif($owner)) {
            abort(403);
        }

        return view('cooperative.invitation', [
            'invitation' => $invitation,
            'actor' => $actor,
            'isExpired' => $invitation->invitation_expires_at && now()->greaterThan($invitation->invitation_expires_at),
            'isPhoneMismatch' => (string) $actor->telephone !== (string) $invitation->invited_phone,
        ]);
    }

    public function acceptInvitation(string $token)
    {
        $actor = auth()->user();
        $invitation = $this->cooperativeService->findInvitationByToken($token);
        if (! $invitation) {
            abort(404);
        }

        $accepted = $this->cooperativeService->acceptInvitation($actor, $invitation);
        if (! $accepted) {
            return redirect()->route('cooperative.invitation.show', ['token' => $token])
                ->with('error', 'Impossible d’accepter cette invitation (expirée, déjà utilisée ou numéro différent).');
        }

        return redirect()->route('cooperative.members')
            ->with('success', 'Invitation acceptée. Vous êtes désormais membre actif.');
    }

    public function exportAuditCsv(Request $request): StreamedResponse
    {
        $actor = auth()->user();
        if (! $this->cooperativeService->canViewAudit($actor)) {
            abort(403);
        }

        $owner = $this->cooperativeService->resolveOwner($actor);
        $coop = $this->cooperativeService->ensureOwnedCooperative($owner);

        $action = (string) $request->query('action', '');
        $memberUserId = (int) $request->query('member_user_id', 0);
        $dateDebut = (string) $request->query('date_debut', '');
        $dateFin = (string) $request->query('date_fin', '');

        $rows = $coop->audits()
            ->with(['actor:id,nom,prenom,telephone', 'member:id,nom,prenom,telephone'])
            ->when($action !== '', fn ($q) => $q->where('action', $action))
            ->when($memberUserId > 0, fn ($q) => $q->where('member_user_id', $memberUserId))
            ->when($dateDebut !== '', fn ($q) => $q->whereDate('created_at', '>=', $dateDebut))
            ->when($dateFin !== '', fn ($q) => $q->whereDate('created_at', '<=', $dateFin))
            ->latest('id')
            ->limit(5000)
            ->get();

        $filename = 'audit_cooperative_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            if (! $handle) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF");
            fwrite($handle, "sep=;\r\n");
            fputcsv($handle, ['Date', 'Action', 'Acteur', 'Membre cible', 'Meta'], ';');

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->created_at?->format('d/m/Y H:i:s'),
                    $row->action,
                    $row->actor ? trim($row->actor->prenom.' '.$row->actor->nom).' ('.$row->actor->telephone.')' : 'Système',
                    $row->member ? trim($row->member->prenom.' '.$row->member->nom).' ('.$row->member->telephone.')' : '—',
                    json_encode($row->meta ?? [], JSON_UNESCAPED_UNICODE),
                ], ';');
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
