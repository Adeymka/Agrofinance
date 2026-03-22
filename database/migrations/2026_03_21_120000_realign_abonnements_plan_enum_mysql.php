<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Remet l’enum `plan` sur gratuit / essentielle / pro / cooperative (MySQL uniquement).
     * Étape intermédiaire VARCHAR pour éviter la troncature avant ALTER ENUM.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE abonnements MODIFY COLUMN plan VARCHAR(32) NOT NULL');

        DB::statement("
            UPDATE abonnements SET plan = CASE plan
                WHEN 'mensuel' THEN 'essentielle'
                WHEN 'annuel' THEN 'pro'
                WHEN 'essai' THEN 'gratuit'
                WHEN 'essentielle' THEN 'essentielle'
                WHEN 'gratuit' THEN 'gratuit'
                WHEN 'pro' THEN 'pro'
                WHEN 'cooperative' THEN 'cooperative'
                ELSE 'essentielle'
            END
        ");

        DB::statement("
            ALTER TABLE abonnements MODIFY COLUMN plan
            ENUM('gratuit', 'essentielle', 'pro', 'cooperative') NOT NULL
        ");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE abonnements MODIFY COLUMN plan VARCHAR(32) NOT NULL');

        DB::statement("
            UPDATE abonnements SET plan = CASE plan
                WHEN 'essentielle' THEN 'mensuel'
                WHEN 'pro' THEN 'annuel'
                WHEN 'gratuit' THEN 'essai'
                WHEN 'cooperative' THEN 'annuel'
                ELSE 'mensuel'
            END
        ");

        DB::statement("
            ALTER TABLE abonnements MODIFY COLUMN plan
            ENUM('essai', 'mensuel', 'annuel') NOT NULL
        ");
    }
};
