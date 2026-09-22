<?php

use App\Models\Category;
use App\Models\Listing;
use App\Models\Role;
use App\Models\SellerProfile;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware(PreventRequestForgery::class);
    $this->seed(RbacSeeder::class);
    $this->seed(CategorySeeder::class);
});

function listingUserWithRole(User $user, string $role): void
{
    $user->roles()->attach(Role::query()->where('name', $role)->firstOrFail());
}

function verifiedSeller(): User
{
    $user = User::factory()->create();
    listingUserWithRole($user, 'seller');

    SellerProfile::query()->create([
        'user_id' => $user->id,
        'seller_type' => 'individual',
        'contact_number' => '09170000000',
        'status' => SellerProfile::StatusVerified,
        'verified_at' => now(),
    ]);

    return $user->fresh();
}

test('category seeder creates dynamic spec fields', function () {
    $vehicle = Category::query()->where('slug', 'cars')->firstOrFail();

    expect(Category::query()->count())->toBe(9)
        ->and($vehicle->specFields()->where('name', 'transmission')->exists())->toBeTrue();
});

test('verified sellers can create listings with category specs images and pdf attachments', function () {
    Storage::fake('public');

    $seller = verifiedSeller();
    $vehicle = Category::query()->where('slug', 'cars')->with('specFields')->firstOrFail();
    $bodyType = $vehicle->specFields->firstWhere('name', 'body_type');
    $transmission = $vehicle->specFields->firstWhere('name', 'transmission');
    $fuelType = $vehicle->specFields->firstWhere('name', 'fuel_type');

    $this->actingAs($seller)
        ->post(route('seller.listings.store'), [
            'title' => '2022 Pickup Truck',
            'category_id' => $vehicle->id,
            'price' => '1250000',
            'condition' => Listing::ConditionUsed,
            'negotiable' => true,
            'brand' => 'Toyota',
            'model' => 'Hilux',
            'specs' => [
                $bodyType->id => 'Pickup',
                $transmission->id => 'Automatic',
                $fuelType->id => 'Diesel',
            ],
            'images' => [
                UploadedFile::fake()->image('front.jpg'),
            ],
            'attachments' => [
                UploadedFile::fake()->create('delivery-receipt.pdf', 128, 'application/pdf'),
            ],
        ])
        ->assertRedirect();

    $listing = Listing::query()->with(['attachments', 'specValues', 'images'])->firstOrFail();

    expect($listing->status)->toBe(Listing::StatusPending)
        ->and($listing->specValues)->toHaveCount(3)
        ->and($listing->images)->toHaveCount(1)
        ->and($listing->attachments)->toHaveCount(1);

    Storage::disk('public')->assertExists($listing->images->first()->path);
    Storage::disk('public')->assertExists($listing->attachments->first()->path);
});

test('listing attachments must be pdf files up to five megabytes', function () {
    $seller = verifiedSeller();
    $vehicle = Category::query()->where('slug', 'cars')->firstOrFail();

    $this->actingAs($seller)
        ->post(route('seller.listings.store'), [
            'title' => 'Invalid Attachment',
            'category_id' => $vehicle->id,
            'price' => '100000',
            'condition' => Listing::ConditionUsed,
            'attachments' => [
                UploadedFile::fake()->create('delivery-receipt.docx', 100, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
                UploadedFile::fake()->create('transfer-receipt.pdf', 5200, 'application/pdf'),
            ],
        ])
        ->assertSessionHasErrors(['attachments.0', 'attachments.1']);
});

test('dynamic specs must belong to selected category', function () {
    $seller = verifiedSeller();
    $vehicle = Category::query()->where('slug', 'cars')->firstOrFail();
    $farmField = Category::query()
        ->where('slug', 'agricultural-equipment')
        ->firstOrFail()
        ->specFields()
        ->firstOrFail();

    $this->actingAs($seller)
        ->post(route('seller.listings.store'), [
            'title' => 'Invalid Specs',
            'category_id' => $vehicle->id,
            'price' => '100000',
            'condition' => Listing::ConditionUsed,
            'specs' => [
                $farmField->id => '100',
            ],
        ])
        ->assertSessionHasErrors("specs.{$farmField->id}");
});

test('pending listings are hidden publicly until admin approval', function () {
    $seller = verifiedSeller();
    $admin = User::factory()->create();
    listingUserWithRole($admin, 'superadmin');
    $agent = User::factory()->create();
    listingUserWithRole($agent, 'coordinator');
    $manager = User::factory()->create();
    listingUserWithRole($manager, 'manager');

    $listing = Listing::query()->create([
        'user_id' => $seller->id,
        'seller_profile_id' => $seller->sellerProfile->id,
        'category_id' => Category::query()->firstOrFail()->id,
        'title' => 'Pending Unit',
        'price' => '500000',
        'condition' => Listing::ConditionUsed,
        'status' => Listing::StatusPending,
    ]);

    $this->getJson(route('listings.show', $listing))->assertNotFound();

    $this->actingAs($agent)
        ->post(route('adm.listings.approve', $listing))
        ->assertRedirect();
    $this->actingAs($manager)
        ->post(route('adm.listings.approve', $listing->refresh()))
        ->assertRedirect();
    $this->actingAs($admin)
        ->post(route('adm.listings.approve', $listing))
        ->assertRedirect();

    $this->get(route('listings.show', $listing->refresh()), [
        'X-Inertia' => 'true',
        'X-Inertia-Version' => hash_file('xxh128', public_path('build/manifest.json')),
    ])
        ->assertOk()
        ->assertJsonPath('component', 'listings/show')
        ->assertJsonPath('props.listing.title', 'Pending Unit');
});

test('unverified seller cannot access listing creation', function () {
    $seller = User::factory()->create();
    listingUserWithRole($seller, 'seller');

    SellerProfile::query()->create([
        'user_id' => $seller->id,
        'seller_type' => 'individual',
        'contact_number' => '09170000000',
        'status' => SellerProfile::StatusPending,
    ]);

    $this->actingAs($seller)
        ->getJson(route('seller.listings.create'))
        ->assertForbidden();
});
