<?php

use App\Models\Category;
use App\Models\CommissionLog;
use App\Models\Lead;
use App\Models\Listing;
use App\Models\Role;
use App\Models\SellerProfile;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RbacSeeder::class);
    $this->seed(CategorySeeder::class);
});

function leadRole(User $user, string $role): void
{
    $user->roles()->attach(Role::query()->where('name', $role)->firstOrFail());
}

function leadSeller(): User
{
    $seller = User::factory()->create();
    leadRole($seller, 'seller');

    SellerProfile::query()->create([
        'user_id' => $seller->id,
        'seller_type' => 'individual',
        'contact_number' => '09170000000',
        'status' => SellerProfile::StatusVerified,
        'verified_at' => now(),
    ]);

    return $seller->fresh();
}

function approvedLeadListing(User $seller): Listing
{
    return Listing::query()->create([
        'user_id' => $seller->id,
        'seller_profile_id' => $seller->sellerProfile->id,
        'category_id' => Category::query()->firstOrFail()->id,
        'title' => 'Approved Unit',
        'price' => '900000',
        'condition' => Listing::ConditionUsed,
        'status' => Listing::StatusApproved,
        'approved_at' => now(),
    ]);
}

test('buyer inquiry creates a reference lead and appends follow up messages', function () {
    $seller = leadSeller();
    $buyer = User::factory()->create();
    leadRole($buyer, 'buyer');
    $listing = approvedLeadListing($seller);

    $this->actingAs($buyer)
        ->post(route('leads.store'), [
            'listing_id' => $listing->id,
            'message' => 'Is this available?',
        ])
        ->assertRedirect()
        ->assertSessionHas('inquiry');

    $lead = Lead::query()->firstOrFail();

    expect($lead->reference_code)->toMatch('/^PU-[A-Z0-9]{7}$/')
        ->and($lead->seller_id)->toBe($seller->id)
        ->and($lead->buyer_id)->toBe($buyer->id)
        ->and($lead->messages)->toHaveCount(1);

    $this->actingAs($buyer)
        ->post(route('leads.store'), [
            'listing_id' => $listing->id,
            'message' => 'Can I inspect tomorrow?',
        ])
        ->assertRedirect();

    expect(Lead::query()->count())->toBe(1)
        ->and($lead->refresh()->messages)->toHaveCount(2)
        ->and($lead->message)->toBe('Can I inspect tomorrow?');
});

test('seller updates lead status and creates transaction with commission', function () {
    $seller = leadSeller();
    $buyer = User::factory()->create();
    leadRole($buyer, 'buyer');
    $listing = approvedLeadListing($seller);

    $lead = Lead::query()->create([
        'listing_id' => $listing->id,
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
        'reference_code' => Lead::generateReferenceCode(),
        'status' => Lead::StatusInquiry,
    ]);

    $this->actingAs($seller)
        ->patch(route('seller.leads.status', $lead), [
            'status' => Lead::StatusNegotiating,
        ])
        ->assertRedirect();

    expect($lead->refresh()->status)->toBe(Lead::StatusNegotiating)
        ->and($lead->negotiated_at)->not->toBeNull();

    $this->actingAs($seller)
        ->post(route('seller.leads.transactions.store', $lead), [
            'agreed_price' => '850000',
        ])
        ->assertRedirect();

    $transaction = Transaction::query()->firstOrFail();

    expect($transaction->commission_rate)->toBe('2.00')
        ->and($transaction->commission_amount)->toBe('17000.00')
        ->and($lead->refresh()->status)->toBe(Lead::StatusClosed);
});

test('buyer and seller confirmation records commission log', function () {
    $seller = leadSeller();
    $buyer = User::factory()->create();
    leadRole($buyer, 'buyer');
    $listing = approvedLeadListing($seller);

    $lead = Lead::query()->create([
        'listing_id' => $listing->id,
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
        'reference_code' => Lead::generateReferenceCode(),
        'status' => Lead::StatusClosed,
    ]);

    $transaction = Transaction::query()->create([
        'lead_id' => $lead->id,
        'listing_id' => $listing->id,
        'agreed_price' => '500000',
        'commission_rate' => '2.00',
        'commission_amount' => '10000',
        'status' => Transaction::StatusPending,
    ]);

    $this->actingAs($buyer)
        ->post(route('transactions.confirm-buyer', $transaction))
        ->assertRedirect();

    expect($transaction->refresh()->status)->toBe(Transaction::StatusPending)
        ->and($transaction->commissionLog)->toBeNull();

    $this->actingAs($seller)
        ->post(route('transactions.confirm-seller', $transaction))
        ->assertRedirect();

    expect($transaction->refresh()->status)->toBe(Transaction::StatusConfirmed)
        ->and($transaction->commissionLog)->not->toBeNull()
        ->and($transaction->commissionLog->status)->toBe(CommissionLog::StatusUnpaid);
});

test('transaction proof upload and admin paid marking work', function () {
    Storage::fake('public');

    $seller = leadSeller();
    $buyer = User::factory()->create();
    leadRole($buyer, 'buyer');
    $admin = User::factory()->create();
    leadRole($admin, 'superadmin');
    $listing = approvedLeadListing($seller);

    $lead = Lead::query()->create([
        'listing_id' => $listing->id,
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
        'reference_code' => Lead::generateReferenceCode(),
        'status' => Lead::StatusClosed,
    ]);

    $transaction = Transaction::query()->create([
        'lead_id' => $lead->id,
        'listing_id' => $listing->id,
        'agreed_price' => '500000',
        'commission_rate' => '2.00',
        'commission_amount' => '10000',
        'status' => Transaction::StatusConfirmed,
        'buyer_confirmed' => true,
        'seller_confirmed' => true,
        'confirmed_at' => now(),
    ]);
    $transaction->commissionLog()->create([
        'amount' => $transaction->commission_amount,
        'status' => CommissionLog::StatusUnpaid,
    ]);

    $this->actingAs($buyer)
        ->post(route('transactions.proof', $transaction), [
            'proof_file' => UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf'),
        ])
        ->assertRedirect();

    Storage::disk('public')->assertExists($transaction->refresh()->proof_file);

    $this->actingAs($admin)
        ->post(route('adm.transactions.mark-paid', $transaction))
        ->assertRedirect();

    expect($transaction->commissionLog->refresh()->status)->toBe(CommissionLog::StatusPaid);
});
