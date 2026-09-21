<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\RentalPackage;
use App\Models\RentalUnit;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Standalone demo data: four agricultural drone listings covering a
 * verified-pilot spraying service, a licensed-pilot rental, a mapping and
 * crop-monitoring service, and a rental still awaiting operator
 * verification (demonstrates the compliance-blocked booking path).
 *
 * Requires FarmEquipmentDemoUserSeeder, DronePilotCredentialSeeder, and
 * CategorySeeder to have run first.
 */
class AgriculturalDroneListingSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([CategorySeeder::class, DronePilotCredentialSeeder::class]);

        $provider = User::query()->where('email', 'provider.agriflight@primeunits.test')->firstOrFail();
        $category = Category::query()->where('slug', 'agricultural-equipment')->firstOrFail();
        $verifiedPilot = User::query()->where('email', 'pilot.verified@primeunits.test')->firstOrFail();
        $pendingPilot = User::query()->where('email', 'pilot.pending@primeunits.test')->firstOrFail();

        $this->sprayingService($provider, $category, $verifiedPilot);
        $this->sprayingDroneRental($provider, $category, $verifiedPilot);
        $this->mappingService($provider, $category, $verifiedPilot);
        $this->pendingVerificationRental($provider, $category, $pendingPilot);
    }

    private function sprayingService(User $provider, Category $category, User $pilot): void
    {
        $unit = RentalUnit::query()->updateOrCreate(
            ['name' => 'Agricultural Drone Spraying Service with Verified Pilot'],
            [
                'user_id' => $provider->id,
                'category_id' => $category->id,
                'rental_type' => RentalUnit::TypeDroneService,
                'description' => 'Crop spraying and fertilizer application service performed by a verified drone operator. Priced per hectare.',
                'brand' => 'DJI',
                'model' => 'Agras T30',
                'year_model' => 2023,
                'price_per_day' => 9000,
                'price_per_hectare' => 650,
                'operator_included' => true,
                'transportation_included' => true,
                'requires_verified_drone_operator' => true,
                'allows_self_operation' => false,
                'intended_uses' => 'Crop spraying, fertilizer application',
                'drone_pilot_user_id' => $pilot->id,
                'compliance_status' => RentalUnit::ComplianceVerifiedOperatorAssigned,
                'region' => 'Region XI (Davao Region)',
                'province' => 'Davao del Norte',
                'municipality' => 'City of Panabo',
                'status' => RentalUnit::StatusApproved,
                'approved_at' => now()->subDays(15),
                'views_count' => 720,
            ],
        );

        RentalPackage::query()->updateOrCreate(
            ['rental_unit_id' => $unit->id, 'name' => 'Per Hectare Spraying Service'],
            ['duration_type' => RentalPackage::DurationPerHectare, 'duration_value' => 1, 'price' => 650, 'inclusions' => 'Verified operator and transportation included', 'is_active' => true],
        );
    }

    private function sprayingDroneRental(User $provider, Category $category, User $pilot): void
    {
        RentalUnit::query()->updateOrCreate(
            ['name' => 'Agricultural Spraying Drone with Licensed Pilot'],
            [
                'user_id' => $provider->id,
                'category_id' => $category->id,
                'rental_type' => RentalUnit::TypeDroneRental,
                'description' => 'Agricultural spraying drone available for rent with a licensed pilot. 16L spray tank capacity, two batteries and charging equipment included.',
                'brand' => 'XAG',
                'model' => 'P100 Pro',
                'year_model' => 2023,
                'price_per_day' => 7500,
                'price_per_hour' => 1200,
                'operator_included' => true,
                'transportation_included' => false,
                'requires_verified_drone_operator' => true,
                'allows_self_operation' => false,
                'intended_uses' => 'Crop spraying',
                'drone_pilot_user_id' => $pilot->id,
                'compliance_status' => RentalUnit::ComplianceVerifiedOperatorAssigned,
                'region' => 'Region XI (Davao Region)',
                'province' => 'Davao del Norte',
                'municipality' => 'City of Panabo',
                'status' => RentalUnit::StatusApproved,
                'approved_at' => now()->subDays(8),
                'views_count' => 340,
            ],
        );
    }

    private function mappingService(User $provider, Category $category, User $pilot): void
    {
        $unit = RentalUnit::query()->updateOrCreate(
            ['name' => 'Farm Mapping and Crop Monitoring Drone Service'],
            [
                'user_id' => $provider->id,
                'category_id' => $category->id,
                'rental_type' => RentalUnit::TypeDroneService,
                'description' => 'Field mapping, crop monitoring, and multispectral imaging service using an RTK-capable mapping drone flown by a verified operator.',
                'brand' => 'DJI',
                'model' => 'Matrice 350 RTK',
                'year_model' => 2023,
                'price_per_day' => 12000,
                'price_per_hectare' => 450,
                'operator_included' => true,
                'transportation_included' => true,
                'requires_verified_drone_operator' => true,
                'allows_self_operation' => false,
                'intended_uses' => 'Field mapping, crop monitoring, multispectral imaging',
                'drone_pilot_user_id' => $pilot->id,
                'compliance_status' => RentalUnit::ComplianceVerifiedOperatorAssigned,
                'region' => 'Region XI (Davao Region)',
                'province' => 'Davao del Norte',
                'municipality' => 'City of Panabo',
                'status' => RentalUnit::StatusApproved,
                'approved_at' => now()->subDays(3),
                'views_count' => 210,
            ],
        );

        RentalPackage::query()->updateOrCreate(
            ['rental_unit_id' => $unit->id, 'name' => 'Per Hectare Mapping'],
            ['duration_type' => RentalPackage::DurationPerHectare, 'duration_value' => 1, 'price' => 450, 'inclusions' => 'Verified operator included', 'is_active' => true],
        );
        RentalPackage::query()->updateOrCreate(
            ['rental_unit_id' => $unit->id, 'name' => 'Fixed Project Quotation'],
            ['duration_type' => RentalPackage::DurationFixedProject, 'duration_value' => 1, 'price' => 35000, 'inclusions' => 'Full-farm mapping project, verified operator included', 'is_active' => true],
        );
    }

    private function pendingVerificationRental(User $provider, Category $category, User $pendingPilot): void
    {
        RentalUnit::query()->updateOrCreate(
            ['name' => 'Agricultural Drone Rental Pending Operator Verification'],
            [
                'user_id' => $provider->id,
                'category_id' => $category->id,
                'rental_type' => RentalUnit::TypeDroneRental,
                'description' => 'Agricultural drone available for rent, pending completion of the assigned operator\'s credential verification. Not currently bookable for verified-operator service.',
                'brand' => 'XAG',
                'model' => 'P60',
                'year_model' => 2022,
                'price_per_day' => 6000,
                'operator_included' => false,
                'transportation_included' => false,
                'requires_verified_drone_operator' => true,
                'allows_self_operation' => false,
                'intended_uses' => 'Crop spraying',
                'drone_pilot_user_id' => $pendingPilot->id,
                'compliance_status' => RentalUnit::ComplianceUnresolved,
                'region' => 'Region XI (Davao Region)',
                'province' => 'Davao del Norte',
                'municipality' => 'City of Panabo',
                'status' => RentalUnit::StatusApproved,
                'approved_at' => now()->subDays(1),
                'views_count' => 40,
            ],
        );
    }
}
