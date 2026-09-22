<?php

use App\Models\Role;
use App\Models\SellerProfile;
use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RbacSeeder::class);
});

function assignRole(User $user, string $role): void
{
    $user->roles()->attach(Role::query()->where('name', $role)->firstOrFail());
}

test('only seller role users can access seller application form', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson(route('seller.apply'))
        ->assertForbidden();

    assignRole($user, 'seller');

    $this->actingAs($user->fresh())
        ->get(route('seller.apply'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('seller/apply')
            ->where('sellerTypes.0.value', 'individual'),
        );
});

test('seller can submit an application with files', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    assignRole($user, 'seller');

    $this->actingAs($user)
        ->post(route('seller.store'), [
            'seller_type' => 'business',
            'business_name' => 'Prime Homes',
            'contact_number' => '09171234567',
            'valid_id_file' => UploadedFile::fake()->image('id.jpg'),
            'permit_file' => UploadedFile::fake()->create('permit.pdf', 100, 'application/pdf'),
        ])
        ->assertRedirect(route('seller.status'));

    $profile = SellerProfile::query()->firstOrFail();

    expect($profile->status)->toBe(SellerProfile::StatusPending)
        ->and($profile->business_name)->toBe('Prime Homes')
        ->and($profile->valid_id_file)->not->toBeNull()
        ->and($profile->permit_file)->not->toBeNull();

    // Government ID and permit are identity/ownership documents — they must
    // never land on the public disk, matching the private-storage rule
    // already enforced for membership application and drone credential
    // documents (see SecureDocumentController).
    Storage::disk('local')->assertExists($profile->valid_id_file);
    Storage::disk('local')->assertExists($profile->permit_file);
    Storage::disk('public')->assertMissing($profile->valid_id_file);
    Storage::disk('public')->assertMissing($profile->permit_file);
});

test('rejected seller can edit and resubmit application', function () {
    $user = User::factory()->create();
    assignRole($user, 'seller');

    $profile = SellerProfile::query()->create([
        'user_id' => $user->id,
        'seller_type' => 'individual',
        'contact_number' => '09170000000',
        'status' => SellerProfile::StatusRejected,
        'rejected_reason' => 'Missing valid ID.',
    ]);

    $this->actingAs($user)
        ->put(route('seller.update'), [
            'seller_type' => 'individual',
            'owner_name' => 'Updated Seller',
            'contact_number' => '09179999999',
        ])
        ->assertRedirect(route('seller.status'));

    expect($profile->refresh()->status)->toBe(SellerProfile::StatusPending)
        ->and($profile->owner_name)->toBe('Updated Seller')
        ->and($profile->rejected_reason)->toBeNull();
});

test('admin can approve and reject seller applications', function () {
    $admin = User::factory()->create();
    assignRole($admin, 'superadmin');

    $seller = User::factory()->create();
    assignRole($seller, 'seller');

    $profile = SellerProfile::query()->create([
        'user_id' => $seller->id,
        'seller_type' => 'individual',
        'contact_number' => '09170000000',
        'status' => SellerProfile::StatusPending,
    ]);

    $this->actingAs($admin)
        ->get(route('adm.sellers.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('adm/sellers/index')
            ->has('profiles', 1)
            ->where('profiles.0.status_label', 'Under review'),
        );

    $this->actingAs($admin)
        ->post(route('adm.sellers.approve', $profile))
        ->assertRedirect();

    expect($profile->refresh()->status)->toBe(SellerProfile::StatusVerified)
        ->and($profile->verified_at)->not->toBeNull();

    $this->actingAs($admin)
        ->post(route('adm.sellers.reject', $profile), [
            'rejected_reason' => 'Documents do not match.',
        ])
        ->assertRedirect();

    expect($profile->refresh()->status)->toBe(SellerProfile::StatusRejected)
        ->and($profile->rejected_reason)->toBe('Documents do not match.')
        ->and($profile->verified_at)->toBeNull();
});

test('listing creation requires a verified seller profile', function () {
    $seller = User::factory()->create();
    assignRole($seller, 'seller');

    SellerProfile::query()->create([
        'user_id' => $seller->id,
        'seller_type' => 'individual',
        'contact_number' => '09170000000',
        'status' => SellerProfile::StatusPending,
    ]);

    $this->actingAs($seller)
        ->getJson(route('seller.listings.create'))
        ->assertForbidden();

    $seller->sellerProfile()->update([
        'status' => SellerProfile::StatusVerified,
        'verified_at' => now(),
    ]);

    $this->actingAs($seller->fresh())
        ->get(route('seller.listings.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('seller/listings/create'),
        );
});
