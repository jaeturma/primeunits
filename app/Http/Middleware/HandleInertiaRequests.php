<?php

namespace App\Http\Middleware;

use App\Models\MembershipAccess;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        $user?->loadMissing('roles.permissions');

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'marketplaceMode' => $request->attributes->get('marketplace_mode', MembershipAccess::LevelRegular),
            'auth' => [
                'user' => $user,
                'roles' => $user?->roles->pluck('name')->values()->all() ?? [],
                'permissions' => $user?->permissions()->pluck('name')->values()->all() ?? [],
            ],
            'flash' => [
                'inquiry' => $request->session()->get('inquiry'),
            ],
            'notificationSummary' => [
                'unread_count' => $user?->unreadNotifications()->count() ?? 0,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
