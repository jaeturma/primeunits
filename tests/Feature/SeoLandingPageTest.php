<?php

use App\Models\Category;
use App\Models\Listing;
use App\Models\Role;
use App\Models\SeoPage;
use App\Models\SellerProfile;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RbacSeeder;
use Database\Seeders\SeoPageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RbacSeeder::class);
    $this->seed(CategorySeeder::class);
    $this->seed(SeoPageSeeder::class);
});

function seoSeller(): User
{
    $seller = User::factory()->create();
    $seller->roles()->attach(Role::query()->where('name', 'seller')->firstOrFail());

    SellerProfile::query()->create([
        'user_id' => $seller->id,
        'seller_type' => 'business',
        'business_name' => 'SEO Seller',
        'contact_number' => '09170000000',
        'status' => SellerProfile::StatusVerified,
        'verified_at' => now(),
    ]);

    return $seller->fresh();
}

function seoListing(User $seller, Category $category, string $title, string $province, string $status = Listing::StatusApproved): Listing
{
    return Listing::query()->create([
        'user_id' => $seller->id,
        'seller_profile_id' => $seller->sellerProfile->id,
        'category_id' => $category->id,
        'title' => $title,
        'price' => '500000',
        'condition' => Listing::ConditionUsed,
        'region' => 'Region VII',
        'province' => $province,
        'status' => $status,
        'approved_at' => $status === Listing::StatusApproved ? now() : null,
    ]);
}

test('category seo page renders metadata and relevant approved listings', function () {
    $seller = seoSeller();
    $vehicle = Category::query()->where('slug', 'vehicle')->firstOrFail();
    $heavy = Category::query()->where('slug', 'heavy_equipment')->firstOrFail();

    seoListing($seller, $vehicle, 'SEO Pickup', 'Cebu');
    seoListing($seller, $vehicle, 'Draft Pickup', 'Cebu', Listing::StatusPending);
    seoListing($seller, $heavy, 'SEO Loader', 'Cebu');

    $this->get(route('seo.category', 'vehicles'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('seo/landing')
            ->where('seoPage.meta_title', 'Vehicles for Sale | PrimeUnits Philippines')
            ->where('seoPage.category.slug', 'vehicle')
            ->where('listings.data.0.title', 'SEO Pickup')
            ->has('listings.data', 1),
        );
});

test('category location seo page filters by province and supports request filters', function () {
    $seller = seoSeller();
    $vehicle = Category::query()->where('slug', 'vehicle')->firstOrFail();

    seoListing($seller, $vehicle, 'Cebu Hilux', 'Cebu');
    seoListing($seller, $vehicle, 'Davao Hilux', 'Davao del Sur');

    $this->get(route('seo.category-location', ['category' => 'vehicles', 'location' => 'cebu']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('seo/landing')
            ->where('seoPage.location', 'Cebu')
            ->where('listings.data.0.title', 'Cebu Hilux')
            ->has('listings.data', 1),
        );

    $this->get(route('seo.category-location', [
        'category' => 'vehicles',
        'location' => 'cebu',
        'q' => 'missing',
    ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('seo/landing')
            ->where('filters.q', 'missing')
            ->has('listings.data', 0),
        );
});

test('seo page definitions are stored in the database', function () {
    expect(SeoPage::query()->where('slug', 'heavy-equipment/cebu')->exists())->toBeTrue()
        ->and(SeoPage::query()->where('slug', 'farm-equipment/mindanao')->exists())->toBeTrue();
});
