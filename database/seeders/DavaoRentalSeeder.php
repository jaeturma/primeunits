<?php

namespace Database\Seeders;

use App\Models\RentalAvailability;
use App\Models\RentalPackage;
use App\Models\RentalUnit;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\Concerns\SeedsDavaoLocations;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Standalone demo data: ~50 approved rental units spread across every
 * PrimeUnits rental type, cycling through real brands/models, all located
 * in Davao Region cities/provinces.
 *
 * Not wired into DatabaseSeeder on purpose (keeps DemoSeeder/RentalSeeder's
 * existing counts stable for tests). Run explicitly instead:
 *
 *   php artisan db:seed --class="Database\Seeders\DavaoRentalSeeder"
 */
class DavaoRentalSeeder extends Seeder
{
    use SeedsDavaoLocations;

    /**
     * How many units to create per rental type. Sums to 50.
     *
     * @var array<string, int>
     */
    private const array TARGET_PER_TYPE = [
        RentalUnit::TypeCarRental => 6,
        RentalUnit::TypeVanRental => 5,
        RentalUnit::TypeSelfDrive => 5,
        RentalUnit::TypeChauffeur => 4,
        RentalUnit::TypeAirportTransfer => 4,
        RentalUnit::TypeWeddingCar => 3,
        RentalUnit::TypeShuttle => 5,
        RentalUnit::TypeBusRental => 4,
        RentalUnit::TypeTruckRental => 5,
        RentalUnit::TypeEquipmentRental => 5,
        RentalUnit::TypeMotorcycleRental => 4,
    ];

    public function run(): void
    {
        $this->call([RbacSeeder::class]);

        $providers = $this->providers();
        $counter = 0;

        foreach (self::TARGET_PER_TYPE as $type => $count) {
            $brandModels = $this->brandModels($type);

            for ($i = 1; $i <= $count; $i++) {
                $counter++;

                $unit = $this->unit(
                    provider: $providers[$counter % count($providers)],
                    type: $type,
                    brandModel: $brandModels[($i - 1) % count($brandModels)],
                    index: $counter,
                    location: $this->davaoLocation($counter),
                );

                $this->packages($unit, $type, $counter);
                $this->availability($unit, blockedDays: $this->blockedDays($counter));
            }
        }
    }

