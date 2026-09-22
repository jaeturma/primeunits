<?php

use App\Models\Category;
use App\Models\CategoryAccessRule;
use App\Models\ConfidentialityNotice;
use App\Models\DealerProfile;
use App\Models\Listing;
use App\Models\ListingAccessRequest;
use App\Models\MembershipAccess;
use App\Models\MembershipApplication;
use App\Models\MembershipApplicationDocument;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Role;
use App\Models\SellerProfile;
use App\Models\Subscription;
use App\Models\User;
use App\Services\PaymentService;
use Database\Seeders\CategorySeeder;
use Database\Seeders\PrimeUnitsMembershipDemoSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->withoutMiddleware(PreventRequestForgery::class);
    $this->seed(RbacSeeder::class);
    $this->seed(CategorySeeder::class);
});

function memberRole(User $user, string $role): void
{
    $user->roles()->attach(Role::query()->where('name', $role)->firstOrFail());
}

function buyerWithAccess(string $buyerLevel = MembershipAccess::LevelRegular, array $overrides = []): User
{
    $user = User::factory()->create();
    memberRole($user, 'buyer');

    MembershipAccess::query()->create([
        'user_id' => $user->id,
        'identity_verification_level' => MembershipAccess::IdentityRegular,
        'buyer_access_level' => $buyerLevel,
        'seller_access_level' => MembershipAccess::LevelNone,
        ...$overrides,
    ]);

    return $user->fresh();
}

function carsCategory(): Category
{
    return Category::query()->where('slug', 'cars')->firstOrFail();
}

function restrictedListing(User $seller, string $visibility, array $overrides = []): Listing
{
    return Listing::query()->create([
        'user_id' => $seller->id,
        'category_id' => carsCategory()->id,
        'title' => 'Restricted Listing '.uniqid(),
        'price' => 1000000,
        'condition' => Listing::ConditionUsed,
        'marketplace_tier' => Listing::TierSilver,
        'visibility_level' => $visibility,
        'seller_capacity' => Listing::CapacityPrivateOwner,
        'status' => Listing::StatusApproved,
        'approved_at' => now(),
        ...$overrides,
    ]);
}

// --- Regular public browsing -------------------------------------------

test('a guest can browse a public regular listing without an account', function () {
    $seller = User::factory()->create();
    $listing = restrictedListing($seller, Listing::VisibilityPublic, ['marketplace_tier' => Listing::TierRegular]);

    $this->get(route('listings.show', $listing))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('listing.can_view_full', true));
});

// --- Membership activation and buyer/seller separation ------------------

test('an approved membership application grants buyer access, not payment confirmation alone', function () {
    $user = buyerWithAccess();
    $plan = Plan::query()->create(['name' => 'Silver Test Plan', 'type' => Plan::TypeMembership, 'tier' => Plan::TierSilver, 'price' => 1999, 'is_active' => true]);
    $subscription = Subscription::query()->create(['user_id' => $user->id, 'plan_id' => $plan->id, 'status' => Subscription::StatusPendingPayment]);
    $payment = $subscription->payment()->create(['user_id' => $user->id, 'amount' => 1999, 'method' => Payment::MethodGcash, 'status' => Payment::StatusPending]);

    app(PaymentService::class)->confirm($payment);

    expect($user->fresh()->membershipAccess->hasBuyerAccessAtLeast(MembershipAccess::LevelSilver))->toBeFalse();

    $reviewer = User::factory()->create();
    memberRole($reviewer, 'admin');
    $application = MembershipApplication::query()->create([
        'user_id' => $user->id,
        'type' => MembershipApplication::TypeBuyer,
        'target_level' => 'silver',
        'status' => MembershipApplication::StatusPendingReview,
        'submitted_at' => now(),
    ]);

    $this->actingAs($reviewer)
        ->post(route('adm.membership-applications.approve', $application))
        ->assertRedirect();

    expect($user->fresh()->membershipAccess->hasBuyerAccessAtLeast(MembershipAccess::LevelSilver))->toBeTrue();
});

