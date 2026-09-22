<?php

namespace Database\Seeders;

use App\Models\DealerProfile;
use App\Models\MembershipAccess;
use App\Models\MembershipApplication;
use App\Models\Plan;
use App\Models\Role;
use App\Models\SellerProfile;
use App\Models\Subscription;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Standalone demo data: the ten membership-tier demo accounts named in
 * the implementation spec, covering every buyer/seller/store access
 * combination the acceptance criteria exercise. All use the project's
 * standard demo password: "password".
 *
 * Requires RbacSeeder to have run first.
 */
class MembershipDemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([RbacSeeder::class, MembershipPlanSeeder::class]);

        $regular = $this->user('regular.demo@primeunits.test', 'Regular Demo Member', ['buyer', 'seller']);
        $this->access($regular, [
            'identity_verification_level' => MembershipAccess::IdentityRegular,
            'buyer_access_level' => MembershipAccess::LevelRegular,
            'seller_access_level' => MembershipAccess::LevelRegular,
        ]);
        $this->sellerProfile($regular, SellerProfile::StatusVerified);

        $silverBuyer = $this->user('silver.buyer@primeunits.test', 'Silver Buyer Demo', ['buyer']);
        $this->access($silverBuyer, [
            'identity_verification_level' => MembershipAccess::IdentityEnhanced,
            'buyer_access_level' => MembershipAccess::LevelSilver,
        ]);
        $this->subscription($silverBuyer, Plan::TierSilver, Subscription::StatusActive);

        $silverSeller = $this->user('silver.seller@primeunits.test', 'Silver Seller Demo', ['buyer', 'seller']);
        $this->access($silverSeller, [
            'identity_verification_level' => MembershipAccess::IdentityEnhanced,
            'buyer_access_level' => MembershipAccess::LevelRegular,
            'seller_access_level' => MembershipAccess::LevelSilver,
        ]);
        $this->sellerProfile($silverSeller, SellerProfile::StatusVerified);

        $silverStore = $this->user('silver.store@primeunits.test', 'Silver Store Demo', ['buyer', 'dealer']);
        $this->access($silverStore, [
            'identity_verification_level' => MembershipAccess::IdentityEnhanced,
            'buyer_access_level' => MembershipAccess::LevelRegular,
            'seller_access_level' => MembershipAccess::LevelSilver,
        ]);
        $this->dealerProfile($silverStore, 'Silver Motor Gallery (Demo)', DealerProfile::StoreTierSilver, DealerProfile::StoreTierStatusActive);

        $goldBuyer = $this->user('gold.buyer@primeunits.test', 'Gold Buyer Demo', ['buyer']);
        $this->access($goldBuyer, [
            'identity_verification_level' => MembershipAccess::IdentityEnhanced,
            'buyer_access_level' => MembershipAccess::LevelGold,
        ]);
        $this->subscription($goldBuyer, Plan::TierGold, Subscription::StatusActive);

        $goldPending = $this->user('gold.pending@primeunits.test', 'Gold Candidate Demo', ['buyer']);
        $this->access($goldPending, [
            'identity_verification_level' => MembershipAccess::IdentityEnhanced,
            'buyer_access_level' => MembershipAccess::LevelSilver,
        ]);
        $this->subscription($goldPending, Plan::TierGold, Subscription::StatusPendingReview);
        MembershipApplication::query()->updateOrCreate(
            ['user_id' => $goldPending->id, 'type' => MembershipApplication::TypeBuyer, 'target_level' => 'gold'],
            [
                'status' => MembershipApplication::StatusPendingReview,
                'applicant_notes' => 'Demo application: requesting Gold buyer access to view private aviation and marine listings.',
                'submitted_at' => now()->subDays(2),
            ],
        );

        $goldSeller = $this->user('gold.seller@primeunits.test', 'Gold Seller Demo', ['buyer', 'seller']);
        $this->access($goldSeller, [
            'identity_verification_level' => MembershipAccess::IdentityEnhanced,
            'buyer_access_level' => MembershipAccess::LevelSilver,
            'seller_access_level' => MembershipAccess::LevelGold,
        ]);
        $this->sellerProfile($goldSeller, SellerProfile::StatusVerified);

        $goldStore = $this->user('gold.store@primeunits.test', 'Gold Professional Store Demo', ['buyer', 'dealer']);
        $this->access($goldStore, [
            'identity_verification_level' => MembershipAccess::IdentityEnhanced,
            'buyer_access_level' => MembershipAccess::LevelGold,
            'seller_access_level' => MembershipAccess::LevelGold,
        ]);
        $this->dealerProfile($goldStore, 'Meridian Aviation & Marine Brokerage (Demo)', DealerProfile::StoreTierGoldProfessional, DealerProfile::StoreTierStatusActive);

        $silverSuspended = $this->user('silver.suspended@primeunits.test', 'Suspended Silver Demo', ['buyer']);
        $this->access($silverSuspended, [
            'identity_verification_level' => MembershipAccess::IdentityEnhanced,
            'buyer_access_level' => MembershipAccess::LevelSilver,
            'buyer_access_status' => MembershipAccess::StatusSuspended,
            'is_restricted' => true,
            'restricted_reason' => 'Demo data: suspended pending resolution of a disputed transaction.',
        ]);
        $this->subscription($silverSuspended, Plan::TierSilver, Subscription::StatusSuspended);

        $goldExpired = $this->user('gold.expired@primeunits.test', 'Expired Gold Demo', ['buyer']);
        $this->access($goldExpired, [
            'identity_verification_level' => MembershipAccess::IdentityEnhanced,
            'buyer_access_level' => MembershipAccess::LevelGold,
            'buyer_access_status' => MembershipAccess::StatusExpired,
        ]);
        $this->subscription($goldExpired, Plan::TierGold, Subscription::StatusExpired, now()->subDays(60), now()->subDays(5));
    }

    public function user(string $email, string $name, array $roles): User
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

        $user->roles()->syncWithoutDetaching(Role::query()->whereIn('name', $roles)->pluck('id'));
        $user->notificationPreferenceOrDefault();

        return $user->fresh();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function access(User $user, array $attributes): MembershipAccess
    {
        return MembershipAccess::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'buyer_access_status' => MembershipAccess::StatusActive,
                'seller_access_status' => MembershipAccess::StatusActive,
                'is_restricted' => false,
                'restricted_reason' => null,
                ...$attributes,
            ],
        );
    }

    private function sellerProfile(User $user, string $status): SellerProfile
    {
        return SellerProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'seller_type' => 'individual',
                'contact_number' => '0917'.str_pad((string) $user->id, 7, '0', STR_PAD_LEFT),
                'status' => $status,
                'verified_at' => $status === SellerProfile::StatusVerified ? now()->subMonths(1) : null,
            ],
        );
    }

    private function dealerProfile(User $user, string $businessName, string $storeTier, string $storeTierStatus): DealerProfile
    {
        return DealerProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'business_name' => $businessName,
                'slug' => Str::slug($businessName),
                'contact_number' => '0917'.str_pad((string) $user->id, 7, '0', STR_PAD_LEFT),
                'status' => DealerProfile::StatusVerified,
                'verified_at' => now()->subMonths(2),
                'store_tier' => $storeTier,
                'store_tier_status' => $storeTierStatus,
            ],
        );
    }

    private function subscription(User $user, string $tier, string $status, ?CarbonInterface $startsAt = null, ?CarbonInterface $endsAt = null): Subscription
    {
        $plan = Plan::query()->where('type', Plan::TypeMembership)->where('tier', $tier)->firstOrFail();

        return Subscription::query()->updateOrCreate(
            ['user_id' => $user->id, 'plan_id' => $plan->id],
            [
                'status' => $status,
                'starts_at' => $startsAt ?? now()->subDays(10),
                'ends_at' => $endsAt ?? now()->addDays(20),
            ],
        );
    }
}
