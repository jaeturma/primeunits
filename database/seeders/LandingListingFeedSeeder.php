<?php

namespace Database\Seeders;

use App\Models\Listing;
use App\Models\ListingBoost;
use App\Models\Plan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Standalone demo data for the landing feed: marks a handful of the
 * existing demo listings as Featured (ListingBoost) or Sponsored
 * (promotional_type), and seeds one expired and one pending listing so the
 * feed's exclusion rules are visibly demonstrable.
 *
 * Requires UsersSeeder (regular demo listings) and
 * PrimeUnitsMembershipDemoSeeder (Silver/Gold demo listings) to have run
 * first — both are called here so this seeder is safe to run standalone.
 * Idempotent via updateOrCreate throughout.
 */
class LandingListingFeedSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([UsersSeeder::class, PrimeUnitsMembershipDemoSeeder::class]);

        $boostPlan = Plan::query()->firstOrCreate(
            ['name' => '7-Day Listing Boost'],
            [
                'type' => Plan::TypeBoost,
                'price' => 499,
                'duration_days' => 7,
                'features' => ['featured placement', 'highlighted listing card'],
                'is_active' => true,
            ],
        );

        // Featured: an active ListingBoost, rotated across a few organic
        // listings so "Load 12 More" doesn't always surface the same one.
        collect([
            '2021 Toyota Vios 1.3 XLE Sedan',
            'Kubota 45HP Farm Tractor',
            '2022 Yamaha R15 Sports Motorcycle',
        ])->each(function (string $title) use ($boostPlan): void {
            $listing = Listing::query()->where('title', $title)->first();

            if (! $listing instanceof Listing) {
                return;
            }

            ListingBoost::query()->updateOrCreate(
                ['listing_id' => $listing->id, 'plan_id' => $boostPlan->id],
                ['starts_at' => now()->subDay(), 'ends_at' => now()->addDays(6), 'is_active' => true],
            );
        });

        // Sponsored: promotional_type = SL with a scheduled expiry.
        collect([
            '2020 Mitsubishi Montero Sport SUV',
            '2019 Hyundai H-100 Utility Van',
            'Premium Big Bike, Low Mileage',
        ])->each(function (string $title): void {
            Listing::query()->where('title', $title)->first()?->update([
                'promotional_type' => Listing::PromoSponsored,
                'promoted_until' => now()->addDays(14),
            ]);
        });

        $this->exclusionExamples();
    }

    /**
     * Demonstrates the feed's eligibility rules: an expired listing and a
     * pending (unapproved) listing, both excluded from every batch.
     */
    private function exclusionExamples(): void
    {
        $seller = Listing::query()->where('title', '2022 Toyota Hilux 4x4')->first()?->user;
        $category = Listing::query()->where('title', '2022 Toyota Hilux 4x4')->first()?->category;

        if (! $seller || ! $category) {
            return;
        }

        Listing::query()->updateOrCreate(
            ['title' => 'Expired Demo Listing — Excluded From Feed'],
            [
                'user_id' => $seller->id,
                'seller_profile_id' => $seller->sellerProfile?->id,
                'category_id' => $category->id,
                'description' => 'Demo listing whose listing period has ended; the feed must exclude it.',
                'price' => 250000,
                'condition' => Listing::ConditionUsed,
                'negotiable' => true,
                'status' => Listing::StatusApproved,
                'approved_at' => now()->subDays(90),
                'expires_at' => Carbon::now()->subDay(),
            ],
        );

        Listing::query()->updateOrCreate(
            ['title' => 'Pending Review Demo Listing — Excluded From Feed'],
            [
                'user_id' => $seller->id,
                'seller_profile_id' => $seller->sellerProfile?->id,
                'category_id' => $category->id,
                'description' => 'Demo listing still awaiting moderation; the feed must exclude it until approved.',
                'price' => 310000,
                'condition' => Listing::ConditionUsed,
                'negotiable' => true,
                'status' => Listing::StatusPending,
            ],
        );
    }
}
