<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('help_article_images', function (Blueprint $table) {
            $table->id();

            $table->foreignId('help_article_id')
                ->constrained('help_articles')
                ->cascadeOnDelete();

            $table->string('chemin', 255);
            $table->string('alt', 200)->nullable();
            $table->string('legende', 255)->nullable();
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('help_article_images');
    }
};
