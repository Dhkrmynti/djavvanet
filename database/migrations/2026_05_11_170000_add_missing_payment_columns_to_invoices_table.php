<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('status');
            }

            if (!Schema::hasColumn('invoices', 'payment_method')) {
                $table->string('payment_method', 64)->nullable()->after('paid_at');
            }

            if (!Schema::hasColumn('invoices', 'payment_channel')) {
                $table->string('payment_channel', 64)->nullable()->after('payment_method');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'payment_channel')) {
                $table->dropColumn('payment_channel');
            }

            if (Schema::hasColumn('invoices', 'payment_method')) {
                $table->dropColumn('payment_method');
            }

            if (Schema::hasColumn('invoices', 'paid_at')) {
                $table->dropColumn('paid_at');
            }
        });
    }
};
