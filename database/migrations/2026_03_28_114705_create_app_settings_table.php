<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('label')->nullable(); // Human-readable label
            $table->string('group')->default('general');
            $table->timestamps();
        });

        // Seed default billing settings
        DB::table('app_settings')->insert([
            ['key' => 'billing_due_days',   'value' => '7',  'label' => 'Jatuh Tempo Tagihan (Hari)',    'group' => 'billing', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'billing_tax_percent','value' => '11', 'label' => 'Persentase Pajak (%)',           'group' => 'billing', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'company_name',       'value' => 'DJAVVA Net', 'label' => 'Nama Perusahaan',       'group' => 'general', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
