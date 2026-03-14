<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Update existing expenses with null currency fields
        DB::table('expenses')
            ->whereNull('currency')
            ->orWhereNull('converted_amount')
            ->orWhereNull('exchange_rate')
            ->update([
                'currency' => 'UAH',
                'converted_amount' => DB::raw('amount'),
                'exchange_rate' => 1.0000
            ]);

        // Update existing group_expenses with null currency fields
        DB::table('group_expenses')
            ->whereNull('currency')
            ->orWhereNull('converted_amount')
            ->orWhereNull('exchange_rate')
            ->update([
                'currency' => 'UAH',
                'converted_amount' => DB::raw('amount'),
                'exchange_rate' => 1.0000
            ]);
    }

    public function down(): void
    {
        // No rollback needed - data migration is one-way
    }
};
