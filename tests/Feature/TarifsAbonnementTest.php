<?php

namespace Tests\Feature;

use App\Services\AbonnementService;
use App\Support\TarifsAbonnement;
use Tests\TestCase;

class TarifsAbonnementTest extends TestCase
{
    public function test_montants_alignes_config_et_service(): void
    {
        $this->assertSame(5000, TarifsAbonnement::montant('mensuel'));
        $this->assertSame(10000, TarifsAbonnement::montant('annuel'));
        $this->assertSame(16000, TarifsAbonnement::montant('cooperative'));
        $this->assertSame(5000, config('tarifs_abonnement.fcfa.mensuel'));
    }

    public function test_libelle_espace_format_fr(): void
    {
        $this->assertSame('5 000', TarifsAbonnement::libelleEspace('mensuel'));
        $this->assertSame('10 000', TarifsAbonnement::libelleEspace('annuel'));
        $this->assertSame('16 000', TarifsAbonnement::libelleEspace('cooperative'));
    }

    public function test_normaliser_plan_insensible_a_la_casse_et_espaces(): void
    {
        $s = app(AbonnementService::class);
        $this->assertSame('essentielle', $s->normaliserPlan('ESSENTIELLE'));
        $this->assertSame('essentielle', $s->normaliserPlan(' Essentielle '));
        $this->assertSame('pro', $s->normaliserPlan('PRO'));
    }
}
