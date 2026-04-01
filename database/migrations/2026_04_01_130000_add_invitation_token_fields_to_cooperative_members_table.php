<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cooperative_members', function (Blueprint $table) {
            $table->string('invitation_token', 80)->nullable()->after('invited_phone');
            $table->timestamp('invitation_expires_at')->nullable()->after('invitation_token');
            $table->timestamp('accepted_at')->nullable()->after('joined_at');
            $table->unique('invitation_token');
            $table->index('invitation_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('cooperative_members', function (Blueprint $table) {
            $table->dropUnique(['invitation_token']);
            $table->dropIndex(['invitation_expires_at']);
            $table->dropColumn(['invitation_token', 'invitation_expires_at', 'accepted_at']);
        });
    }
};
