<?php

namespace Tests\Feature;

use App\Models\Abonnement;
use App\Models\Activite;
use App\Models\Exploitation;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CooperativeWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithPlan(string $plan): User
    {
        $suffix = random_int(10000, 99999);

        $user = User::create([
            'nom' => 'Coop',
            'prenom' => 'Test',
            'telephone' => '+22967'.$suffix,
            'type_exploitation' => 'mixte',
            'pin_hash' => bcrypt('1234'),
        ]);

        Abonnement::create([
            'user_id' => $user->id,
            'plan' => $plan,
            'statut' => 'actif',
            'date_debut' => now()->subDay()->toDateString(),
            'date_fin' => now()->addMonth()->toDateString(),
            'montant' => 0,
        ]);

        return $user;
    }

    private function createActiviteFor(User $user): Activite
    {
        $exploitation = Exploitation::create([
            'user_id' => $user->id,
            'nom' => 'Ferme Coop',
            'type' => 'mixte',
            'localisation' => 'Abomey',
        ]);

        return Activite::create([
            'exploitation_id' => $exploitation->id,
            'nom' => 'Campagne Mais',
            'type' => 'culture',
            'date_debut' => '2026-01-01',
            'date_fin' => '2026-12-31',
            'statut' => 'en_cours',
            'budget_previsionnel' => 100000,
        ]);
    }

    public function test_web_store_sets_pending_validation_for_cooperative_plan(): void
    {
        $user = $this->createUserWithPlan('cooperative');
        $activite = $this->createActiviteFor($user);

        $this->actingAs($user)
            ->post('/transactions', [
                'activite_id' => $activite->id,
                'type' => 'depense',
                'categorie' => 'main_oeuvre',
                'montant' => 12000,
                'date_transaction' => '2026-04-01',
                'note' => 'Saisie coop',
            ])
            ->assertRedirect('/activites/'.$activite->id);

        $this->assertDatabaseHas('transactions', [
            'activite_id' => $activite->id,
            'categorie' => 'main_oeuvre',
            'statut_validation' => Transaction::STATUT_VALIDATION_EN_ATTENTE,
            'validee_par_user_id' => null,
        ]);
    }

    public function test_web_can_validate_then_set_back_to_pending(): void
    {
        $user = $this->createUserWithPlan('cooperative');
        $activite = $this->createActiviteFor($user);

        $transaction = Transaction::create([
            'activite_id' => $activite->id,
            'type' => 'depense',
            'nature' => 'variable',
            'categorie' => 'main_oeuvre',
            'montant' => 15000,
            'date_transaction' => '2026-04-01',
            'synced' => true,
            'statut_validation' => Transaction::STATUT_VALIDATION_EN_ATTENTE,
        ]);

        $this->actingAs($user)
            ->post('/transactions/'.$transaction->id.'/valider')
            ->assertRedirect('/transactions?statut_validation=all');

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'statut_validation' => Transaction::STATUT_VALIDATION_VALIDEE,
            'validee_par_user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->post('/transactions/'.$transaction->id.'/remettre-en-attente')
            ->assertRedirect('/transactions?statut_validation=all');

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'statut_validation' => Transaction::STATUT_VALIDATION_EN_ATTENTE,
            'validee_par_user_id' => null,
        ]);
    }

    public function test_api_store_sets_pending_validation_for_cooperative_plan(): void
    {
        $user = $this->createUserWithPlan('cooperative');
        $activite = $this->createActiviteFor($user);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/transactions', [
            'transactions' => [
                [
                    'activite_id' => $activite->id,
                    'type' => 'depense',
                    'categorie' => 'main_oeuvre',
                    'montant' => 5000,
                    'date_transaction' => '2026-04-01',
                ],
            ],
        ])->assertStatus(201)
            ->assertJsonPath('succes', true);

        $this->assertDatabaseHas('transactions', [
            'activite_id' => $activite->id,
            'categorie' => 'main_oeuvre',
            'montant' => 5000,
            'statut_validation' => Transaction::STATUT_VALIDATION_EN_ATTENTE,
            'validee_par_user_id' => null,
        ]);
    }

    public function test_csv_export_requires_cooperative_plan(): void
    {
        $coop = $this->createUserWithPlan('cooperative');
        $this->createActiviteFor($coop);

        $this->actingAs($coop)
            ->get('/dashboard/export/consolide-entreprise-csv')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $pro = $this->createUserWithPlan('pro');
        $this->createActiviteFor($pro);

        $this->actingAs($pro)
            ->get('/dashboard/export/consolide-entreprise-csv')
            ->assertStatus(403);
    }
}
