<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['activite_id', 'date_transaction'], 'transactions_activite_date_idx');
            $table->index('date_transaction', 'transactions_date_transaction_idx');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('transactions_activite_date_idx');
            $table->dropIndex('transactions_date_transaction_idx');
        });
    }
};
