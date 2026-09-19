<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\RentalAvailability;
use App\Models\RentalPackage;
use App\Models\RentalUnit;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RentalSeeder extends Seeder
{
    public function run(): void
    {
        $providerA = $this->provider(
            'Cebu Rides Car Rental',
            'cebu.rides@prime.test',
            'cebu_rides',
        );
        $providerB = $this->provider(
            'Davao Transport Solutions',
            'davao.transport@prime.test',
            'davao_trnsp',
        );

        // --- Provider A units (Cebu) ---

        $hilux = $this->unit($providerA, [
            'rental_type' => RentalUnit::TypeCarRental,
            'name' => 'Toyota Hilux 2022 Pickup – Daily/Weekend Rental',
            'description' => 'Spacious pickup truck for provincial trips, farm access, and group travel. Diesel engine, 4x4, with aircon and Bluetooth audio.',
            'brand' => 'Toyota',
            'model' => 'Hilux',
            'year_model' => 2022,
            'capacity' => 5,
            'with_driver' => true,
            'price_per_day' => 3200,
            'price_per_hour' => null,
            'region' => 'Visayas',
            'province' => 'Cebu',
            'municipality' => 'City of Mandaue',
        ]);
        $this->packages($hilux, [
            ['name' => 'Day Rate', 'duration_type' => RentalPackage::DurationDaily, 'duration_value' => 1, 'price' => 3200, 'inclusions' => 'Driver, fuel allowance 20L, insurance'],
            ['name' => '3-Day Package', 'duration_type' => RentalPackage::DurationDaily, 'duration_value' => 3, 'price' => 8700, 'inclusions' => 'Driver, 60L fuel, basic insurance, one free road trip stop'],
            ['name' => 'Weekly Rate', 'duration_type' => RentalPackage::DurationWeekly, 'duration_value' => 1, 'price' => 19500, 'inclusions' => 'Driver, 150L fuel, full insurance coverage'],
        ]);
        $this->availability($hilux, blockedDays: [3, 4]);

        $innova = $this->unit($providerA, [
            'rental_type' => RentalUnit::TypeVanRental,
            'name' => 'Toyota Innova 2021 – Airport & City Transfer',
            'description' => 'Clean and spacious 8-seater van for airport pick-up and drop-off, hotel transfers, and city tours across Cebu.',
            'brand' => 'Toyota',
            'model' => 'Innova',
            'year_model' => 2021,
            'capacity' => 8,
            'with_driver' => true,
            'price_per_day' => 2800,
            'price_per_hour' => 450,
            'region' => 'Visayas',
            'province' => 'Cebu',
            'municipality' => 'Cebu City',
        ]);
        $this->packages($innova, [
            ['name' => 'Airport Transfer (One-way)', 'duration_type' => RentalPackage::DurationHourly, 'duration_value' => 3, 'price' => 1200, 'inclusions' => 'Driver, airport fee, toll'],
            ['name' => 'Full Day City Tour', 'duration_type' => RentalPackage::DurationDaily, 'duration_value' => 1, 'price' => 2800, 'inclusions' => 'Driver, fuel, parking at major stops'],
            ['name' => 'Weekend Island Hop Shuttle', 'duration_type' => RentalPackage::DurationDaily, 'duration_value' => 2, 'price' => 5000, 'inclusions' => 'Driver, fuel, island transfer coordination'],
        ]);
        $this->availability($innova, blockedDays: [6, 7]);

        $scooter = $this->unit($providerA, [
            'rental_type' => RentalUnit::TypeSelfDrive,
            'name' => 'Honda Click 125 – Self-Drive Scooter Rental',
            'description' => 'Automatic scooter ideal for solo travellers exploring Cebu. No driver needed, just show valid ID and license.',
            'brand' => 'Honda',
            'model' => 'Click 125i',
            'year_model' => 2023,
            'capacity' => 2,
            'with_driver' => false,
            'price_per_day' => 700,
            'price_per_hour' => 120,
            'region' => 'Visayas',
            'province' => 'Cebu',
            'municipality' => 'Cebu City',
        ]);
        $this->packages($scooter, [
            ['name' => 'Half Day (4 hrs)', 'duration_type' => RentalPackage::DurationHourly, 'duration_value' => 4, 'price' => 450, 'inclusions' => 'Helmet, basic insurance'],
            ['name' => 'Full Day', 'duration_type' => RentalPackage::DurationDaily, 'duration_value' => 1, 'price' => 700, 'inclusions' => 'Helmet, basic insurance, free breakdown assist'],
            ['name' => '3-Day Explorers Pass', 'duration_type' => RentalPackage::DurationDaily, 'duration_value' => 3, 'price' => 1800, 'inclusions' => 'Helmet, full insurance, one free top-up'],
        ]);
        $this->availability($scooter, blockedDays: [1]);

        $weddingCar = $this->unit($providerA, [
            'rental_type' => RentalUnit::TypeWeddingCar,
            'name' => 'Mercedes-Benz E-Class Wedding Car',
            'description' => 'Elegant white Mercedes for your wedding day. Includes ribbons, floral arrangement on hood, and uniformed driver.',
            'brand' => 'Mercedes-Benz',
            'model' => 'E200',
            'year_model' => 2020,
            'capacity' => 4,
            'with_driver' => true,
            'price_per_day' => 9800,
            'price_per_hour' => 1800,
            'region' => 'Visayas',
            'province' => 'Cebu',
            'municipality' => 'Cebu City',
        ]);
        $this->packages($weddingCar, [
            ['name' => 'Ceremony Package (4 hrs)', 'duration_type' => RentalPackage::DurationHourly, 'duration_value' => 4, 'price' => 6500, 'inclusions' => 'Uniformed driver, floral hood décor, ribbons, red carpet'],
            ['name' => 'Full Wedding Day', 'duration_type' => RentalPackage::DurationDaily, 'duration_value' => 1, 'price' => 9800, 'inclusions' => 'Full day, uniformed driver, floral décor, ribbons, standby during reception'],
        ]);
        $this->availability($weddingCar, blockedDays: [1, 2, 3]);

        // --- Provider B units (Davao) ---

        $forklift = $this->unit($providerB, [
            'rental_type' => RentalUnit::TypeEquipmentRental,
            'name' => 'Toyota 3-Ton Diesel Forklift – Warehouse Rental',
            'description' => 'Heavy-duty forklift available for daily and weekly hire. Suitable for warehouses, ports, and construction sites in Davao.',
            'brand' => 'Toyota',
            'model' => '8FD30',
            'year_model' => 2019,
            'capacity' => 3,
            'with_driver' => true,
            'price_per_day' => 5500,
            'price_per_hour' => 900,
            'region' => 'Mindanao',
            'province' => 'Davao del Sur',
            'municipality' => 'City of Davao',
        ]);
        $this->packages($forklift, [
            ['name' => 'Hourly (min 4 hrs)', 'duration_type' => RentalPackage::DurationHourly, 'duration_value' => 4, 'price' => 3500, 'inclusions' => 'Operator, fuel, insurance'],
            ['name' => 'Daily Rate', 'duration_type' => RentalPackage::DurationDaily, 'duration_value' => 1, 'price' => 5500, 'inclusions' => 'Operator, fuel, insurance, delivery within 15km'],
            ['name' => 'Weekly Rate', 'duration_type' => RentalPackage::DurationWeekly, 'duration_value' => 1, 'price' => 32000, 'inclusions' => 'Operator, fuel, insurance, on-site standby'],
        ]);
        $this->availability($forklift, blockedDays: [7]);

        $bus = $this->unit($providerB, [
            'rental_type' => RentalUnit::TypeBusRental,
            'name' => 'Hino Coaster 30-Seater Bus – Provincial Charter',
            'description' => 'Air-conditioned 30-seater bus for company outings, school trips, pilgrimages, and provincial transfers from Davao.',
            'brand' => 'Hino',
            'model' => 'Coaster 4x2',
            'year_model' => 2020,
            'capacity' => 30,
            'with_driver' => true,
            'price_per_day' => 12000,
            'price_per_hour' => null,
            'region' => 'Mindanao',
            'province' => 'Davao del Sur',
            'municipality' => 'City of Davao',
        ]);
        $this->packages($bus, [
            ['name' => 'Day Charter (City of Davao)', 'duration_type' => RentalPackage::DurationDaily, 'duration_value' => 1, 'price' => 12000, 'inclusions' => 'Driver, conductor, fuel within City of Davao'],
            ['name' => 'Provincial Trip (Round)', 'duration_type' => RentalPackage::DurationDaily, 'duration_value' => 2, 'price' => 22000, 'inclusions' => 'Driver, conductor, fuel, toll fees'],
            ['name' => 'Company Outing Package', 'duration_type' => RentalPackage::DurationDaily, 'duration_value' => 3, 'price' => 30000, 'inclusions' => 'Driver, conductor, fuel, cooler on board, toll'],
        ]);
        $this->availability($bus, blockedDays: [6, 7]);

        $truck = $this->unit($providerB, [
            'rental_type' => RentalUnit::TypeTruckRental,
            'name' => 'Isuzu Elf Cargo Truck – Delivery Rental',
            'description' => 'Light commercial truck ideal for moving furniture, construction materials, or agricultural produce around Davao.',
            'brand' => 'Isuzu',
            'model' => 'Elf 4HF1',
            'year_model' => 2021,
            'capacity' => 2,
            'with_driver' => true,
            'price_per_day' => 4200,
            'price_per_hour' => 700,
            'region' => 'Mindanao',
            'province' => 'Davao del Sur',
            'municipality' => 'City of Davao',
        ]);
        $this->packages($truck, [
            ['name' => 'Half Day (4 hrs)', 'duration_type' => RentalPackage::DurationHourly, 'duration_value' => 4, 'price' => 2600, 'inclusions' => 'Driver, fuel, loading assist'],
            ['name' => 'Full Day', 'duration_type' => RentalPackage::DurationDaily, 'duration_value' => 1, 'price' => 4200, 'inclusions' => 'Driver, fuel, loading assist, toll'],
            ['name' => 'Weekly Rate', 'duration_type' => RentalPackage::DurationWeekly, 'duration_value' => 1, 'price' => 24000, 'inclusions' => 'Driver, fuel, full insurance'],
        ]);
        $this->availability($truck, blockedDays: [1]);

        $shuttle = $this->unit($providerB, [
            'rental_type' => RentalUnit::TypeShuttle,
            'name' => 'Toyota HiAce Shuttle – Office & Hotel Runs',
            'description' => '12-seater HiAce shuttle for daily office commute contracts, hotel-to-airport, and regular event transfers in Davao.',
            'brand' => 'Toyota',
            'model' => 'HiAce Grandia',
            'year_model' => 2022,
            'capacity' => 12,
            'with_driver' => true,
            'price_per_day' => 3800,
            'price_per_hour' => 620,
            'region' => 'Mindanao',
            'province' => 'Davao del Sur',
            'municipality' => 'City of Davao',
        ]);
        $this->packages($shuttle, [
            ['name' => 'Airport Shuttle (One-way)', 'duration_type' => RentalPackage::DurationHourly, 'duration_value' => 2, 'price' => 1100, 'inclusions' => 'Driver, fuel, toll'],
            ['name' => 'Daily Office Run', 'duration_type' => RentalPackage::DurationDaily, 'duration_value' => 1, 'price' => 3800, 'inclusions' => 'Driver, fuel, 2 trips AM/PM'],
            ['name' => 'Monthly Contract', 'duration_type' => RentalPackage::DurationMonthly, 'duration_value' => 1, 'price' => 62000, 'inclusions' => 'Dedicated driver, fuel, 2 daily trips, GPS tracking'],
        ]);
        $this->availability($shuttle, blockedDays: [7]);
    }

    private function provider(string $name, string $email, string $username): User
    {
        $role = Role::query()->where('name', 'rental_provider')->firstOrFail();

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'username' => $username,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ],
        );

        $user->roles()->syncWithoutDetaching([$role->id]);
        $user->notificationPreferenceOrDefault();

        return $user->fresh();
    }

    private function unit(User $provider, array $attributes): RentalUnit
    {
        return RentalUnit::query()->updateOrCreate(
            ['name' => $attributes['name']],
            [
                'user_id' => $provider->id,
                'status' => RentalUnit::StatusApproved,
                'approved_at' => now()->subDays(rand(5, 30)),
                'views_count' => rand(12, 340),
                ...$attributes,
            ],
        );
    }

    /** Seed 90 days of availability; $blockedDays are ISO day-of-week numbers (1=Mon … 7=Sun). */
    private function availability(RentalUnit $unit, array $blockedDays = []): void
    {
        $start = now()->toDateString();
        $end   = now()->addDays(90)->toDateString();

        foreach (CarbonPeriod::create($start, $end) as $date) {
            $isBlocked = in_array($date->isoWeekday(), $blockedDays);
            RentalAvailability::query()->updateOrCreate(
                ['rental_unit_id' => $unit->id, 'date' => $date->toDateString()],
                ['is_available' => ! $isBlocked],
            );
        }
    }

    /**
     * @param  array<int, array{name: string, duration_type: string, duration_value: int, price: float|int, inclusions: string}>  $packages
     */
    private function packages(RentalUnit $unit, array $packages): void
    {
        foreach ($packages as $pkg) {
            RentalPackage::query()->updateOrCreate(
                ['rental_unit_id' => $unit->id, 'name' => $pkg['name']],
                [
                    'duration_type' => $pkg['duration_type'],
                    'duration_value' => $pkg['duration_value'],
                    'price' => $pkg['price'],
                    'inclusions' => $pkg['inclusions'],
                    'is_active' => true,
                ],
            );
        }
    }
}
