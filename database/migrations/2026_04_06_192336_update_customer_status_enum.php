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
        // Map old values to new values first to avoid truncation error
        DB::table('customers')->where('status', 'suspended')->update(['status' => 'active']); // or suspend if I can, but ENUM doesn't allow it yet.
        // Wait, I can't set it to 'suspend' yet because 'suspend' is not in the ENUM.
        // I should probably use a RAW SQL to change the ENUM first OR allow multiple values.
        
        Schema::table('customers', function (Blueprint $table) {
            $table->string('status')->default('active')->change(); // Temporary change to string
        });

        DB::table('customers')->where('status', 'suspended')->update(['status' => 'suspend']);
        DB::table('customers')->where('status', 'terminated')->update(['status' => 'inactive']);

        Schema::table('customers', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive', 'suspend'])->default('active')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->enum('status', ['active', 'suspended', 'terminated'])->default('active')->change();
        });
    }
};
