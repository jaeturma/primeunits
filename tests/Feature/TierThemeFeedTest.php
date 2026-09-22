<?php

use App\Models\Category;
use App\Models\LandingAd;
use App\Models\Listing;
use App\Models\ListingBoost;
use App\Models\MembershipAccess;
use App\Models\Plan;
use App\Models\Role;
use App\Models\User;
use App\Services\FeedCompositionService;
use App\Support\FeedCursor;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->withoutMiddleware(PreventRequestForgery::class);
    $this->seed(RbacSeeder::class);
    $this->seed(CategorySeeder::class);
});

function feedBuyerWithAccess(string $buyerLevel = MembershipAccess::LevelRegular, array $overrides = []): User
{
    $user = User::factory()->create();

    MembershipAccess::query()->create([
        'user_id' => $user->id,
        'identity_verification_level' => MembershipAccess::IdentityRegular,
        'buyer_access_level' => $buyerLevel,
        'seller_access_level' => MembershipAccess::LevelNone,
        'current_mode' => $buyerLevel,
        ...$overrides,
    ]);

    return $user->fresh();
}

function feedCategory(): Category
{
    return Category::query()->where('slug', 'cars')->firstOrFail();
}

function feedListing(array $overrides = []): Listing
{
    $seller = User::factory()->create();

    return Listing::query()->create([
        'user_id' => $seller->id,
        'category_id' => feedCategory()->id,
        'title' => 'Feed Listing '.uniqid(),
        'price' => 500000,
        'condition' => Listing::ConditionUsed,
        'marketplace_tier' => Listing::TierRegular,
        'visibility_level' => Listing::VisibilityPublic,
        'status' => Listing::StatusApproved,
        'approved_at' => now(),
        ...$overrides,
    ]);
}

function feedListings(int $count, array $overrides = []): void
{
    for ($i = 0; $i < $count; $i++) {
        feedListing($overrides);
    }
}

// --- Theme selection -----------------------------------------------------

test('a guest is served the regular marketplace mode', function () {
    $this->get('/')->assertInertia(fn ($page) => $page->where('marketplaceMode', 'regular'));
});

test('a regular authenticated user is served the regular marketplace mode', function () {
    $user = feedBuyerWithAccess(MembershipAccess::LevelRegular);

    $this->actingAs($user)->get('/')->assertInertia(fn ($page) => $page->where('marketplaceMode', 'regular'));
});

test('a silver active user is served the silver marketplace mode', function () {
    $user = feedBuyerWithAccess(MembershipAccess::LevelSilver);

    $this->actingAs($user)->get('/')->assertInertia(fn ($page) => $page->where('marketplaceMode', 'silver'));
});

test('a gold active user is served the gold marketplace mode', function () {
    $user = feedBuyerWithAccess(MembershipAccess::LevelGold);

    $this->actingAs($user)->get('/')->assertInertia(fn ($page) => $page->where('marketplaceMode', 'gold'));
});

test('an expired silver user safely falls back to the regular theme', function () {
    $user = feedBuyerWithAccess(MembershipAccess::LevelSilver, ['buyer_access_status' => MembershipAccess::StatusExpired]);

    $this->actingAs($user)->get('/')->assertInertia(fn ($page) => $page->where('marketplaceMode', 'regular'));
});

test('a suspended gold user safely falls back to the regular theme', function () {
    $user = feedBuyerWithAccess(MembershipAccess::LevelGold, ['buyer_access_status' => MembershipAccess::StatusSuspended]);

    $this->actingAs($user)->get('/')->assertInertia(fn ($page) => $page->where('marketplaceMode', 'regular'));
});

test('the last authorized marketplace mode persists across requests', function () {
    $user = feedBuyerWithAccess(MembershipAccess::LevelGold, ['current_mode' => 'silver']);

    $this->actingAs($user)->get('/')->assertInertia(fn ($page) => $page->where('marketplaceMode', 'silver'));
});

test('client-supplied mode on the feed endpoint never overrides server authorization', function () {
    $user = feedBuyerWithAccess(MembershipAccess::LevelRegular);
    feedListings(3, ['visibility_level' => Listing::VisibilitySilverExclusive, 'marketplace_tier' => Listing::TierSilver]);

    $response = $this->actingAs($user)->getJson('/feed/listings?mode=gold&cursor=');

    $response->assertOk();
    $listingIds = collect($response->json('cards'))->pluck('listing.id')->filter();
    $silverTitles = Listing::query()->where('visibility_level', Listing::VisibilitySilverExclusive)->pluck('id');

    expect($listingIds->intersect($silverTitles))->toBeEmpty();
});

// --- Feed composition ------------------------------------------------------

