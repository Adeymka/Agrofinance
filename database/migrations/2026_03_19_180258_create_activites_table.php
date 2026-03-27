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
        Schema::create('activites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exploitation_id')
                ->constrained('exploitations')
                ->onDelete('cascade');
            $table->string('nom', 150);
            // Types d'activite agricole (alignes sur les categories FAO/FSA)
            $table->enum('type', ['culture', 'elevage', 'transformation']);
            $table->date('date_debut');
            $table->date('date_fin')->nullable(); // null = activite sans fin prevue (elevage permanent)
            // en_cours | termine | abandonne — seules les activites 'en_cours' entrent dans la consolidation
            $table->enum('statut', ['actif', 'termine', 'archive'])->default('actif');
            // Budget previsionnel en FCFA — sert au calcul de l'alerte budget (70%/90%/100%)
            $table->decimal('budget_previsionnel', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activites');
    }
};
