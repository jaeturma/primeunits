<?php

namespace App\Services;

use App\Models\Listing;
use App\Models\PromotionImpression;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Admin-facing review of Featured and Sponsored landing-feed placements:
 * how many times each was actually rendered to a viewer (recorded by
 * PromotionImpressionController, deduped per session/user per hour — see
 * that controller's docblock). Advertisement impressions/clicks are
 * already surfaced directly on the LandingAd admin list
 * (impressions_count/clicks_count), so this service focuses on the two
 * placement types that had no admin visibility at all.
 */
class PromotionAnalyticsService
{
    /**
     * @param  array{from?: string|null, to?: string|null}  $filters
     * @return array<string, int>
     */
    public function summary(array $filters = []): array
    {
        $counts = $this->impressionQuery($filters)
            ->select('promotion_type', DB::raw('COUNT(*) as total'))
            ->groupBy('promotion_type')
            ->pluck('total', 'promotion_type');

        return [
            'featured_impressions' => (int) ($counts[PromotionImpression::TypeFeatured] ?? 0),
            'sponsored_impressions' => (int) ($counts[PromotionImpression::TypeSponsored] ?? 0),
            'advertisement_impressions' => (int) ($counts[PromotionImpression::TypeAdvertisement] ?? 0),
        ];
    }

    /**
     * @param  array{from?: string|null, to?: string|null}  $filters
     * @return list<array{id: int, title: string, impressions: int}>
     */
    public function topFeaturedListings(array $filters = [], int $limit = 10): array
    {
        return $this->topListings(PromotionImpression::TypeFeatured, $filters, $limit);
    }

    /**
     * @param  array{from?: string|null, to?: string|null}  $filters
     * @return list<array{id: int, title: string, impressions: int}>
     */
    public function topSponsoredListings(array $filters = [], int $limit = 10): array
    {
        return $this->topListings(PromotionImpression::TypeSponsored, $filters, $limit);
    }

    /**
     * @param  array{from?: string|null, to?: string|null}  $filters
     * @return list<array{id: int, title: string, impressions: int}>
     */
    private function topListings(string $promotionType, array $filters, int $limit): array
    {
        return $this->impressionQuery($filters)
            ->where('promotion_type', $promotionType)
            ->where('promotable_type', Listing::class)
            ->join('listings', 'listings.id', '=', 'promotion_impressions.promotable_id')
            ->select('listings.id', 'listings.title', DB::raw('COUNT(*) as impressions'))
            ->groupBy('listings.id', 'listings.title')
            ->orderByDesc('impressions')
            ->orderBy('listings.title')
            ->limit($limit)
            ->get()
            ->map(fn (object $row): array => [
                'id' => (int) $row->id,
                'title' => (string) $row->title,
                'impressions' => (int) $row->impressions,
            ])
            ->all();
    }

    /**
     * @param  array{from?: string|null, to?: string|null}  $filters
     * @return Builder<PromotionImpression>
     */
    private function impressionQuery(array $filters): Builder
    {
        $query = PromotionImpression::query();

        if (! empty($filters['from'])) {
            $query->whereDate('viewed_at', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->whereDate('viewed_at', '<=', $filters['to']);
        }

        return $query;
    }
}
