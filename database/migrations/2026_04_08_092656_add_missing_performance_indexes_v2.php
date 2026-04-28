<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->index('username');
            $table->index('full_name');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->index('payment_hash');
            $table->index('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['username']);
            $table->dropIndex(['full_name']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['payment_hash']);
            $table->dropIndex(['customer_id']);
        });
    }
};
