<?php

use App\Models\LandingAd;
use App\Models\LandingPage;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\LandingSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RbacSeeder::class);
    $this->seed(LandingSeeder::class);
});

function landingManager(): User
{
    $user = User::factory()->create();
    $user->roles()->attach(Role::query()->where('name', 'manager')->firstOrFail());

    return $user->fresh();
}

test('manager can update the main landing page', function () {
    $manager = landingManager();

    $this->actingAs($manager)
        ->get(route('adm.landing.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('adm/landing/index')
            ->where('auth.permissions', fn (mixed $permissions): bool => collect($permissions)->contains('manage_landing')),
        );

    $this->actingAs($manager)
        ->withSession(['_token' => 'landing-page-test-token'])
        ->put(route('adm.landing.update'), [
            '_token' => 'landing-page-test-token',
            'hero_badge' => 'Managed by managers',
            'hero_title' => 'Managed landing hero',
            'hero_subtitle' => 'Managed landing subtitle',
            'search_title' => 'Search inventory',
            'featured_title' => 'Featured units',
            'featured_subtitle' => 'Featured subtitle',
            'results_title' => 'Filtered results',
            'results_subtitle' => 'Filtered subtitle',
            'budget_title' => 'Budget bands',
            'seller_cta_title' => 'Seller CTA',
            'seller_cta_body' => 'Seller CTA body',
            'seller_cta_button' => 'Apply now',
            'is_active' => true,
        ])
        ->assertRedirect();

    expect(LandingPage::query()->where('key', 'home')->firstOrFail()->hero_title)
        ->toBe('Managed landing hero');
});

test('manager can create landing ads', function () {
    Storage::fake('public');

    $manager = landingManager();

    $this->actingAs($manager)
        ->get(route('adm.landing-ads.index'))
        ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
            ->component('adm/landing-ads/index')
            ->has('ads', 7),
        );

    $this->actingAs($manager)
        ->withSession(['_token' => 'landing-ad-test-token'])
        ->post(route('adm.landing-ads.store'), [
            '_token' => 'landing-ad-test-token',
            'title' => 'Motorcycle Dealer Promo',
            'category' => 'Motorcycle Dealer',
            'body' => 'Dealer ad slot managed by managers.',
            'cta_label' => 'View dealer',
            'cta_url' => '/?classification=Motorcycle%20Dealer#ads',
            'image_file' => UploadedFile::fake()->image('dealer-ad.jpg', 1200, 800),
            'accent_color' => '#16a34a',
            'sort_order' => 10,
            'is_active' => true,
        ])
        ->assertRedirect();

    $ad = LandingAd::query()->where('title', 'Motorcycle Dealer Promo')->firstOrFail();

    expect($ad->image_url)->toStartWith('/storage/landing-ads/')
        ->and(Storage::disk('public')->exists(str($ad->image_url)->after('/storage/')->toString()))->toBeTrue();
});
