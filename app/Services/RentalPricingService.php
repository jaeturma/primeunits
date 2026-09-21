<?php

namespace App\Services;

use App\Models\RentalPackage;
use App\Models\RentalUnit;
use Carbon\Carbon;

/**
 * Decimal-safe, server-side price itemization for rental and farm-service
 * bookings. All totals submitted by the browser are ignored; this service
 * is the single source of truth for what a renter is charged.
 */
class RentalPricingService
{
    public function __construct(private readonly CommissionService $commissionService) {}

    /**
     * @param  array{pricing_unit: string, area_hectares?: float|null, start_date?: string|null, end_date?: string|null, with_operator?: bool, with_transportation?: bool, rental_package?: RentalPackage|null}  $input
     * @return array<string, float|null>
     */
    public function calculate(RentalUnit $unit, array $input): array
    {
        $pricingUnit = $input['pricing_unit'] ?? RentalPackage::DurationDaily;
        $wantsOperator = (bool) ($input['with_operator'] ?? false);
        $wantsTransportation = (bool) ($input['with_transportation'] ?? false);

        $baseAmount = match ($pricingUnit) {
            RentalPackage::DurationHourly => (float) ($unit->price_per_hour ?? 0),
            RentalPackage::DurationPerHectare => (float) ($unit->price_per_hectare ?? 0) * (float) ($input['area_hectares'] ?? 0),
            RentalPackage::DurationFixedProject => (float) ($input['rental_package']?->price ?? 0),
            default => $this->dailyBase($unit, $input['start_date'] ?? null, $input['end_date'] ?? null),
        };

        $operatorFeeAmount = (! $unit->operator_included && $wantsOperator)
            ? (float) ($unit->operator_fee ?? 0)
            : 0.0;

        $transportationFeeAmount = (! $unit->transportation_included && $wantsTransportation)
            ? (float) ($unit->transportation_fee ?? 0)
            : 0.0;

        // No standalone fuel-rate column exists yet; fuel is either bundled
        // (fuel_included) or left for the provider to quote separately.
        $fuelAmount = $unit->fuel_included === false ? null : 0.0;

        $depositAmount = round((float) ($unit->security_deposit ?? 0), 2);

        $chargeableAmount = $baseAmount + $operatorFeeAmount + $transportationFeeAmount;
        $platformFeeAmount = $this->commissionService->amount($chargeableAmount);

        $discountAmount = 0.0;
        $taxAmount = 0.0;

        $totalAmount = round(
            $chargeableAmount + ($fuelAmount ?? 0) + $platformFeeAmount + $taxAmount - $discountAmount,
            2,
        );

        return [
            'base_amount' => round($baseAmount, 2),
            'operator_fee_amount' => round($operatorFeeAmount, 2),
            'transportation_fee_amount' => round($transportationFeeAmount, 2),
            'fuel_amount' => $fuelAmount === null ? null : round($fuelAmount, 2),
            'deposit_amount' => $depositAmount,
            'platform_fee_amount' => round($platformFeeAmount, 2),
            'discount_amount' => round($discountAmount, 2),
            'tax_amount' => round($taxAmount, 2),
            'total_amount' => $totalAmount,
        ];
    }

    private function dailyBase(RentalUnit $unit, ?string $start, ?string $end): float
    {
        $days = 1;

        if ($start !== null && $end !== null) {
            $days = max(1, Carbon::parse($start)->diffInDays(Carbon::parse($end)) + 1);
        }

        return (float) ($unit->price_per_day ?? 0) * $days;
    }
}
