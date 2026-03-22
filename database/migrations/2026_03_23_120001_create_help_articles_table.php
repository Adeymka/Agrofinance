<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('help_articles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('help_category_id')
                ->constrained('help_categories')
                ->cascadeOnDelete();

            $table->string('titre', 200);
            $table->string('slug', 200)->unique();
            $table->string('resume', 255)->nullable();

            $table->longText('contenu');
            $table->longText('contenu_texte');

            $table->text('mots_cles')->nullable();
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->boolean('actif')->default(true);
            $table->unsignedInteger('vues')->default(0);

            $table->timestamps();

            $table->unique(['help_category_id', 'ordre'], 'help_articles_category_ordre_unique');

            $table->fullText(['titre', 'contenu_texte', 'mots_cles'], 'help_articles_search');
        });
    }

    public function down(): void
    {
        Schema::table('help_articles', function (Blueprint $table) {
            $table->dropFullText('help_articles_search');
        });

        Schema::dropIfExists('help_articles');
    }
};
