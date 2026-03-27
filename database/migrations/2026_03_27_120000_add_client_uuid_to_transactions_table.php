<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Idempotence des synchros client (hors ligne / retry) : UUID généré côté navigateur.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->uuid('client_uuid')->nullable()->after('id');
            $table->unique('client_uuid');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropUnique(['client_uuid']);
            $table->dropColumn('client_uuid');
        });
    }
};
