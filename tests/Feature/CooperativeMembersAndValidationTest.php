<?php

namespace Tests\Feature;

use App\Models\Abonnement;
use App\Models\Activite;
use App\Models\Cooperative;
use App\Models\CooperativeAuditLog;
use App\Models\CooperativeMember;
use App\Models\Exploitation;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CooperativeMembersAndValidationTest extends TestCase
{
    use RefreshDatabase;

    private function createOwner(): User
    {
        $owner = User::create([
            'nom' => 'Owner',
            'prenom' => 'Coop',
            'telephone' => '+22967990001',
            'type_exploitation' => 'mixte',
            'pin_hash' => bcrypt('1234'),
        ]);

        Abonnement::create([
            'user_id' => $owner->id,
            'plan' => 'cooperative',
            'statut' => 'actif',
            'date_debut' => now()->subDay()->toDateString(),
            'date_fin' => now()->addMonth()->toDateString(),
            'montant' => 0,
        ]);

        Cooperative::create([
            'owner_user_id' => $owner->id,
            'nom' => 'Coop Test',
            'double_validation_threshold' => 100000,
        ]);

        return $owner;
    }

    public function test_admin_can_invite_member_and_log_audit(): void
    {
        $owner = $this->createOwner();
        $member = User::create([
            'nom' => 'Vali',
            'prenom' => 'Dateur',
            'telephone' => '+22967990002',
            'type_exploitation' => 'mixte',
            'pin_hash' => bcrypt('1234'),
        ]);

        $this->actingAs($owner)
            ->post('/cooperative/membres/inviter', [
                'telephone' => $member->telephone,
                'role' => 'validateur',
            ])->assertRedirect('/cooperative/membres');

        $this->assertDatabaseHas('cooperative_members', [
            'user_id' => $member->id,
            'role' => 'validateur',
            'statut' => 'active',
        ]);
        $this->assertDatabaseHas('cooperative_audit_logs', [
            'action' => 'member.invited',
            'actor_user_id' => $owner->id,
            'member_user_id' => $member->id,
        ]);
    }

    public function test_double_validation_requires_two_distinct_validators(): void
    {
        $owner = $this->createOwner();
        $validator = User::create([
            'nom' => 'Second',
            'prenom' => 'Validator',
            'telephone' => '+22967990003',
            'type_exploitation' => 'mixte',
            'pin_hash' => bcrypt('1234'),
        ]);

        $coop = Cooperative::where('owner_user_id', $owner->id)->firstOrFail();
        CooperativeMember::create([
            'cooperative_id' => $coop->id,
            'user_id' => $validator->id,
            'invited_phone' => $validator->telephone,
            'role' => CooperativeMember::ROLE_VALIDATEUR,
            'statut' => CooperativeMember::STATUT_ACTIVE,
            'joined_at' => now(),
        ]);

        $exploitation = Exploitation::create([
            'user_id' => $owner->id,
            'nom' => 'Ferme Coop',
            'type' => 'mixte',
            'localisation' => 'Bohicon',
        ]);
        $activite = Activite::create([
            'exploitation_id' => $exploitation->id,
            'nom' => 'Campagne',
            'type' => 'culture',
            'date_debut' => '2026-01-01',
            'date_fin' => '2026-12-31',
            'statut' => 'en_cours',
            'budget_previsionnel' => 200000,
        ]);
        $transaction = Transaction::create([
            'activite_id' => $activite->id,
            'type' => 'depense',
            'nature' => 'variable',
            'categorie' => 'main_oeuvre',
            'montant' => 150000,
            'date_transaction' => '2026-04-01',
            'synced' => true,
            'statut_validation' => Transaction::STATUT_VALIDATION_EN_ATTENTE,
            'validation_niveau' => 0,
        ]);

        $this->actingAs($owner)
            ->post('/transactions/'.$transaction->id.'/valider')
            ->assertRedirect('/transactions?statut_validation=all');

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'statut_validation' => Transaction::STATUT_VALIDATION_EN_ATTENTE,
            'validation_niveau' => 1,
            'validee_niveau1_par_user_id' => $owner->id,
            'validee_par_user_id' => null,
        ]);

        $this->actingAs($owner)
            ->post('/transactions/'.$transaction->id.'/valider')
            ->assertRedirect('/transactions');

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'validation_niveau' => 1,
            'statut_validation' => Transaction::STATUT_VALIDATION_EN_ATTENTE,
            'validee_par_user_id' => null,
        ]);

        $this->actingAs($validator)
            ->post('/transactions/'.$transaction->id.'/valider')
            ->assertRedirect('/transactions?statut_validation=all');

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'validation_niveau' => 2,
            'statut_validation' => Transaction::STATUT_VALIDATION_VALIDEE,
            'validee_par_user_id' => $validator->id,
        ]);

        $this->assertGreaterThanOrEqual(2, CooperativeAuditLog::query()->where('cooperative_id', $coop->id)->count());
    }
}
