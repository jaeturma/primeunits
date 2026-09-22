<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ListingTierController extends Controller
{
    public function index(Request $request): Response
    {
        $listings = Listing::query()
            ->with('user:id,name', 'category:id,name')
            ->when($request->filled('gold_candidates'), fn ($q) => $q->where('is_gold_candidate', true)->where('marketplace_tier', '!=', Listing::TierGold))
            ->when($request->filled('tier'), fn ($q) => $q->where('marketplace_tier', $request->string('tier')->toString()))
            ->latest()
            ->paginate(20)
            ->through(fn (Listing $listing): array => [
                'id' => $listing->id,
                'title' => $listing->title,
                'category' => $listing->category?->name,
                'seller' => $listing->user?->name,
                'marketplace_tier' => $listing->marketplace_tier,
                'tier_label' => $listing->tierLabel(),
                'visibility_level' => $listing->visibility_level,
                'visibility_label' => $listing->visibilityLabel(),
                'is_gold_candidate' => $listing->is_gold_candidate,
                'status' => $listing->status,
                'promotional_type' => $listing->promotional_type,
                'promo_label' => $listing->promoLabel(),
                'promoted_until' => $listing->promoted_until?->toISOString(),
            ]);

        return Inertia::render('adm/listing-tiers/index', [
            'listings' => $listings,
            'filters' => [
                'tier' => $request->string('tier')->toString(),
                'gold_candidates' => $request->boolean('gold_candidates'),
            ],
            'tiers' => [Listing::TierRegular, Listing::TierSilver, Listing::TierGold, Listing::TierGoldEnterprise],
            'visibilityLevels' => [
                Listing::VisibilityPublic,
                Listing::VisibilityPublicPreview,
                Listing::VisibilitySilverExclusive,
                Listing::VisibilityGoldExclusive,
                Listing::VisibilityVerifiedBuyerOnly,
                Listing::VisibilityInvitationOnly,
            ],
        ]);
    }

    public function update(Request $request, Listing $listing): RedirectResponse
    {
        Gate::authorize('manageTier', $listing);

        $data = $request->validate([
            'marketplace_tier' => ['required', Rule::in([Listing::TierRegular, Listing::TierSilver, Listing::TierGold, Listing::TierGoldEnterprise])],
            'visibility_level' => ['required', Rule::in([
                Listing::VisibilityPublic,
                Listing::VisibilityPublicPreview,
                Listing::VisibilitySilverExclusive,
                Listing::VisibilityGoldExclusive,
                Listing::VisibilityVerifiedBuyerOnly,
                Listing::VisibilityInvitationOnly,
            ])],
            'promotional_type' => ['sometimes', Rule::in([Listing::PromoNone, Listing::PromoSponsored, Listing::PromoPrime])],
            'promoted_until' => ['nullable', 'date'],
        ]);

        if ($data['marketplace_tier'] === Listing::TierGold || $data['marketplace_tier'] === Listing::TierGoldEnterprise) {
            Gate::authorize('reviewGoldCandidate', $listing);
        }

        $listing->update($data);

        return back()->with('success', 'Listing tier and visibility updated.');
    }

    public function approveGoldCandidate(Listing $listing): RedirectResponse
    {
        Gate::authorize('reviewGoldCandidate', $listing);

        abort_unless($listing->is_gold_candidate, 404);

        $listing->update([
            'marketplace_tier' => Listing::TierGold,
            'is_gold_candidate' => false,
        ]);

        return back()->with('success', 'Listing approved as a Gold listing.');
    }
}
