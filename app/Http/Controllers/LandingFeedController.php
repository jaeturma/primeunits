<?php

namespace App\Http\Controllers;

use App\Models\LandingAd;
use App\Models\Listing;
use App\Models\MembershipAccess;
use App\Services\FeedCompositionService;
use App\Support\FeedCursor;
use App\Support\ResolvesListingStockImage;
use App\Support\SafeUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LandingFeedController extends Controller
{
    use ResolvesListingStockImage;

    public function more(Request $request, FeedCompositionService $feed): JsonResponse
    {
        $viewer = $request->user();
        $mode = $viewer?->membershipAccess?->effectiveMode() ?? MembershipAccess::LevelRegular;
        $cursor = FeedCursor::decode($request->string('cursor')->toString());

        $result = $feed->compose($viewer, $mode, $cursor['listings'], $cursor['ads']);

        return response()->json([
            'cards' => collect($result['cards'])->map(fn (array $card): array => [
                'type' => $card['type'],
                'listing' => $card['listing'] instanceof Listing ? $this->serializeFeedListingCard($card['listing']) : null,
                'ad' => $card['ad'] instanceof LandingAd ? $this->serializeFeedAd($card['ad']) : null,
            ])->values(),
            'cursor' => FeedCursor::encode($mode, $result['shown_listing_ids'], $result['shown_ad_ids']),
            'has_more' => $result['has_more'],
        ]);
    }

    /**
     * Server-mediated advertisement click-through: revalidates the
     * destination against the same safe-URL policy enforced at save time
     * (defense in depth), records the click, and only then redirects.
     * Never redirects to an unsafe scheme even if one somehow reached the
     * database directly.
     */
    public function click(Request $request, LandingAd $landingAd): RedirectResponse
    {
        $destination = $request->string('to')->toString();

        if (blank($destination) || ! SafeUrl::isSafeDestination($destination) || $destination !== $landingAd->cta_url) {
            abort(404);
        }

        LandingAd::query()->whereKey($landingAd->id)->increment('clicks_count');

        return redirect()->away($destination);
    }
}
