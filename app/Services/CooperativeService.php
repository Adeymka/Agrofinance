<?php

namespace App\Services;

use App\Models\Cooperative;
use App\Models\CooperativeAuditLog;
use App\Models\CooperativeMember;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
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
        $amountRule = (float) $transaction->montant >= $this->thresholdForDoubleValidation($actor);
        $categoryRule = $this->matchesCategoryRule($transaction, $actor);
        $periodRule = $this->matchesPeriodRule($transaction, $actor);

        return $amountRule || $categoryRule || $periodRule;
    }

    public function ensureOwnedCooperative(User $owner): Cooperative
    {
        return Cooperative::query()->firstOrCreate(
            ['owner_user_id' => $owner->id],
            [
                'nom' => 'Coopérative '.$owner->prenom,
                'double_validation_threshold' => 100000,
                'validation_rules' => [
                    'categories_always_double' => [],
                    'period_rule' => 'none',
                ],
            ]
        );
    }

    public function updateAdvancedValidationRules(
        User $owner,
        User $actor,
        float $threshold,
        array $categoriesAlwaysDouble,
        string $periodRule
    ): Cooperative {
        $coop = $this->ensureOwnedCooperative($owner);
        $normalizedCategories = collect($categoriesAlwaysDouble)
            ->map(fn ($c) => trim((string) $c))
            ->filter(fn ($c) => $c !== '')
            ->unique()
            ->values()
            ->all();

        $allowedPeriodRules = ['none', 'month_start', 'month_end', 'month_start_end', 'weekend'];
        if (! in_array($periodRule, $allowedPeriodRules, true)) {
            $periodRule = 'none';
        }

        $coop->update([
            'double_validation_threshold' => $threshold,
            'validation_rules' => [
                'categories_always_double' => $normalizedCategories,
                'period_rule' => $periodRule,
            ],
        ]);

        $this->log(
            $coop,
            $actor,
            'cooperative.validation_rules_updated',
            [
                'double_validation_threshold' => $threshold,
                'categories_always_double' => $normalizedCategories,
                'period_rule' => $periodRule,
            ]
        );

        return $coop->fresh();
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
                'member_id' => $member->id,
                'role' => $role,
                'invited_phone' => $phone,
                'linked_user_id' => $invitedUser?->id,
                'invitation_expires_at' => $member->invitation_expires_at?->toIso8601String(),
            ],
            $member->user_id
        );

        return $member;
    }

    public function rotateInvitationToken(User $owner, User $actor, CooperativeMember $member): CooperativeMember
    {
        $coop = $this->ensureOwnedCooperative($owner);
        if ((int) $member->cooperative_id !== (int) $coop->id) {
            abort(404);
        }
        if ($member->statut !== CooperativeMember::STATUT_INVITED) {
            abort(422, 'Rotation possible uniquement pour une invitation en attente.');
        }

        $member->update([
            'invitation_token' => Str::uuid()->toString().Str::random(16),
            'invitation_expires_at' => now()->addDays(7),
        ]);

        $this->log(
            $coop,
            $actor,
            'member.invitation_rotated',
            [
                'member_id' => $member->id,
                'invited_phone' => $member->invited_phone,
                'invitation_expires_at' => $member->invitation_expires_at?->toIso8601String(),
            ],
            $member->user_id
        );

        return $member->fresh();
    }

    public function revokeInvitationToken(User $owner, User $actor, CooperativeMember $member): CooperativeMember
    {
        $coop = $this->ensureOwnedCooperative($owner);
        if ((int) $member->cooperative_id !== (int) $coop->id) {
            abort(404);
        }
        if ($member->statut !== CooperativeMember::STATUT_INVITED) {
            abort(422, 'Révocation possible uniquement pour une invitation en attente.');
        }

        $member->update([
            'invitation_token' => null,
            'invitation_expires_at' => now(),
        ]);

        $this->log(
            $coop,
            $actor,
            'member.invitation_revoked',
            [
                'member_id' => $member->id,
                'invited_phone' => $member->invited_phone,
            ],
            $member->user_id
        );

        return $member->fresh();
    }

    public function findInvitationByToken(string $token): ?CooperativeMember
    {
        if (trim($token) === '') {
            return null;
        }

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

    private function matchesCategoryRule(Transaction $transaction, User $actor): bool
    {
        $coop = $this->cooperativeFor($actor);
        $rules = (array) ($coop?->validation_rules ?? []);
        $categories = collect((array) ($rules['categories_always_double'] ?? []))
            ->map(fn ($c) => (string) $c)
            ->filter(fn ($c) => $c !== '')
            ->values()
            ->all();

        if ($categories === []) {
            return false;
        }

        $transactionCategory = (string) $transaction->categorie;
        foreach ($categories as $ruleCategory) {
            if ($this->categoriesEquivalent($ruleCategory, $transactionCategory)) {
                return true;
            }
        }

        return false;
    }

    private function matchesPeriodRule(Transaction $transaction, User $actor): bool
    {
        $coop = $this->cooperativeFor($actor);
        $rules = (array) ($coop?->validation_rules ?? []);
        $periodRule = (string) ($rules['period_rule'] ?? 'none');
        if ($periodRule === 'none') {
            return false;
        }

        $date = Carbon::parse((string) $transaction->date_transaction);
        $day = (int) $date->day;
        $lastDay = (int) $date->copy()->endOfMonth()->day;
        $isWeekend = $date->isWeekend();

        return match ($periodRule) {
            'month_start' => $day <= 5,
            'month_end' => $day >= max(1, $lastDay - 4),
            'month_start_end' => $day <= 5 || $day >= max(1, $lastDay - 4),
            'weekend' => $isWeekend,
            default => false,
        };
    }

    private function normalizeCategory(string $value): string
    {
        $v = Str::of($value)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->value();

        return $v;
    }

    private function categoriesEquivalent(string $ruleCategory, string $transactionCategory): bool
    {
        $ruleBase = $this->normalizeCategory($ruleCategory);
        $txBase = $this->normalizeCategory($transactionCategory);
        if ($ruleBase === $txBase) {
            return true;
        }

        $ruleSingular = $this->singularizeNormalizedCategory($ruleBase);
        $txSingular = $this->singularizeNormalizedCategory($txBase);

        return $ruleSingular === $txSingular;
    }

    private function singularizeNormalizedCategory(string $normalized): string
    {
        if ($normalized === '') {
            return '';
        }

        $parts = explode('_', $normalized);
        $parts = array_map(function (string $part): string {
            if (strlen($part) >= 4 && str_ends_with($part, 's')) {
                return substr($part, 0, -1);
            }

            return $part;
        }, $parts);

        return implode('_', $parts);
    }
}
