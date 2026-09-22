<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\MembershipAccess;
use App\Models\MembershipApplication;
use App\Services\MembershipAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The account-settings home for a user's marketplace access. Deliberately
 * plain and factual: no plan pricing, no upsell copy. Silver and Gold
 * buyer/seller access is granted only through an admin-initiated
 * invitation (see Adm\MembershipApplicationController::invite), which
 * this page lets the user accept or decline — entirely optional.
 */
class MembershipController extends Controller
{
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $access = $user->membershipAccessOrDefault();

        $pendingInvitations = $user->membershipApplications()
            ->where('status', MembershipApplication::StatusInvited)
            ->latest()
            ->get()
            ->map(fn (MembershipApplication $a): array => [
                'id' => $a->id,
                'type' => $a->type,
                'target_level' => $a->target_level,
                'reviewer_notes' => $a->reviewer_notes,
            ]);

        return Inertia::render('settings/membership', [
            'access' => [
                'identity_verification_level' => $access->identity_verification_level,
                'buyer_access_level' => $access->buyer_access_level,
                'buyer_access_status' => $access->buyer_access_status,
                'seller_access_level' => $access->seller_access_level,
                'seller_access_status' => $access->seller_access_status,
                'current_mode' => $access->effectiveMode(),
                'available_modes' => $access->availableModes(),
            ],
            'pendingInvitations' => $pendingInvitations,
        ]);
    }

    public function switchMode(Request $request, MembershipAccessService $service): RedirectResponse
    {
        $data = $request->validate([
            'mode' => ['required', Rule::in([
                MembershipAccess::LevelRegular,
                MembershipAccess::LevelSilver,
                MembershipAccess::LevelGold,
            ])],
        ]);

        $service->switchMode($request->user(), $data['mode']);

        return back();
    }
}
