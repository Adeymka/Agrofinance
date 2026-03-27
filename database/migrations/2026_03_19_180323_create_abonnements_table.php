<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('abonnements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            // Valeurs canoniques de plan : gratuit | essentielle | pro | cooperative
            // NE PAS utiliser 'mensuel' ou 'annuel' ici — ce sont des plans de facturation FedaPay
            // La normalisation est faite par AbonnementService::planPourBase()
            $table->enum('plan', ['gratuit', 'essentielle', 'pro', 'cooperative']);
            $table->date('date_debut');
            $table->date('date_fin');
            // actif | expire | suspendu | essai
            $table->enum('statut', ['actif', 'expire', 'suspendu', 'essai'])->default('essai');
            // Identifiant de transaction FedaPay — UNIQUE (contrainte ajoutee en migration ulterieure)
            // Sert de cle d'idempotence pour eviter les doubles activations (#14)
            $table->string('ref_fedapay')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('abonnements');
    }
};
