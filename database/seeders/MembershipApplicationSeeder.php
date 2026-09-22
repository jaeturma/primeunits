<?php

namespace Database\Seeders;

use App\Models\DealerProfile;
use App\Models\MembershipApplication;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Standalone demo data: representative reviewed applications —
 * approved Silver buyer, approved Silver seller, approved Gold seller,
 * and a store application returned for additional information. (The
 * pending Gold buyer application lives on the gold.pending@ demo user,
 * seeded alongside that account in MembershipDemoUserSeeder.)
 *
 * Requires MembershipDemoUserSeeder to have run first.
 */
class MembershipApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([MembershipDemoUserSeeder::class]);

        $superadmin = User::query()->whereHas('roles', fn ($q) => $q->where('name', 'superadmin'))->first();

        $silverBuyer = User::query()->where('email', 'silver.buyer@primeunits.test')->firstOrFail();
        $this->approvedApplication($silverBuyer, MembershipApplication::TypeBuyer, 'silver', $superadmin);

        $silverSeller = User::query()->where('email', 'silver.seller@primeunits.test')->firstOrFail();
        $this->approvedApplication($silverSeller, MembershipApplication::TypeSeller, 'silver', $superadmin);

        $goldSeller = User::query()->where('email', 'gold.seller@primeunits.test')->firstOrFail();
        $this->approvedApplication($goldSeller, MembershipApplication::TypeSeller, 'gold', $superadmin);

        $storeApplicant = User::query()->updateOrCreate(
            ['email' => 'store.info-required@primeunits.test'],
            [
                'name' => 'Store Applicant Demo',
                'username' => 'store-applicant-demo',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ],
        );
        $storeApplicant->roles()->syncWithoutDetaching(Role::query()->whereIn('name', ['buyer', 'dealer'])->pluck('id'));
        $storeApplicant->notificationPreferenceOrDefault();

        $dealerProfile = DealerProfile::query()->updateOrCreate(
            ['user_id' => $storeApplicant->id],
            [
                'business_name' => 'Pending Review Motors (Demo)',
                'slug' => Str::slug('Pending Review Motors Demo').'-'.$storeApplicant->id,
                'contact_number' => '0917'.str_pad((string) $storeApplicant->id, 7, '0', STR_PAD_LEFT),
                'status' => DealerProfile::StatusPending,
                'store_tier' => DealerProfile::StoreTierRegular,
                'store_tier_status' => DealerProfile::StoreTierStatusPending,
            ],
        );

        MembershipApplication::query()->updateOrCreate(
            ['user_id' => $storeApplicant->id, 'type' => MembershipApplication::TypeStore, 'target_level' => 'silver'],
            [
                'dealer_profile_id' => $dealerProfile->id,
                'status' => MembershipApplication::StatusInfoRequired,
                'applicant_notes' => 'Demo application: requesting Silver store status.',
                'applicant_visible_notes' => 'Please submit a clearer copy of your business registration certificate.',
                'reviewer_id' => $superadmin?->id,
                'reviewed_at' => now()->subDay(),
                'submitted_at' => now()->subDays(5),
            ],
        );
    }

    private function approvedApplication(User $user, string $type, string $targetLevel, ?User $reviewer): void
    {
        MembershipApplication::query()->updateOrCreate(
            ['user_id' => $user->id, 'type' => $type, 'target_level' => $targetLevel],
            [
                'status' => MembershipApplication::StatusApproved,
                'applicant_notes' => "Demo application: requesting {$targetLevel} {$type} access.",
                'reviewer_id' => $reviewer?->id,
                'reviewed_at' => now()->subWeeks(2),
                'submitted_at' => now()->subWeeks(3),
            ],
        );
    }
}
