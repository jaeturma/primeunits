<?php

use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RbacSeeder::class);
    $this->seed(CategorySeeder::class);
});

function capacityCategory(): Category
{
    return Category::query()->where('slug', 'cars')->firstOrFail();
}

function capacityListing(array $overrides = []): Listing
{
    $seller = User::factory()->create();

    return Listing::query()->create([
        'user_id' => $seller->id,
        'category_id' => capacityCategory()->id,
        'title' => 'Capacity Listing '.uniqid(),
        'price' => 1000000,
        'condition' => Listing::ConditionUsed,
        'marketplace_tier' => Listing::TierRegular,
        'visibility_level' => Listing::VisibilityPublic,
        'status' => Listing::StatusApproved,
        'approved_at' => now(),
        ...$overrides,
    ]);
}

test('a public listing discloses the seller capacity label on the full view', function () {
    $listing = capacityListing(['seller_capacity' => Listing::CapacityAuthorizedDealer]);

    $this->get(route('listings.show', $listing))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('listing.seller_capacity', Listing::CapacityAuthorizedDealer)
            ->where('listing.seller_capacity_label', 'Authorized Dealer'));
});

test('a listing without a declared capacity omits the label without error', function () {
    $listing = capacityListing(['seller_capacity' => null]);

    $this->get(route('listings.show', $listing))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('listing.seller_capacity', null)
            ->where('listing.seller_capacity_label', null));
});

test('a restricted public-preview listing still discloses the seller capacity, since it is not confidential', function () {
    $listing = capacityListing([
        'marketplace_tier' => Listing::TierGold,
        'visibility_level' => Listing::VisibilityPublicPreview,
        'seller_capacity' => Listing::CapacityBrokerageCompany,
        'confidentiality_required' => true,
    ]);

    $this->get(route('listings.show', $listing))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('listing.can_view_full', false)
            ->where('listing.seller_capacity_label', 'Brokerage Company'));
});

test('every seller capacity constant resolves to a readable label', function () {
    $labels = [
        Listing::CapacityPrivateOwner => 'Private Owner',
        Listing::CapacityAuthorizedDealer => 'Authorized Dealer',
        Listing::CapacityIndependentBroker => 'Independent Broker',
        Listing::CapacityBrokerageCompany => 'Brokerage Company',
        Listing::CapacityCharterOperator => 'Charter Operator',
        Listing::CapacityFleetOrCorporateOwner => 'Fleet or Corporate Owner',
        Listing::CapacityManufacturerOrDistributor => 'Manufacturer or Distributor',
    ];

    foreach ($labels as $capacity => $label) {
        $listing = capacityListing(['seller_capacity' => $capacity]);

        expect($listing->sellerCapacityLabel())->toBe($label);
    }
});
