<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CommissionLog;
use App\Models\FinancingPartner;
use App\Models\FinancingProduct;
use App\Models\Lead;
use App\Models\Listing;
use App\Models\ListingBoost;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\PrimeUnitsNotification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = $this->users();
        $plans = $this->plans();
        $listings = $this->listings($users);
        $leads = $this->leads($users, $listings);

        $this->financingPartners();
        $this->transactions($leads);
        $this->monetization($users, $listings, $plans);
        $this->notifications($users);
    }

    /**
     * @return array<string, User>
     */
    private function users(): array
    {
        return app(UsersSeeder::class)->seedUsers();
    }

    /**
     * @return array<string, Plan>
     */
    private function plans(): array
    {
        $subscription = Plan::query()->updateOrCreate(
            ['name' => 'Prime Seller Monthly'],
            [
                'type' => Plan::TypeSubscription,
                'price' => 999,
                'duration_days' => 30,
                'features' => ['priority seller badge', 'higher listing quota', 'analytics access'],
                'is_active' => true,
            ],
        );

        $boost = Plan::query()->updateOrCreate(
            ['name' => '7-Day Listing Boost'],
            [
                'type' => Plan::TypeBoost,
                'price' => 499,
                'duration_days' => 7,
                'features' => ['featured placement', 'highlighted listing card'],
                'is_active' => true,
            ],
        );

        return compact('subscription', 'boost');
    }

    /**
     * @param  array<string, User>  $users
     * @return array<string, Listing>
     */
    private function listings(array $users): array
    {
        $vehicles = Category::query()->where('slug', 'cars')->firstOrFail();
        $farm = Category::query()->where('slug', 'agricultural-equipment')->firstOrFail();
        $heavy = Category::query()->where('slug', 'heavy-equipment')->firstOrFail();
        $construction = Category::query()->where('slug', 'other-units')->firstOrFail();
        $motorcycle = Category::query()->where('slug', 'motorcycles')->firstOrFail();
        $commercial = Category::query()->where('slug', 'commercial-vehicles')->firstOrFail();
        $electric = Category::query()->where('slug', 'electric-vehicles')->firstOrFail();

        $listings = [
            'pickup' => $this->listing($users['sellerOne'], $vehicles, [
                'title' => '2022 Toyota Hilux 4x4',
                'description' => 'Fleet-maintained pickup with updated registration and service records.',
                'price' => 1280000,
                'condition' => Listing::ConditionUsed,
                'year_model' => 2022,
                'brand' => 'Toyota',
                'model' => 'Hilux',
                'region' => 'Region VII',
                'province' => 'Cebu',
                'municipality' => 'City of Mandaue',
                'status' => Listing::StatusApproved,
                'approved_at' => now()->subDays(35),
                'created_at' => now()->subDays(40),
            ], ['body_type' => 'Pickup', 'transmission' => 'automatic', 'fuel_type' => 'Diesel', 'mileage' => '28000', 'color' => 'white']),
            'sedan' => $this->listing($users['sellerOne'], $vehicles, [
                'title' => '2021 Toyota Vios 1.3 XLE Sedan',
                'description' => 'City sedan with complete maintenance records and clean interior.',
                'price' => 548000,
                'condition' => Listing::ConditionUsed,
                'year_model' => 2021,
                'brand' => 'Toyota',
                'model' => 'Vios',
                'region' => 'Luzon',
                'province' => 'Metro Manila',
                'municipality' => 'Quezon City',
                'status' => Listing::StatusApproved,
                'approved_at' => now()->subDays(34),
                'created_at' => now()->subDays(38),
            ], ['body_type' => 'Sedan', 'transmission' => 'automatic', 'fuel_type' => 'Gasoline', 'mileage' => '39000', 'color' => 'silver']),
            'suv' => $this->listing($users['sellerTwo'], $vehicles, [
                'title' => '2020 Mitsubishi Montero Sport SUV',
                'description' => 'Family SUV with diesel engine, leather seats, and fresh service.',
                'price' => 1185000,
                'condition' => Listing::ConditionUsed,
                'year_model' => 2020,
                'brand' => 'Mitsubishi',
                'model' => 'Montero Sport',
                'region' => 'Mindanao',
                'province' => 'Davao del Sur',
                'municipality' => 'City of Davao',
                'status' => Listing::StatusApproved,
                'approved_at' => now()->subDays(33),
                'created_at' => now()->subDays(37),
            ], ['body_type' => 'SUV', 'transmission' => 'automatic', 'fuel_type' => 'Diesel', 'mileage' => '56000', 'color' => 'black']),
            'van' => $this->listing($users['sellerOne'], $vehicles, [
                'title' => '2019 Hyundai H-100 Utility Van',
                'description' => 'Light commercial van ready for deliveries and field service.',
                'price' => 690000,
                'condition' => Listing::ConditionUsed,
                'year_model' => 2019,
                'brand' => 'Hyundai',
                'model' => 'H-100',
                'region' => 'Visayas',
                'province' => 'Cebu',
                'municipality' => 'City of Mandaue',
                'status' => Listing::StatusApproved,
                'approved_at' => now()->subDays(31),
                'created_at' => now()->subDays(36),
            ], ['body_type' => 'Van', 'transmission' => 'manual', 'fuel_type' => 'Diesel', 'mileage' => '72000', 'color' => 'white']),
            'tractor' => $this->listing($users['sellerTwo'], $farm, [
                'title' => 'Kubota 45HP Farm Tractor',
                'description' => 'Reliable tractor with rotary tiller attachment for rice and corn farms.',
                'price' => 865000,
                'condition' => Listing::ConditionUsed,
                'year_model' => 2020,
                'brand' => 'Kubota',
                'model' => 'L4508',
                'region' => 'Region XI',
                'province' => 'Davao del Sur',
                'municipality' => 'City of Davao',
                'status' => Listing::StatusApproved,
                'approved_at' => now()->subDays(22),
                'created_at' => now()->subDays(26),
            ], ['equipment_type' => 'Tractor', 'horsepower' => '45', 'fuel_type' => 'Diesel', 'usage_hours' => '1160']),
            'harvester' => $this->listing($users['sellerTwo'], $farm, [
                'title' => 'Yanmar Compact Rice Harvester',
                'description' => 'Good as new harvester for small to mid-size rice farms.',
                'price' => 1420000,
                'condition' => Listing::ConditionSurplus,
                'year_model' => 2021,
                'brand' => 'Yanmar',
                'model' => 'AW70V',
                'region' => 'Mindanao',
                'province' => 'Davao del Sur',
                'municipality' => 'City of Davao',
                'status' => Listing::StatusApproved,
                'approved_at' => now()->subDays(21),
                'created_at' => now()->subDays(25),
            ], ['equipment_type' => 'Combine Harvester', 'horsepower' => '70', 'fuel_type' => 'Diesel', 'usage_hours' => '840']),
            'loader' => $this->listing($users['sellerOne'], $heavy, [
                'title' => 'Komatsu Wheel Loader WA200',
                'description' => 'Ready-for-work loader for quarry, hauling, and site preparation.',
                'price' => 2350000,
                'condition' => Listing::ConditionSurplus,
                'year_model' => 2018,
                'brand' => 'Komatsu',
                'model' => 'WA200',
                'region' => 'Region VII',
                'province' => 'Cebu',
                'municipality' => 'City of Lapu-Lapu',
                'status' => Listing::StatusApproved,
                'approved_at' => now()->subDays(15),
                'created_at' => now()->subDays(19),
            ], ['equipment_type' => 'Wheel Loader', 'operating_weight' => '11300 kg', 'bucket_capacity' => '1.9 m3', 'engine_power' => '126 HP']),
            'excavator' => $this->listing($users['sellerOne'], $heavy, [
                'title' => 'Caterpillar 320D Hydraulic Excavator',
                'description' => 'Heavy excavator inspected for site development and quarry work.',
                'price' => 3850000,
                'condition' => Listing::ConditionSurplus,
                'year_model' => 2017,
                'brand' => 'Caterpillar',
                'model' => '320D',
                'region' => 'Visayas',
                'province' => 'Cebu',
                'municipality' => 'City of Lapu-Lapu',
                'status' => Listing::StatusApproved,
                'approved_at' => now()->subDays(14),
                'created_at' => now()->subDays(18),
            ], ['equipment_type' => 'Excavator', 'operating_weight' => '20930 kg', 'bucket_capacity' => '1.2 m3', 'engine_power' => '148 HP']),
            'forklift' => $this->listing($users['sellerTwo'], $heavy, [
                'title' => 'Toyota 3-Ton Diesel Forklift',
                'description' => 'Warehouse forklift with tested hydraulics and fresh tires.',
                'price' => 520000,
                'condition' => Listing::ConditionUsed,
                'year_model' => 2018,
                'brand' => 'Toyota',
                'model' => '8FD30',
                'region' => 'Luzon',
                'province' => 'Laguna',
                'municipality' => 'City of Calamba',
                'status' => Listing::StatusApproved,
                'approved_at' => now()->subDays(12),
                'created_at' => now()->subDays(16),
            ], ['equipment_type' => 'Forklift', 'operating_weight' => '4300 kg', 'bucket_capacity' => '3 ton lift', 'engine_power' => 'diesel']),
            'mixer' => $this->listing($users['sellerOne'], $construction, [
                'title' => 'Portable Concrete Mixer 1-Bag Capacity',
                'description' => 'Light construction equipment for small contractors and repair teams.',
                'price' => 68000,
                'condition' => Listing::ConditionBrandNew,
                'brand' => 'PrimeBuild',
                'model' => 'CM-1B',
                'region' => 'Luzon',
                'province' => 'Batangas',
                'municipality' => 'Lemery',
                'status' => Listing::StatusApproved,
                'approved_at' => now()->subDays(10),
                'created_at' => now()->subDays(13),
            ], ['unit_type' => 'Industrial Equipment', 'power_source' => 'gasoline engine', 'capacity' => '1 bag', 'dimensions' => 'portable tow frame']),
            'generator' => $this->listing($users['sellerTwo'], $construction, [
                'title' => 'Silent Type Diesel Generator 25kVA',
                'description' => 'Backup power unit for shops, farms, and field offices.',
                'price' => 245000,
                'condition' => Listing::ConditionUsed,
                'year_model' => 2022,
                'brand' => 'Kipor',
                'model' => 'KDE25',
                'region' => 'Mindanao',
                'province' => 'Davao del Sur',
                'municipality' => 'City of Davao',
                'status' => Listing::StatusApproved,
                'approved_at' => now()->subDays(9),
                'created_at' => now()->subDays(12),
            ], ['unit_type' => 'Generator', 'power_source' => 'diesel', 'capacity' => '25 kVA', 'dimensions' => 'silent canopy']),
            'sportBike' => $this->listing($users['sellerOne'], $motorcycle, [
                'title' => '2022 Yamaha R15 Sports Motorcycle',
                'description' => 'Sports bike with low mileage and fresh registration.',
                'price' => 138000,
                'condition' => Listing::ConditionUsed,
                'year_model' => 2022,
                'brand' => 'Yamaha',
                'model' => 'R15',
                'region' => 'Visayas',
                'province' => 'Cebu',
                'municipality' => 'City of Mandaue',
                'status' => Listing::StatusApproved,
                'approved_at' => now()->subDays(8),
                'created_at' => now()->subDays(10),
            ], ['motorcycle_type' => 'Sports', 'fuel_type' => 'Gasoline', 'mileage' => '9200', 'displacement' => '155cc']),
            'adventureBike' => $this->listing($users['sellerTwo'], $motorcycle, [
                'title' => '2021 KTM 390 Adventure Big Bike',
                'description' => 'Adventure motorcycle set up for weekend rides and provincial roads.',
                'price' => 265000,
                'condition' => Listing::ConditionUsed,
                'year_model' => 2021,
                'brand' => 'KTM',
                'model' => '390 Adventure',
                'region' => 'Luzon',
                'province' => 'Pampanga',
                'municipality' => 'City of San Fernando',
                'status' => Listing::StatusApproved,
                'approved_at' => now()->subDays(7),
                'created_at' => now()->subDays(9),
            ], ['motorcycle_type' => 'Adventure / Touring', 'fuel_type' => 'Gasoline', 'mileage' => '14800', 'displacement' => '373cc']),
            'ebike' => $this->listing($users['sellerOne'], $motorcycle, [
                'title' => 'Brand New Electric E-Bike Utility Scooter',
                'description' => 'EV scooter for barangay errands, short commutes, and fleet use.',
                'price' => 52000,
                'condition' => Listing::ConditionBrandNew,
                'year_model' => 2026,
                'brand' => 'Yadea',
                'model' => 'Utility E8',
                'region' => 'Luzon',
                'province' => 'Metro Manila',
                'municipality' => 'City of Makati',
                'status' => Listing::StatusApproved,
                'approved_at' => now()->subDays(6),
                'created_at' => now()->subDays(8),
            ], ['motorcycle_type' => 'Electric Motorcycle', 'fuel_type' => 'BEV', 'mileage' => '0', 'displacement' => 'electric motor']),
            'scaffold' => $this->listing($users['sellerTwo'], $construction, [
                'title' => 'Modular Scaffolding Set',
                'description' => 'Complete scaffold package suitable for residential and mid-rise projects.',
                'price' => 185000,
                'condition' => Listing::ConditionUsed,
                'brand' => 'PrimeBuild',
                'model' => 'MS-500',
                'region' => 'Region XI',
                'province' => 'Davao del Sur',
                'municipality' => 'City of Davao',
                'status' => Listing::StatusPending,
                'created_at' => now()->subDays(5),
            ], ['power_source' => 'manual assembly', 'capacity' => '500 kg per bay', 'dimensions' => '1.8m x 1.2m bay']),
        ];

        return $this->topUpListings($users, [
            'cars' => $vehicles,
            'agricultural_equipment' => $farm,
            'heavy_equipment' => $heavy,
            'other_units' => $construction,
            'motorcycles' => $motorcycle,
            'commercial_vehicles' => $commercial,
            'electric_vehicles' => $electric,
        ], $listings);
    }

    /**
     * @param  array<string, User>  $users
     * @param  array<string, Category>  $categories
     * @param  array<string, Listing>  $listings
     * @return array<string, Listing>
     */
    private function topUpListings(array $users, array $categories, array $listings): array
    {
        foreach ($categories as $slug => $category) {
            $approvedCount = collect($listings)
                ->filter(fn (Listing $listing): bool => $listing->category_id === $category->id && $listing->status === Listing::StatusApproved)
                ->count();

            for ($index = $approvedCount + 1; $index <= 16; $index++) {
                $demo = $this->demoListingData($slug, $index);
                $seller = $index % 2 === 0 ? $users['sellerOne'] : $users['sellerTwo'];

                $listings["{$slug}_demo_{$index}"] = $this->listing($seller, $category, [
                    ...$demo['attributes'],
                    'status' => Listing::StatusApproved,
                    'approved_at' => now()->subDays(40 - $index),
                    'created_at' => now()->subDays(45 - $index),
                ], $demo['specs']);
            }
        }

        return $listings;
    }

    /**
     * @return array{attributes: array<string, mixed>, specs: array<string, string>}
     */
    private function demoListingData(string $slug, int $index): array
    {
        $locations = [
            ['region' => 'Luzon', 'province' => 'Metro Manila', 'municipality' => 'Quezon City'],
            ['region' => 'Region IV-A', 'province' => 'Laguna', 'municipality' => 'City of Calamba'],
            ['region' => 'Visayas', 'province' => 'Cebu', 'municipality' => 'City of Mandaue'],
            ['region' => 'Region XI', 'province' => 'Davao del Sur', 'municipality' => 'City of Davao'],
        ];
        $location = $locations[$index % count($locations)];

        return match ($slug) {
            'cars' => [
                'attributes' => [
                    'title' => "Demo Car {$index} - ".['Toyota Avanza MPV', 'JMC Grand Avenue Pickup', 'BYD Dolphin EV', 'Geely Coolray SUV', 'MG ZS Crossover', 'Changan CS35 Plus', 'Chery Tiggo 5X'][$index % 7],
                    'description' => 'Buyer-ready demo car listing with verified seller details and current photos.',
                    'price' => 450000 + ($index * 85000),
                    'condition' => $index % 3 === 0 ? Listing::ConditionBrandNew : Listing::ConditionUsed,
                    'year_model' => 2018 + ($index % 8),
                    'brand' => ['Toyota', 'JMC', 'BYD', 'Geely', 'MG', 'Changan', 'Chery'][$index % 7],
                    'model' => ['Avanza', 'Grand Avenue', 'Dolphin', 'Coolray', 'ZS', 'CS35 Plus', 'Tiggo 5X'][$index % 7],
                    ...$location,
                ],
                'specs' => [
                    'body_type' => ['MPV', 'Pickup', 'Hatchback', 'SUV', 'Crossover', 'SUV', 'SUV'][$index % 7],
                    'transmission' => $index % 2 === 0 ? 'automatic' : 'manual',
                    'fuel_type' => ['Gasoline', 'Diesel', 'HEV', 'BEV'][$index % 4],
                    'mileage' => (string) ($index % 3 === 0 ? 0 : $index * 11800),
                    'color' => ['white', 'black', 'silver', 'red'][$index % 4],
                ],
            ],
            'agricultural_equipment' => [
                'attributes' => [
                    'title' => "Demo Farm Unit {$index} - ".['Kubota Tractor', 'Yanmar Harvester', 'LOVOL Tractor', 'TYM Compact Tractor', 'FitCorea Planter'][$index % 5],
                    'description' => 'Farm equipment demo listing for rice, corn, and mixed-crop operations.',
                    'price' => 380000 + ($index * 120000),
                    'condition' => $index % 2 === 0 ? Listing::ConditionSurplus : Listing::ConditionUsed,
                    'year_model' => 2017 + ($index % 7),
                    'brand' => ['Kubota', 'Yanmar', 'LOVOL', 'TYM', 'FitCorea'][$index % 5],
                    'model' => ['L4508', 'AW70V', 'M504', 'T55', 'FP100'][$index % 5],
                    ...$location,
                ],
                'specs' => [
                    'equipment_type' => ['Tractor', 'Combine Harvester', 'Tractor', 'Tractor', 'Seeder'][$index % 5],
                    'horsepower' => (string) (35 + ($index * 5)),
                    'fuel_type' => 'Diesel',
                    'usage_hours' => (string) (300 + ($index * 190)),
                ],
            ],
            'heavy_equipment' => [
                'attributes' => [
                    'title' => "Demo Heavy Equipment {$index} - ".['Komatsu Excavator', 'Volvo Loader', 'SANY Crane', 'XCMG Grader', 'Zoomlion Pump'][$index % 5],
                    'description' => 'Heavy equipment demo unit inspected for quarry, hauling, and site work.',
                    'price' => 980000 + ($index * 330000),
                    'condition' => Listing::ConditionSurplus,
                    'year_model' => 2015 + ($index % 8),
                    'brand' => ['Komatsu', 'Volvo', 'SANY', 'XCMG', 'Zoomlion'][$index % 5],
                    'model' => ['PC200', 'L90', 'STC250', 'GR180', 'HBT60'][$index % 5],
                    ...$location,
                ],
                'specs' => [
                    'equipment_type' => ['Excavator', 'Wheel Loader', 'Crane', 'Motor Grader', 'Other'][$index % 5],
                    'operating_weight' => (8500 + ($index * 1400)).' kg',
                    'bucket_capacity' => (1 + ($index / 10)).' m3',
                    'engine_power' => (100 + ($index * 12)).' HP',
                ],
            ],
            'commercial_vehicles' => [
                'attributes' => [
                    'title' => "Demo Commercial Unit {$index} - ".['Isuzu Elf Truck', 'Foton Transvan', 'Hino Minibus', 'JAC Refrigerated Van', 'Sinotruk Dump Truck'][$index % 5],
                    'description' => 'Fleet-ready commercial vehicle demo listing for delivery, hauling, and passenger routes.',
                    'price' => 780000 + ($index * 210000),
                    'condition' => $index % 3 === 0 ? Listing::ConditionBrandNew : Listing::ConditionUsed,
                    'year_model' => 2017 + ($index % 8),
                    'brand' => ['Isuzu', 'Foton', 'Hino', 'JAC', 'Sinotruk'][$index % 5],
                    'model' => ['Elf', 'Transvan', 'Liesse', 'Refrigerated Van', 'Dump Truck'][$index % 5],
                    ...$location,
                ],
                'specs' => [
                    'vehicle_type' => ['Truck', 'Van', 'Minibus', 'Refrigerated Van', 'Dump Truck'][$index % 5],
                    'fuel_type' => 'Diesel',
                    'payload_capacity' => (string) (1 + ($index % 6)),
                    'seating_capacity' => (string) (2 + ($index % 15)),
                ],
            ],
            'electric_vehicles' => [
                'attributes' => [
                    'title' => "Demo EV {$index} - ".['BYD Dolphin EV', 'Tesla Model Y', 'MG4 Electric', 'Yadea E-Scooter', 'Geely EV Sedan'][$index % 5],
                    'description' => 'Electric unit demo listing with battery health and charging details noted.',
                    'price' => 65000 + ($index * 210000),
                    'condition' => $index % 2 === 0 ? Listing::ConditionBrandNew : Listing::ConditionUsed,
                    'year_model' => 2021 + ($index % 5),
                    'brand' => ['BYD', 'Tesla', 'MG', 'Yadea', 'Geely'][$index % 5],
                    'model' => ['Dolphin', 'Model Y', 'MG4', 'E-Scooter', 'EV Sedan'][$index % 5],
                    ...$location,
                ],
                'specs' => [
                    'ev_type' => ['Electric Car (BEV)', 'Electric Car (BEV)', 'Electric Car (BEV)', 'Electric Scooter / E-Bike', 'Electric Car (BEV)'][$index % 5],
                    'battery_capacity' => (string) (10 + ($index * 4)),
                    'range_km' => (string) (80 + ($index * 25)),
                ],
            ],
            'other_units' => [
                'attributes' => [
                    'title' => "Demo Construction Equipment {$index} - ".['Concrete Mixer', 'Diesel Generator', 'Plate Compactor', 'Scaffold Set', 'Air Compressor'][$index % 5],
                    'description' => 'Light construction equipment demo unit for contractors and service shops.',
                    'price' => 48000 + ($index * 35000),
                    'condition' => $index % 2 === 0 ? Listing::ConditionBrandNew : Listing::ConditionUsed,
                    'year_model' => 2020 + ($index % 6),
                    'brand' => ['PrimeBuild', 'Kipor', 'Honda', 'OEM', 'Atlas Copco'][$index % 5],
                    'model' => ['CM-1B', 'KDE25', 'PC90', 'MS-500', 'AC185'][$index % 5],
                    ...$location,
                ],
                'specs' => [
                    'unit_type' => ['Industrial Equipment', 'Generator', 'Industrial Equipment', 'Other', 'Industrial Equipment'][$index % 5],
                    'power_source' => ['gasoline engine', 'diesel', 'electric motor'][$index % 3],
                    'capacity' => ['1 bag', '25 kVA', '90 kg', '500 kg per bay'][$index % 4],
                    'dimensions' => 'compact jobsite frame',
                ],
            ],
            default => [
                'attributes' => [
                    'title' => "Demo Motorcycle {$index} - ".['Yamaha NMAX Scooter', 'Kawasaki Ninja Sports', 'BMW GS Adventure', 'Kymco Like Street', 'TVS King 3-Wheel'][$index % 5],
                    'description' => 'Motorcycle demo listing covering scooters, street bikes, big bikes, and three-wheel units.',
                    'price' => 42000 + ($index * 28000),
                    'condition' => $index % 3 === 0 ? Listing::ConditionBrandNew : Listing::ConditionUsed,
                    'year_model' => 2019 + ($index % 7),
                    'brand' => ['Yamaha', 'Kawasaki', 'BMW', 'Kymco', 'TVS'][$index % 5],
                    'model' => ['NMAX', 'Ninja 400', 'GS 1250', 'Like 150i', 'King'][$index % 5],
                    ...$location,
                ],
                'specs' => [
                    'motorcycle_type' => ['Scooter', 'Sports', 'Adventure / Touring', 'Naked / Street', 'Sidecar'][$index % 5],
                    'fuel_type' => $index % 4 === 0 ? 'BEV' : 'Gasoline',
                    'mileage' => (string) ($index * 3200),
                    'displacement' => ['155cc', '400cc', '1250cc', '150cc', '200cc'][$index % 5],
                ],
            ],
        };
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, string>  $specs
     */
    private function listing(User $seller, Category $category, array $attributes, array $specs): Listing
    {
        $listing = Listing::query()->updateOrCreate(
            ['title' => $attributes['title']],
            [
                'user_id' => $seller->id,
                'seller_profile_id' => $seller->sellerProfile->id,
                'category_id' => $category->id,
                'negotiable' => true,
                ...$attributes,
            ],
        );

        foreach ($specs as $name => $value) {
            $field = $category->specFields()->where('name', $name)->first();

            if ($field !== null) {
                $listing->specValues()->updateOrCreate(
                    ['spec_field_id' => $field->id],
                    ['value' => $value],
                );
            }
        }

        $listing->images()->updateOrCreate(
            ['path' => 'demo/listings/'.Str::slug($listing->title).'.jpg'],
            ['is_primary' => true],
        );

        return $listing->fresh(['specValues', 'images']);
    }

    /**
     * @param  array<string, User>  $users
     * @param  array<string, Listing>  $listings
     * @return array<string, Lead>
     */
    private function leads(array $users, array $listings): array
    {
        return [
            'pickupClosed' => $this->lead('PU-DEMO-001', $listings['pickup'], $users['buyerOne'], Lead::StatusClosed, now()->subDays(32)),
            'pickupNegotiating' => $this->lead('PU-DEMO-002', $listings['pickup'], $users['buyerTwo'], Lead::StatusNegotiating, now()->subDays(9)),
            'tractorClosed' => $this->lead('PU-DEMO-003', $listings['tractor'], $users['buyerTwo'], Lead::StatusClosed, now()->subDays(18)),
            'loaderContacted' => $this->lead('PU-DEMO-004', $listings['loader'], $users['buyerThree'], Lead::StatusContacted, now()->subDays(4)),
        ];
    }

    private function lead(string $reference, Listing $listing, User $buyer, string $status, mixed $createdAt): Lead
    {
        return Lead::query()->updateOrCreate(
            ['reference_code' => $reference],
            [
                'listing_id' => $listing->id,
                'buyer_id' => $buyer->id,
                'seller_id' => $listing->user_id,
                'status' => $status,
                'message' => 'I would like to inspect this unit and discuss final pricing.',
                'contacted_at' => in_array($status, [Lead::StatusContacted, Lead::StatusNegotiating, Lead::StatusClosed], true) ? $createdAt->copy()->addDay() : null,
                'negotiated_at' => in_array($status, [Lead::StatusNegotiating, Lead::StatusClosed], true) ? $createdAt->copy()->addDays(2) : null,
                'closed_at' => $status === Lead::StatusClosed ? $createdAt->copy()->addDays(5) : null,
                'created_at' => $createdAt,
                'updated_at' => now(),
            ],
        );
    }

    /**
     * @param  array<string, Lead>  $leads
     */
    private function transactions(array $leads): void
    {
        $this->transaction($leads['pickupClosed'], 1200000, 2.5, true, now()->subDays(25), CommissionLog::StatusPaid);
        $this->transaction($leads['tractorClosed'], 820000, 2.5, true, now()->subDays(12), CommissionLog::StatusUnpaid);
    }

    private function transaction(Lead $lead, float $price, float $rate, bool $confirmed, mixed $createdAt, string $commissionStatus): Transaction
    {
        $transaction = Transaction::query()->updateOrCreate(
            ['lead_id' => $lead->id],
            [
                'listing_id' => $lead->listing_id,
                'agreed_price' => $price,
                'commission_rate' => $rate,
                'commission_amount' => round($price * ($rate / 100), 2),
                'status' => $confirmed ? Transaction::StatusConfirmed : Transaction::StatusPending,
                'buyer_confirmed' => $confirmed,
                'seller_confirmed' => $confirmed,
                'confirmed_at' => $confirmed ? $createdAt->copy()->addDays(2) : null,
                'created_at' => $createdAt,
                'updated_at' => now(),
            ],
        );

        CommissionLog::query()->updateOrCreate(
            ['transaction_id' => $transaction->id],
            [
                'amount' => $transaction->commission_amount,
                'status' => $commissionStatus,
                'paid_at' => $commissionStatus === CommissionLog::StatusPaid ? now()->subDays(20) : null,
            ],
        );

        return $transaction;
    }

    /**
     * @param  array<string, User>  $users
     * @param  array<string, Listing>  $listings
     * @param  array<string, Plan>  $plans
     */
    private function monetization(array $users, array $listings, array $plans): void
    {
        $subscription = Subscription::query()->updateOrCreate(
            ['user_id' => $users['sellerOne']->id, 'plan_id' => $plans['subscription']->id],
            [
                'starts_at' => now()->subDays(8),
                'ends_at' => now()->addDays(22),
                'status' => Subscription::StatusActive,
            ],
        );

        $this->payment($users['sellerOne'], $subscription, 999, Payment::StatusConfirmed, now()->subDays(8));

        $boost = ListingBoost::query()->updateOrCreate(
            ['listing_id' => $listings['loader']->id, 'plan_id' => $plans['boost']->id],
            [
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addDays(6),
                'is_active' => true,
            ],
        );

        $this->payment($users['sellerOne'], $boost, 499, Payment::StatusConfirmed, now()->subDay());

        $pendingSubscription = Subscription::query()->updateOrCreate(
            ['user_id' => $users['sellerTwo']->id, 'plan_id' => $plans['subscription']->id],
            [
                'starts_at' => null,
                'ends_at' => null,
                'status' => Subscription::StatusPending,
            ],
        );

        $this->payment($users['sellerTwo'], $pendingSubscription, 999, Payment::StatusPending, null);
    }

    private function payment(User $user, mixed $payable, float $amount, string $status, mixed $paidAt): Payment
    {
        return Payment::query()->updateOrCreate(
            [
                'payable_type' => $payable::class,
                'payable_id' => $payable->id,
            ],
            [
                'user_id' => $user->id,
                'amount' => $amount,
                'method' => Payment::MethodGcash,
                'reference_number' => $status === Payment::StatusConfirmed ? 'GCASH-DEMO-'.$payable->id : null,
                'status' => $status,
                'paid_at' => $paidAt,
            ],
        );
    }

    private function financingPartners(): void
    {
        $partners = [
            ['Prime Credit Auto Finance', 'PCAF', '#059669', 'Flexible auto loans for cars, motorcycles, trucks, and equipment buyers.', 'Metro Manila', 'Makati City'],
            ['Bayanihan Vehicle Loans', 'BVL', '#2563eb', 'Dealer-assisted financing for used cars and light commercial vehicles.', 'Cebu', 'City of Mandaue'],
            ['AgriMach Finance Corp.', 'AFC', '#65a30d', 'Farm machine and heavy equipment financing for growing operators.', 'Davao del Sur', 'City of Davao'],
            ['ORCR Capital Lending', 'OCL', '#7c3aed', 'Sangla OR/CR evaluation and short-term vehicle-backed loan assistance.', 'Laguna', 'City of Calamba'],
            ['MetroFleet Bank', 'MFB', '#ca8a04', 'Fleet, truck, and commercial vehicle financing for business buyers.', 'Metro Manila', 'Quezon City'],
            ['VisMin Mobility Finance', 'VMF', '#0891b2', 'Regional financing coverage for buyers across Visayas and Mindanao.', 'Cebu', 'City of Lapu-Lapu'],
        ];

        $role = Role::query()->where('name', 'financing_partner')->firstOrFail();

        foreach ($partners as $index => [$name, $initials, $color, $description, $province, $municipality]) {
            $user = User::query()->updateOrCreate(
                ['email' => Str::slug($name).'.finance@prime.test'],
                [
                    'name' => $name,
                    'username' => Str::slug($name, '_'),
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                ],
            );
            $user->roles()->syncWithoutDetaching([$role->id]);

            $logoPath = 'financing/logos/'.Str::slug($name).'.svg';
            Storage::disk('public')->put($logoPath, $this->financingLogo($initials, $color));

            $partner = FinancingPartner::query()->updateOrCreate(
                ['company_name' => $name],
                [
                    'user_id' => $user->id,
                    'registration_number' => 'PU-FIN-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                    'license_number' => 'SEC-FIN-'.str_pad((string) ($index + 41), 5, '0', STR_PAD_LEFT),
                    'description' => $description,
                    'website' => 'https://primeunits.test/financing',
                    'contact_number' => '0917'.str_pad((string) (8000000 + $index), 7, '0', STR_PAD_LEFT),
                    'contact_email' => Str::slug($name).'.finance@prime.test',
                    'logo' => $logoPath,
                    'region' => $index % 2 === 0 ? 'Luzon' : 'Visayas',
                    'province' => $province,
                    'municipality' => $municipality,
                    'full_address' => "{$municipality}, {$province}",
                    'status' => FinancingPartner::StatusVerified,
                    'verified_at' => now()->subDays(20 - $index),
                ],
            );

            $this->financingProduct($partner, 'Used Vehicle Loan', FinancingProduct::TypeTermLoan, 150000, 3500000, 1, 1.75, 12, 60, 1);
            $this->financingProduct($partner, 'Brand New Unit Financing', FinancingProduct::TypeInstallment, 250000, 5000000, 0.9, 1.45, 12, 72, 2);
            $this->financingProduct($partner, 'Sangla OR/CR Assist', FinancingProduct::TypeChattelMortgage, 50000, 900000, 1.8, 2.75, 6, 36, 3);
        }
    }

    private function financingProduct(
        FinancingPartner $partner,
        string $name,
        string $type,
        int $minAmount,
        int $maxAmount,
        float $minRate,
        float $maxRate,
        int $minTerm,
        int $maxTerm,
        int $sortOrder,
    ): FinancingProduct {
        return FinancingProduct::query()->updateOrCreate(
            ['financing_partner_id' => $partner->id, 'name' => $name],
            [
                'product_type' => $type,
                'description' => "Sample {$name} package for PrimeUnits buyers.",
                'min_amount' => $minAmount,
                'max_amount' => $maxAmount,
                'interest_rate_min' => $minRate,
                'interest_rate_max' => $maxRate,
                'min_term_months' => $minTerm,
                'max_term_months' => $maxTerm,
                'applicable_categories' => ['cars', 'motorcycles', 'commercial-vehicles', 'heavy-equipment'],
                'is_active' => true,
                'sort_order' => $sortOrder,
            ],
        );
    }

    private function financingLogo(string $initials, string $color): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="160" height="160" viewBox="0 0 160 160" role="img" aria-label="{$initials} logo">
  <rect width="160" height="160" rx="32" fill="{$color}"/>
  <circle cx="118" cy="38" r="18" fill="rgba(255,255,255,.22)"/>
  <path d="M34 104h92v12H34zM42 76h76v12H42zM52 48h56v12H52z" fill="white" opacity=".92"/>
  <text x="80" y="137" text-anchor="middle" font-family="Arial, sans-serif" font-size="28" font-weight="700" fill="white">{$initials}</text>
</svg>
SVG;
    }

    /**
     * @param  array<string, User>  $users
     */
    private function notifications(array $users): void
    {
        $this->notification($users['sellerOne'], 'demo.seller', 'Welcome to PrimeUnits', 'Your seller dashboard is ready with listings, leads, and analytics.');
        $this->notification($users['sellerTwo'], 'demo.payment', 'Payment awaiting review', 'Your Prime Seller Monthly subscription payment is pending admin confirmation.');
        $this->notification($users['buyerOne'], 'demo.buyer', 'Inquiry closed', 'Your Hilux inquiry has been marked as a completed transaction.');
        $this->notification($users['superadmin'], 'demo.admin', 'Demo data ready', 'Sample sellers, listings, leads, transactions, and payments are available.');
    }

    private function notification(User $user, string $event, string $title, string $message): void
    {
        DB::table('notifications')->updateOrInsert(
            ['id' => $this->deterministicUuid($user->email.'|'.$event)],
            [
                'type' => PrimeUnitsNotification::class,
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'data' => json_encode([
                    'event' => $event,
                    'title' => $title,
                    'message' => $message,
                    'url' => '/notifications',
                ]),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }

    private function deterministicUuid(string $value): string
    {
        $hash = md5($value);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hash, 0, 8),
            substr($hash, 8, 4),
            substr($hash, 12, 4),
            substr($hash, 16, 4),
            substr($hash, 20, 12),
        );
    }
}
