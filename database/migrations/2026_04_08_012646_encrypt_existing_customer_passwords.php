<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $customers = \Illuminate\Support\Facades\DB::table('customers')->get();
        foreach ($customers as $customer) {
            if ($customer->password_plain && !str_starts_with($customer->password_plain, 'eyJpdiI')) {
                \Illuminate\Support\Facades\DB::table('customers')
                    ->where('id', $customer->id)
                    ->update(['password_plain' => encrypt($customer->password_plain)]);
            }
        }
    }

    public function down(): void
    {
        $customers = \Illuminate\Support\Facades\DB::table('customers')->get();
        foreach ($customers as $customer) {
            try {
                if ($customer->password_plain) {
                    $decrypted = decrypt($customer->password_plain);
                    \Illuminate\Support\Facades\DB::table('customers')
                        ->where('id', $customer->id)
                        ->update(['password_plain' => $decrypted]);
                }
            } catch (\Exception $e) {
                //
            }
        }
    }
};
