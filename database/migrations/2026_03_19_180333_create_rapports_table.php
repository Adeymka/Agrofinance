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
        Schema::create('rapports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exploitation_id')
                ->constrained('exploitations')
                ->onDelete('cascade');
            $table->enum('type', ['campagne', 'mensuel', 'annuel', 'dossier_credit']);
            $table->date('periode_debut');
            $table->date('periode_fin');
            $table->string('chemin_pdf');
            $table->string('lien_token')->nullable();
            // Critique : expiration lien de partage 72h
            $table->timestamp('lien_expire_le')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rapports');
    }
};
