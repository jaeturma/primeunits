<?php

namespace App\Services;

use App\Models\ConfidentialAccessLog;
use App\Models\Listing;
use App\Models\ListingAccessRequest;
use App\Models\MembershipAccess;
use App\Models\User;

/**
 * The single place that decides whether a viewer may see a listing's
 * full (confidential) details versus only its redacted public preview,
 * and logs every confidential-content decision. Marketplace tier and
 * listing visibility are deliberately different axes here: tier governs
 * browsing/mode filtering and seller requirements, visibility governs
 * what a specific viewer is allowed to see on this specific listing.
 */
class ListingVisibilityService
{
    public function canViewFull(?User $user, Listing $listing): bool
    {
        return match ($listing->visibility_level) {
            Listing::VisibilityPublic => true,
            Listing::VisibilityPublicPreview => $this->hasStandingAccess($user, $listing),
            Listing::VisibilitySilverExclusive => $this->buyerAccess($user)?->hasBuyerAccessAtLeast(MembershipAccess::LevelSilver) === true,
            Listing::VisibilityGoldExclusive => $this->buyerAccess($user)?->hasBuyerAccessAtLeast(MembershipAccess::LevelGold) === true,
            Listing::VisibilityVerifiedBuyerOnly => $this->buyerAccess($user)?->hasBuyerAccessAtLeast(MembershipAccess::LevelRegular) === true,
            Listing::VisibilityInvitationOnly => $this->hasApprovedAccessRequest($user, $listing),
            default => true,
        };
    }

    /**
     * Whether the listing page itself is reachable at all for this
     * viewer (as opposed to whether full details are visible). Public
     * and Public Preview listings are always reachable so their preview
     * can be shown; the other levels require at least a minimum buyer
     * access level to know the listing exists.
     */
    public function canViewPage(?User $user, Listing $listing): bool
    {
        if (in_array($listing->visibility_level, [Listing::VisibilityPublic, Listing::VisibilityPublicPreview], true)) {
            return true;
        }

        return $this->canViewFull($user, $listing) || $this->canRequestAccess($user, $listing);
    }

    public function canRequestAccess(?User $user, Listing $listing): bool
    {
        if ($user === null || ! $listing->isRestrictedVisibility()) {
            return false;
        }

        return $this->buyerAccess($user)?->hasBuyerAccessAtLeast(MembershipAccess::LevelRegular) === true;
    }

    /**
     * The redacted, non-confidential payload shown when the viewer
     * cannot see full details. Never includes owner identity, exact
     * location, registration/serial numbers, or private documents.
     *
     * @return array<string, mixed>
     */
    public function redactedPreview(Listing $listing): array
    {
        return [
            'category' => $listing->category?->name,
            'description' => $listing->public_preview_summary ?? $this->genericSummary($listing),
            'price' => $listing->price_on_request ? null : $listing->price,
            'price_on_request' => $listing->price_on_request,
            'region' => $listing->region,
            'tier' => $listing->marketplace_tier,
            'tier_label' => $listing->tierLabel(),
            'seller_capacity' => $listing->seller_capacity,
            'seller_capacity_label' => $listing->sellerCapacityLabel(),
        ];
    }

    public function logAccess(?User $user, Listing $listing, string $action): void
    {
        ConfidentialAccessLog::query()->create([
            'user_id' => $user?->id,
            'listing_id' => $listing->id,
            'action' => $action,
            'ip_address' => request()?->ip(),
        ]);
    }

    private function hasStandingAccess(?User $user, Listing $listing): bool
    {
        if ($user === null) {
            return false;
        }

        if ($listing->user_id === $user->id) {
            return true;
        }

        $requiredLevel = match ($listing->marketplace_tier) {
            Listing::TierGold, Listing::TierGoldEnterprise => MembershipAccess::LevelGold,
            Listing::TierSilver => MembershipAccess::LevelSilver,
            default => MembershipAccess::LevelRegular,
        };

        if ($this->buyerAccess($user)?->hasBuyerAccessAtLeast($requiredLevel) === true) {
            return true;
        }

        return $this->hasApprovedAccessRequest($user, $listing);
    }

    private function hasApprovedAccessRequest(?User $user, Listing $listing): bool
    {
        if ($user === null) {
            return false;
        }

        if ($listing->user_id === $user->id) {
            return true;
        }

        return $listing->accessRequests()
            ->where('user_id', $user->id)
            ->where('status', ListingAccessRequest::StatusApproved)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->exists();
    }

    private function buyerAccess(?User $user): ?MembershipAccess
    {
        return $user?->membershipAccess;
    }

    private function genericSummary(Listing $listing): string
    {
        return $listing->tierLabel().' listing in the '.($listing->category?->name ?? 'marketplace').' category.';
    }
}
