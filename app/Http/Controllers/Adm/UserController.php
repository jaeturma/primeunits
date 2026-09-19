<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('adm/users/index', [
            'users' => User::query()
                ->with('roles:id,name,label')
                ->latest()
                ->get(['id', 'name', 'email', 'created_at'])
                ->map(fn (User $user): array => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at?->toISOString(),
                    'roles' => $user->roles->map(fn (Role $role): array => [
                        'id' => $role->id,
                        'name' => $role->name,
                        'label' => $role->label,
                    ])->values(),
                ]),
        ]);
    }
}
