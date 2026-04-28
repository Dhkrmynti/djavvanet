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
        // Set all existing customers billing cycle to 1
        DB::table('customers')->update(['billing_cycle' => 1]);

        Schema::table('customers', function (Blueprint $table) {
            $table->integer('billing_cycle')->default(1)->change();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->integer('billing_cycle')->default(1)->change();
        });
    }
};
