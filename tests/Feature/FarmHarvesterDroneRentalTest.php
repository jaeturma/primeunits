<?php

use App\Models\Category;
use App\Models\DronePilotCredential;
use App\Models\Listing;
use App\Models\RentalBooking;
use App\Models\RentalPackage;
use App\Models\RentalUnit;
use App\Models\Role;
use App\Models\SellerProfile;
use App\Models\User;
use App\Services\RentalPricingService;
use Database\Seeders\CategorySeeder;
use Database\Seeders\PrimeUnitsFarmMarketplaceDemoSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->withoutMiddleware(PreventRequestForgery::class);
    $this->seed(RbacSeeder::class);
    $this->seed(CategorySeeder::class);
});

function farmRole(User $user, string $role): void
{
    $user->roles()->attach(Role::query()->where('name', $role)->firstOrFail());
}

function rentalProvider(): User
{
    $user = User::factory()->create();
    farmRole($user, 'rental_provider');

    \App\Models\RentalProfile::query()->create([
        'user_id' => $user->id,
        'business_name' => 'Test Farm Provider',
        'slug' => 'test-farm-provider-'.$user->id,
        'contact_number' => '09170000000',
        'business_address' => 'Test Address',
        'business_registration_file' => 'demo/x.jpg',
        'valid_id_file' => 'demo/y.jpg',
        'status' => \App\Models\RentalProfile::StatusApproved,
        'approved_at' => now(),
    ]);

    return $user->fresh();
}

function farmCategory(): Category
{
    return Category::query()->where('slug', 'agricultural-equipment')->firstOrFail();
}

function droneUnit(User $provider, array $overrides = []): RentalUnit
{
    return RentalUnit::query()->create([
        'user_id' => $provider->id,
        'category_id' => farmCategory()->id,
        'rental_type' => RentalUnit::TypeDroneRental,
        'name' => 'Test Drone '.uniqid(),
        'price_per_day' => 5000,
        'operator_included' => false,
        'transportation_included' => false,
        'requires_verified_drone_operator' => true,
        'allows_self_operation' => true,
        'status' => RentalUnit::StatusApproved,
        'approved_at' => now(),
        ...$overrides,
    ]);
}

function verifiedCredentialFor(User $user): DronePilotCredential
{
    return DronePilotCredential::query()->create([
        'user_id' => $user->id,
        'credential_type' => 'Remote Pilot Certificate',
        'credential_number' => 'TEST-VERIFIED-'.$user->id,
        'issue_date' => now()->subMonths(6),
        'expiration_date' => now()->addYear(),
        'status' => DronePilotCredential::StatusVerified,
    ]);
}

// --- Category / demo seeder idempotency -------------------------------

test('category seeder is idempotent', function () {
    $before = Category::query()->count();
    $beforeFields = Category::query()->where('slug', 'agricultural-equipment')->firstOrFail()->specFields()->count();

    $this->seed(CategorySeeder::class);

    expect(Category::query()->count())->toBe($before)
        ->and(Category::query()->where('slug', 'agricultural-equipment')->firstOrFail()->specFields()->count())->toBe($beforeFields)
        ->and(farmCategory()->specFields()->where('name', 'equipment_type')->firstOrFail()->options)
        ->toContain('Rice Combine Harvester', 'Agricultural Drone');
});

test('farm marketplace demo seeder runs on a clean database and is idempotent when rerun', function () {
    $this->seed(PrimeUnitsFarmMarketplaceDemoSeeder::class);

    $farmUnitCount = RentalUnit::query()->whereIn('rental_type', [
        RentalUnit::TypeHarvesterRental, RentalUnit::TypeHarvesterService,
        RentalUnit::TypeDroneRental, RentalUnit::TypeDroneService,
    ])->count();
    $credentialCount = DronePilotCredential::query()->count();
    $bookingCount = RentalBooking::query()->where('reference_code', 'like', 'RB-DEMO-%')->count();

    expect($farmUnitCount)->toBe(6)
        ->and($credentialCount)->toBe(3)
        ->and($bookingCount)->toBe(3)
        ->and(User::query()->where('email', 'pilot.verified@primeunits.test')->exists())->toBeTrue()
        ->and(User::query()->where('email', 'farmer.demo@primeunits.test')->exists())->toBeTrue();

    $this->seed(PrimeUnitsFarmMarketplaceDemoSeeder::class);

    expect(RentalUnit::query()->whereIn('rental_type', [
        RentalUnit::TypeHarvesterRental, RentalUnit::TypeHarvesterService,
        RentalUnit::TypeDroneRental, RentalUnit::TypeDroneService,
    ])->count())->toBe($farmUnitCount)
        ->and(DronePilotCredential::query()->count())->toBe($credentialCount)
        ->and(RentalBooking::query()->where('reference_code', 'like', 'RB-DEMO-%')->count())->toBe($bookingCount);
});

