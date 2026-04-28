<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update enum status to include 'paid_cash'
        // Using DB::statement for compatibility with some DB drivers that don't support change() on enum easily
        if (config('database.default') === 'mysql') {
            DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('unpaid', 'paid', 'paid_cash', 'overdue', 'cancelled') NOT NULL DEFAULT 'unpaid'");
        } else {
            // For other DBs (like SQLite in testing), we might need a different approach
            Schema::table('invoices', function (Blueprint $table) {
                $table->string('status')->default('unpaid')->change();
            });
        }
    }

    public function down(): void
    {
        if (config('database.default') === 'mysql') {
            DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('unpaid', 'paid', 'overdue', 'cancelled') NOT NULL DEFAULT 'unpaid'");
        } else {
            Schema::table('invoices', function (Blueprint $table) {
                $table->enum('status', ['unpaid', 'paid', 'overdue', 'cancelled'])->default('unpaid')->change();
            });
        }
    }
};
