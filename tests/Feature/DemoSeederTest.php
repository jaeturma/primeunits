<?php

use App\Models\CommissionLog;
use App\Models\Lead;
use App\Models\Listing;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\SellerProfile;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('database seeder creates a usable primeunits demo snapshot', function () {
    $this->seed(DatabaseSeeder::class);

    $admin = User::query()->where('email', 'admin@prime.test')->firstOrFail();
    $seller = User::query()->where('email', 'seller.cebu@prime.test')->firstOrFail();
    $buyer = User::query()->where('email', 'buyer.miguel@prime.test')->firstOrFail();

    expect($admin->hasRole('superadmin'))->toBeTrue()
        ->and($seller->hasRole('seller'))->toBeTrue()
        ->and($buyer->hasRole('buyer'))->toBeTrue()
        ->and(SellerProfile::query()->where('status', SellerProfile::StatusVerified)->count())->toBe(2)
        ->and(Listing::query()->where('status', Listing::StatusApproved)->count())->toBe(112)
        ->and(Lead::query()->count())->toBe(4)
        ->and(Transaction::query()->where('status', Transaction::StatusConfirmed)->count())->toBe(2)
        ->and(CommissionLog::query()->count())->toBe(2)
        ->and(Payment::query()->count())->toBe(3)
        ->and(Plan::query()->where('is_active', true)->count())->toBe(2)
        ->and(DB::table('notifications')->count())->toBe(4);
});