test('the first batch returns 12 positions when enough content exists', function () {
    feedListings(20);

    $service = app(FeedCompositionService::class);
    $batch = $service->compose(null, MembershipAccess::LevelRegular, [], []);

    expect($batch['cards'])->toHaveCount(12)
        ->and($batch['has_more'])->toBeTrue();
});

test('a batch never contains more than one featured, one sponsored, or one advertisement', function () {
    feedListings(20);
    $plan = Plan::query()->create(['name' => 'Test Boost', 'type' => Plan::TypeBoost, 'price' => 100, 'is_active' => true]);

    Listing::query()->take(5)->get()->each(function (Listing $listing) use ($plan): void {
        ListingBoost::query()->create([
            'listing_id' => $listing->id,
            'plan_id' => $plan->id,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(5),
            'is_active' => true,
        ]);
    });

    Listing::query()->skip(5)->take(5)->get()->each(fn (Listing $listing) => $listing->update([
        'promotional_type' => Listing::PromoSponsored,
        'promoted_until' => now()->addDays(5),
    ]));

    LandingAd::query()->create([
        'title' => 'Test Ad', 'category' => 'Test', 'body' => 'Body', 'accent_color' => '#059669',
        'is_active' => true, 'show_in_feed' => true, 'review_status' => LandingAd::ReviewApproved,
    ]);

    $service = app(FeedCompositionService::class);
    $batch = $service->compose(null, MembershipAccess::LevelRegular, [], []);
    $types = collect($batch['cards'])->pluck('type');

    expect($types->filter(fn ($t) => $t === 'featured'))->toHaveCount(1)
        ->and($types->filter(fn ($t) => $t === 'sponsored'))->toHaveCount(1)
        ->and($types->filter(fn ($t) => $t === 'advertisement'))->toHaveCount(1);
});

test('a missing promotion type is filled with an organic listing instead of an empty slot', function () {
    feedListings(20);

    $service = app(FeedCompositionService::class);
    $batch = $service->compose(null, MembershipAccess::LevelRegular, [], []);

    expect($batch['cards'])->toHaveCount(12);
    expect(collect($batch['cards'])->pluck('type')->unique()->all())->toBe(['organic']);
});

test('load more never repeats a listing already delivered in the loaded feed', function () {
    feedListings(30);

    $service = app(FeedCompositionService::class);
    $first = $service->compose(null, MembershipAccess::LevelRegular, [], []);
    $second = $service->compose(null, MembershipAccess::LevelRegular, $first['shown_listing_ids'], $first['shown_ad_ids']);

    $firstIds = collect($first['cards'])->pluck('listing')->filter()->pluck('id');
    $secondIds = collect($second['cards'])->pluck('listing')->filter()->pluck('id');

    expect($firstIds->intersect($secondIds))->toBeEmpty();
});

test('expired and pending listings are excluded from every batch', function () {
    $expired = feedListing(['expires_at' => now()->subDay()]);
    $pending = feedListing(['status' => Listing::StatusPending, 'approved_at' => null]);
    feedListings(15);

    $service = app(FeedCompositionService::class);
    $batch = $service->compose(null, MembershipAccess::LevelRegular, [], []);

    $ids = collect($batch['cards'])->pluck('listing')->filter()->pluck('id');

    expect($ids)->not->toContain($expired->id)
        ->not->toContain($pending->id);
});

test('unauthorized silver and gold content never leaks into a regular viewer feed', function () {
    feedListings(15);
    feedListings(5, ['visibility_level' => Listing::VisibilitySilverExclusive, 'marketplace_tier' => Listing::TierSilver]);
    feedListings(5, ['visibility_level' => Listing::VisibilityGoldExclusive, 'marketplace_tier' => Listing::TierGold]);

    $service = app(FeedCompositionService::class);
    $restrictedIds = Listing::query()->whereIn('visibility_level', [Listing::VisibilitySilverExclusive, Listing::VisibilityGoldExclusive])->pluck('id');

    $shown = [];

    for ($i = 0; $i < 3; $i++) {
        $batch = $service->compose(null, MembershipAccess::LevelRegular, $shown, []);
        $shown = $batch['shown_listing_ids'];

        $ids = collect($batch['cards'])->pluck('listing')->filter()->pluck('id');
        expect($ids->intersect($restrictedIds))->toBeEmpty();

        if (! $batch['has_more']) {
            break;
        }
    }
});

