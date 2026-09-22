<?php

namespace App\Http\Controllers;

use App\Models\LandingAd;
use App\Models\Listing;
use App\Models\MembershipAccess;
use App\Models\PromotionImpression;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Records that a Featured, Sponsored, or Advertisement card was actually
 * rendered in the viewer's browser (sent once per card via an
 * IntersectionObserver "viewability" check on the frontend, not merely
 * server delivery). De-duplicated per session/user and promoted item
 * within a one-hour bucket so a page refresh or repeated "Load 12 More"
 * cannot inflate impression counts.
 */
class PromotionImpressionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'max:12'],
            'items.*.type' => ['required', Rule::in([
                PromotionImpression::TypeFeatured,
                PromotionImpression::TypeSponsored,
                PromotionImpression::TypeAdvertisement,
            ])],
            'items.*.listing_id' => ['nullable', 'integer', 'exists:listings,id'],
            'items.*.ad_id' => ['nullable', 'integer', 'exists:landing_ads,id'],
        ]);

        $viewer = $request->user();
        $mode = $viewer?->membershipAccess?->effectiveMode() ?? MembershipAccess::LevelRegular;
        $sessionId = $request->session()->getId();
        $hourBucket = now()->format('Y-m-d-H');

        foreach ($data['items'] as $item) {
            $isAd = $item['type'] === PromotionImpression::TypeAdvertisement;
            $subjectType = $isAd ? LandingAd::class : Listing::class;
            $subjectId = $isAd ? ($item['ad_id'] ?? null) : ($item['listing_id'] ?? null);

            if ($subjectId === null) {
                continue;
            }

            $dedupeKey = hash('sha256', implode('|', [$item['type'], $subjectType, $subjectId, $viewer?->id ?? $sessionId, $hourBucket]));

            $inserted = DB::table('promotion_impressions')->insertOrIgnore([
                'promotion_type' => $item['type'],
                'promotable_type' => $subjectType,
                'promotable_id' => $subjectId,
                'user_id' => $viewer?->id,
                'session_id' => $sessionId,
                'marketplace_mode' => $mode,
                'viewed_at' => now(),
                'dedupe_key' => $dedupeKey,
            ]);

            if ($inserted > 0 && $isAd) {
                LandingAd::query()->whereKey($subjectId)->increment('impressions_count');
            }
        }

        return response()->json(['recorded' => true]);
    }
}