// --- Listing creation ---------------------------------------------------

test('a seller can create a rice combine harvester sale listing with reconditioned condition', function () {
    $seller = User::factory()->create();
    farmRole($seller, 'seller');
    SellerProfile::query()->create([
        'user_id' => $seller->id,
        'seller_type' => 'individual',
        'contact_number' => '09170000000',
        'status' => SellerProfile::StatusVerified,
        'verified_at' => now(),
    ]);

    $category = farmCategory()->load('specFields');
    $equipmentType = $category->specFields->firstWhere('name', 'equipment_type');

    $this->actingAs($seller)
        ->post(route('seller.listings.store'), [
            'title' => 'Compact Rice Combine Harvester for Sale',
            'category_id' => $category->id,
            'price' => '780000',
            'condition' => Listing::ConditionReconditioned,
            'brand' => 'Yanmar',
            'model' => 'AW70V',
            'specs' => [$equipmentType->id => 'Rice Combine Harvester'],
        ])
        ->assertRedirect();

    $listing = Listing::query()->where('title', 'Compact Rice Combine Harvester for Sale')->firstOrFail();

    expect($listing->condition)->toBe(Listing::ConditionReconditioned);
});

test('a rental provider can create a harvester rental listing', function () {
    $provider = rentalProvider();
    $category = farmCategory();

    $this->actingAs($provider)
        ->post(route('rental-provider.units.store'), [
            'rental_type' => RentalUnit::TypeHarvesterRental,
            'name' => 'Test Harvester Rental',
            'price_per_day' => '15000',
            'category_id' => $category->id,
            'operator_included' => '1',
        ])
        ->assertRedirect();

    $unit = RentalUnit::query()->where('name', 'Test Harvester Rental')->firstOrFail();

    expect($unit->rental_type)->toBe(RentalUnit::TypeHarvesterRental)
        ->and($unit->category_id)->toBe($category->id)
        ->and($unit->operator_included)->toBeTrue();
});

test('a rental provider can create a per-hectare harvesting service listing', function () {
    $provider = rentalProvider();
    $category = farmCategory();

    $this->actingAs($provider)
        ->post(route('rental-provider.units.store'), [
            'rental_type' => RentalUnit::TypeHarvesterService,
            'name' => 'Test Per-Hectare Harvesting Service',
            'price_per_day' => '18000',
            'price_per_hectare' => '3500',
            'category_id' => $category->id,
            'minimum_area_hectares' => '2',
        ])
        ->assertRedirect();

    $unit = RentalUnit::query()->where('name', 'Test Per-Hectare Harvesting Service')->firstOrFail();

    expect((float) $unit->price_per_hectare)->toBe(3500.0)
        ->and((float) $unit->minimum_area_hectares)->toBe(2.0);
});

test('a rental provider can create an agricultural drone listing', function () {
    $provider = rentalProvider();
    $category = farmCategory();

    $this->actingAs($provider)
        ->post(route('rental-provider.units.store'), [
            'rental_type' => RentalUnit::TypeDroneRental,
            'name' => 'Test Drone Listing',
            'price_per_day' => '6000',
            'category_id' => $category->id,
            'requires_verified_drone_operator' => '1',
            'allows_self_operation' => '1',
        ])
        ->assertRedirect();

    $unit = RentalUnit::query()->where('name', 'Test Drone Listing')->firstOrFail();

    expect($unit->rental_type)->toBe(RentalUnit::TypeDroneRental)
        ->and($unit->requires_verified_drone_operator)->toBeTrue();
});

// --- Pilot credentials ---------------------------------------------------

