<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $settings = \Illuminate\Support\Facades\DB::table('mikrotik_settings')->get();
        foreach ($settings as $setting) {
            if (!str_starts_with($setting->password, 'eyJpdiI')) {
                \Illuminate\Support\Facades\DB::table('mikrotik_settings')
                    ->where('id', $setting->id)
                    ->update(['password' => encrypt($setting->password)]);
            }
        }
    }

    public function down(): void
    {
        $settings = \Illuminate\Support\Facades\DB::table('mikrotik_settings')->get();
        foreach ($settings as $setting) {
            try {
                $decrypted = decrypt($setting->password);
                \Illuminate\Support\Facades\DB::table('mikrotik_settings')
                    ->where('id', $setting->id)
                    ->update(['password' => $decrypted]);
            } catch (\Exception $e) {
                // 
            }
        }
    }
};
