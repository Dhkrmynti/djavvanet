<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (config('database.default') === 'mysql') {
            DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('unpaid', 'paid', 'paid_transfer', 'paid_cash', 'paid_gateway', 'overdue', 'cancelled') NOT NULL DEFAULT 'unpaid'");
            return;
        }

        Schema::table('invoices', function (Blueprint $table) {
            $table->string('status', 32)->default('unpaid')->change();
        });
    }

    public function down(): void
    {
        if (config('database.default') === 'mysql') {
            DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('unpaid', 'paid', 'paid_cash', 'overdue', 'cancelled') NOT NULL DEFAULT 'unpaid'");
            return;
        }

        Schema::table('invoices', function (Blueprint $table) {
            $table->string('status', 16)->default('unpaid')->change();
        });
    }
};
