<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mikrotik_settings', function (Blueprint $table) {
            $table->string('isolir_profile')->default('isolir')->after('default_profile');
        });
    }

    public function down(): void
    {
        Schema::table('mikrotik_settings', function (Blueprint $table) {
            $table->dropColumn('isolir_profile');
        });
    }
};
