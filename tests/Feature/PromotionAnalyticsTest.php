<?php

use App\Models\Category;
use App\Models\LandingAd;
use App\Models\Listing;
use App\Models\PromotionImpression;
use App\Models\Role;
use App\Models\User;
use Carbon\CarbonInterface;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RbacSeeder::class);
    $this->seed(CategorySeeder::class);
});

function promotionAdmin(): User
{
    $admin = User::factory()->create();
    $admin->roles()->attach(Role::query()->where('name', 'admin')->firstOrFail());

    return $admin->fresh();
}

function promotionListing(string $title): Listing
{
    $seller = User::factory()->create();
    $category = Category::query()->where('slug', 'cars')->firstOrFail();

    return Listing::query()->create([
        'user_id' => $seller->id,
        'category_id' => $category->id,
        'title' => $title,
        'price' => 500000,
        'condition' => Listing::ConditionUsed,
        'status' => Listing::StatusApproved,
        'approved_at' => now(),
    ]);
}

function recordImpression(string $type, string $promotableClass, int $promotableId, ?CarbonInterface $viewedAt = null): void
{
    PromotionImpression::query()->create([
        'promotion_type' => $type,
        'promotable_type' => $promotableClass,
        'promotable_id' => $promotableId,
        'session_id' => (string) Str::uuid(),
        'marketplace_mode' => 'regular',
        'viewed_at' => $viewedAt ?? now(),
        'dedupe_key' => (string) Str::uuid(),
    ]);
}

test('the promotions dashboard requires view_reports permission', function () {
    $user = User::factory()->create();
    $coordinator = Role::query()->where('name', 'coordinator')->firstOrFail();
    $user->roles()->attach($coordinator);

    $this->actingAs($user)
        ->get(route('adm.promotions.index'))
        ->assertForbidden();
});

test('the promotions dashboard summarizes featured, sponsored, and advertisement impressions', function () {
    $admin = promotionAdmin();
    $featured = promotionListing('Featured Pickup');
    $sponsored = promotionListing('Sponsored Sedan');
    $ad = LandingAd::query()->create([
        'title' => 'Test Ad', 'category' => 'Test', 'body' => 'Body', 'accent_color' => '#059669',
        'is_active' => true, 'show_in_feed' => true, 'review_status' => LandingAd::ReviewApproved,
    ]);

    recordImpression(PromotionImpression::TypeFeatured, Listing::class, $featured->id);
    recordImpression(PromotionImpression::TypeFeatured, Listing::class, $featured->id);
    recordImpression(PromotionImpression::TypeSponsored, Listing::class, $sponsored->id);
    recordImpression(PromotionImpression::TypeAdvertisement, LandingAd::class, $ad->id);

    $this->actingAs($admin)
        ->get(route('adm.promotions.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('adm/promotions/index')
            ->where('summary.featured_impressions', 2)
            ->where('summary.sponsored_impressions', 1)
            ->where('summary.advertisement_impressions', 1)
            ->where('topFeaturedListings.0.title', 'Featured Pickup')
            ->where('topFeaturedListings.0.impressions', 2)
            ->where('topSponsoredListings.0.title', 'Sponsored Sedan')
            ->where('topSponsoredListings.0.impressions', 1),
        );
});

test('the promotions dashboard date range filter excludes impressions outside it', function () {
    $admin = promotionAdmin();
    $listing = promotionListing('Old Featured Listing');

    recordImpression(PromotionImpression::TypeFeatured, Listing::class, $listing->id, now()->subDays(30));

    $this->actingAs($admin)
        ->get(route('adm.promotions.index', ['from' => now()->subDays(2)->toDateString()]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('summary.featured_impressions', 0)
            ->where('topFeaturedListings', []),
        );
});

test('sponsored impressions never inflate the featured count or vice versa', function () {
    $admin = promotionAdmin();
    $listing = promotionListing('Dual Promotion Listing');

    recordImpression(PromotionImpression::TypeFeatured, Listing::class, $listing->id);
    recordImpression(PromotionImpression::TypeSponsored, Listing::class, $listing->id);

    $this->actingAs($admin)
        ->get(route('adm.promotions.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('summary.featured_impressions', 1)
            ->where('summary.sponsored_impressions', 1),
        );
});
