<?php

namespace App\Services;

use App\Models\LandingAd;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Composes the landing-page listing feed in batches of exactly
 * `primeunits.feed.batch_size` positions, with at most one Featured, one
 * Sponsored, and one Advertisement placement per batch, the remaining
 * positions filled by organic listings.
 *
 * Every eligibility check reuses the existing authorization surface
 * (`Listing::scopeVisibleTo()`, `Listing::scopeApproved()`,
 * `Listing::scopeNotExpired()`) rather than re-implementing it, so a
 * listing that isn't safe to show in search/browse can never surface here
 * either. The composed batch never depends on client-supplied state for
 * authorization — only for which ids have already been shown, so a
 * tampered "shown ids" list can at worst produce a duplicate or repeat, not
 * an access bypass.
 */
class FeedCompositionService
{
    public function batchSize(): int
    {
        return (int) config('primeunits.feed.batch_size', 12);
    }

    /**
     * @param  array<int, int>  $shownListingIds  Listing ids already delivered in this loaded feed (organic, featured, and sponsored alike).
     * @param  array<int, int>  $shownAdIds  Advertisement ids already delivered in this loaded feed.
     * @return array{cards: array<int, array{type: string, listing: ?Listing, ad: ?LandingAd}>, shown_listing_ids: array<int, int>, shown_ad_ids: array<int, int>, has_more: bool}
     */
    public function compose(?User $viewer, string $mode, array $shownListingIds, array $shownAdIds): array
    {
        $batchSize = $this->batchSize();

        $featured = $this->pickFeatured($viewer, $shownListingIds);
        $sponsored = $this->pickSponsored($viewer, [...$shownListingIds, ...array_filter([$featured?->id])]);
        $ad = $this->pickAd($mode, $shownAdIds);

        $promoted = array_filter([
            'featured' => $featured,
            'sponsored' => $sponsored,
            'advertisement' => $ad,
        ]);

        $organicExcludeIds = [...$shownListingIds, ...array_filter([$featured?->id, $sponsored?->id])];
        $organicNeeded = max(0, $batchSize - count($promoted));
        $organic = $this->organicListings($viewer, $organicExcludeIds)->limit($organicNeeded)->get();

        $cards = $this->layout($organic, $promoted);

        $newShownListingIds = [
            ...$shownListingIds,
            ...$organic->pluck('id')->all(),
            ...array_filter([$featured?->id, $sponsored?->id]),
        ];
        $newShownAdIds = $ad instanceof LandingAd ? [...$shownAdIds, $ad->id] : $shownAdIds;

        return [
            'cards' => $cards,
            'shown_listing_ids' => array_values(array_unique($newShownListingIds)),
            'shown_ad_ids' => array_values(array_unique($newShownAdIds)),
            'has_more' => $this->hasMoreAfter($viewer, $mode, $newShownListingIds, $newShownAdIds),
        ];
    }

    private function baseEligible(?User $viewer): Builder
    {
        return Listing::query()
            ->with(['category:id,name,slug', 'images', 'specValues.specField'])
            ->approved()
            ->notExpired()
            ->visibleTo($viewer);
    }

    private function pickFeatured(?User $viewer, array $excludeIds): ?Listing
    {
        return $this->baseEligible($viewer)
            ->whereNotIn('id', $excludeIds)
            ->currentlyBoosted()
            ->inRandomOrder()
            ->first();
    }

    private function pickSponsored(?User $viewer, array $excludeIds): ?Listing
    {
        return $this->baseEligible($viewer)
            ->whereNotIn('id', $excludeIds)
            ->currentlySponsored()
            ->inRandomOrder()
            ->first();
    }

    private function pickAd(string $mode, array $excludeIds): ?LandingAd
    {
        return LandingAd::query()
            ->eligibleForFeed()
            ->forMode($mode)
            ->whereNotIn('id', $excludeIds)
            ->inRandomOrder()
            ->first();
    }

    /**
     * @param  array<int, int>  $excludeIds
     */
    private function organicListings(?User $viewer, array $excludeIds): Builder
    {
        return $this->baseEligible($viewer)
            ->whereNotIn('id', $excludeIds)
            ->whereDoesntHave('boosts', fn (Builder $q) => $q->where('is_active', true)->where('ends_at', '>', now()))
            ->where(fn (Builder $q) => $q->where('promotional_type', '!=', Listing::PromoSponsored)->orWhereNull('promotional_type'))
            ->withExists(['boosts as has_active_boost' => fn (Builder $q) => $q
                ->where('is_active', true)
                ->where('ends_at', '>', now())])
            ->orderByDesc('has_active_boost')
            ->latest('approved_at')
            ->latest();
    }

    /**
     * @param  array<int, int>  $excludedListingIds
     * @param  array<int, int>  $excludedAdIds
     */
    private function hasMoreAfter(?User $viewer, string $mode, array $excludedListingIds, array $excludedAdIds): bool
    {
        $listingExists = $this->baseEligible($viewer)->whereNotIn('id', $excludedListingIds)->exists();
        $adExists = LandingAd::query()->eligibleForFeed()->forMode($mode)->whereNotIn('id', $excludedAdIds)->exists();

        return $listingExists || $adExists;
    }

    /**
     * Interleaves promoted cards among the organic ones so promoted
     * placements are never consecutive and the first position is an
     * ordinary listing whenever one is available.
     *
     * @param  Collection<int, Listing>  $organic
     * @param  array<string, Listing|LandingAd>  $promoted
     * @return array<int, array{type: string, listing: ?Listing, ad: ?LandingAd}>
     */
    private function layout(Collection $organic, array $promoted): array
    {
        $total = $organic->count() + count($promoted);

        if ($total === 0) {
            return [];
        }

        $availablePositions = range(0, $total - 1);
        $preferred = array_values(array_filter($availablePositions, fn (int $p): bool => $p !== 0));
        shuffle($preferred);

        $placements = [];

        foreach ($promoted as $type => $item) {
            $chosen = null;

            foreach ($preferred as $position) {
                if (isset($placements[$position])) {
                    continue;
                }

                if (isset($placements[$position - 1]) || isset($placements[$position + 1])) {
                    continue;
                }

                $chosen = $position;
                break;
            }

            if ($chosen === null) {
                foreach ($preferred as $position) {
                    if (! isset($placements[$position])) {
                        $chosen = $position;
                        break;
                    }
                }
            }

            if ($chosen === null && ! isset($placements[0])) {
                $chosen = 0;
            }

            if ($chosen !== null) {
                $placements[$chosen] = ['type' => $type, 'item' => $item];
                $preferred = array_values(array_diff($preferred, [$chosen]));
            }
        }

        $organicQueue = $organic->values()->all();
        $cards = [];

        for ($position = 0; $position < $total; $position++) {
            if (isset($placements[$position])) {
                $type = $placements[$position]['type'];
                $item = $placements[$position]['item'];

                $cards[] = [
                    'type' => $type,
                    'listing' => $type === 'advertisement' ? null : $item,
                    'ad' => $type === 'advertisement' ? $item : null,
                ];

                continue;
            }

            $listing = array_shift($organicQueue);

            if ($listing === null) {
                continue;
            }

            $cards[] = ['type' => 'organic', 'listing' => $listing, 'ad' => null];
        }

        return $cards;
    }
}
