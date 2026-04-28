<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Hapus foreign key registration_id dari customers (jika ada)
        if (Schema::hasColumn('customers', 'registration_id')) {
            Schema::table('customers', function (Blueprint $table) {
                // Drop FK jika ada
                try { $table->dropForeign(['registration_id']); } catch (\Throwable $e) {}
                $table->dropColumn('registration_id');
            });
        }

        // 2. Drop tabel registrations
        Schema::dropIfExists('registrations');

        // 3. Drop tabel coverage_areas
        Schema::dropIfExists('coverage_areas');

        // 4. Drop tabel web_settings
        Schema::dropIfExists('web_settings');

        // 5. Drop contacts (tidak terpakai di admin panel)
        Schema::dropIfExists('contacts');
    }

    public function down(): void
    {
        // Tidak perlu rollback untuk cleanup ini
    }
};
