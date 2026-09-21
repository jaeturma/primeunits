<?php

namespace Database\Seeders;

use App\Models\RentalAvailability;
use App\Models\RentalUnit;
use Illuminate\Database\Seeder;

/**
 * Standalone demo data: 30 days of availability for every seeded farm
 * harvester and agricultural drone rental/service listing, including an
 * available date, an unavailable maintenance date, and a reserved date.
 *
 * Requires RiceHarvesterListingSeeder and AgriculturalDroneListingSeeder
 * to have run first.
 */
class FarmRentalAvailabilitySeeder extends Seeder
{
    private const array UNIT_NAMES = [
        'Modern Rice Combine Harvester with Operator',
        'Complete Rice Harvesting Service per Hectare',
        'Agricultural Drone Spraying Service with Verified Pilot',
        'Agricultural Spraying Drone with Licensed Pilot',
        'Farm Mapping and Crop Monitoring Drone Service',
        'Agricultural Drone Rental Pending Operator Verification',
    ];

    public function run(): void
    {
        $this->call([RiceHarvesterListingSeeder::class, AgriculturalDroneListingSeeder::class]);

        foreach (self::UNIT_NAMES as $name) {
            $unit = RentalUnit::query()->where('name', $name)->first();

            if ($unit !== null) {
                $this->availability($unit);
            }
        }
    }

    private function availability(RentalUnit $unit): void
    {
        $start = now()->startOfDay();
        $rows = [];

        for ($day = 0; $day < 30; $day++) {
            $date = $start->copy()->addDays($day);

            [$isAvailable, $note] = match (true) {
                $day === 5 => [false, 'Scheduled maintenance'],
                $day === 12 => [false, 'Reserved'],
                default => [true, null],
            };

            $rows[] = [
                'rental_unit_id' => $unit->id,
                'date' => $date->toDateString(),
                'is_available' => $isAvailable,
                'note' => $note,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        RentalAvailability::query()->where('rental_unit_id', $unit->id)->delete();
        RentalAvailability::query()->insert($rows);
    }
}