test('a user can submit drone pilot credentials for review', function () {
    Storage::fake('local');
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('drone-credentials.store'), [
            'credential_type' => 'Remote Pilot Certificate',
            'issuing_authority' => 'Demo Authority',
            'credential_number' => 'DEMO-123456',
            'country' => 'Demo Jurisdiction',
            'front_document' => UploadedFile::fake()->image('front.jpg'),
        ])
        ->assertRedirect();

    $credential = DronePilotCredential::query()->where('user_id', $user->id)->firstOrFail();

    expect($credential->status)->toBe(DronePilotCredential::StatusPendingReview);
    Storage::disk('local')->assertExists($credential->front_document_path);
    Storage::disk('public')->assertMissing($credential->front_document_path);
});

test('an authorized reviewer can approve and reject drone pilot credentials', function () {
    $reviewer = User::factory()->create();
    farmRole($reviewer, 'admin');
    $pilot = User::factory()->create();
    $credential = DronePilotCredential::query()->create([
        'user_id' => $pilot->id,
        'credential_type' => 'Remote Pilot Certificate',
        'status' => DronePilotCredential::StatusPendingReview,
    ]);

    $this->actingAs($reviewer)
        ->post(route('adm.drone-credentials.approve', $credential))
        ->assertRedirect();

    expect($credential->refresh()->status)->toBe(DronePilotCredential::StatusVerified);

    $credential2 = DronePilotCredential::query()->create([
        'user_id' => User::factory()->create()->id,
        'credential_type' => 'Remote Pilot Certificate',
        'status' => DronePilotCredential::StatusPendingReview,
    ]);

    $this->actingAs($reviewer)
        ->post(route('adm.drone-credentials.reject', $credential2), ['reason' => 'Illegible document'])
        ->assertRedirect();

    expect($credential2->refresh()->status)->toBe(DronePilotCredential::StatusRejected)
        ->and($credential2->rejection_reason)->toBe('Illegible document');
});

test('an authorized reviewer can suspend a verified pilot credential', function () {
    $reviewer = User::factory()->create();
    farmRole($reviewer, 'admin');
    $pilot = User::factory()->create();
    $credential = verifiedCredentialFor($pilot);

    $this->actingAs($reviewer)
        ->post(route('adm.drone-credentials.suspend', $credential), ['reason' => 'Reported unsafe operation'])
        ->assertRedirect();

    expect($credential->refresh()->status)->toBe(DronePilotCredential::StatusSuspended)
        ->and($pilot->fresh()->hasVerifiedDroneCredential())->toBeFalse();
});

test('expired credentials are automatically flagged by the scheduled command', function () {
    $pilot = User::factory()->create();
    $credential = DronePilotCredential::query()->create([
        'user_id' => $pilot->id,
        'credential_type' => 'Remote Pilot Certificate',
        'status' => DronePilotCredential::StatusVerified,
        'expiration_date' => now()->subDay(),
    ]);

    Artisan::call('drone-credentials:expire');

    expect($credential->refresh()->status)->toBe(DronePilotCredential::StatusExpired);
});

test('public credential numbers are masked', function () {
    $pilot = User::factory()->create();
    $credential = DronePilotCredential::query()->create([
        'user_id' => $pilot->id,
        'credential_type' => 'Remote Pilot Certificate',
        'credential_number' => 'DEMO-RPAS-VERIFIED-001',
        'status' => DronePilotCredential::StatusVerified,
    ]);

    expect($credential->maskedCredentialNumber())
        ->not->toBe('DEMO-RPAS-VERIFIED-001')
        ->toEndWith('-001');
});

test('private credential documents are only visible to the owner or an authorized reviewer', function () {
    Storage::fake('local');
    $pilot = User::factory()->create();
    $stranger = User::factory()->create();
    $reviewer = User::factory()->create();
    farmRole($reviewer, 'admin');

    $path = 'drone-credentials/front-demo.jpg';
    Storage::disk('local')->put($path, 'fake-image-content');

    $credential = DronePilotCredential::query()->create([
        'user_id' => $pilot->id,
        'credential_type' => 'Remote Pilot Certificate',
        'status' => DronePilotCredential::StatusPendingReview,
        'front_document_path' => $path,
    ]);

    $this->actingAs($stranger)
        ->get(route('drone-credentials.documents', ['credential' => $credential->id, 'type' => 'front']))
        ->assertForbidden();

    $this->actingAs($pilot)
        ->get(route('drone-credentials.documents', ['credential' => $credential->id, 'type' => 'front']))
        ->assertOk();

    $this->actingAs($reviewer)
        ->get(route('drone-credentials.documents', ['credential' => $credential->id, 'type' => 'front']))
        ->assertOk();
});