test('gold buyer access does not grant seller authorization', function () {
    $user = buyerWithAccess(MembershipAccess::LevelGold);

    expect($user->membershipAccess->hasBuyerAccessAtLeast(MembershipAccess::LevelGold))->toBeTrue()
        ->and($user->membershipAccess->hasSellerAccessAtLeast(MembershipAccess::LevelRegular))->toBeFalse();
});

test('silver seller authorization does not grant silver buyer access', function () {
    $user = buyerWithAccess(MembershipAccess::LevelNone, ['seller_access_level' => MembershipAccess::LevelSilver]);

    expect($user->membershipAccess->hasSellerAccessAtLeast(MembershipAccess::LevelSilver))->toBeTrue()
        ->and($user->membershipAccess->hasBuyerAccessAtLeast(MembershipAccess::LevelSilver))->toBeFalse();
});

test('store access is independent from personal membership access', function () {
    $owner = User::factory()->create();
    memberRole($owner, 'dealer');
    $dealer = DealerProfile::query()->create([
        'user_id' => $owner->id,
        'business_name' => 'Test Gold Store',
        'slug' => 'test-gold-store-'.$owner->id,
        'contact_number' => '09170000000',
        'status' => DealerProfile::StatusVerified,
        'verified_at' => now(),
        'store_tier' => DealerProfile::StoreTierGoldProfessional,
        'store_tier_status' => DealerProfile::StoreTierStatusActive,
    ]);

    expect($dealer->hasActiveStoreTierAtLeast(DealerProfile::StoreTierGoldProfessional))->toBeTrue()
        ->and($owner->membershipAccess)->toBeNull();
});

// --- Seller-side gating for premium categories ---------------------------

test('selling in a silver-restricted category requires silver seller authorization', function () {
    $category = carsCategory();
    CategoryAccessRule::query()->create([
        'category_id' => $category->id,
        'default_marketplace_tier' => 'silver',
        'min_buyer_access' => 'silver',
        'min_seller_access' => 'silver',
    ]);

    $seller = User::factory()->create();
    memberRole($seller, 'seller');
    SellerProfile::query()->create([
        'user_id' => $seller->id,
        'seller_type' => 'individual',
        'contact_number' => '09170000000',
        'status' => SellerProfile::StatusVerified,
        'verified_at' => now(),
    ]);

    $this->actingAs($seller)
        ->post(route('seller.listings.store'), [
            'title' => 'Should Be Blocked',
            'category_id' => $category->id,
            'price' => '500000',
            'condition' => Listing::ConditionUsed,
        ])
        ->assertSessionHasErrors('category_id');

    MembershipAccess::query()->create([
        'user_id' => $seller->id,
        'seller_access_level' => MembershipAccess::LevelSilver,
    ]);

    $bodyType = $category->specFields()->where('name', 'body_type')->firstOrFail();
    $fuelType = $category->specFields()->where('name', 'fuel_type')->firstOrFail();
    $transmission = $category->specFields()->where('name', 'transmission')->firstOrFail();

    $this->actingAs($seller->fresh())
        ->post(route('seller.listings.store'), [
            'title' => 'Now Allowed',
            'category_id' => $category->id,
            'price' => '500000',
            'condition' => Listing::ConditionUsed,
            'specs' => [
                $bodyType->id => 'Sedan',
                $fuelType->id => 'Gasoline',
                $transmission->id => 'Automatic',
            ],
        ])
        ->assertSessionDoesntHaveErrors('category_id');

    expect(Listing::query()->where('title', 'Now Allowed')->exists())->toBeTrue();
});

// --- Listing visibility authorization -------------------------------------

test('silver exclusive listings are blocked for regular buyers and visible to silver buyers', function () {
    $seller = User::factory()->create();
    $listing = restrictedListing($seller, Listing::VisibilitySilverExclusive);

    $regularBuyer = buyerWithAccess(MembershipAccess::LevelRegular);
    $this->actingAs($regularBuyer)
        ->get(route('listings.show', $listing))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('listing.can_view_full', false));

    $silverBuyer = buyerWithAccess(MembershipAccess::LevelSilver);
    $this->actingAs($silverBuyer)
        ->get(route('listings.show', $listing))
        ->assertInertia(fn ($page) => $page->where('listing.can_view_full', true));
});

