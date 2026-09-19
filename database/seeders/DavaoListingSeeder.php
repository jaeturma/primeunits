<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Listing;
use App\Models\Role;
use App\Models\SellerProfile;
use App\Models\User;
use Database\Seeders\Concerns\SeedsDavaoLocations;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Standalone demo data: ~100 approved listings spread across every
 * PrimeUnits category, cycling through each category's real brands and
 * classification options, all located in Davao Region cities/provinces.
 *
 * Not wired into DatabaseSeeder on purpose, since DemoSeederTest asserts
 * exact listing counts against that seeder. Run explicitly instead:
 *
 *   php artisan db:seed --class="Database\Seeders\DavaoListingSeeder"
 */
class DavaoListingSeeder extends Seeder
{
    use SeedsDavaoLocations;

    /**
     * How many listings to create per category slug. Sums to 100.
     *
     * @var array<string, int>
     */
    private const array TARGET_PER_CATEGORY = [
        'cars' => 22,
        'motorcycles' => 16,
        'commercial-vehicles' => 14,
        'agricultural-equipment' => 12,
        'heavy-equipment' => 12,
        'electric-vehicles' => 12,
        'other-units' => 12,
    ];

    public function run(): void
    {
        $this->call([RbacSeeder::class, CategorySeeder::class, BrandSeeder::class]);

        $sellers = $this->sellers();
        $counter = 0;

        foreach (self::TARGET_PER_CATEGORY as $slug => $count) {
            $category = Category::query()->where('slug', $slug)->firstOrFail();
            $classifications = $category->classificationField?->options ?? ['Other'];
            $brandModels = $this->brandModels($slug);

            for ($i = 1; $i <= $count; $i++) {
                $counter++;

                $this->listing(
                    seller: $sellers[$counter % count($sellers)],
                    category: $category,
                    classification: $classifications[($i - 1) % count($classifications)],
                    brandModel: $brandModels[($i - 1) % count($brandModels)],
                    index: $counter,
                    location: $this->davaoLocation($counter),
                );
            }
        }
    }

