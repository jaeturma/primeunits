<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\BrandSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RbacSeeder::class);
    $this->seed(CategorySeeder::class);
    $this->seed(BrandSeeder::class);
});

test('manager can manage categories and brands', function () {
    $manager = User::factory()->create();
    $manager->roles()->attach(Role::query()->where('name', 'manager')->firstOrFail());

    $this->actingAs($manager)
        ->get(route('adm.categories.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('adm/categories/index')
            ->has('categories'),
        );

    $this->actingAs($manager)
        ->post(route('adm.categories.store'), [
            'name' => 'Service Units',
            'slug' => 'service_units',
        ])
        ->assertRedirect();

    $this->actingAs($manager)
        ->get(route('adm.brands.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('adm/brands/index')
            ->has('brands')
            ->has('groups'),
        );

    $this->actingAs($manager)
        ->post(route('adm.brands.store'), [
            'name' => 'DemoBrand',
            'logo' => '/brand-logos/oem.svg',
            'category_group' => 'vehicle',
            'sort_order' => 99,
            'is_active' => true,
        ])
        ->assertRedirect();

    expect(Category::query()->where('slug', 'service_units')->exists())->toBeTrue()
        ->and(Brand::query()->where('name', 'DemoBrand')->exists())->toBeTrue();
});
