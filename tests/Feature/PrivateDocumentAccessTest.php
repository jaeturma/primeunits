<?php

use App\Models\Category;
use App\Models\Listing;
use App\Models\ResourceAttachment;
use App\Models\Role;
use App\Models\SellerProfile;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Storage::fake('local');
    $this->seed(RbacSeeder::class);
    $this->seed(CategorySeeder::class);
});

function documentRole(User $user, string $role): void
{
    $user->roles()->attach(Role::query()->where('name', $role)->firstOrFail());
}

test('a seller can view their own verification document, but a stranger cannot', function () {
    Storage::disk('local')->put('sellers/ids/secret-id.jpg', 'fake-id-content');

    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $profile = SellerProfile::query()->create([
        'user_id' => $owner->id,
        'seller_type' => 'individual',
        'contact_number' => '09170000000',
        'status' => SellerProfile::StatusPending,
        'valid_id_file' => 'sellers/ids/secret-id.jpg',
    ]);

    $this->actingAs($owner)
        ->get(route('sellers.documents', [$profile, 'valid_id_file']))
        ->assertOk();

    $this->actingAs($stranger)
        ->get(route('sellers.documents', [$profile, 'valid_id_file']))
        ->assertForbidden();
});

test('a guest is redirected to login rather than reaching a verification document', function () {
    Storage::disk('local')->put('sellers/ids/secret-id.jpg', 'fake-id-content');

    $owner = User::factory()->create();
    $profile = SellerProfile::query()->create([
        'user_id' => $owner->id,
        'seller_type' => 'individual',
        'contact_number' => '09170000000',
        'status' => SellerProfile::StatusPending,
        'valid_id_file' => 'sellers/ids/secret-id.jpg',
    ]);

    $this->get(route('sellers.documents', [$profile, 'valid_id_file']))
        ->assertRedirect(route('login'));
});

test('an admin with verify_sellers can view a seller document they do not own', function () {
    Storage::disk('local')->put('sellers/ids/secret-id.jpg', 'fake-id-content');

    $owner = User::factory()->create();
    $reviewer = User::factory()->create();
    documentRole($reviewer, 'admin');

    $profile = SellerProfile::query()->create([
        'user_id' => $owner->id,
        'seller_type' => 'individual',
        'contact_number' => '09170000000',
        'status' => SellerProfile::StatusPending,
        'valid_id_file' => 'sellers/ids/secret-id.jpg',
    ]);

    $this->actingAs($reviewer)
        ->get(route('sellers.documents', [$profile, 'valid_id_file']))
        ->assertOk();
});

test('a non-whitelisted field on a seller profile document route 404s instead of leaking arbitrary columns', function () {
    $owner = User::factory()->create();
    $profile = SellerProfile::query()->create([
        'user_id' => $owner->id,
        'seller_type' => 'individual',
        'contact_number' => '09170000000',
        'status' => SellerProfile::StatusPending,
    ]);

    $this->actingAs($owner)
        ->get(route('sellers.documents', [$profile, 'contact_number']))
        ->assertNotFound();
});

test('a listing owner can view their identity document, but another buyer cannot', function () {
    Storage::disk('local')->put('listings/identity/secret-id.jpg', 'fake-id-content');

    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $category = Category::query()->where('slug', 'cars')->firstOrFail();
    $listing = Listing::query()->create([
        'user_id' => $owner->id,
        'category_id' => $category->id,
        'title' => 'Private Document Listing',
        'price' => 500000,
        'condition' => Listing::ConditionUsed,
        'status' => Listing::StatusPending,
        'valid_id_file' => 'listings/identity/secret-id.jpg',
    ]);

    $this->actingAs($owner)
        ->get(route('listings.documents', [$listing, 'valid_id_file']))
        ->assertOk();

    $this->actingAs($stranger)
        ->get(route('listings.documents', [$listing, 'valid_id_file']))
        ->assertForbidden();
});

test('an admin with approve_listings can view a listing identity document', function () {
    Storage::disk('local')->put('listings/identity/secret-id.jpg', 'fake-id-content');

    $owner = User::factory()->create();
    $reviewer = User::factory()->create();
    documentRole($reviewer, 'admin');

    $category = Category::query()->where('slug', 'cars')->firstOrFail();
    $listing = Listing::query()->create([
        'user_id' => $owner->id,
        'category_id' => $category->id,
        'title' => 'Reviewer Document Listing',
        'price' => 500000,
        'condition' => Listing::ConditionUsed,
        'status' => Listing::StatusPending,
        'or_cr_file' => 'listings/identity/secret-id.jpg',
    ]);

    $this->actingAs($reviewer)
        ->get(route('listings.documents', [$listing, 'or_cr_file']))
        ->assertOk();
});

test('a listing resource attachment is only reachable by the owner or an approve_listings reviewer', function () {
    Storage::disk('local')->put('listings/attachments/receipt.pdf', 'fake-pdf-content');

    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $reviewer = User::factory()->create();
    documentRole($reviewer, 'admin');

    $category = Category::query()->where('slug', 'cars')->firstOrFail();
    $listing = Listing::query()->create([
        'user_id' => $owner->id,
        'category_id' => $category->id,
        'title' => 'Attachment Listing',
        'price' => 500000,
        'condition' => Listing::ConditionUsed,
        'status' => Listing::StatusPending,
    ]);

    $attachment = $listing->attachments()->create([
        'user_id' => $owner->id,
        'name' => 'Delivery receipt.pdf',
        'path' => 'listings/attachments/receipt.pdf',
        'mime_type' => 'application/pdf',
        'size' => 128,
    ]);

    expect($attachment->url())->toContain('/attachments/'.$attachment->id);

    $this->actingAs($owner)->get($attachment->url())->assertOk();
    $this->actingAs($reviewer)->get($attachment->url())->assertOk();
    $this->actingAs($stranger)->get($attachment->url())->assertForbidden();
});

test('verification documents are never reachable on the public disk', function () {
    Storage::fake('public');
    Storage::disk('local')->put('sellers/ids/secret-id.jpg', 'fake-id-content');

    Storage::disk('local')->assertExists('sellers/ids/secret-id.jpg');
    Storage::disk('public')->assertMissing('sellers/ids/secret-id.jpg');
});

test('a resource attachment for an unrecognized attachable type 404s rather than exposing it', function () {
    $orphan = ResourceAttachment::query()->forceCreate([
        'attachable_type' => User::class,
        'attachable_id' => User::factory()->create()->id,
        'name' => 'orphan.pdf',
        'path' => 'orphan.pdf',
        'mime_type' => 'application/pdf',
        'size' => 1,
    ]);

    $viewer = User::factory()->create();
    documentRole($viewer, 'admin');

    $this->actingAs($viewer)
        ->get(route('attachments.show', $orphan))
        ->assertNotFound();
});
