<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('recurring_transaction_id')
                ->nullable()
                ->after('category_id')
                ->constrained('recurring_transactions')
                ->nullOnDelete();

            $table->index(['recurring_transaction_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('recurring_transaction_id');
        });
    }
};
