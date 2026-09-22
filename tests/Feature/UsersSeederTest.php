<?php

use App\Models\Category;
use App\Models\Listing;
use App\Models\Municipality;
use App\Models\Province;
use App\Models\SellerProfile;
use App\Models\User;
use Database\Seeders\UsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('users seeder creates demo users with roles and seller profiles', function () {
    $this->seed(UsersSeeder::class);

    $admin = User::query()->where('email', 'admin@prime.test')->firstOrFail();
    $insuranceManager = User::query()->where('email', 'insurance@prime.test')->firstOrFail();
    $seller = User::query()->where('email', 'seller.cebu@prime.test')->firstOrFail();
    $buyer = User::query()->where('email', 'buyer.miguel@prime.test')->firstOrFail();

    // 9 base demo users + 6 financing-partner users created by the
    // DemoSeeder that UsersSeeder chains in.
    expect(User::query()->count())->toBe(15)
        ->and(Hash::check('super123', $admin->password))->toBeTrue()
        ->and($admin->hasRole('superadmin'))->toBeTrue()
        ->and($insuranceManager->hasRole('insurance_manager'))->toBeTrue()
        ->and($insuranceManager->hasPermission('manage_insurance'))->toBeTrue()
        ->and($seller->hasRole('seller'))->toBeTrue()
        ->and($buyer->hasRole('buyer'))->toBeTrue()
        ->and(SellerProfile::query()->where('status', SellerProfile::StatusVerified)->count())->toBe(2)
        ->and(SellerProfile::query()->where('status', SellerProfile::StatusPending)->count())->toBe(1)
        ->and($seller->notificationPreference()->exists())->toBeTrue();
});

test('users seeder prepares marketplace dropdowns and sample listings', function () {
    $this->seed(UsersSeeder::class);

    expect(Category::query()->where('is_active', true)->count())->toBeGreaterThanOrEqual(7)
        ->and(Category::query()->where('slug', 'cars')->exists())->toBeTrue()
        ->and(Province::query()->where('name', 'Cebu')->exists())->toBeTrue()
        ->and(Municipality::query()->where('name', 'City of Mandaue')->exists())->toBeTrue()
        ->and(Listing::query()->where('status', Listing::StatusApproved)->count())->toBeGreaterThanOrEqual(50)
        ->and(Listing::query()->where('province', 'Cebu')->where('municipality', 'City of Mandaue')->exists())->toBeTrue();
});
