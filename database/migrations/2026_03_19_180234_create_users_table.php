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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 100);
            $table->string('prenom', 100);
            // Identifiant principal : pas de null, unique
            $table->string('telephone', 20)->unique();
            // PIN bcrypt : jamais en clair
            $table->string('pin_hash')->nullable();
            $table->string('email', 255)->nullable()->unique();
            $table->enum('type_exploitation', [
                'cultures_vivrieres',
                'elevage',
                'maraichage',
                'transformation',
                'mixte',
            ])->default('mixte');
            $table->string('departement', 100)->nullable();
            $table->string('commune', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
