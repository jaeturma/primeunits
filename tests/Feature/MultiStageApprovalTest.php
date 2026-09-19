<?php

use App\Models\Category;
use App\Models\Listing;
use App\Models\RentalUnit;
use App\Models\Role;
use App\Models\SellerProfile;
use App\Models\User;
use App\Services\ApprovalWorkflowService;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed([RbacSeeder::class, CategorySeeder::class]);
});

function reviewer(string $role): User
{
    $user = User::factory()->create();
    $user->roles()->attach(Role::query()->where('name', $role)->firstOrFail());
    return $user;
}

test('sale listings require agent manager and admin approval in order', function () {
    $seller = reviewer('seller');
    $profile = SellerProfile::query()->create(['user_id' => $seller->id, 'seller_type' => 'individual', 'contact_number' => '0917', 'status' => SellerProfile::StatusVerified]);
    $listing = Listing::query()->create(['user_id' => $seller->id, 'seller_profile_id' => $profile->id, 'category_id' => Category::query()->firstOrFail()->id, 'title' => 'Staged unit', 'price' => 100, 'condition' => Listing::ConditionUsed, 'status' => Listing::StatusPending]);
    $workflow = app(ApprovalWorkflowService::class);

    expect(fn () => $workflow->advance($listing, reviewer('superadmin')))->toThrow(Exception::class);
    $workflow->advance($listing, $agent = reviewer('coordinator'));
    expect($listing->status)->toBe(Listing::StatusAgentValidated)->and($listing->agent_validated_by)->toBe($agent->id);
    $workflow->advance($listing, $manager = reviewer('manager'));
    expect($listing->status)->toBe(Listing::StatusManagerAccepted)->and($listing->manager_accepted_by)->toBe($manager->id);
    $workflow->advance($listing, $admin = reviewer('admin'));
    expect($listing->status)->toBe(Listing::StatusApproved)->and($listing->approved_by)->toBe($admin->id);
});

test('rental units use the same three stage publication workflow', function () {
    $provider = reviewer('rental_provider');
    $unit = RentalUnit::query()->create(['user_id' => $provider->id, 'rental_type' => RentalUnit::TypeCarRental, 'name' => 'Rental car', 'price_per_day' => 1000, 'status' => RentalUnit::StatusPending, 'valid_id_file' => 'identity/id.pdf', 'or_cr_file' => 'identity/or-cr.pdf']);
    $workflow = app(ApprovalWorkflowService::class);

    $workflow->advance($unit, reviewer('coordinator'));
    $workflow->advance($unit, reviewer('manager'));
    $workflow->advance($unit, reviewer('superadmin'));

    expect($unit->status)->toBe(RentalUnit::StatusApproved)->and($unit->approved_at)->not->toBeNull();
});