    /**
     * @return array<int, User>
     */
    private function sellers(): array
    {
        $sellers = [
            ['name' => 'Davao Motors Trading', 'email' => 'seller.davaomotors@prime.test', 'username' => 'davao_motors', 'seller_type' => 'business', 'business_name' => 'Davao Motors Trading', 'municipality' => 'City of Davao'],
            ['name' => 'Davao Rides & Wheels', 'email' => 'seller.davaorides@prime.test', 'username' => 'davao_rides', 'seller_type' => 'business', 'business_name' => 'Davao Rides & Wheels', 'municipality' => 'City of Tagum'],
            ['name' => 'Mindanao Heavy Equipment Depot', 'email' => 'seller.mindanaoheavy@prime.test', 'username' => 'mindanao_heavy', 'seller_type' => 'business', 'business_name' => 'Mindanao Heavy Equipment Depot', 'municipality' => 'City of Digos'],
            ['name' => 'Ramil Suarez', 'email' => 'seller.ramilsuarez@prime.test', 'username' => 'ramil_suarez', 'seller_type' => 'individual', 'business_name' => null, 'municipality' => 'City of Panabo'],
        ];

        return collect($sellers)->map(function (array $data): User {
            $user = User::query()->updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'username' => $data['username'],
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                ],
            );

            $role = Role::query()->where('name', 'seller')->firstOrFail();
            $user->roles()->syncWithoutDetaching([$role->id]);
            $user->notificationPreferenceOrDefault();

            SellerProfile::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'seller_type' => $data['seller_type'],
                    'business_name' => $data['business_name'],
                    'owner_name' => $data['name'],
                    'contact_number' => '09'.str_pad((string) (170000000 + $user->id), 9, '0', STR_PAD_LEFT),
                    'email' => $data['email'],
                    'region' => 'Region XI (Davao Region)',
                    'province' => 'Davao del Sur',
                    'municipality' => $data['municipality'],
                    'full_address' => $data['municipality'].' Office',
                    'permit_number' => 'PU-DAVAO-'.$user->id,
                    'status' => SellerProfile::StatusVerified,
                    'verified_at' => now()->subMonths(1),
                ],
            );

            return $user->fresh();
        })->values()->all();
    }

    /**
     * @return array<int, array{brand: string, model: string}>
     */
    private function brandModels(string $slug): array
    {
        $pairs = match ($slug) {
            'cars' => [
                ['Toyota', 'Fortuner'], ['Toyota', 'Vios'], ['Toyota', 'Avanza'],
                ['Honda', 'Civic'], ['Honda', 'CR-V'], ['Honda', 'Brio'],
                ['Mitsubishi', 'Xpander'], ['Mitsubishi', 'Montero Sport'],
                ['Ford', 'Ranger'], ['Hyundai', 'Accent'], ['Hyundai', 'Tucson'],
                ['Nissan', 'Almera'], ['Suzuki', 'Ertiga'], ['Kia', 'Sportage'],
                ['Mazda', 'CX-5'], ['BMW', 'X3'], ['Geely', 'Coolray'],
                ['MG', 'ZS'], ['Changan', 'CS35 Plus'], ['Chery', 'Tiggo 5X'],
                ['GAC', 'GS3'], ['JMC', 'Vigus Pro'],
            ],
            'motorcycles' => [
                ['Yamaha', 'NMAX'], ['Yamaha', 'Mio i125'], ['Honda', 'Click 160i'],
                ['Honda', 'XRM 125'], ['BMW', 'G310GS'], ['Kawasaki', 'Ninja 400'],
                ['KTM', 'Duke 200'], ['Ducati', 'Monster 937'], ['Kymco', 'Like 150i'],
                ['SYM', 'Bonus 110'], ['Motorstar', 'Volcano 150'], ['Rusi', 'Classic 110'],
                ['Suzuki', 'Raider 150'], ['TVS', 'King'], ['Bajaj', 'RE'],
                ['Piaggio', 'Ape'], ['Yadea', 'G5'], ['ADO', 'Air'],
            ],
            'commercial-vehicles' => [
                ['Isuzu', 'Elf NLR85'], ['Mitsubishi', 'Fuso Canter'], ['Hino', 'Dutro XKU'],
                ['Foton', 'Transvan View'], ['JMC', 'Carrying N720'], ['Volvo', 'FM440'],
                ['Sinotruk', 'Howo A7'], ['Shacman', 'X3000'], ['Dongfeng', 'KR Series'],
                ['FAW', 'J6'], ['JAC', 'N-Series'],
            ],
            'agricultural-equipment' => [
                ['Kubota', 'L4508'], ['Yanmar', 'AW70V'], ['John Deere', '5075E'],
                ['LOVOL', 'M804'], ['TYM', 'T750'], ['FitCorea', 'FP100'],
                ['Mahindra', 'Yuvo 275 DI'], ['OEM', 'Hand Tractor'],
            ],
            'heavy-equipment' => [
                ['Komatsu', 'PC200'], ['Volvo', 'EC210'], ['Hyundai', 'R220LC'],
                ['Caterpillar', '320D'], ['Hitachi', 'ZX200'], ['SANY', 'SY215C'],
                ['SDLG', 'LG936L'], ['XCMG', 'XE215C'], ['Zoomlion', 'ZE215E'],
                ['LiuGong', 'CLG835'], ['Develon', 'DX140LC'], ['Lonking', 'CDM833'],
                ['Sandvik', 'DD210'], ['Daewoo', 'Solar 220'],
            ],
            'electric-vehicles' => [
                ['BYD', 'Dolphin'], ['BYD', 'Atto 3'], ['BYD', 'Seal'],
                ['Tesla', 'Model Y'], ['MG', 'MG4 Electric'], ['MG', 'ZS EV'],
                ['Geely', 'Geometry C'], ['Yadea', 'G5'], ['NWOW', 'E-Trike'],
                ['ADO', 'Air 20'],
            ],
            default => [
                ['Yamaha', 'Outboard Motor 40HP'], ['Honda', 'EU65is Generator'],
                ['Kipor', 'KDE25 Generator'], ['Atlas Copco', 'XAS 185 Compressor'],
                ['OEM', 'Water Pump 4-inch'], ['Bosch', 'GBH Rotary Hammer Set'],
                ['Grundfos', 'CR Series Pump'],
            ],
        };

        return collect($pairs)->map(fn (array $pair): array => ['brand' => $pair[0], 'model' => $pair[1]])->all();
    }

    /**
     * @param  array{brand: string, model: string}  $brandModel
     * @param  array{region: string, province: string, municipality: string}  $location
     */
    private function listing(User $seller, Category $category, string $classification, array $brandModel, int $index, array $location): void
    {
        $conditions = [Listing::ConditionBrandNew, Listing::ConditionUsed, Listing::ConditionUsed, Listing::ConditionSurplus];
        $condition = $conditions[$index % count($conditions)];
        $yearModel = 2016 + ($index % 9);
        $title = "{$yearModel} {$brandModel['brand']} {$brandModel['model']} - {$classification}";

        $listing = Listing::query()->updateOrCreate(
            ['title' => $title],
            [
                'user_id' => $seller->id,
                'seller_profile_id' => $seller->sellerProfile->id,
                'category_id' => $category->id,
                'description' => "{$classification} unit available in {$location['municipality']}, {$location['province']}. Well-maintained and ready for viewing.",
                'price' => $this->price($category->slug, $index),
                'negotiable' => $index % 3 !== 0,
                'condition' => $condition,
                'year_model' => $yearModel,
                'brand' => $brandModel['brand'],
                'model' => $brandModel['model'],
                'status' => Listing::StatusApproved,
                'approved_at' => now()->subDays(90 - ($index % 90)),
                'created_at' => now()->subDays(95 - ($index % 90)),
                ...$location,
            ],
        );

        foreach ($this->specs($category->slug, $classification, $index) as $name => $value) {
            $field = $category->specFields()->where('name', $name)->first();

            if ($field !== null) {
                $listing->specValues()->updateOrCreate(
                    ['spec_field_id' => $field->id],
                    ['value' => $value],
                );
            }
        }

        $listing->images()->updateOrCreate(
            ['path' => 'demo/listings/davao/'.Str::slug($title).'.jpg'],
            ['is_primary' => true],
        );
    }

    private function price(string $slug, int $index): float
    {
        return match ($slug) {
            'cars' => 450000 + ($index * 45000),
            'motorcycles' => 65000 + ($index * 12000),
            'commercial-vehicles' => 850000 + ($index * 95000),
            'agricultural-equipment' => 320000 + ($index * 60000),
            'heavy-equipment' => 1100000 + ($index * 180000),
            'electric-vehicles' => 90000 + ($index * 140000),
            default => 35000 + ($index * 18000),
        };
    }

    /**
     * @return array<string, string>
     */
    private function specs(string $slug, string $classification, int $index): array
    {
        $transmissions = ['Manual', 'Automatic', 'CVT'];
        $colors = ['White', 'Black', 'Silver', 'Gray', 'Red', 'Blue'];
        $fuelTypes = ['Gasoline', 'Diesel', 'BEV', 'HEV'];

        return match ($slug) {
            'cars' => [
                'body_type' => $classification,
                'transmission' => $transmissions[$index % count($transmissions)],
                'fuel_type' => $fuelTypes[$index % count($fuelTypes)],
                'mileage' => (string) (($index % 3 === 0) ? 0 : $index * 2800),
                'color' => $colors[$index % count($colors)],
            ],
            'motorcycles' => [
                'motorcycle_type' => $classification,
                'fuel_type' => $classification === 'Electric Motorcycle' ? 'BEV' : 'Gasoline',
                'displacement' => (string) (110 + (($index * 15) % 290)),
                'mileage' => (string) ($index * 950),
                'transmission' => $index % 2 === 0 ? 'Automatic / CVT' : 'Manual',
            ],
            'commercial-vehicles' => [
                'vehicle_type' => $classification,
                'fuel_type' => 'Diesel',
                'transmission' => $transmissions[$index % count($transmissions)],
                'mileage' => (string) ($index * 4200),
                'payload_capacity' => (string) (1 + ($index % 8)),
                'seating_capacity' => (string) (2 + ($index % 14)),
            ],
            'agricultural-equipment' => [
                'equipment_type' => $classification,
                'fuel_type' => 'Diesel',
                'horsepower' => (string) (35 + ($index * 4)),
                'usage_hours' => (string) (250 + ($index * 165)),
            ],
            'heavy-equipment' => [
                'equipment_type' => $classification,
                'fuel_type' => 'Diesel',
                'engine_power' => (string) (90 + ($index * 11)),
                'operating_weight' => (string) (7 + ($index % 20)),
                'usage_hours' => (string) (600 + ($index * 210)),
            ],
            'electric-vehicles' => [
                'ev_type' => $classification,
                'battery_capacity' => (string) (8 + ($index * 3)),
                'range_km' => (string) (70 + ($index * 22)),
                'charging_type' => ['AC Level 1', 'AC Level 2', 'DC Fast Charge'][$index % 3],
                'mileage' => (string) ($index * 1900),
            ],
            default => [
                'unit_type' => $classification,
                'fuel_type' => ['Gasoline', 'Diesel', 'Solar', 'Manual'][$index % 4],
                'power_output' => (string) (5 + ($index % 25)).' kVA',
                'capacity' => (string) (10 + ($index % 90)).' L',
                'usage_hours' => (string) (150 + ($index * 90)),
            ],
        };
    }
}
