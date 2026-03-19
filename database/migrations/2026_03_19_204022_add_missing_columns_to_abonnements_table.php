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
        Schema::table('abonnements', function (Blueprint $table) {
            if (!Schema::hasColumn('abonnements', 'montant')) {
                $table->decimal('montant', 15, 2)->default(0)->after('date_fin');
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE abonnements MODIFY plan ENUM('essai', 'mensuel', 'annuel') NOT NULL"
            );
            DB::statement(
                "ALTER TABLE abonnements MODIFY statut ENUM('actif', 'essai', 'expire', 'suspendu') NOT NULL DEFAULT 'essai'"
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
                "ALTER TABLE abonnements MODIFY plan ENUM('gratuit', 'essentielle', 'pro', 'cooperative') NOT NULL"
            );
            DB::statement(
                "ALTER TABLE abonnements MODIFY statut ENUM('actif', 'expire', 'suspendu', 'essai') NOT NULL DEFAULT 'essai'"
            );
        }

        Schema::table('abonnements', function (Blueprint $table) {
            if (Schema::hasColumn('abonnements', 'montant')) {
                $table->dropColumn('montant');
            }
        });
    }
};