test('gold exclusive listings are blocked for silver buyers and visible to gold buyers', function () {
    $seller = User::factory()->create();
    $listing = restrictedListing($seller, Listing::VisibilityGoldExclusive, ['marketplace_tier' => Listing::TierGold]);

    $silverBuyer = buyerWithAccess(MembershipAccess::LevelSilver);
    $this->actingAs($silverBuyer)
        ->get(route('listings.show', $listing))
        ->assertInertia(fn ($page) => $page->where('listing.can_view_full', false));

    $goldBuyer = buyerWithAccess(MembershipAccess::LevelGold);
    $this->actingAs($goldBuyer)
        ->get(route('listings.show', $listing))
        ->assertInertia(fn ($page) => $page->where('listing.can_view_full', true));
});

test('verified buyer only listings require at least regular verified buyer access', function () {
    $seller = User::factory()->create();
    $listing = restrictedListing($seller, Listing::VisibilityVerifiedBuyerOnly);

    // A guest cannot even see the restricted page (not just its details).
    $this->get(route('listings.show', $listing))->assertNotFound();

    $verifiedBuyer = buyerWithAccess(MembershipAccess::LevelRegular);
    $this->actingAs($verifiedBuyer)
        ->get(route('listings.show', $listing))
        ->assertInertia(fn ($page) => $page->where('listing.can_view_full', true));
});

test('public preview listings redact confidential details for unauthorized viewers', function () {
    $seller = User::factory()->create();
    $listing = restrictedListing($seller, Listing::VisibilityPublicPreview, [
        'marketplace_tier' => Listing::TierGold,
        'registration_number' => 'SECRET-REG-12345',
        'public_preview_summary' => 'A confidential Gold asset.',
    ]);

    $response = $this->get(route('listings.show', $listing))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('listing.can_view_full', false)
            ->where('listing.description', 'A confidential Gold asset.')
            ->missing('listing.masked_registration_number'));

    $response->assertDontSee('SECRET-REG-12345', false);
});

test('invitation only listings require an approved unexpired access request', function () {
    $seller = User::factory()->create();
    $listing = restrictedListing($seller, Listing::VisibilityInvitationOnly, ['marketplace_tier' => Listing::TierGold]);

    $buyer = buyerWithAccess(MembershipAccess::LevelGold);

    $this->actingAs($buyer)
        ->get(route('listings.show', $listing))
        ->assertInertia(fn ($page) => $page->where('listing.can_view_full', false));

    $accessRequest = ListingAccessRequest::query()->create([
        'listing_id' => $listing->id,
        'user_id' => $buyer->id,
        'status' => ListingAccessRequest::StatusApproved,
        'expires_at' => now()->addDays(10),
    ]);

    $this->actingAs($buyer)
        ->get(route('listings.show', $listing))
        ->assertInertia(fn ($page) => $page->where('listing.can_view_full', true));

    $accessRequest->update(['status' => ListingAccessRequest::StatusRevoked]);

    $this->actingAs($buyer)
        ->get(route('listings.show', $listing))
        ->assertInertia(fn ($page) => $page->where('listing.can_view_full', false));
});

test('a suspended buyer immediately loses access to previously available restricted listings', function () {
    $seller = User::factory()->create();
    $listing = restrictedListing($seller, Listing::VisibilitySilverExclusive);
    $buyer = buyerWithAccess(MembershipAccess::LevelSilver);

    $this->actingAs($buyer)
        ->get(route('listings.show', $listing))
        ->assertInertia(fn ($page) => $page->where('listing.can_view_full', true));

    $buyer->membershipAccess->update(['buyer_access_status' => MembershipAccess::StatusSuspended]);

    // Suspended access blocks the restricted listing page entirely, not
    // just its confidential details.
    $this->actingAs($buyer)
        ->get(route('listings.show', $listing))
        ->assertNotFound();
});

// --- No leakage through search -------------------------------------------

