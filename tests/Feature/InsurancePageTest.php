<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Database\Seeders\InsuranceCompanySeeder;

uses(RefreshDatabase::class);

test('insurance page renders for guests', function () {
    $this->seed(InsuranceCompanySeeder::class);

    $this->get(route('insurance'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('insurance')
            ->has('companies', 2)
            ->where('companies.0.name', 'PrimeSure Insurance Agency')
            ->where('companies.0.coverage_types.0', 'CTPL Insurance')
            ->where('companies.0.coverage_types.1', 'Premium Insurance'),
        );
});
