<?php

namespace App\Services;

class CommissionService
{
    public function defaultRate(): float
    {
        return (float) config('primeunits.commission_rate', 2.00);
    }

    public function amount(float $agreedPrice, ?float $rate = null): float
    {
        $commissionRate = $rate ?? $this->defaultRate();

        return round($agreedPrice * ($commissionRate / 100), 2);
    }
}