// --- Drone booking eligibility -------------------------------------------

test('self-operated drone booking is blocked for an unverified renter', function () {
    $provider = rentalProvider();
    $renter = User::factory()->create();
    $unit = droneUnit($provider);

    $this->actingAs($renter)
        ->post(route('rentals.book', $unit), [
            'start_date' => now()->addDays(3)->toDateString(),
            'self_operate' => '1',
            'compliance_acknowledged' => '1',
        ])
        ->assertSessionHasErrors('self_operate');

    expect(RentalBooking::query()->where('rental_unit_id', $unit->id)->count())->toBe(0);
});

test('self-operation is allowed for a verified pilot when the listing permits it', function () {
    $provider = rentalProvider();
    $renter = User::factory()->create();
    verifiedCredentialFor($renter);
    $unit = droneUnit($provider);

    $this->actingAs($renter)
        ->post(route('rentals.book', $unit), [
            'start_date' => now()->addDays(3)->toDateString(),
            'self_operate' => '1',
            'compliance_acknowledged' => '1',
        ])
        ->assertSessionHasNoErrors();

    $booking = RentalBooking::query()->where('rental_unit_id', $unit->id)->firstOrFail();
    expect($booking->drone_pilot_user_id)->toBe($renter->id);
});

test('a drone service can be booked with a verified assigned operator', function () {
    $provider = rentalProvider();
    $pilotUser = User::factory()->create();
    verifiedCredentialFor($pilotUser);
    $renter = User::factory()->create();

    $unit = droneUnit($provider, [
        'rental_type' => RentalUnit::TypeDroneService,
        'price_per_hectare' => 650,
        'drone_pilot_user_id' => $pilotUser->id,
        'allows_self_operation' => false,
    ]);

    $this->actingAs($renter)
        ->post(route('rentals.book', $unit), [
            'start_date' => now()->addDays(5)->toDateString(),
            'pricing_unit' => RentalPackage::DurationPerHectare,
            'area_hectares' => '2',
            'compliance_acknowledged' => '1',
        ])
        ->assertSessionHasNoErrors();

    $booking = RentalBooking::query()->where('rental_unit_id', $unit->id)->firstOrFail();
    expect($booking->drone_pilot_user_id)->toBe($pilotUser->id)
        ->and((float) $booking->base_amount)->toBe(1300.0);
});

test('pending and expired pilots cannot be booked for operational drone service', function () {
    $provider = rentalProvider();
    $renter = User::factory()->create();

    $pendingPilot = User::factory()->create();
    DronePilotCredential::query()->create([
        'user_id' => $pendingPilot->id,
        'credential_type' => 'Remote Pilot Certificate',
        'status' => DronePilotCredential::StatusPendingReview,
    ]);

    $unit = droneUnit($provider, [
        'rental_type' => RentalUnit::TypeDroneService,
        'drone_pilot_user_id' => $pendingPilot->id,
        'allows_self_operation' => false,
    ]);

    $this->actingAs($renter)
        ->post(route('rentals.book', $unit), [
            'start_date' => now()->addDays(2)->toDateString(),
            'compliance_acknowledged' => '1',
        ])
        ->assertSessionHasErrors('self_operate');

    expect(RentalBooking::query()->where('rental_unit_id', $unit->id)->count())->toBe(0);
});

// --- Harvester per-hectare booking and pricing ---------------------------

