<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * SQLite : la migration MySQL (ENUM en_cours / termine / abandonne) ne s’appliquait pas.
 * L’app insère statut=en_cours alors que l’ancien schéma imposait actif|termine|archive → CHECK failed.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite' || ! Schema::hasTable('activites')) {
            return;
        }

        $rows = DB::table('activites')->orderBy('id')->get();

        Schema::disableForeignKeyConstraints();

        Schema::drop('activites');

        Schema::create('activites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exploitation_id')
                ->constrained('exploitations')
                ->cascadeOnDelete();
            $table->string('nom', 150);
            $table->enum('type', ['culture', 'elevage', 'transformation']);
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->enum('statut', ['en_cours', 'termine', 'abandonne'])->default('en_cours');
            $table->decimal('budget_previsionnel', 15, 2)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        foreach ($rows as $row) {
            $statut = (string) $row->statut;
            $statut = match ($statut) {
                'actif' => 'en_cours',
                'archive' => 'abandonne',
                'en_cours', 'termine', 'abandonne' => $statut,
                default => 'en_cours',
            };

            DB::table('activites')->insert([
                'id' => $row->id,
                'exploitation_id' => $row->exploitation_id,
                'nom' => $row->nom,
                'type' => $row->type,
                'date_debut' => $row->date_debut,
                'date_fin' => $row->date_fin,
                'statut' => $statut,
                'budget_previsionnel' => $row->budget_previsionnel,
                'description' => $row->description ?? null,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }

        $maxId = (int) DB::table('activites')->max('id');
        if ($maxId > 0) {
            DB::statement("DELETE FROM sqlite_sequence WHERE name = 'activites'");
            DB::statement("INSERT INTO sqlite_sequence (name, seq) VALUES ('activites', {$maxId})");
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // Non réversible sans perdre la cohérence ENUM MySQL / SQLite.
    }
};
