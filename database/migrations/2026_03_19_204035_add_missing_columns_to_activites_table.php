<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('activites', function (Blueprint $table) {
            if (!Schema::hasColumn('activites', 'description')) {
                $table->text('description')->nullable()->after('budget_previsionnel');
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE activites MODIFY statut ENUM('en_cours', 'termine', 'abandonne') NOT NULL DEFAULT 'en_cours'"
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE activites MODIFY statut ENUM('actif', 'termine', 'archive') NOT NULL DEFAULT 'actif'"
            );
        }

        Schema::table('activites', function (Blueprint $table) {
            if (Schema::hasColumn('activites', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
