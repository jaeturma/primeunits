<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Orchestrates every standalone membership-tier and private-marketplace
 * demo seeder in dependency order.
 *
 * Not wired into DatabaseSeeder on purpose (keeps DemoSeeder/RentalSeeder's
 * existing counts stable for tests, matching the DavaoListingSeeder /
 * PrimeUnitsFarmMarketplaceDemoSeeder convention already used in this
 * project). Run explicitly instead:
 *
 *   php artisan db:seed --class="Database\Seeders\PrimeUnitsMembershipDemoSeeder"
 *
 * Demo accounts all use the project's standard demo password: "password".
 */
class PrimeUnitsMembershipDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RbacSeeder::class,
            CategorySeeder::class,
            MembershipPlanSeeder::class,
            MarketplaceAccessRuleSeeder::class,
            ConfidentialityNoticeSeeder::class,
            MembershipDemoUserSeeder::class,
            PremiumStoreSeeder::class,
            PremiumListingSeeder::class,
            MembershipApplicationSeeder::class,
            RestrictedListingAccessSeeder::class,
        ]);
    }
}
