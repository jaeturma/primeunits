<?php

use App\Models\MembershipAccess;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\PrimeUnitsNotification;
use App\Services\MembershipAccessService;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

function expirySubscriber(string $buyerLevel, string $subscriptionStatus, ?CarbonInterface $endsAt, ?CarbonInterface $graceEndsAt = null): array
{
    $user = User::factory()->create();

    MembershipAccess::query()->create([
        'user_id' => $user->id,
        'buyer_access_level' => $buyerLevel,
        'buyer_access_status' => MembershipAccess::StatusActive,
    ]);

    $plan = Plan::query()->firstOrCreate(
        ['type' => Plan::TypeMembership, 'tier' => $buyerLevel],
        ['name' => 'PrimeUnits '.ucfirst($buyerLevel), 'price' => 1999, 'duration_days' => 365, 'is_active' => true],
    );

    $subscription = Subscription::query()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'starts_at' => now()->subDays(400),
        'ends_at' => $endsAt,
        'grace_ends_at' => $graceEndsAt,
        'status' => $subscriptionStatus,
    ]);

    return [$user, $subscription];
}

test('a subscription with a future end date is left untouched', function () {
    [$user, $subscription] = expirySubscriber(MembershipAccess::LevelSilver, Subscription::StatusActive, now()->addDays(10));

    $counts = app(MembershipAccessService::class)->syncExpiredMemberships();

    expect($counts)->toBe(['grace' => 0, 'expired' => 0])
        ->and($subscription->fresh()->status)->toBe(Subscription::StatusActive)
        ->and($user->fresh()->membershipAccess->buyer_access_status)->toBe(MembershipAccess::StatusActive);
});

test('a lapsed subscription still inside its grace period moves to grace but keeps buyer access', function () {
    [$user, $subscription] = expirySubscriber(
        MembershipAccess::LevelGold,
        Subscription::StatusActive,
        now()->subDay(),
        now()->addDays(5),
    );

    $counts = app(MembershipAccessService::class)->syncExpiredMemberships();

    expect($counts)->toBe(['grace' => 1, 'expired' => 0])
        ->and($subscription->fresh()->status)->toBe(Subscription::StatusGracePeriod)
        ->and($user->fresh()->membershipAccess->buyer_access_status)->toBe(MembershipAccess::StatusActive)
        ->and($user->fresh()->membershipAccess->hasBuyerAccessAtLeast(MembershipAccess::LevelGold))->toBeTrue();
});

test('a subscription past its grace period expires and downgrades buyer access', function () {
    [$user, $subscription] = expirySubscriber(
        MembershipAccess::LevelSilver,
        Subscription::StatusGracePeriod,
        now()->subDays(10),
        now()->subDay(),
    );

    $counts = app(MembershipAccessService::class)->syncExpiredMemberships();

    expect($counts)->toBe(['grace' => 0, 'expired' => 1])
        ->and($subscription->fresh()->status)->toBe(Subscription::StatusExpired)
        ->and($user->fresh()->membershipAccess->buyer_access_status)->toBe(MembershipAccess::StatusExpired)
        ->and($user->fresh()->membershipAccess->hasBuyerAccessAtLeast(MembershipAccess::LevelSilver))->toBeFalse()
        ->and($user->fresh()->membershipAccess->effectiveMode())->toBe(MembershipAccess::LevelRegular);
});

test('an expired member is notified their membership lapsed', function () {
    Notification::fake();

    [$user] = expirySubscriber(MembershipAccess::LevelGold, Subscription::StatusActive, now()->subDay());

    app(MembershipAccessService::class)->syncExpiredMemberships();

    Notification::assertSentTo(
        $user,
        PrimeUnitsNotification::class,
        fn (PrimeUnitsNotification $notification): bool => $notification->event === 'membership.expired',
    );
});

test('a subscription with no grace period at all expires immediately once ends_at passes', function () {
    [$user, $subscription] = expirySubscriber(MembershipAccess::LevelGold, Subscription::StatusActive, now()->subMinute());

    app(MembershipAccessService::class)->syncExpiredMemberships();

    expect($subscription->fresh()->status)->toBe(Subscription::StatusExpired)
        ->and($user->fresh()->membershipAccess->buyer_access_status)->toBe(MembershipAccess::StatusExpired);
});

test('buyer access is not touched if an admin already changed it away from the lapsed plan tier', function () {
    [$user, $subscription] = expirySubscriber(MembershipAccess::LevelSilver, Subscription::StatusActive, now()->subDay());

    // An admin independently upgraded this member to Gold before the old
    // Silver subscription's sync ran — the sync must not clobber that.
    $user->membershipAccess->update(['buyer_access_level' => MembershipAccess::LevelGold]);

    app(MembershipAccessService::class)->syncExpiredMemberships();

    expect($user->fresh()->membershipAccess->buyer_access_level)->toBe(MembershipAccess::LevelGold)
        ->and($user->fresh()->membershipAccess->buyer_access_status)->toBe(MembershipAccess::StatusActive);
});

test('regular tier subscriptions are never expired by the sync', function () {
    [$user, $subscription] = expirySubscriber(MembershipAccess::LevelRegular, Subscription::StatusActive, now()->subDay());

    app(MembershipAccessService::class)->syncExpiredMemberships();

    expect($subscription->fresh()->status)->toBe(Subscription::StatusActive)
        ->and($user->fresh()->membershipAccess->buyer_access_status)->toBe(MembershipAccess::StatusActive);
});

test('running the sync twice is idempotent and does not error or double count', function () {
    expirySubscriber(MembershipAccess::LevelGold, Subscription::StatusActive, now()->subDay());

    $service = app(MembershipAccessService::class);
    $first = $service->syncExpiredMemberships();
    $second = $service->syncExpiredMemberships();

    expect($first['expired'])->toBe(1)
        ->and($second['expired'])->toBe(0);
});

test('the scheduled command reports what it did', function () {
    expirySubscriber(MembershipAccess::LevelSilver, Subscription::StatusActive, now()->subDay());

    $this->artisan('membership:sync-access-expiry')
        ->assertSuccessful()
        ->expectsOutputToContain('expired 1 subscription(s)');
});
