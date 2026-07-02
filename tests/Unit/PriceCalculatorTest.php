<?php

namespace Tests\Unit;

use App\Support\PriceCalculator;
use PHPUnit\Framework\TestCase;

class PriceCalculatorTest extends TestCase
{
    public function test_converts_ht_to_ttc_with_benin_vat_rate(): void
    {
        $this->assertSame(118000.0, PriceCalculator::convertHtToTtc(100000, 0.18));
    }
}
