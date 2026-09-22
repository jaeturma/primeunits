<?php

namespace App\Http\Middleware;

use App\Models\MembershipAccess;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the current request's authorized marketplace mode (regular,
 * silver, or gold) once, from the server-side membership state, and makes
 * it available both to the initial Blade render (so the tier theme applies
 * before hydration, mirroring HandleAppearance) and to Inertia's shared
 * props (so client-side navigations can keep it in sync). Never reads the
 * mode from client input — it is always MembershipAccess::effectiveMode(),
 * which already falls back safely on expiry or suspension.
 *
 * Reads the relation directly rather than membershipAccessOrDefault():
 * this middleware runs on every request, and a user with no
 * MembershipAccess row yet defaults to Regular either way, so there is no
 * reason to write a row as a side effect of merely rendering a page.
 */
class HandleMarketplaceMode
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $mode = $request->user()?->membershipAccess?->effectiveMode() ?? MembershipAccess::LevelRegular;

        $request->attributes->set('marketplace_mode', $mode);
        View::share('marketplaceMode', $mode);

        return $next($request);
    }
}
