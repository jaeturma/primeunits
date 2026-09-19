<?php

use App\Models\Category;
use App\Models\CommissionLog;
use App\Models\Lead;
use App\Models\Listing;
use App\Models\Payment;
use App\Models\Role;
use App\Models\SellerProfile;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RbacSeeder::class);
    $this->seed(CategorySeeder::class);
});

function analyticsRole(User $user, string $role): void
{
    $user->roles()->attach(Role::query()->where('name', $role)->firstOrFail());
}

function analyticsSeller(string $name = 'Analytics Seller'): User
{
    $seller = User::factory()->create(['name' => $name]);
    analyticsRole($seller, 'seller');

    SellerProfile::query()->create([
        'user_id' => $seller->id,
        'seller_type' => 'individual',
        'contact_number' => '09170000000',
        'status' => SellerProfile::StatusVerified,
        'verified_at' => now(),
    ]);

    return $seller->fresh();
}

function analyticsListing(User $seller, Category $category, string $title, string $province = 'Cebu'): Listing
{
    return Listing::query()->create([
        'user_id' => $seller->id,
        'seller_profile_id' => $seller->sellerProfile->id,
        'category_id' => $category->id,
        'title' => $title,
        'price' => '750000',
        'condition' => Listing::ConditionUsed,
        'region' => 'Region VII',
        'province' => $province,
        'status' => Listing::StatusApproved,
        'approved_at' => now(),
    ]);
}

function analyticsLead(Listing $listing, User $buyer, string $status = Lead::StatusInquiry): Lead
{
    return Lead::query()->create([
        'listing_id' => $listing->id,
        'buyer_id' => $buyer->id,
        'seller_id' => $listing->user_id,
        'reference_code' => Lead::generateReferenceCode(),
        'status' => $status,
        'message' => 'Analytics inquiry',
        'created_at' => now()->subDays(2),
        'updated_at' => now()->subDays(2),
    ]);
}

test('admin dashboard renders aggregate analytics', function () {
    $admin = User::factory()->create();
    analyticsRole($admin, 'superadmin');
    $seller = analyticsSeller('Top Seller');
    $buyer = User::factory()->create();
    analyticsRole($buyer, 'buyer');
    $category = Category::query()->where('slug', 'vehicle')->firstOrFail();
    $listing = analyticsListing($seller, $category, 'Loader Unit');
    $lead = analyticsLead($listing, $buyer, Lead::StatusClosed);
    $transaction = Transaction::query()->create([
        'lead_id' => $lead->id,
        'listing_id' => $listing->id,
        'agreed_price' => '700000',
        'commission_rate' => '3',
        'commission_amount' => '21000',
        'status' => Transaction::StatusConfirmed,
        'buyer_confirmed' => true,
        'seller_confirmed' => true,
        'confirmed_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    CommissionLog::query()->create([
        'transaction_id' => $transaction->id,
        'amount' => '21000',
        'status' => CommissionLog::StatusPaid,
        'paid_at' => now(),
    ]);

    Payment::query()->create([
        'user_id' => $seller->id,
        'payable_type' => SellerProfile::class,
        'payable_id' => $seller->sellerProfile->id,
        'amount' => '1000',
        'method' => Payment::MethodGcash,
        'status' => Payment::StatusConfirmed,
        'paid_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('adm.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('adm/dashboard')
            ->where('summary.total_sellers', 1)
            ->where('summary.total_buyers', 1)
            ->where('summary.total_listings', 1)
            ->where('summary.active_listings', 1)
            ->where('summary.total_leads', 1)
            ->where('summary.total_transactions', 1)
            ->where('summary.conversion_rate', 100)
            ->where('revenue.total_commission', 21000)
            ->where('revenue.total_paid_commission', 21000)
            ->where('revenue.total_payments_received', 1000)
            ->where('listingStats.top_listings.0.title', 'Loader Unit')
            ->where('listingStats.top_sellers.0.name', 'Top Seller'),
        );
});

test('analytics endpoints support category and location filters', function () {
    $admin = User::factory()->create();
    analyticsRole($admin, 'superadmin');
    $seller = analyticsSeller();
    $buyer = User::factory()->create();
    analyticsRole($buyer, 'buyer');
    $vehicle = Category::query()->where('slug', 'vehicle')->firstOrFail();
    $property = Category::query()->where('slug', 'heavy_equipment')->firstOrFail();
    $cebuListing = analyticsListing($seller, $vehicle, 'Cebu Vehicle', 'Cebu');
    $boholListing = analyticsListing($seller, $property, 'Bohol Property', 'Bohol');

    analyticsLead($cebuListing, $buyer);
    analyticsLead($cebuListing, $buyer);
    analyticsLead($boholListing, $buyer);

    $this->actingAs($admin)
        ->getJson(route('adm.analytics.listings', [
            'category_id' => $vehicle->id,
            'location' => 'Cebu',
        ]))
        ->assertOk()
        ->assertJsonPath('listings_per_category.0.name', $vehicle->name)
        ->assertJsonPath('listings_per_category.0.total', 1)
        ->assertJsonPath('top_listings.0.title', 'Cebu Vehicle')
        ->assertJsonPath('top_listings.0.leads_count', 2);

    $this->actingAs($admin)
        ->getJson(route('adm.analytics.conversions', [
            'category_id' => $vehicle->id,
            'location' => 'Cebu',
        ]))
        ->assertOk()
        ->assertJsonPath('total_leads', 2)
        ->assertJsonPath('total_transactions', 0)
        ->assertJsonPath('leads_to_transactions_rate', 0);
});
