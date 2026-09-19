<?php

use App\Models\Category;
use App\Models\DealerProfile;
use App\Models\Listing;
use App\Models\RentalUnit;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->withoutMiddleware(PreventRequestForgery::class);
    $this->seed([RbacSeeder::class, CategorySeeder::class]);
});

test('a verified dealer has a public portal at the singular dealer url', function () {
    $dealer = User::factory()->create(['username' => 'prime-motors']);
    $dealer->roles()->attach(Role::query()->where('name', 'dealer')->firstOrFail());
    $profile = DealerProfile::query()->create([
        'user_id' => $dealer->id,
        'business_name' => 'Prime Motors',
        'slug' => 'prime-motors',
        'contact_number' => '09170000000',
        'status' => DealerProfile::StatusVerified,
        'verified_at' => now(),
    ]);

    $this->get(route('dealer.show', $profile->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('dealers/show')
            ->where('dealer.slug', 'prime-motors'));
});

test('a rental provider has a public portal with nested unit urls', function () {
    $provider = User::factory()->create(['username' => 'cebu-vans']);
    $provider->roles()->attach(Role::query()->where('name', 'rental_provider')->firstOrFail());
    $unit = RentalUnit::query()->create([
        'user_id' => $provider->id,
        'rental_type' => RentalUnit::TypeVanRental,
        'name' => 'Toyota HiAce',
        'price_per_day' => 4500,
        'status' => RentalUnit::StatusApproved,
        'approved_at' => now(),
    ]);

    $this->get(route('rentals.provider', $provider->username))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('rentals/provider')
            ->where('provider.username', 'cebu-vans')
            ->has('rentals.data', 1));

    $this->get(route('rentals.show', [$provider->username, $unit]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('rentals/show')
            ->where('rental.slug', $unit->slug));

    $this->get("/rentals/{$unit->slug}")
        ->assertRedirect(route('rentals.show', [$provider->username, $unit]));
});

test('a verified dealer can create and manage dealer units', function () {
    $dealer = User::factory()->create();
    $dealer->roles()->attach(Role::query()->where('name', 'dealer')->firstOrFail());
    $profile = DealerProfile::query()->create([
        'user_id' => $dealer->id,
        'business_name' => 'Dealer Inventory',
        'slug' => 'dealer-inventory',
        'contact_number' => '09170000000',
        'status' => DealerProfile::StatusVerified,
        'verified_at' => now(),
    ]);
    $category = Category::query()->with('specFields')->where('is_active', true)->firstOrFail();
    $specs = $category->specFields->mapWithKeys(fn ($field): array => [
        $field->id => $field->type === 'select' ? $field->options[0] : ($field->type === 'number' ? '1' : 'Test value'),
    ])->all();

    $this->actingAs($dealer)->post(route('dealer.units.store'), [
        'title' => 'New Dealer Unit',
        'category_id' => $category->id,
        'price' => 1500000,
        'condition' => Listing::ConditionBrandNew,
        'negotiable' => false,
        'listing_type' => Listing::TypeFree,
        'specs' => $specs,
        'images' => [],
        'attachments' => [],
    ])->assertRedirect('/dealer/units');

    $listing = Listing::query()->firstOrFail();

    expect($listing->dealer_profile_id)->toBe($profile->id)
        ->and($listing->seller_profile_id)->toBeNull()
        ->and($listing->listing_type)->toBe(Listing::TypeDealer);

    $this->actingAs($dealer)
        ->delete(route('dealer.units.destroy', $listing))
        ->assertRedirect('/dealer/units');

    expect(Listing::query()->count())->toBe(0);
});

test('rental providers cannot delete another providers unit', function () {
    $owner = User::factory()->create();
    $otherProvider = User::factory()->create();
    $otherProvider->roles()->attach(Role::query()->where('name', 'rental_provider')->firstOrFail());
    $unit = RentalUnit::query()->create([
        'user_id' => $owner->id,
        'rental_type' => RentalUnit::TypeCarRental,
        'name' => 'Protected Car',
        'price_per_day' => 2500,
        'status' => RentalUnit::StatusPending,
    ]);

    $this->actingAs($otherProvider)
        ->delete(route('rental-provider.units.destroy', $unit))
        ->assertForbidden();

    expect($unit->fresh())->not->toBeNull();
});
