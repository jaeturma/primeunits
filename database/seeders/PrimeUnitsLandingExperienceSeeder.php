<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Orchestrates every standalone demo seeder needed to review the tier
 * theme / mobile landing / listing feed feature end to end: membership
 * demo users (Regular/Silver/Gold, including an expired-Gold and a
 * suspended-Silver account to exercise the safe fallback), enough eligible
 * listings for at least three full 12-position batches, Featured and
 * Sponsored placements, and feed-eligible advertisements.
 *
 * Not wired into DatabaseSeeder, matching the PrimeUnitsMembershipDemoSeeder
 * convention. Run explicitly:
 *
 *   php artisan db:seed --class="Database\Seeders\PrimeUnitsLandingExperienceSeeder"
 *
 * Demo accounts use the project's standard demo password: "password".
 */
class PrimeUnitsLandingExperienceSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            LandingListingFeedSeeder::class,
            LandingAdvertisementSeeder::class,
        ]);
    }
}
