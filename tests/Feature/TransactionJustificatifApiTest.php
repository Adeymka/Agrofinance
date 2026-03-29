<?php

namespace Tests\Feature;

use App\Models\Abonnement;
use App\Models\Activite;
use App\Models\Exploitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TransactionJustificatifApiTest extends TestCase
{
    use RefreshDatabase;

    private function userAvecAbonnementEtTransaction(): array
    {
        $user = User::create([
            'nom' => 'J',
            'prenom' => 'Test',
            'telephone' => '+22967000050',
            'type_exploitation' => 'mixte',
            'pin_hash' => bcrypt('1234'),
        ]);

        Abonnement::create([
            'user_id' => $user->id,
            'plan' => 'essentielle',
            'statut' => 'actif',
            'date_debut' => now()->subDay()->toDateString(),
            'date_fin' => now()->addMonth()->toDateString(),
            'montant' => 0,
        ]);

        $exploitation = Exploitation::create([
            'user_id' => $user->id,
            'nom' => 'Ferme',
            'type' => 'mixte',
            'localisation' => 'X',
        ]);

        $activite = Activite::create([
            'exploitation_id' => $exploitation->id,
            'nom' => 'Campagne',
            'type' => 'culture',
            'date_debut' => '2025-01-01',
            'date_fin' => '2025-12-31',
            'statut' => 'en_cours',
            'budget_previsionnel' => null,
        ]);

        $transaction = \App\Models\Transaction::create([
            'activite_id' => $activite->id,
            'type' => 'depense',
            'nature' => 'variable',
            'categorie' => 'intrants',
            'montant' => 1000,
            'date_transaction' => '2025-06-01',
            'synced' => true,
        ]);

        return [$user, $transaction];
    }

    public function test_post_justificatif_uploads_and_get_returns_file(): void
    {
        Storage::fake('local');

        [$user, $transaction] = $this->userAvecAbonnementEtTransaction();
        Sanctum::actingAs($user);

        $file = UploadedFile::fake()->create('facture.pdf', 100, 'application/pdf');

        $this->postJson('/api/v1/transactions/'.$transaction->id.'/justificatif', [])
            ->assertStatus(422);

        $this->post('/api/v1/transactions/'.$transaction->id.'/justificatif', [
            'justificatif' => $file,
        ], ['Accept' => 'application/json'])
            ->assertStatus(201)
            ->assertJsonPath('succes', true);

        $transaction->refresh();
        $this->assertNotEmpty($transaction->photo_justificatif);
        Storage::disk('local')->assertExists($transaction->photo_justificatif);

        $this->getJson('/api/v1/transactions/'.$transaction->id.'/justificatif')
            ->assertOk();
    }

    public function test_get_transaction_includes_has_justificatif_not_path(): void
    {
        [$user, $transaction] = $this->userAvecAbonnementEtTransaction();
        Sanctum::actingAs($user);

        $json = $this->getJson('/api/v1/transactions/'.$transaction->id)
            ->assertOk()
            ->json('data');

        $this->assertArrayHasKey('has_justificatif', $json);
        $this->assertArrayNotHasKey('photo_justificatif', $json);
    }
}
