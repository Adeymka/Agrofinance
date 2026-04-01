<?php

namespace App\Services;

use App\Models\Cooperative;
use App\Models\CooperativeAuditLog;
use App\Models\CooperativeMember;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CooperativeService
{
    public function cooperativeFor(User $actor): ?Cooperative
    {
        $owned = Cooperative::query()->where('owner_user_id', $actor->id)->first();
        if ($owned) {
            return $owned;
        }

        $membership = CooperativeMember::query()
            ->where('user_id', $actor->id)
            ->where('statut', CooperativeMember::STATUT_ACTIVE)
            ->with('cooperative')
            ->first();

        return $membership?->cooperative;
    }

    public function resolveOwner(User $actor): User
    {
        $coop = $this->cooperativeFor($actor);
        if (! $coop) {
            return $actor;
        }

        return $coop->owner ?? $actor;
    }

    public function roleFor(User $actor): ?string
    {
        $owned = Cooperative::query()->where('owner_user_id', $actor->id)->exists();
        if ($owned) {
            return CooperativeMember::ROLE_ADMIN;
        }

        $membership = CooperativeMember::query()
            ->where('user_id', $actor->id)
            ->where('statut', CooperativeMember::STATUT_ACTIVE)
            ->first();

        return $membership?->role;
    }

    public function canValidateTransactions(User $actor): bool
    {
        return in_array($this->roleFor($actor), [CooperativeMember::ROLE_ADMIN, CooperativeMember::ROLE_VALIDATEUR], true);
    }

    public function canManageMembers(User $actor): bool
    {
        return $this->roleFor($actor) === CooperativeMember::ROLE_ADMIN;
    }

    public function canManageSettings(User $actor): bool
    {
        return $this->roleFor($actor) === CooperativeMember::ROLE_ADMIN;
    }

    public function canViewAudit(User $actor): bool
    {
        return in_array($this->roleFor($actor), [CooperativeMember::ROLE_ADMIN, CooperativeMember::ROLE_VALIDATEUR], true);
    }

    public function canExportEntreprise(User $actor): bool
    {
        return in_array($this->roleFor($actor), [CooperativeMember::ROLE_ADMIN, CooperativeMember::ROLE_VALIDATEUR], true);
    }

    public function thresholdForDoubleValidation(User $actor): float
    {
        $coop = $this->cooperativeFor($actor);

        return (float) ($coop?->double_validation_threshold ?? 100000);
    }

    public function requiresDoubleValidation(Transaction $transaction, User $actor): bool
    {
        return (float) $transaction->montant >= $this->thresholdForDoubleValidation($actor);
    }

    public function ensureOwnedCooperative(User $owner): Cooperative
    {
        return Cooperative::query()->firstOrCreate(
            ['owner_user_id' => $owner->id],
            ['nom' => 'Coopérative '.$owner->prenom, 'double_validation_threshold' => 100000]
        );
    }

    public function inviteMember(User $owner, User $actor, string $phone, string $role): CooperativeMember
    {
        $coop = $this->ensureOwnedCooperative($owner);
        $invitedUser = User::query()->where('telephone', $phone)->first();
        $invitationToken = Str::uuid()->toString().Str::random(16);
        $invitationExpiresAt = now()->addDays(7);

        $payload = [
            'cooperative_id' => $coop->id,
            'invited_phone' => $phone,
            'invitation_token' => $invitationToken,
            'invitation_expires_at' => $invitationExpiresAt,
            'role' => $role,
            'invited_by_user_id' => $actor->id,
        ];

        if ($invitedUser) {
            $payload['user_id'] = $invitedUser->id;
            $payload['statut'] = CooperativeMember::STATUT_ACTIVE;
            $payload['joined_at'] = now();
            $payload['accepted_at'] = now();
        } else {
            $payload['user_id'] = null;
            $payload['statut'] = CooperativeMember::STATUT_INVITED;
            $payload['joined_at'] = null;
            $payload['accepted_at'] = null;
        }

        $member = DB::transaction(function () use ($payload, $coop, $invitedUser) {
            if ($invitedUser) {
                return CooperativeMember::query()->updateOrCreate(
                    ['cooperative_id' => $coop->id, 'user_id' => $invitedUser->id],
                    $payload
                );
            }

            return CooperativeMember::query()->updateOrCreate(
                ['cooperative_id' => $coop->id, 'invited_phone' => $payload['invited_phone']],
                $payload
            );
        });

        $this->log(
            $coop,
            $actor,
            'member.invited',
            [
                'role' => $role,
                'invited_phone' => $phone,
                'linked_user_id' => $invitedUser?->id,
                'invitation_token' => $member->invitation_token,
                'invitation_expires_at' => $member->invitation_expires_at?->toIso8601String(),
            ],
            $member->user_id
        );

        return $member;
    }

    public function findInvitationByToken(string $token): ?CooperativeMember
    {
        return CooperativeMember::query()
            ->where('invitation_token', $token)
            ->with(['cooperative.owner:id,nom,prenom,telephone'])
            ->first();
    }

    public function acceptInvitation(User $actor, CooperativeMember $member): bool
    {
        if ($member->statut !== CooperativeMember::STATUT_INVITED) {
            return false;
        }
        if ($member->invitation_expires_at && now()->greaterThan($member->invitation_expires_at)) {
            return false;
        }
        if ($member->invited_phone && $member->invited_phone !== $actor->telephone) {
            return false;
        }

        $member->update([
            'user_id' => $actor->id,
            'statut' => CooperativeMember::STATUT_ACTIVE,
            'joined_at' => now(),
            'accepted_at' => now(),
        ]);

        $this->log(
            $member->cooperative,
            $actor,
            'member.invitation_accepted',
            ['member_id' => $member->id, 'role' => $member->role],
            $actor->id
        );

        return true;
    }

    public function log(
        Cooperative $coop,
        ?User $actor,
        string $action,
        array $meta = [],
        ?int $memberUserId = null,
        ?int $transactionId = null
    ): void {
        CooperativeAuditLog::query()->create([
            'cooperative_id' => $coop->id,
            'actor_user_id' => $actor?->id,
            'member_user_id' => $memberUserId,
            'transaction_id' => $transactionId,
            'action' => $action,
            'meta' => $meta,
        ]);
    }
}