test('a harvester service can be booked by hectare with a minimum area enforced', function () {
    $provider = rentalProvider();
    $renter = User::factory()->create();

    $unit = RentalUnit::query()->create([
        'user_id' => $provider->id,
        'category_id' => farmCategory()->id,
        'rental_type' => RentalUnit::TypeHarvesterService,
        'name' => 'Test Harvesting Service',
        'price_per_day' => 20000,
        'price_per_hectare' => 3800,
        'operator_included' => true,
        'transportation_included' => true,
        'minimum_area_hectares' => 2,
        'status' => RentalUnit::StatusApproved,
        'approved_at' => now(),
    ]);

    $this->actingAs($renter)
        ->post(route('rentals.book', $unit), [
            'start_date' => now()->addDays(4)->toDateString(),
            'pricing_unit' => RentalPackage::DurationPerHectare,
            'area_hectares' => '1',
        ])
        ->assertSessionHasErrors('area_hectares');

    $this->actingAs($renter)
        ->post(route('rentals.book', $unit), [
            'start_date' => now()->addDays(4)->toDateString(),
            'pricing_unit' => RentalPackage::DurationPerHectare,
            'area_hectares' => '3',
        ])
        ->assertSessionHasNoErrors();

    $booking = RentalBooking::query()->where('rental_unit_id', $unit->id)->firstOrFail();
    expect((float) $booking->area_hectares)->toBe(3.0)
        ->and((float) $booking->base_amount)->toBe(11400.0);
});

test('price calculation itemizes base, operator, transportation, platform fee, and total', function () {
    $provider = rentalProvider();
    $unit = RentalUnit::query()->create([
        'user_id' => $provider->id,
        'category_id' => farmCategory()->id,
        'rental_type' => RentalUnit::TypeHarvesterRental,
        'name' => 'Pricing Test Harvester',
        'price_per_day' => 18000,
        'price_per_hectare' => 3500,
        'operator_fee' => 1000,
        'transportation_fee' => 2500,
        'operator_included' => false,
        'transportation_included' => false,
        'status' => RentalUnit::StatusApproved,
        'approved_at' => now(),
    ]);

    $pricing = app(RentalPricingService::class)->calculate($unit, [
        'pricing_unit' => RentalPackage::DurationPerHectare,
        'area_hectares' => 2,
        'with_operator' => true,
        'with_transportation' => true,
    ]);

    expect($pricing['base_amount'])->toBe(7000.0)
        ->and($pricing['operator_fee_amount'])->toBe(1000.0)
        ->and($pricing['transportation_fee_amount'])->toBe(2500.0)
        ->and($pricing['platform_fee_amount'])->toBeGreaterThan(0)
        ->and($pricing['total_amount'])->toBe(round(7000 + 1000 + 2500 + $pricing['platform_fee_amount'], 2));
});

test('overlapping confirmed bookings on the same rental unit are rejected', function () {
    $provider = rentalProvider();
    $unit = RentalUnit::query()->create([
        'user_id' => $provider->id,
        'category_id' => farmCategory()->id,
        'rental_type' => RentalUnit::TypeHarvesterRental,
        'name' => 'Overlap Test Harvester',
        'price_per_day' => 18000,
        'status' => RentalUnit::StatusApproved,
        'approved_at' => now(),
    ]);

    RentalBooking::query()->create([
        'rental_unit_id' => $unit->id,
        'renter_id' => User::factory()->create()->id,
        'provider_id' => $provider->id,
        'reference_code' => 'RB-OVERLAP-TEST',
        'start_date' => now()->addDays(10)->toDateString(),
        'end_date' => now()->addDays(12)->toDateString(),
        'status' => RentalBooking::StatusConfirmed,
    ]);

    $renter = User::factory()->create();

    $this->actingAs($renter)
        ->post(route('rentals.book', $unit), [
            'start_date' => now()->addDays(11)->toDateString(),
        ])
        ->assertSessionHasErrors('start_date');
});

// --- Seeded verified-operator badge and pending-listing lockout ----------

test('seeded verified drone operator has an active credential and the pending listing cannot be booked', function () {
    $this->seed(PrimeUnitsFarmMarketplaceDemoSeeder::class);

    $verifiedPilot = User::query()->where('email', 'pilot.verified@primeunits.test')->firstOrFail();
    expect($verifiedPilot->hasVerifiedDroneCredential())->toBeTrue();

    $pendingUnit = RentalUnit::query()->where('name', 'Agricultural Drone Rental Pending Operator Verification')->firstOrFail();
    $farmer = User::query()->where('email', 'farmer.demo@primeunits.test')->firstOrFail();

    $this->actingAs($farmer)
        ->post(route('rentals.book', $pendingUnit), [
            'start_date' => now()->addDays(2)->toDateString(),
            'compliance_acknowledged' => '1',
        ])
        ->assertSessionHasErrors('self_operate');
});
