<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedTinyInteger('validation_niveau')->default(0)->after('statut_validation');
            $table->foreignId('validee_niveau1_par_user_id')
                ->nullable()
                ->after('validation_niveau')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('validee_niveau1_le')->nullable()->after('validee_niveau1_par_user_id');
            $table->index('validation_niveau');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['validation_niveau']);
            $table->dropConstrainedForeignId('validee_niveau1_par_user_id');
            $table->dropColumn(['validation_niveau', 'validee_niveau1_le']);
        });
    }
};
