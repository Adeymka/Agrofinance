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
        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['activite_id', 'date_transaction'], 'idx_tx_activite_date');
            $table->index(['activite_id', 'type'], 'idx_tx_activite_type');
            $table->index(['activite_id', 'type', 'nature'], 'idx_tx_activite_type_nature');
            $table->index(['activite_id', 'categorie'], 'idx_tx_activite_categorie');
        });

        Schema::table('activites', function (Blueprint $table) {
            $table->index(['exploitation_id', 'statut'], 'idx_activites_exploitation_statut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('idx_tx_activite_date');
            $table->dropIndex('idx_tx_activite_type');
            $table->dropIndex('idx_tx_activite_type_nature');
            $table->dropIndex('idx_tx_activite_categorie');
        });

        Schema::table('activites', function (Blueprint $table) {
            $table->dropIndex('idx_activites_exploitation_statut');
        });
    }
};
