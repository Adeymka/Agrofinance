<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('statut_validation', 20)->default('validee')->after('synced');
            $table->foreignId('validee_par_user_id')
                ->nullable()
                ->after('statut_validation')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('validee_le')->nullable()->after('validee_par_user_id');
            $table->index('statut_validation');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['statut_validation']);
            $table->dropConstrainedForeignId('validee_par_user_id');
            $table->dropColumn(['statut_validation', 'validee_le']);
        });
    }
};

