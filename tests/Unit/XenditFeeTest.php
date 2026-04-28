<?php

namespace Tests\Unit;

use App\Models\XenditFee;
use Tests\TestCase;

class XenditFeeTest extends TestCase
{
    /**
     * Test calculation for fixed fee + 11% PPN
     */
    public function test_calculate_fixed_fee_with_tax()
    {
        $fee = new XenditFee([
            'type' => 'fixed',
            'value' => 4000
        ]);

        // Base 4000 + 11% (440) = 4440
        $this->assertEquals(4440, $fee->calculateFee(150000));
    }

    /**
     * Test calculation for percentage fee + 11% PPN
     */
    public function test_calculate_percent_fee_with_tax()
    {
        $fee = new XenditFee([
            'type' => 'percent',
            'value' => 0.63
        ]);

        // 0.63% of 150000 = 945
        // 945 + 11% (103.95) = 1048.95
        // round(1048.95) = 1049
        $this->assertEquals(1049, $fee->calculateFee(150000));
    }
}
