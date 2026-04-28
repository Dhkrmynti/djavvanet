<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class XenditFeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fees = [
            [
                'name' => 'Virtual Account',
                'code' => 'va',
                'type' => 'fixed',
                'value' => 4000,
            ],
            [
                'name' => 'QRIS',
                'code' => 'qris',
                'type' => 'percent',
                'value' => 0.63,
            ],
            [
                'name' => 'E-Wallet (OVO, DANA, LinkAja)',
                'code' => 'ewallet',
                'type' => 'percent',
                'value' => 1.5,
            ],
            [
                'name' => 'Retail (Alfamart)',
                'code' => 'retailAlfamart',
                'type' => 'fixed',
                'value' => 5500,
            ],
            [
                'name' => 'Retail (Indomaret)',
                'code' => 'retailIndomaret',
                'type' => 'fixed',
                'value' => 5500,
            ],
        ];

        foreach ($fees as $fee) {
            \App\Models\XenditFee::updateOrCreate(['code' => $fee['code']], $fee);
        }
    }
}
