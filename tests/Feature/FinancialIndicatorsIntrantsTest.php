<?php

namespace Tests\Feature;

use App\Helpers\TransactionCategories;
use App\Models\Activite;
use App\Models\Exploitation;
use App\Models\Transaction;
use App\Models\User;
use App\Services\FinancialIndicatorsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialIndicatorsIntrantsTest extends TestCase
{
    use RefreshDatabase;

    private function creerUserExploitationActivite(): array
    {
        $user = User::create([
            'nom' => 'Test',
            'prenom' => 'U',
            'telephone' => '+22967000099',
            'type_exploitation' => 'mixte',
            'pin_hash' => bcrypt('1234'),
        ]);

        $exploitation = Exploitation::create([
            'user_id' => $user->id,
            'nom' => 'Ferme T',
            'type' => 'mixte',
            'localisation' => 'L',
        ]);

        $activite = Activite::create([
            'exploitation_id' => $exploitation->id,
            'nom' => 'Campagne T',
            'type' => 'culture',
            'date_debut' => '2025-01-01',
            'date_fin' => '2025-12-31',
            'statut' => Activite::STATUT_EN_COURS,
            'budget_previsionnel' => null,
        ]);

        return [$user, $exploitation, $activite];
    }

    public function test_ci_includes_intrant_production_when_not_standard_slug(): void
    {
        [, , $activite] = $this->creerUserExploitationActivite();

        Transaction::create([
            'activite_id' => $activite->id,
            'type' => 'depense',
            'nature' => 'variable',
            'categorie' => 'location_terrain',
            'intrant_production' => true,
            'montant' => 100000,
            'date_transaction' => now()->toDateString(),
            'synced' => true,
        ]);

        $service = app(FinancialIndicatorsService::class);
        $ind = $service->calculer($activite->id);

        $this->assertFalse(TransactionCategories::estSlugChargesIntermediaires('location_terrain'));
        $this->assertSame(100000.0, (float) $ind['CI']);
    }

    public function test_donnees_indicatives_true_when_few_transactions(): void
    {
        [, , $activite] = $this->creerUserExploitationActivite();

        Transaction::create([
            'activite_id' => $activite->id,
            'type' => 'recette',
            'categorie' => 'vente_marche',
            'montant' => 50000,
            'date_transaction' => now()->toDateString(),
            'synced' => true,
        ]);

        $service = app(FinancialIndicatorsService::class);
        $ind = $service->calculer($activite->id);

        $this->assertTrue($ind['donnees_indicatives']);
    }
}
