<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RbacSeeder::class);
});

test('seeded superadmin has every permission', function () {
    $superadmin = Role::query()->where('name', 'superadmin')->firstOrFail();

    expect(Role::query()->count())->toBeGreaterThan(0)
        ->and($superadmin->permissions)->toHaveCount(Permission::query()->count());
});

test('users can have roles and derived permissions', function () {
    $user = User::factory()->create();
    $superadmin = Role::query()->where('name', 'superadmin')->firstOrFail();

    $user->roles()->attach($superadmin);

    expect($user->fresh()->hasRole('superadmin'))->toBeTrue()
        ->and($user->fresh()->hasPermission('manage_users'))->toBeTrue();
});

test('admin routes reject users without an allowed role', function () {
    $user = User::factory()->create();
    $buyer = Role::query()->where('name', 'buyer')->firstOrFail();

    $user->roles()->attach($buyer);

    $this->actingAs($user)
        ->getJson(route('adm.dashboard'))
        ->assertForbidden();
});

test('admin users can visit the admin dashboard', function () {
    $user = User::factory()->create();
    $admin = Role::query()->where('name', 'admin')->firstOrFail();

    $user->roles()->attach($admin);

    $this->actingAs($user)
        ->get(route('adm.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('adm/dashboard')
            ->where('auth.roles.0', 'admin'),
        );
});

test('user list requires manage users permission', function () {
    $user = User::factory()->create();
    $coordinator = Role::query()->where('name', 'coordinator')->firstOrFail();

    $user->roles()->attach($coordinator);

    expect($user->fresh()->hasPermission('manage_users'))->toBeFalse();

    $this->actingAs($user)
        ->getJson(route('adm.users.index'))
        ->assertForbidden();
});

test('superadmin can view users with roles', function () {
    $user = User::factory()->create();
    $superadmin = Role::query()->where('name', 'superadmin')->firstOrFail();

    $user->roles()->attach($superadmin);

    $this->actingAs($user)
        ->get(route('adm.users.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('adm/users/index')
            ->where('auth.permissions.0', 'manage_users')
            ->has('users', 1)
            ->where('users.0.roles.0.name', 'superadmin'),
        );
});
