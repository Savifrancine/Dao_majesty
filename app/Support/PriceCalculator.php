<?php

namespace App\Support;

class PriceCalculator
{
    public const BENIN_VAT_RATE = 0.18;

    public static function convertHtToTtc(float|int|string|null $priceHt, float|int|string|null $vatRate = self::BENIN_VAT_RATE): float
    {
        $priceHtValue = is_numeric($priceHt) ? (float) $priceHt : 0.0;
        $vatRateValue = is_numeric($vatRate) ? (float) $vatRate : self::BENIN_VAT_RATE;

        return round($priceHtValue * (1 + $vatRateValue), 2);
    }

    public static function convertHtToTva(float|int|string|null $priceHt, float|int|string|null $vatRate = self::BENIN_VAT_RATE): float
    {
        $priceHtValue = is_numeric($priceHt) ? (float) $priceHt : 0.0;
        $vatRateValue = is_numeric($vatRate) ? (float) $vatRate : self::BENIN_VAT_RATE;

        return round($priceHtValue * $vatRateValue, 2);
    }
}
