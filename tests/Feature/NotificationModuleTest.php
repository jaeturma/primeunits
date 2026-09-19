<?php

use App\Models\Category;
use App\Models\Lead;
use App\Models\Listing;
use App\Models\Payment;
use App\Models\Role;
use App\Models\SellerProfile;
use App\Models\User;
use App\Notifications\PrimeUnitsNotification;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RbacSeeder::class);
    $this->seed(CategorySeeder::class);
});

function notificationRole(User $user, string $role): void
{
    $user->roles()->attach(Role::query()->where('name', $role)->firstOrFail());
}

function notificationSeller(): User
{
    $seller = User::factory()->create();
    notificationRole($seller, 'seller');

    SellerProfile::query()->create([
        'user_id' => $seller->id,
        'seller_type' => 'individual',
        'contact_number' => '09170000000',
        'status' => SellerProfile::StatusVerified,
        'verified_at' => now(),
    ]);

    $seller->notificationPreferenceOrDefault()->update([
        'email_enabled' => false,
    ]);

    return $seller->fresh();
}

function notificationListing(User $seller): Listing
{
    return Listing::query()->create([
        'user_id' => $seller->id,
        'seller_profile_id' => $seller->sellerProfile->id,
        'category_id' => Category::query()->firstOrFail()->id,
        'title' => 'Notification Unit',
        'price' => '500000',
        'condition' => Listing::ConditionUsed,
        'status' => Listing::StatusApproved,
        'approved_at' => now(),
    ]);
}

test('new inquiry notifies the seller and creates unread shared count', function () {
    $seller = notificationSeller();
    $buyer = User::factory()->create();
    notificationRole($buyer, 'buyer');
    $listing = notificationListing($seller);

    $this->actingAs($buyer)
        ->post(route('leads.store'), [
            'listing_id' => $listing->id,
            'message' => 'Interested.',
        ])
        ->assertRedirect();

    expect($seller->notifications()->count())->toBe(1)
        ->and($seller->unreadNotifications()->count())->toBe(1);

    $this->actingAs($seller)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('notifications/index')
            ->where('notifications.data.0.event', 'lead.created')
            ->where('notifications.data.0.title', 'New inquiry')
            ->where('notificationSummary.unread_count', 1),
        );
});

test('notification preferences control delivery channels', function () {
    Notification::fake();

    $user = User::factory()->create();
    $user->notificationPreferenceOrDefault()->update([
        'database_enabled' => false,
        'email_enabled' => true,
    ]);

    $user->notify(new PrimeUnitsNotification(
        event: 'test.event',
        title: 'Preference test',
        message: 'Testing preferences.',
        url: '/notifications',
    ));

    Notification::assertSentTo($user, PrimeUnitsNotification::class, function (PrimeUnitsNotification $notification, array $channels) {
        return $channels === ['mail'];
    });
});

test('user can update preferences and mark notifications read', function () {
    $user = User::factory()->create();
    $user->notificationPreferenceOrDefault()->update([
        'email_enabled' => false,
    ]);

    $user->notify(new PrimeUnitsNotification(
        event: 'test.event',
        title: 'Read test',
        message: 'Mark me read.',
    ));

    $notification = $user->notifications()->firstOrFail();

    $this->actingAs($user)
        ->put(route('notifications.preferences'), [
            'database_enabled' => true,
            'email_enabled' => false,
            'sms_enabled' => true,
        ])
        ->assertRedirect();

    expect($user->notificationPreferenceOrDefault()->refresh()->email_enabled)->toBeFalse()
        ->and($user->notificationPreferenceOrDefault()->sms_enabled)->toBeTrue();

    $this->actingAs($user)
        ->patch(route('notifications.read', $notification->id))
        ->assertRedirect();

    expect($notification->refresh()->read_at)->not->toBeNull();
});

test('listing and payment admin updates notify affected users', function () {
    $seller = notificationSeller();
    $admin = User::factory()->create();
    notificationRole($admin, 'superadmin');
    $listing = notificationListing($seller);

    $this->actingAs($admin)
        ->post(route('adm.listings.reject', $listing), [
            'rejected_reason' => 'Needs clearer photos.',
        ])
        ->assertRedirect();

    expect($seller->notifications()->where('data->event', 'listing.rejected')->exists())->toBeTrue();

    $payment = Payment::query()->create([
        'user_id' => $seller->id,
        'payable_type' => SellerProfile::class,
        'payable_id' => $seller->sellerProfile->id,
        'amount' => '100',
        'method' => Payment::MethodGcash,
        'status' => Payment::StatusPending,
    ]);

    $this->actingAs($admin)
        ->post(route('adm.payments.reject', $payment))
        ->assertRedirect();

    expect($seller->notifications()->where('data->event', 'payment.rejected')->exists())->toBeTrue();
});
