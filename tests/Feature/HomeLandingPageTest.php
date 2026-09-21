<?php

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('home landing page exposes marketplace search data and listing sections', function () {
    $this->seed(DatabaseSeeder::class);

    $response = $this->get(route('home', ['region' => 'Visayas']), [
        'X-Inertia' => 'true',
        'X-Inertia-Version' => hash_file('xxh128', public_path('build/manifest.json')),
    ]);

    $response->assertOk()
        ->assertJsonPath('component', 'welcome')
        ->assertJsonPath('props.filters.region', 'Visayas')
        ->assertJsonPath('props.landing.search_title', 'Find your unit')
        ->assertJsonPath('props.searchOptions.fuelTypes', ['Gasoline', 'Diesel', 'LPG', 'BEV', 'HEV', 'PHEV', 'eREV', 'FCEV'])
        ->assertJsonPath('props.searchOptions.brands.0.name', 'ADO')
        ->assertJsonPath('props.ads.0.image_url', '/images/landing-equipment-yard.png')
        ->assertJsonCount(7, 'props.ads')
        ->assertJsonStructure([
            'props' => [
                'categories',
                'marketplaceListings',
                'featuredListings',
                'miniListings',
                'searchOptions' => [
                    'brandGroups' => ['vehicle', 'truck', 'motorcycle', 'threeWheel', 'eBike', 'equipment', 'farm'],
                    'vehicleTypes',
                    'equipmentTypes',
                    'motorcycleTypes',
                    'locationOptions' => ['regions', 'provinces', 'municipalities'],
                ],
                'categoryBrandGroups',
                'rentalTypes',
            ],
        ]);

    $props = $response->json('props');

    expect($props['categories'])->not->toBeEmpty()
        ->and(collect($props['categories'])->pluck('slug'))->toContain('cars', 'heavy-equipment')
        ->and(collect($props['categories'])->firstWhere('slug', 'cars')['classification_field']['name'])->toBe('body_type')
        ->and($props['categoryBrandGroups']['cars'])->toBe(['vehicle'])
        ->and(collect($props['rentalTypes'])->pluck('value'))->toContain('car_rental')
        ->and($props['searchOptions']['vehicleTypes'])->toContain('SUV')
        ->and($props['searchOptions']['locationOptions']['provinces'])->not->toBeEmpty()
        ->and(collect($props['searchOptions']['locationOptions']['provinces'])->pluck('name'))->toContain('Cebu')
        ->and($props['searchOptions']['locationOptions']['municipalities'])->not->toBeEmpty()
        ->and(collect($props['searchOptions']['locationOptions']['municipalities'])->pluck('name'))->toContain('City of Mandaue')
        ->and($props['marketplaceListings'])->not->toBeEmpty()
        ->and($props['featuredListings'])->not->toBeEmpty()
        ->and($props['featuredListings'][0]['image_url'])->toMatch('#^/images/vehicles/[a-z-]+\.svg$#');
});

test('home landing page loads a marketplace-style twelve card feed', function () {
    $this->seed(DatabaseSeeder::class);

    $response = $this->get(route('home'), [
        'X-Inertia' => 'true',
        'X-Inertia-Version' => hash_file('xxh128', public_path('build/manifest.json')),
    ]);

    $response->assertOk()
        ->assertJsonCount(11, 'props.marketplaceListings')
        ->assertJsonCount(7, 'props.ads')
        ->assertJsonStructure([
            'props' => [
                'marketplaceListings' => [
                    '*' => ['id', 'title', 'description', 'price', 'municipality', 'province', 'image_url'],
                ],
            ],
        ]);

    expect($response->json('props.marketplaceListings.0.image_url'))->toMatch('#^/images/vehicles/[a-z-]+\.svg$#');
});
