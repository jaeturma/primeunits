<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Orchestrates every standalone farm-harvester and agricultural-drone demo
 * seeder in dependency order, so the whole feature can be reviewed after a
 * single command.
 *
 * Not wired into DatabaseSeeder on purpose (keeps DemoSeeder/RentalSeeder's
 * existing counts stable for tests, matching the DavaoListingSeeder /
 * DavaoRentalSeeder convention already used in this project). Run
 * explicitly instead:
 *
 *   php artisan db:seed --class="Database\Seeders\PrimeUnitsFarmMarketplaceDemoSeeder"
 *
 * Demo accounts all use the project's standard demo password: "password".
 */
class PrimeUnitsFarmMarketplaceDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RbacSeeder::class,
            CategorySeeder::class,
            FarmEquipmentDemoUserSeeder::class,
            DronePilotCredentialSeeder::class,
            RiceHarvesterListingSeeder::class,
            AgriculturalDroneListingSeeder::class,
            FarmRentalAvailabilitySeeder::class,
            DroneComplianceNoticeSeeder::class,
            FarmRentalBookingSeeder::class,
        ]);
    }
}
