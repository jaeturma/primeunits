<?php

use App\Models\Municipality;
use App\Models\Province;
use App\Models\Region;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\LocationSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RbacSeeder::class);
    $this->seed(LocationSeeder::class);
});

test('location seeder populates philippine regions provinces and towns', function () {
    expect(Region::query()->count())->toBeGreaterThanOrEqual(17)
        ->and(Province::query()->count())->toBeGreaterThanOrEqual(80)
        ->and(Municipality::query()->count())->toBeGreaterThanOrEqual(1500);
});

test('manager can manage locations in the backend', function () {
    $manager = User::factory()->create();
    $manager->roles()->attach(Role::query()->where('name', 'manager')->firstOrFail());
    $region = Region::query()->where('code', '0700000000')->firstOrFail();

    $this->actingAs($manager)
        ->get(route('adm.locations.index', ['q' => 'Mandaue']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('adm/locations/index')
            ->has('regions')
            ->has('provinces')
            ->has('municipalities'),
        );

    $this->actingAs($manager)
        ->post(route('adm.locations.provinces.store'), [
            'region_id' => $region->id,
            'code' => '0799900000',
            'name' => 'Demo Province',
            'is_active' => true,
        ])
        ->assertRedirect();

    expect(Province::query()->where('name', 'Demo Province')->exists())->toBeTrue();
});
