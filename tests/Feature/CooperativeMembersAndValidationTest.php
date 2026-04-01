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

    public function test_read_only_role_cannot_write_transactions_but_can_list(): void
    {
        $owner = $this->createOwner();
        $reader = User::create([
            'nom' => 'Read',
            'prenom' => 'Only',
            'telephone' => '+22967990004',
            'type_exploitation' => 'mixte',
            'pin_hash' => bcrypt('1234'),
        ]);

        $coop = Cooperative::where('owner_user_id', $owner->id)->firstOrFail();
        CooperativeMember::create([
            'cooperative_id' => $coop->id,
            'user_id' => $reader->id,
            'invited_phone' => $reader->telephone,
            'role' => CooperativeMember::ROLE_LECTURE,
            'statut' => CooperativeMember::STATUT_ACTIVE,
            'joined_at' => now(),
        ]);

        $exploitation = Exploitation::create([
            'user_id' => $owner->id,
            'nom' => 'Ferme Coop',
            'type' => 'mixte',
            'localisation' => 'Parakou',
        ]);
        $activite = Activite::create([
            'exploitation_id' => $exploitation->id,
            'nom' => 'Campagne',
            'type' => 'culture',
            'date_debut' => '2026-01-01',
            'date_fin' => '2026-12-31',
            'statut' => 'en_cours',
            'budget_previsionnel' => 100000,
        ]);

        $this->actingAs($reader)
            ->get('/transactions')
            ->assertOk();

        $this->actingAs($reader)
            ->post('/transactions', [
                'activite_id' => $activite->id,
                'type' => 'depense',
                'categorie' => 'main_oeuvre',
                'montant' => 10000,
                'date_transaction' => '2026-04-01',
            ])
            ->assertRedirect('/transactions');

        $this->assertDatabaseMissing('transactions', [
            'activite_id' => $activite->id,
            'montant' => 10000,
        ]);

        $this->actingAs($reader)
            ->postJson('/api/v1/transactions', [
                'transactions' => [[
                    'activite_id' => $activite->id,
                    'type' => 'depense',
                    'categorie' => 'main_oeuvre',
                    'montant' => 10000,
                    'date_transaction' => '2026-04-01',
                ]],
            ])
            ->assertStatus(403);
    }

    public function test_role_permissions_for_export_audit_and_threshold_update(): void
    {
        $owner = $this->createOwner();
        $validator = User::create([
            'nom' => 'Vali',
            'prenom' => 'Dateur',
            'telephone' => '+22967990005',
            'type_exploitation' => 'mixte',
            'pin_hash' => bcrypt('1234'),
        ]);
        $reader = User::create([
            'nom' => 'Read',
            'prenom' => 'Only2',
            'telephone' => '+22967990006',
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
        CooperativeMember::create([
            'cooperative_id' => $coop->id,
            'user_id' => $reader->id,
            'invited_phone' => $reader->telephone,
            'role' => CooperativeMember::ROLE_LECTURE,
            'statut' => CooperativeMember::STATUT_ACTIVE,
            'joined_at' => now(),
        ]);

        Exploitation::create([
            'user_id' => $owner->id,
            'nom' => 'Ferme Export',
            'type' => 'mixte',
            'localisation' => 'Cotonou',
        ]);

        $this->actingAs($validator)
            ->get('/dashboard/export/consolide-entreprise-csv')
            ->assertOk();

        $this->actingAs($reader)
            ->get('/dashboard/export/consolide-entreprise-csv')
            ->assertStatus(403);

        $this->actingAs($reader)
            ->get('/cooperative/membres')
            ->assertOk()
            ->assertDontSee('Audit coopérative');

        $this->actingAs($validator)
            ->get('/cooperative/membres')
            ->assertOk()
            ->assertSee('Audit coopérative');

        $this->actingAs($validator)
            ->post('/cooperative/seuil-validation', [
                'double_validation_threshold' => 200000,
            ])->assertStatus(403);

        $this->actingAs($owner)
            ->post('/cooperative/seuil-validation', [
                'double_validation_threshold' => 200000,
            ])->assertRedirect('/cooperative/membres');

        $this->assertDatabaseHas('cooperatives', [
            'id' => $coop->id,
            'double_validation_threshold' => 200000,
        ]);
    }

    public function test_invitation_token_acceptance_and_audit_export_filtering(): void
    {
        $owner = $this->createOwner();
        $invited = User::create([
            'nom' => 'Invite',
            'prenom' => 'User',
            'telephone' => '+22967990007',
            'type_exploitation' => 'mixte',
            'pin_hash' => bcrypt('1234'),
        ]);

        $this->actingAs($owner)
            ->post('/cooperative/membres/inviter', [
                'telephone' => $invited->telephone,
                'role' => 'validateur',
            ])->assertRedirect('/cooperative/membres');

        $member = CooperativeMember::query()->where('cooperative_id', Cooperative::where('owner_user_id', $owner->id)->firstOrFail()->id)
            ->where('invited_phone', $invited->telephone)
            ->firstOrFail();

        $member->update([
            'user_id' => null,
            'statut' => CooperativeMember::STATUT_INVITED,
            'joined_at' => null,
            'accepted_at' => null,
        ]);

        $this->actingAs($invited)
            ->get('/cooperative/invitation/'.$member->invitation_token)
            ->assertOk()
            ->assertSee('Accepter l’invitation');

        $this->actingAs($invited)
            ->post('/cooperative/invitation/'.$member->invitation_token.'/accepter')
            ->assertRedirect('/cooperative/membres');

        $this->assertDatabaseHas('cooperative_members', [
            'id' => $member->id,
            'user_id' => $invited->id,
            'statut' => CooperativeMember::STATUT_ACTIVE,
        ]);
        $this->assertDatabaseHas('cooperative_audit_logs', [
            'action' => 'member.invitation_accepted',
            'actor_user_id' => $invited->id,
            'member_user_id' => $invited->id,
        ]);

        $this->actingAs($owner)
            ->get('/cooperative/membres?action=member.invitation_accepted')
            ->assertOk()
            ->assertSee('member.invitation_accepted');

        $this->actingAs($owner)
            ->get('/cooperative/audit/export.csv?action=member.invitation_accepted')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}
