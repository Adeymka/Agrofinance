<?php

namespace Tests\Feature;

use App\Models\Abonnement;
use App\Models\Activite;
use App\Models\Exploitation;
use App\Models\Rapport;
use App\Models\User;
use Barryvdh\DomPDF\PDF as DomPdfWrapper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class Sprint5Test extends TestCase
{
    use RefreshDatabase;

    public function test_get_rapports_without_token_returns_401(): void
    {
        $this->getJson('/api/rapports')->assertStatus(401);
    }

    public function test_partage_token_invalide_returns_404(): void
    {
        $this->get('/partage/ceci-nexiste-pas')->assertStatus(404);
    }

    public function test_abonnement_mock_initier_repond_200_sans_fedapay(): void
    {
        Config::set('services.fedapay.mock', true);

        $user = User::create([
            'nom' => 'Test',
            'prenom' => 'User',
            'telephone' => '+22967000099',
            'pin_hash' => null,
            'type_exploitation' => 'mixte',
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/abonnement/initier', [
            'plan' => 'mensuel',
            'telephone' => '+22967000099',
        ])
            ->assertStatus(200)
            ->assertJsonPath('succes', true)
            ->assertJsonPath('data.mock', true)
            ->assertJsonPath('data.url_paiement', null);

        $this->assertDatabaseMissing('abonnements', ['user_id' => $user->id]);
    }

    /**
     * Sur MySQL, la migration élargit plan (mensuel / annuel). SQLite tests (:memory:) garde l’ancien schéma.
     */
    public function test_abonnement_mock_finaliser_cree_abonnement_si_mysql_ou_plan_compatible(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('abonnements.plan SQLite ne inclut pas mensuel (voir migrations MySQL).');
        }

        Config::set('services.fedapay.mock', true);

        $user = User::create([
            'nom' => 'Test',
            'prenom' => 'User',
            'telephone' => '+22967000100',
            'pin_hash' => null,
            'type_exploitation' => 'mixte',
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/abonnement/initier', [
            'plan' => 'mensuel',
            'telephone' => '+22967000100',
        ])->assertStatus(200);

        $this->postJson('/api/abonnement/finaliser-mock')
            ->assertStatus(200)
            ->assertJsonPath('succes', true)
            ->assertJsonPath('data.statut', 'actif');

        $this->assertDatabaseHas('abonnements', [
            'user_id' => $user->id,
            'plan' => 'mensuel',
            'statut' => 'actif',
        ]);
    }

    public function test_finaliser_mock_interdit_si_mock_desactive(): void
    {
        Config::set('services.fedapay.mock', false);

        $user = User::create([
            'nom' => 'Test',
            'prenom' => 'User',
            'telephone' => '+22967000098',
            'pin_hash' => null,
            'type_exploitation' => 'mixte',
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/abonnement/finaliser-mock')
            ->assertStatus(403);
    }

    public function test_post_rapports_generer_cree_pdf_et_rapport(): void
    {
        $pdfMock = \Mockery::mock(DomPdfWrapper::class);
        $pdfMock->shouldReceive('loadView')->once()->withAnyArgs()->andReturnSelf();
        $pdfMock->shouldReceive('output')->once()->andReturn('%PDF-1.4 test fake content');
        $this->instance('dompdf.wrapper', $pdfMock);

        $user = User::create([
            'nom' => 'PDF',
            'prenom' => 'Tester',
            'telephone' => '+22967000097',
            'pin_hash' => null,
            'type_exploitation' => 'mixte',
        ]);

        $exploitation = Exploitation::create([
            'user_id' => $user->id,
            'nom' => 'Ferme test',
            'type' => 'mixte',
            'localisation' => 'Test',
        ]);

        $activite = Activite::create([
            'exploitation_id' => $exploitation->id,
            'nom' => 'Campagne test',
            'type' => 'culture',
            'date_debut' => '2025-01-01',
            'date_fin' => '2025-12-31',
            'statut' => 'actif',
            'budget_previsionnel' => null,
        ]);

        Abonnement::create([
            'user_id' => $user->id,
            'plan' => 'essentielle',
            'statut' => 'actif',
            'date_debut' => now()->subDay()->toDateString(),
            'date_fin' => now()->addMonth()->toDateString(),
            'montant' => 0,
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/rapports/generer', [
            'activite_id' => $activite->id,
            'type' => 'campagne',
            'periode_debut' => '2025-01-01',
            'periode_fin' => '2025-12-31',
        ])
            ->assertStatus(201)
            ->assertJsonPath('succes', true);

        $this->assertDatabaseCount('rapports', 1);
        $rapport = Rapport::first();
        $this->assertNotEmpty($rapport->lien_token);
        $this->assertNotEmpty($rapport->chemin_pdf);
    }
}