    /**
     * @return array<int, User>
     */
    private function providers(): array
    {
        $providers = [
            ['name' => 'Davao City Car Rentals', 'email' => 'provider.davaocity@prime.test', 'username' => 'davao_city_rentals'],
            ['name' => 'Panabo Fleet Services', 'email' => 'provider.panabofleet@prime.test', 'username' => 'panabo_fleet'],
            ['name' => 'Samal Island Transport Co.', 'email' => 'provider.samalisland@prime.test', 'username' => 'samal_transport'],
        ];

        return collect($providers)->map(function (array $data): User {
            $role = Role::query()->where('name', 'rental_provider')->firstOrFail();

            $user = User::query()->updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'username' => $data['username'],
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                ],
            );

            $user->roles()->syncWithoutDetaching([$role->id]);
            $user->notificationPreferenceOrDefault();

            return $user->fresh();
        })->values()->all();
    }

    /**
     * @return array<int, array{brand: string, model: string, capacity: int, with_driver: bool}>
     */
    private function brandModels(string $type): array
    {
        $pairs = match ($type) {
            RentalUnit::TypeCarRental => [
                ['Toyota', 'Vios', 5, true], ['Honda', 'City', 5, true],
                ['Mitsubishi', 'Mirage', 5, true], ['Hyundai', 'Accent', 5, true],
                ['Suzuki', 'Ertiga', 7, true], ['Nissan', 'Almera', 5, true],
            ],
            RentalUnit::TypeVanRental => [
                ['Toyota', 'HiAce', 12, true], ['Toyota', 'Innova', 8, true],
                ['Nissan', 'Urvan', 15, true], ['Hyundai', 'Starex', 10, true],
                ['Foton', 'Transvan', 12, true],
            ],
            RentalUnit::TypeSelfDrive => [
                ['Honda', 'Click 160i', 2, false], ['Yamaha', 'Mio i125', 2, false],
                ['Toyota', 'Wigo', 5, false], ['Suzuki', 'Celerio', 5, false],
                ['Mitsubishi', 'Mirage', 5, false],
            ],
            RentalUnit::TypeChauffeur => [
                ['Toyota', 'Camry', 5, true], ['Honda', 'Accord', 5, true],
                ['Mitsubishi', 'Xpander', 7, true], ['Hyundai', 'Tucson', 5, true],
            ],
            RentalUnit::TypeAirportTransfer => [
                ['Toyota', 'Innova', 8, true], ['Hyundai', 'Starex', 10, true],
                ['Toyota', 'HiAce', 12, true], ['Nissan', 'Urvan', 15, true],
            ],
            RentalUnit::TypeWeddingCar => [
                ['Mercedes-Benz', 'E-Class', 4, true], ['BMW', '5 Series', 4, true],
                ['Toyota', 'Alphard', 6, true],
            ],
            RentalUnit::TypeShuttle => [
                ['Toyota', 'HiAce Grandia', 12, true], ['Nissan', 'Urvan', 15, true],
                ['Foton', 'View Transvan', 12, true], ['Hyundai', 'Starex', 10, true],
                ['Isuzu', 'Traviz', 16, true],
            ],
            RentalUnit::TypeBusRental => [
                ['Hino', 'Coaster', 30, true], ['Isuzu', 'NQR Bus', 32, true],
                ['Foton', 'Bus', 35, true], ['Higer', 'Bus', 40, true],
            ],
            RentalUnit::TypeTruckRental => [
                ['Isuzu', 'Elf', 2, true], ['Mitsubishi', 'Fuso Canter', 2, true],
                ['Hino', 'Dutro', 2, true], ['Foton', 'Aumark', 2, true],
                ['JAC', 'N-Series', 2, true],
            ],
            RentalUnit::TypeEquipmentRental => [
                ['Toyota', '3-Ton Forklift', 1, true], ['Komatsu', 'Mini Excavator', 1, true],
                ['Honda', 'Generator Set', 1, false], ['Caterpillar', 'Backhoe Loader', 1, true],
                ['Bomag', 'Road Roller', 1, true],
            ],
            default => [
                ['Honda', 'Click 125i', 2, false], ['Yamaha', 'NMAX', 2, false],
                ['Yamaha', 'Mio i125', 2, false], ['Kymco', 'Like 150i', 2, false],
            ],
        };

        return collect($pairs)->map(fn (array $pair): array => [
            'brand' => $pair[0],
            'model' => $pair[1],
            'capacity' => $pair[2],
            'with_driver' => $pair[3],
        ])->all();
    }

    /**
     * @param  array{brand: string, model: string, capacity: int, with_driver: bool}  $brandModel
     * @param  array{region: string, province: string, municipality: string}  $location
     */
    private function unit(User $provider, string $type, array $brandModel, int $index, array $location): RentalUnit
    {
        $yearModel = 2018 + ($index % 7);
        $label = RentalUnit::rentalTypes()[$type];
        $name = "{$yearModel} {$brandModel['brand']} {$brandModel['model']} - {$label} ({$location['municipality']})";

        return RentalUnit::query()->updateOrCreate(
            ['name' => $name],
            [
                'user_id' => $provider->id,
                'rental_type' => $type,
                'description' => "{$label} available for hire in {$location['municipality']}, {$location['province']}. ".
                    ($brandModel['with_driver'] ? 'Comes with a professional driver.' : 'Self-drive, valid license required.'),
                'brand' => $brandModel['brand'],
                'model' => $brandModel['model'],
                'year_model' => $yearModel,
                'capacity' => $brandModel['capacity'],
                'with_driver' => $brandModel['with_driver'],
                'price_per_day' => $this->pricePerDay($type, $index),
                'price_per_hour' => $this->pricePerHour($type, $index),
                'status' => RentalUnit::StatusApproved,
                'approved_at' => now()->subDays(60 - ($index % 60)),
                'views_count' => 15 + (($index * 7) % 300),
                ...$location,
            ],
        );
    }

    private function pricePerDay(string $type, int $index): float
    {
        return match ($type) {
            RentalUnit::TypeCarRental => 2200 + ($index * 90),
            RentalUnit::TypeVanRental, RentalUnit::TypeShuttle, RentalUnit::TypeAirportTransfer => 2800 + ($index * 110),
            RentalUnit::TypeSelfDrive, RentalUnit::TypeMotorcycleRental => 650 + ($index * 25),
            RentalUnit::TypeChauffeur => 3800 + ($index * 140),
            RentalUnit::TypeWeddingCar => 8500 + ($index * 220),
            RentalUnit::TypeBusRental => 11000 + ($index * 260),
            RentalUnit::TypeTruckRental => 4000 + ($index * 130),
            RentalUnit::TypeEquipmentRental => 5200 + ($index * 260),
            default => 1500 + ($index * 60),
        };
    }

    private function pricePerHour(string $type, int $index): ?float
    {
        if (in_array($type, [RentalUnit::TypeBusRental, RentalUnit::TypeTruckRental], true)) {
            return null;
        }

        return round($this->pricePerDay($type, $index) / 6, -1);
    }

    private function packages(RentalUnit $unit, string $type, int $index): void
    {
        $day = (float) $unit->price_per_day;
        $hour = $unit->price_per_hour !== null ? (float) $unit->price_per_hour : null;

        $packages = [
            ['name' => 'Day Rate', 'duration_type' => RentalPackage::DurationDaily, 'duration_value' => 1, 'price' => $day, 'inclusions' => 'Fuel allowance, basic insurance'],
            ['name' => 'Weekly Rate', 'duration_type' => RentalPackage::DurationWeekly, 'duration_value' => 1, 'price' => round($day * 5.5), 'inclusions' => 'Fuel allowance, full insurance coverage'],
        ];

        if ($hour !== null) {
            $packages[] = ['name' => 'Hourly (min 4 hrs)', 'duration_type' => RentalPackage::DurationHourly, 'duration_value' => 4, 'price' => round($hour * 4), 'inclusions' => 'Fuel allowance, basic insurance'];
        }

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

    /**
     * @return array<int, int>
     */
    private function blockedDays(int $index): array
    {
        return [1 + ($index % 7)];
    }

    /** Seed 90 days of availability; $blockedDays are ISO day-of-week numbers (1=Mon … 7=Sun). */
    private function availability(RentalUnit $unit, array $blockedDays): void
    {
        $start = now()->startOfDay();
        $rows = [];

        for ($day = 0; $day < 90; $day++) {
            $date = $start->copy()->addDays($day);

            $rows[] = [
                'rental_unit_id' => $unit->id,
                'date' => $date->toDateString(),
                'is_available' => ! in_array($date->isoWeekday(), $blockedDays, true),
                'note' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        RentalAvailability::query()->where('rental_unit_id', $unit->id)->delete();
        RentalAvailability::query()->insert($rows);
    }
}
