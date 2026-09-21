<?php

namespace Database\Seeders;

use App\Models\RentalProfile;
use App\Models\Role;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Standalone demo data: the provider, pilot, and farmer accounts referenced
 * by the rice-harvester and agricultural-drone demo listings, credentials,
 * and bookings.
 *
 * Not wired into DatabaseSeeder on purpose. Run explicitly, or via the
 * PrimeUnitsFarmMarketplaceDemoSeeder orchestrator:
 *
 *   php artisan db:seed --class="Database\Seeders\PrimeUnitsFarmMarketplaceDemoSeeder"
 */
class FarmEquipmentDemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([RbacSeeder::class]);

        $this->goldenFieldsMachinery();
        $this->agriFlightDroneServices();
        $this->pilot('pilot.verified@primeunits.test', 'Verified Demo Pilot');
        $this->pilot('pilot.pending@primeunits.test', 'Pending Demo Pilot');
        $this->pilot('pilot.expired@primeunits.test', 'Expired Demo Pilot');
        $this->farmer();
    }

    public function goldenFieldsMachinery(): User
    {
        $user = User::query()->updateOrCreate(
            ['email' => 'provider.goldenfields@primeunits.test'],
            [
                'name' => 'Golden Fields Machinery',
                'username' => 'golden_fields_machinery',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ],
        );

        $user->roles()->syncWithoutDetaching(
            Role::query()->whereIn('name', ['seller', 'rental_provider'])->pluck('id'),
        );
        $user->notificationPreferenceOrDefault();

        SellerProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'seller_type' => 'business',
                'business_name' => 'Golden Fields Machinery',
                'owner_name' => 'Golden Fields Machinery',
                'contact_number' => '09171234501',
                'email' => $user->email,
                'region' => 'Region XI (Davao Region)',
                'province' => 'Davao del Norte',
                'municipality' => 'City of Tagum',
                'full_address' => 'National Highway, City of Tagum, Davao del Norte',
                'permit_number' => 'PU-FARM-GFM-'.$user->id,
                'status' => SellerProfile::StatusVerified,
                'verified_at' => now()->subMonths(2),
            ],
        );

        RentalProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'business_name' => 'Golden Fields Machinery',
                'slug' => 'golden-fields-machinery-'.$user->id,
                'contact_number' => '09171234501',
                'email' => $user->email,
                'business_address' => 'National Highway, City of Tagum, Davao del Norte',
                'business_registration_file' => 'demo/rental-profiles/golden-fields-registration.jpg',
                'valid_id_file' => 'demo/rental-profiles/golden-fields-id.jpg',
                'status' => RentalProfile::StatusApproved,
                'approved_by' => null,
                'approved_at' => now()->subMonths(2),
            ],
        );

        return $user->fresh();
    }

    public function agriFlightDroneServices(): User
    {
        $user = User::query()->updateOrCreate(
            ['email' => 'provider.agriflight@primeunits.test'],
            [
                'name' => 'AgriFlight Drone Services',
                'username' => 'agriflight_drone_services',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ],
        );

        $user->roles()->syncWithoutDetaching(
            Role::query()->where('name', 'rental_provider')->pluck('id'),
        );
        $user->notificationPreferenceOrDefault();

        RentalProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'business_name' => 'AgriFlight Drone Services',
                'slug' => 'agriflight-drone-services-'.$user->id,
                'contact_number' => '09171234502',
                'email' => $user->email,
                'business_address' => 'Panabo City, Davao del Norte',
                'business_registration_file' => 'demo/rental-profiles/agriflight-registration.jpg',
                'valid_id_file' => 'demo/rental-profiles/agriflight-id.jpg',
                'status' => RentalProfile::StatusApproved,
                'approved_by' => null,
                'approved_at' => now()->subMonths(1),
            ],
        );

        return $user->fresh();
    }

    public function pilot(string $email, string $name): User
    {
        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'username' => Str::slug($name),
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ],
        );

        $user->roles()->syncWithoutDetaching(
            Role::query()->where('name', 'buyer')->pluck('id'),
        );
        $user->notificationPreferenceOrDefault();

        return $user->fresh();
    }

    public function farmer(): User
    {
        $user = User::query()->updateOrCreate(
            ['email' => 'farmer.demo@primeunits.test'],
            [
                'name' => 'Demo Farmer',
                'username' => 'demo_farmer',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ],
        );

        $user->roles()->syncWithoutDetaching(
            Role::query()->where('name', 'buyer')->pluck('id'),
        );
        $user->notificationPreferenceOrDefault();

        return $user->fresh();
    }
}
