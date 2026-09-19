<?php

use App\Models\Category;
use App\Models\CategorySpecField;
use App\Models\Listing;
use App\Models\Role;
use App\Models\SellerProfile;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RbacSeeder::class);
    $this->seed(CategorySeeder::class);
});

function searchSeller(): User
{
    $user = User::factory()->create();
    $user->roles()->attach(Role::query()->where('name', 'seller')->firstOrFail());

    SellerProfile::query()->create([
        'user_id' => $user->id,
        'seller_type' => 'individual',
        'contact_number' => '09170000000',
        'status' => SellerProfile::StatusVerified,
        'verified_at' => now(),
    ]);

    return $user->fresh();
}

/**
 * @param  array<string, string>  $specs  spec field name => value
 */
function searchListing(User $seller, Category $category, string $title, array $specs = [], string $brand = 'Toyota'): Listing
{
    $listing = Listing::query()->create([
        'user_id' => $seller->id,
        'seller_profile_id' => $seller->sellerProfile->id,
        'category_id' => $category->id,
        'title' => $title,
        'brand' => $brand,
        'price' => 800000,
        'condition' => Listing::ConditionUsed,
        'status' => Listing::StatusApproved,
        'approved_at' => now(),
    ]);

    foreach ($specs as $fieldName => $value) {
        $field = CategorySpecField::query()->where('category_id', $category->id)->where('name', $fieldName)->firstOrFail();
        $listing->specValues()->create(['spec_field_id' => $field->id, 'value' => $value]);
    }

    return $listing;
}

test('classification filter matches the spec value, not stray keyword mentions', function () {
    $seller = searchSeller();
    $cars = Category::query()->where('slug', 'cars')->firstOrFail();

    $suv = searchListing($seller, $cars, 'Family Weekend Cruiser', ['body_type' => 'SUV', 'fuel_type' => 'Gasoline']);
    searchListing($seller, $cars, 'Compact City Sedan', ['body_type' => 'Sedan', 'fuel_type' => 'Gasoline']);
    searchListing($seller, $cars, 'SUV-Style Roof Rack Bundle', ['body_type' => 'Sedan', 'fuel_type' => 'Gasoline']);

    $response = $this->get('/listings?'.http_build_query(['category' => 'cars', 'classification' => 'SUV']), [
        'X-Inertia' => 'true',
        'X-Inertia-Version' => hash_file('xxh128', public_path('build/manifest.json')),
    ]);

    $response->assertOk()->assertJsonCount(1, 'props.listings.data');
    expect($response->json('props.listings.data.0.id'))->toBe($suv->id);
});

test('classifications endpoint reports per-option listing counts scoped to the category', function () {
    $seller = searchSeller();
    $cars = Category::query()->where('slug', 'cars')->firstOrFail();
    $motorcycles = Category::query()->where('slug', 'motorcycles')->firstOrFail();

    searchListing($seller, $cars, 'Family SUV One', ['body_type' => 'SUV']);
    searchListing($seller, $cars, 'Family SUV Two', ['body_type' => 'SUV']);
    searchListing($seller, $cars, 'City Sedan', ['body_type' => 'Sedan']);
    searchListing($seller, $motorcycles, 'Weekend Scooter', ['motorcycle_type' => 'Scooter']);

    $payload = $this->getJson("/categories/{$cars->id}/classifications")->assertOk()->json();

    expect($payload['field']['name'])->toBe('body_type');

    $suvOption = collect($payload['options'])->firstWhere('value', 'SUV');
    $sedanOption = collect($payload['options'])->firstWhere('value', 'Sedan');

    expect($suvOption['count'])->toBe(2)
        ->and($sedanOption['count'])->toBe(1);
});

test('an electric keyword search also surfaces electrified listings outside the EV category', function () {
    $seller = searchSeller();
    $cars = Category::query()->where('slug', 'cars')->firstOrFail();

    $ev = searchListing($seller, $cars, 'Compact City Hatchback', ['body_type' => 'Hatchback', 'fuel_type' => 'BEV']);
    searchListing($seller, $cars, 'Chevrolet Trailblazer', ['body_type' => 'SUV', 'fuel_type' => 'Diesel'], brand: 'Chevrolet');

    $electric = $this->get('/listings?q=electric', [
        'X-Inertia' => 'true',
        'X-Inertia-Version' => hash_file('xxh128', public_path('build/manifest.json')),
    ])->assertOk()->json('props.listings.data');

    expect(collect($electric)->pluck('id')->all())->toBe([$ev->id]);

    $chevrolet = $this->get('/listings?q=chevrolet', [
        'X-Inertia' => 'true',
        'X-Inertia-Version' => hash_file('xxh128', public_path('build/manifest.json')),
    ])->assertOk()->json('props.listings.data');

    expect(collect($chevrolet)->pluck('id')->all())->not->toContain($ev->id);
});
