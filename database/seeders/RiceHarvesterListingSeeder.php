<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Listing;
use App\Models\RentalPackage;
use App\Models\RentalUnit;
use App\Models\ResourceAttachment;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Standalone demo data: three rice combine harvester listings covering
 * rental-with-operator, sale, and full per-hectare harvesting service.
 *
 * Requires FarmEquipmentDemoUserSeeder and CategorySeeder to have run first.
 */
class RiceHarvesterListingSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([CategorySeeder::class, FarmEquipmentDemoUserSeeder::class]);

        $provider = User::query()->where('email', 'provider.goldenfields@primeunits.test')->firstOrFail();
        $category = Category::query()->where('slug', 'agricultural-equipment')->firstOrFail();

        $this->rentalWithOperator($provider, $category);
        $this->saleListing($provider, $category);
        $this->harvestingService($provider, $category);
    }

    private function rentalWithOperator(User $provider, Category $category): void
    {
        $unit = RentalUnit::query()->updateOrCreate(
            ['name' => 'Modern Rice Combine Harvester with Operator'],
            [
                'user_id' => $provider->id,
                'category_id' => $category->id,
                'rental_type' => RentalUnit::TypeHarvesterRental,
                'description' => 'Modern rice combine harvester available for rent with a qualified operator. Handles 0.8 to 1.2 hectares per hour, well-maintained and field-ready.',
                'brand' => 'Kubota',
                'model' => 'DC-70',
                'year_model' => 2022,
                'price_per_day' => 18000,
                'price_per_hectare' => 3500,
                'operator_fee' => 0,
                'transportation_fee' => 2500,
                'operator_included' => true,
                'transportation_included' => false,
                'minimum_area_hectares' => 1,
                'region' => 'Region XI (Davao Region)',
                'province' => 'Davao del Norte',
                'municipality' => 'City of Tagum',
                'status' => RentalUnit::StatusApproved,
                'approved_at' => now()->subDays(20),
                'views_count' => 480,
            ],
        );

        RentalPackage::query()->updateOrCreate(
            ['rental_unit_id' => $unit->id, 'name' => 'Per Hectare Service'],
            ['duration_type' => RentalPackage::DurationPerHectare, 'duration_value' => 1, 'price' => 3500, 'inclusions' => 'Operator included, fuel excluded', 'is_active' => true],
        );
        RentalPackage::query()->updateOrCreate(
            ['rental_unit_id' => $unit->id, 'name' => 'Day Rate'],
            ['duration_type' => RentalPackage::DurationDaily, 'duration_value' => 1, 'price' => 18000, 'inclusions' => 'Operator included, fuel excluded', 'is_active' => true],
        );
    }

    private function saleListing(User $provider, Category $category): void
    {
        $listing = Listing::query()->updateOrCreate(
            ['title' => 'Compact Rice Combine Harvester for Sale'],
            [
                'user_id' => $provider->id,
                'seller_profile_id' => $provider->sellerProfile->id,
                'category_id' => $category->id,
                'description' => 'Reconditioned compact rice combine harvester, ready for immediate field use. Full maintenance history available on request; ownership documents available for review.',
                'price' => 780000,
                'negotiable' => true,
                'condition' => Listing::ConditionReconditioned,
                'year_model' => 2018,
                'brand' => 'Yanmar',
                'model' => 'AW70V',
                'region' => 'Region XI (Davao Region)',
                'province' => 'Davao del Sur',
                'municipality' => 'City of Digos',
                'status' => Listing::StatusApproved,
                'approved_at' => now()->subDays(10),
            ],
        );

        $this->specs($category, 'Rice Combine Harvester', $listing, ['hour_meter_reading' => '2140']);

        ResourceAttachment::query()->updateOrCreate(
            ['attachable_type' => Listing::class, 'attachable_id' => $listing->id, 'name' => 'Ownership Transfer Documents'],
            [
                'user_id' => $provider->id,
                'path' => 'demo/listings/attachments/harvester-ownership-documents.pdf',
                'mime_type' => 'application/pdf',
                'size' => 245000,
            ],
        );
    }

    private function harvestingService(User $provider, Category $category): void
    {
        $unit = RentalUnit::query()->updateOrCreate(
            ['name' => 'Complete Rice Harvesting Service per Hectare'],
            [
                'user_id' => $provider->id,
                'category_id' => $category->id,
                'rental_type' => RentalUnit::TypeHarvesterService,
                'description' => 'Full rice harvesting service charged per hectare, operator and transportation included within the configured coverage area.',
                'brand' => 'Kubota',
                'model' => 'DC-70',
                'year_model' => 2023,
                'price_per_day' => 20000,
                'price_per_hectare' => 3800,
                'operator_included' => true,
                'transportation_included' => true,
                'minimum_area_hectares' => 2,
                'minimum_rental_duration' => '2 hectares minimum',
                'service_coverage_area' => 'Nabunturan, Compostela, and Monkayo, Davao de Oro',
                'region' => 'Region XI (Davao Region)',
                'province' => 'Davao de Oro',
                'municipality' => 'Nabunturan',
                'status' => RentalUnit::StatusApproved,
                'approved_at' => now()->subDays(5),
                'views_count' => 610,
            ],
        );

        RentalPackage::query()->updateOrCreate(
            ['rental_unit_id' => $unit->id, 'name' => 'Per Hectare Harvesting Service'],
            ['duration_type' => RentalPackage::DurationPerHectare, 'duration_value' => 1, 'price' => 3800, 'inclusions' => 'Operator and transportation included within coverage area', 'is_active' => true],
        );
    }

    /**
     * @param  array<string, string>  $extra
     */
    private function specs(Category $category, string $classification, Listing $listing, array $extra = []): void
    {
        $values = [
            'equipment_type' => $classification,
            'fuel_type' => 'Diesel',
            'supported_crops' => 'Rice',
            'operating_capacity_ha_per_hour' => '1.0',
            ...$extra,
        ];

        foreach ($values as $name => $value) {
            $field = $category->specFields()->where('name', $name)->first();

            if ($field !== null) {
                $listing->specValues()->updateOrCreate(
                    ['spec_field_id' => $field->id],
                    ['value' => $value],
                );
            }
        }
    }
}