test('restricted listings do not leak through the public search feed', function () {
    $seller = User::factory()->create();
    $silverListing = restrictedListing($seller, Listing::VisibilitySilverExclusive);
    $publicListing = restrictedListing($seller, Listing::VisibilityPublic, ['marketplace_tier' => Listing::TierRegular]);

    $response = $this->get(route('listings.index'), [
        'X-Inertia' => 'true',
        'X-Inertia-Version' => hash_file('xxh128', public_path('build/manifest.json')),
    ])->assertOk();

    $titles = collect($response->json('props.listings.data'))->pluck('title');

    expect($titles)->toContain($publicListing->title)
        ->not->toContain($silverListing->title);
});

// --- Category tier exceptions ---------------------------------------------

test('an admin can approve a gold candidate listing as an exception to its category default', function () {
    $seller = User::factory()->create();
    $listing = Listing::query()->create([
        'user_id' => $seller->id,
        'category_id' => Category::query()->where('slug', 'motorcycles')->firstOrFail()->id,
        'title' => 'Ordinary Chopper Turned Gold Candidate',
        'price' => 2000000,
        'condition' => Listing::ConditionUsed,
        'marketplace_tier' => Listing::TierSilver,
        'visibility_level' => Listing::VisibilityGoldExclusive,
        'is_gold_candidate' => true,
        'status' => Listing::StatusApproved,
        'approved_at' => now(),
    ]);

    $reviewer = User::factory()->create();
    memberRole($reviewer, 'admin');

    $this->actingAs($reviewer)
        ->post(route('adm.listing-tiers.approve-gold', $listing))
        ->assertRedirect();

    expect($listing->fresh()->marketplace_tier)->toBe(Listing::TierGold)
        ->and($listing->fresh()->is_gold_candidate)->toBeFalse();
});

test('a pending gold candidate is not publicly visible until approved', function () {
    $seller = User::factory()->create();
    $listing = Listing::query()->create([
        'user_id' => $seller->id,
        'category_id' => carsCategory()->id,
        'title' => 'Pending Commercial Vessel Demo',
        'price' => 40000000,
        'condition' => Listing::ConditionUsed,
        'marketplace_tier' => Listing::TierSilver,
        'visibility_level' => Listing::VisibilityGoldExclusive,
        'is_gold_candidate' => true,
        'status' => Listing::StatusPending,
    ]);

    $this->get(route('listings.show', $listing))->assertNotFound();
});

// --- Security: documents, masking, IDOR ------------------------------------

test('membership application documents are only visible to the owner or an authorized reviewer', function () {
    Storage::fake('local');
    $applicant = User::factory()->create();
    $stranger = User::factory()->create();
    $reviewer = User::factory()->create();
    memberRole($reviewer, 'admin');

    $application = MembershipApplication::query()->create([
        'user_id' => $applicant->id,
        'type' => MembershipApplication::TypeBuyer,
        'target_level' => 'silver',
        'status' => MembershipApplication::StatusPendingReview,
    ]);

    $path = 'membership-applications/demo-id.jpg';
    Storage::disk('local')->put($path, 'fake-content');
    $document = MembershipApplicationDocument::query()->create([
        'membership_application_id' => $application->id,
        'label' => 'Government ID',
        'path' => $path,
    ]);

    $this->actingAs($stranger)
        ->get(route('membership.applications.documents', $document))
        ->assertForbidden();

    $this->actingAs($applicant)
        ->get(route('membership.applications.documents', $document))
        ->assertOk();

    $this->actingAs($reviewer)
        ->get(route('membership.applications.documents', $document))
        ->assertOk();
});

test('a user can submit a membership application with private documents', function () {
    Storage::fake('local');
    $user = buyerWithAccess();

    $this->actingAs($user)
        ->post(route('membership.applications.store'), [
            'type' => MembershipApplication::TypeSeller,
            'target_level' => 'silver',
            'documents' => [UploadedFile::fake()->image('id.jpg')],
        ])
        ->assertRedirect();

    $application = MembershipApplication::query()->where('user_id', $user->id)->firstOrFail();

    expect($application->status)->toBe(MembershipApplication::StatusPendingReview)
        ->and($application->documents)->toHaveCount(1);

    Storage::disk('local')->assertExists($application->documents->first()->path);
    Storage::disk('public')->assertMissing($application->documents->first()->path);
});

