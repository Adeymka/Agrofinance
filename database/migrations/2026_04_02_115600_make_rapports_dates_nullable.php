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
        Schema::table('rapports', function (Blueprint $table) {
            // Rendre les colonnes de dates nullable
            $table->date('periode_debut')->nullable()->change();
            $table->date('periode_fin')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rapports', function (Blueprint $table) {
            // Révertir à NOT NULL
            $table->date('periode_debut')->nullable(false)->change();
            $table->date('periode_fin')->nullable(false)->change();
        });
    }
};
