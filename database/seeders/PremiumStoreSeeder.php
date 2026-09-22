<?php

namespace Database\Seeders;

use App\Models\DealerProfile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Standalone demo data: three verified stores demonstrating each store
 * level — a Regular equipment dealership, and (reusing the accounts
 * MembershipDemoUserSeeder already creates) the Silver premium-motor
 * dealership and Gold aviation/marine brokerage.
 *
 * Requires MembershipDemoUserSeeder to have run first.
 */
class PremiumStoreSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([MembershipDemoUserSeeder::class]);

        $owner = User::query()->updateOrCreate(
            ['email' => 'regular.store@primeunits.test'],
            [
                'name' => 'Regular Store Demo',
                'username' => 'regular-store-demo',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ],
        );
        $owner->roles()->syncWithoutDetaching(Role::query()->whereIn('name', ['buyer', 'dealer'])->pluck('id'));
        $owner->notificationPreferenceOrDefault();

        DealerProfile::query()->updateOrCreate(
            ['user_id' => $owner->id],
            [
                'business_name' => 'Metro Equipment Supply (Demo)',
                'slug' => Str::slug('Metro Equipment Supply Demo').'-'.$owner->id,
                'contact_number' => '0917'.str_pad((string) $owner->id, 7, '0', STR_PAD_LEFT),
                'status' => DealerProfile::StatusVerified,
                'verified_at' => now()->subMonths(3),
                'store_tier' => DealerProfile::StoreTierRegular,
                'store_tier_status' => DealerProfile::StoreTierStatusActive,
            ],
        );
    }
}
