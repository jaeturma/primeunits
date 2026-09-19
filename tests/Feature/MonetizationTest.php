<?php

use App\Models\Category;
use App\Models\Listing;
use App\Models\ListingBoost;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Role;
use App\Models\SellerProfile;
use App\Models\Subscription;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RbacSeeder::class);
    $this->seed(CategorySeeder::class);
});

function monetizationRole(User $user, string $role): void
{
    $user->roles()->attach(Role::query()->where('name', $role)->firstOrFail());
}

function monetizationSeller(): User
{
    $seller = User::factory()->create();
    monetizationRole($seller, 'seller');

    SellerProfile::query()->create([
        'user_id' => $seller->id,
        'seller_type' => 'individual',
        'contact_number' => '09170000000',
        'status' => SellerProfile::StatusVerified,
        'verified_at' => now(),
    ]);

    return $seller->fresh();
}

function monetizationListing(User $seller, string $title = 'Approved Unit'): Listing
{
    return Listing::query()->create([
        'user_id' => $seller->id,
        'seller_profile_id' => $seller->sellerProfile->id,
        'category_id' => Category::query()->firstOrFail()->id,
        'title' => $title,
        'price' => '500000',
        'condition' => Listing::ConditionUsed,
        'status' => Listing::StatusApproved,
        'approved_at' => now(),
    ]);
}

test('admin can create plans', function () {
    $admin = User::factory()->create();
    monetizationRole($admin, 'superadmin');

    $this->actingAs($admin)
        ->post(route('adm.plans.store'), [
            'name' => '7-Day Boost',
            'type' => Plan::TypeBoost,
            'price' => '499',
            'duration_days' => 7,
            'features' => ['highlight listing'],
            'is_active' => true,
        ])
        ->assertRedirect();

    expect(Plan::query()->where('name', '7-Day Boost')->exists())->toBeTrue();
});

test('seller subscription activates only after payment confirmation', function () {
    $seller = monetizationSeller();
    $admin = User::factory()->create();
    monetizationRole($admin, 'superadmin');
    $plan = Plan::query()->create([
        'name' => 'Pro Seller',
        'type' => Plan::TypeSubscription,
        'price' => '999',
        'duration_days' => 30,
        'features' => ['unlimited listings'],
        'is_active' => true,
    ]);

    $this->actingAs($seller)
        ->post(route('seller.plans.subscribe', $plan))
        ->assertRedirect();

    $subscription = Subscription::query()->firstOrFail();
    $payment = Payment::query()->firstOrFail();

    expect($subscription->status)->toBe(Subscription::StatusPending)
        ->and($payment->status)->toBe(Payment::StatusPending);

    $this->actingAs($admin)
        ->post(route('adm.payments.confirm', $payment))
        ->assertRedirect();

    expect($subscription->refresh()->status)->toBe(Subscription::StatusActive)
        ->and($subscription->starts_at)->not->toBeNull()
        ->and($payment->refresh()->status)->toBe(Payment::StatusConfirmed);
});

test('seller can create boost payment and admin confirmation activates boost', function () {
    Storage::fake('public');

    $seller = monetizationSeller();
    $admin = User::factory()->create();
    monetizationRole($admin, 'superadmin');
    $listing = monetizationListing($seller);
    $plan = Plan::query()->create([
        'name' => '7-Day Boost',
        'type' => Plan::TypeBoost,
        'price' => '499',
        'duration_days' => 7,
        'features' => ['highlight listing'],
        'is_active' => true,
    ]);

    $this->actingAs($seller)
        ->post(route('seller.listings.boosts.store', [$listing, $plan]))
        ->assertRedirect();

    $payment = Payment::query()->firstOrFail();

    $this->actingAs($seller)
        ->post(route('payments.store', $payment), [
            'method' => Payment::MethodGcash,
            'reference_number' => 'GCASH-123',
            'proof_file' => UploadedFile::fake()->image('proof.jpg'),
        ])
        ->assertRedirect();

    Storage::disk('public')->assertExists($payment->refresh()->proof_file);

    $this->actingAs($admin)
        ->post(route('adm.payments.confirm', $payment))
        ->assertRedirect();

    $boost = ListingBoost::query()->firstOrFail();

    expect($boost->refresh()->is_active)->toBeTrue()
        ->and($boost->starts_at)->not->toBeNull()
        ->and($boost->ends_at)->not->toBeNull();
});

test('active boost prevents duplicate boost purchase and appears first publicly', function () {
    $seller = monetizationSeller();
    $listing = monetizationListing($seller, 'Boosted Unit');
    $otherListing = monetizationListing($seller, 'Normal Unit');
    $plan = Plan::query()->create([
        'name' => '7-Day Boost',
        'type' => Plan::TypeBoost,
        'price' => '499',
        'duration_days' => 7,
        'is_active' => true,
    ]);

    $listing->boosts()->create([
        'plan_id' => $plan->id,
        'starts_at' => now(),
        'ends_at' => now()->addDays(7),
        'is_active' => true,
    ]);

    $this->actingAs($seller)
        ->post(route('seller.listings.boosts.store', [$listing, $plan]))
        ->assertSessionHasErrors('listing_id');

    $this->get(route('listings.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('listings/index')
            ->where('listings.data.0.title', 'Boosted Unit')
            ->where('listings.data.0.is_featured', true)
            ->where('listings.data.1.title', 'Normal Unit'),
        );
});