test('listing registration numbers are never exposed unmasked', function () {
    $seller = User::factory()->create();
    $listing = restrictedListing($seller, Listing::VisibilityPublic, [
        'marketplace_tier' => Listing::TierGold,
        'registration_number' => 'SECRET-FULL-NUMBER-9999',
    ]);

    $response = $this->get(route('listings.show', $listing))->assertOk();

    $response->assertDontSee('SECRET-FULL-NUMBER-9999', false);
    expect($listing->maskedRegistrationNumber())->not->toBe('SECRET-FULL-NUMBER-9999');
});

// --- Marketplace mode switcher ---------------------------------------------

test('the marketplace mode switcher rejects an unauthorized mode server-side', function () {
    $user = buyerWithAccess(MembershipAccess::LevelRegular);

    $this->actingAs($user)
        ->post(route('membership.switch-mode'), ['mode' => MembershipAccess::LevelGold])
        ->assertSessionHasErrors('mode');

    expect($user->fresh()->membershipAccess->current_mode)->toBe('regular');
});

test('the marketplace mode switcher allows an authorized mode and falls back safely after suspension', function () {
    $user = buyerWithAccess(MembershipAccess::LevelGold);

    $this->actingAs($user)
        ->post(route('membership.switch-mode'), ['mode' => MembershipAccess::LevelGold])
        ->assertRedirect();

    expect($user->fresh()->membershipAccess->current_mode)->toBe('gold');

    $user->membershipAccess->update(['buyer_access_status' => MembershipAccess::StatusSuspended]);

    expect($user->fresh()->membershipAccess->effectiveMode())->toBe(MembershipAccess::LevelRegular);
});

// --- Seeders ----------------------------------------------------------------

test('the membership demo seeder runs on a clean database and is idempotent when rerun', function () {
    $this->seed(PrimeUnitsMembershipDemoSeeder::class);

    $userCount = User::query()->whereIn('email', [
        'regular.demo@primeunits.test', 'silver.buyer@primeunits.test', 'silver.seller@primeunits.test',
        'silver.store@primeunits.test', 'gold.buyer@primeunits.test', 'gold.pending@primeunits.test',
        'gold.seller@primeunits.test', 'gold.store@primeunits.test', 'silver.suspended@primeunits.test',
        'gold.expired@primeunits.test',
    ])->count();
    $premiumListingCount = Listing::query()->whereIn('marketplace_tier', ['silver', 'gold'])->count();
    $applicationCount = MembershipApplication::query()->count();

    expect($userCount)->toBe(10)
        ->and($premiumListingCount)->toBeGreaterThanOrEqual(12)
        ->and(ConfidentialityNotice::active())->not->toBeNull();

    $this->seed(PrimeUnitsMembershipDemoSeeder::class);

    expect(User::query()->whereIn('email', [
        'regular.demo@primeunits.test', 'silver.buyer@primeunits.test', 'silver.seller@primeunits.test',
        'silver.store@primeunits.test', 'gold.buyer@primeunits.test', 'gold.pending@primeunits.test',
        'gold.seller@primeunits.test', 'gold.store@primeunits.test', 'silver.suspended@primeunits.test',
        'gold.expired@primeunits.test',
    ])->count())->toBe($userCount)
        ->and(Listing::query()->whereIn('marketplace_tier', ['silver', 'gold'])->count())->toBe($premiumListingCount)
        ->and(MembershipApplication::query()->count())->toBe($applicationCount);
});

test('the seeded expired gold member has lost active buyer access', function () {
    $this->seed(PrimeUnitsMembershipDemoSeeder::class);

    $expired = User::query()->where('email', 'gold.expired@primeunits.test')->firstOrFail();

    expect($expired->membershipAccess->hasBuyerAccessAtLeast(MembershipAccess::LevelGold))->toBeFalse();
});
