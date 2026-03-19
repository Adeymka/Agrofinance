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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activite_id')
                ->constrained('activites')
                ->onDelete('cascade');
            $table->enum('type', ['depense', 'recette']);
            // Critique : indispensable pour MB = PB - CV
            $table->enum('nature', ['fixe', 'variable'])->nullable();
            $table->string('categorie', 100);
            $table->decimal('montant', 15, 2);
            $table->date('date_transaction');
            $table->text('note')->nullable();
            $table->boolean('est_imprevue')->default(false);
            // Critique : false = hors ligne, true = synchronisé
            $table->boolean('synced')->default(true);
            $table->string('photo_justificatif')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
