<?php

namespace Tests\Feature;

use App\Models\Abonnement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VerifierAbonnementTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_dashboard_sans_aucun_abonnement_repond_403_avec_code_abonnement_requis(): void
    {
        $user = User::create([
            'nom' => 'Sans',
            'prenom' => 'Abonnement',
            'telephone' => '+22967000120',
            'pin_hash' => null,
            'type_exploitation' => 'mixte',
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/dashboard')
            ->assertStatus(403)
            ->assertJsonPath('code', 'ABONNEMENT_REQUIS')
            ->assertJsonPath('succes', false);
    }

    public function test_api_dashboard_abonnement_expire_repond_403_avec_code_abonnement_expire(): void
    {
        $user = User::create([
            'nom' => 'Expire',
            'prenom' => 'Test',
            'telephone' => '+22967000121',
            'pin_hash' => null,
            'type_exploitation' => 'mixte',
        ]);

        Abonnement::create([
            'user_id' => $user->id,
            'plan' => 'essentielle',
            'statut' => 'actif',
            'date_debut' => now()->subYear()->toDateString(),
            'date_fin' => now()->subDay()->toDateString(),
            'montant' => 0,
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/dashboard')
            ->assertStatus(403)
            ->assertJsonPath('code', 'ABONNEMENT_EXPIRE')
            ->assertJsonPath('succes', false);
    }
}
