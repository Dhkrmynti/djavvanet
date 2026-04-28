<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->addIndexIfNotExists('customers', 'status');
        $this->addIndexIfNotExists('customers', 'billing_cycle');
        $this->addIndexIfNotExists('invoices', 'status');
        $this->addIndexIfNotExists('invoices', 'period_start');
    }

    public function down(): void
    {
        $this->dropIndexIfExists('customers', 'status');
        $this->dropIndexIfExists('customers', 'billing_cycle');
        $this->dropIndexIfExists('invoices', 'status');
        $this->dropIndexIfExists('invoices', 'period_start');
    }

    private function addIndexIfNotExists(string $table, string $column): void
    {
        $indexName = "{$table}_{$column}_index";
        $indexes = Schema::getIndexes($table);
        $exists = collect($indexes)->where('name', $indexName)->count() > 0;
        
        if (!$exists) {
            Schema::table($table, function (Blueprint $table) use ($column) {
                $table->index($column);
            });
        }
    }

    private function dropIndexIfExists(string $table, string $column): void
    {
        $indexName = "{$table}_{$column}_index";
        $indexes = Schema::getIndexes($table);
        $exists = collect($indexes)->where('name', $indexName)->count() > 0;
        
        if ($exists) {
            Schema::table($table, function (Blueprint $table) use ($column) {
                $table->dropIndex([$column]);
            });
        }
    }
};
