<?php

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('financing page exposes selected financing type and partners', function () {
    $this->seed(DatabaseSeeder::class);

    $response = $this->get(route('financing.index', ['type' => 'sangla-or-cr']), [
        'X-Inertia' => 'true',
        'X-Inertia-Version' => hash_file('xxh128', public_path('build/manifest.json')),
    ]);

    $response->assertOk()
        ->assertJsonPath('component', 'financing/index')
        ->assertJsonPath('props.filters.type', 'sangla-or-cr')
        ->assertJsonCount(6, 'props.partners.data')
        ->assertJsonPath('props.partners.data.0.logo_url', fn (string $logo): bool => str_contains($logo, '/storage/financing/logos/'))
        ->assertJsonStructure([
            'props' => [
                'partners' => [
                    'data' => [
                        '*' => ['id', 'slug', 'company_name', 'products_count'],
                    ],
                ],
            ],
        ]);
});
