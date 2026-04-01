<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cooperatives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nom', 150)->nullable();
            $table->decimal('double_validation_threshold', 14, 2)->default(100000);
            $table->timestamps();

            $table->unique('owner_user_id');
        });

        Schema::create('cooperative_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cooperative_id')->constrained('cooperatives')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('invited_phone', 20)->nullable();
            $table->string('role', 30)->default('saisie');
            $table->string('statut', 20)->default('invited');
            $table->foreignId('invited_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['cooperative_id', 'user_id']);
            $table->unique(['cooperative_id', 'invited_phone']);
            $table->index(['cooperative_id', 'statut']);
            $table->index('invited_phone');
        });

        Schema::create('cooperative_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cooperative_id')->constrained('cooperatives')->cascadeOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('member_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->string('action', 80);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['cooperative_id', 'action']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cooperative_audit_logs');
        Schema::dropIfExists('cooperative_members');
        Schema::dropIfExists('cooperatives');
    }
};