test('invitation only listings never appear in the feed even for a gold buyer', function () {
    feedListings(15);
    feedListing(['visibility_level' => Listing::VisibilityInvitationOnly, 'marketplace_tier' => Listing::TierGold]);

    $goldBuyer = feedBuyerWithAccess(MembershipAccess::LevelGold);
    $service = app(FeedCompositionService::class);
    $batch = $service->compose($goldBuyer, MembershipAccess::LevelGold, [], []);

    $ids = collect($batch['cards'])->pluck('listing')->filter()->pluck('id');
    $invitationOnlyIds = Listing::query()->where('visibility_level', Listing::VisibilityInvitationOnly)->pluck('id');

    expect($ids->intersect($invitationOnlyIds))->toBeEmpty();
});

test('a tampered cursor cannot be used to bypass authorization, only to reset exclusions', function () {
    feedListings(15);
    feedListings(5, ['visibility_level' => Listing::VisibilitySilverExclusive, 'marketplace_tier' => Listing::TierSilver]);

    $forgedCursor = base64_encode(json_encode(['v' => 1, 'mode' => 'gold', 'listings' => [], 'ads' => []])).'.forged-signature';

    $response = $this->getJson('/feed/listings?cursor='.urlencode($forgedCursor));

    $response->assertOk();
    $ids = collect($response->json('cards'))->pluck('listing.id')->filter();
    $silverIds = Listing::query()->where('visibility_level', Listing::VisibilitySilverExclusive)->pluck('id');

    expect($ids->intersect($silverIds))->toBeEmpty();
});

// --- Load More endpoint ----------------------------------------------------

test('load 12 more appends the next unique batch via the HTTP endpoint', function () {
    feedListings(30);

    $first = $this->getJson('/feed/listings')->assertOk();
    $cursor = $first->json('cursor');
    $firstIds = collect($first->json('cards'))->pluck('listing.id')->filter();

    $second = $this->getJson('/feed/listings?cursor='.urlencode($cursor))->assertOk();
    $secondIds = collect($second->json('cards'))->pluck('listing.id')->filter();

    expect($firstIds->intersect($secondIds))->toBeEmpty();
});

test('fewer than 12 remaining eligible items are all appended and then has_more is false', function () {
    feedListings(14);

    $first = $this->getJson('/feed/listings')->assertOk();
    expect($first->json('has_more'))->toBeTrue();

    $second = $this->getJson('/feed/listings?cursor='.urlencode($first->json('cursor')))->assertOk();

    expect(count($second->json('cards')))->toBeLessThan(12)
        ->and($second->json('has_more'))->toBeFalse();
});

test('the feed endpoint returns an empty, terminal batch once every eligible listing has been shown', function () {
    feedListings(3);

    $first = $this->getJson('/feed/listings')->assertOk();
    expect($first->json('has_more'))->toBeFalse()
        ->and(count($first->json('cards')))->toBe(3);
});

// --- Advertisement safety ---------------------------------------------------

test('an unsafe advertisement destination url is rejected', function () {
    $admin = User::factory()->create();
    $admin->roles()->attach(Role::query()->where('name', 'admin')->firstOrFail());

    $this->actingAs($admin)
        ->post('/adm/landing-ads', [
            'title' => 'Malicious Ad',
            'category' => 'Test',
            'body' => 'Body',
            'cta_url' => 'javascript:alert(1)',
            'accent_color' => '#059669',
            'sort_order' => 0,
            'is_active' => true,
            'review_status' => LandingAd::ReviewApproved,
        ])
        ->assertSessionHasErrors('cta_url');

    expect(LandingAd::query()->where('title', 'Malicious Ad')->exists())->toBeFalse();
});

test('an advertisement click only redirects to its own configured destination', function () {
    $ad = LandingAd::query()->create([
        'title' => 'Safe Ad', 'category' => 'Test', 'body' => 'Body',
        'cta_url' => 'https://example.test/offer', 'accent_color' => '#059669',
        'is_active' => true, 'show_in_feed' => true, 'review_status' => LandingAd::ReviewApproved,
    ]);

    $this->get("/feed/ads/{$ad->id}/click?to=".urlencode('https://attacker.test/phish'))
        ->assertNotFound();

    $this->get("/feed/ads/{$ad->id}/click?to=".urlencode('https://example.test/offer'))
        ->assertRedirect('https://example.test/offer');

    expect($ad->fresh()->clicks_count)->toBe(1);
});

test('the feed cursor codec round-trips and rejects a mismatched signature', function () {
    $encoded = FeedCursor::encode('regular', [1, 2, 3], [9]);
    $decoded = FeedCursor::decode($encoded);

    expect($decoded['listings'])->toBe([1, 2, 3])
        ->and($decoded['ads'])->toBe([9]);

    $tampered = FeedCursor::decode(substr($encoded, 0, -1).'x');
    expect($tampered['listings'])->toBe([]);
});
