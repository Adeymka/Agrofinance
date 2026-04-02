<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ajouter 'standard' à l'ENUM type
        DB::statement("ALTER TABLE rapports MODIFY type ENUM('campagne', 'mensuel', 'annuel', 'dossier_credit', 'standard')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Retirer 'standard' de l'ENUM
        DB::statement("ALTER TABLE rapports MODIFY type ENUM('campagne', 'mensuel', 'annuel', 'dossier_credit')");
    }
};
