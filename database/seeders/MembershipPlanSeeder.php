<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

/**
 * Standalone demo data: the three PrimeUnits membership plans. Prices
 * are clearly labeled demonstration values, not final business pricing.
 */
class MembershipPlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::query()->updateOrCreate(
            ['type' => Plan::TypeMembership, 'tier' => Plan::TierRegular],
            [
                'name' => 'PrimeUnits Regular (Demo Pricing)',
                'price' => 0,
                'duration_days' => null,
                'features' => [
                    'Browse and search the general marketplace',
                    'Publish listings after basic identity verification',
                    'Contact protected sellers and submit offers',
                ],
                'is_active' => true,
            ],
        );

        Plan::query()->updateOrCreate(
            ['type' => Plan::TypeMembership, 'tier' => Plan::TierSilver],
            [
                'name' => 'PrimeUnits Silver (Demo Pricing)',
                'price' => 1999,
                'duration_days' => 365,
                'features' => [
                    'Access to premium and performance vehicle listings',
                    'Enhanced verification badge',
                    'Priority support queue',
                ],
                'is_active' => true,
            ],
        );

        Plan::query()->updateOrCreate(
            ['type' => Plan::TypeMembership, 'tier' => Plan::TierGold],
            [
                'name' => 'PrimeUnits Gold (Demo Pricing)',
                'price' => 9999,
                'duration_days' => 365,
                'features' => [
                    'Private, professionally reviewed marketplace access',
                    'Aircraft, yacht, and rare collector listings',
                    'Manual Gold-access approval and confidentiality protections',
                ],
                'is_active' => true,
            ],
        );
    }
}
