<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('abonnements', function (Blueprint $table) {
            // ref_fedapay est l’idempotency key FedaPay.
            // Sur MySQL, plusieurs NULL sont autorisés, mais les valeurs non-NULL doivent être uniques.
            $table->unique('ref_fedapay', 'uq_abonnements_ref_fedapay');
        });
    }

    public function down(): void
    {
        Schema::table('abonnements', function (Blueprint $table) {
            $table->dropUnique('uq_abonnements_ref_fedapay');
        });
    }
};

