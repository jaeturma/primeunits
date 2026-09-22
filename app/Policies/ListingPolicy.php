<?php

namespace App\Policies;

use App\Models\Listing;
use App\Models\User;

class ListingPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(?User $user, Listing $listing): bool
    {
        if ($listing->isApproved() && ! $listing->isExpired()) {
            return true;
        }

        if ($user === null) {
            return false;
        }

        return $user->id === $listing->user_id || $user->hasPermission('approve_listings');
    }

    public function create(User $user): bool
    {
        return $user->sellerProfile?->isVerified() === true
            || $user->dealerProfile?->isVerified() === true;
    }

    public function update(User $user, Listing $listing): bool
    {
        return $user->id === $listing->user_id;
    }

    public function delete(User $user, Listing $listing): bool
    {
        return $user->id === $listing->user_id || $user->hasPermission('approve_listings');
    }

    public function approve(User $user, Listing $listing): bool
    {
        return $user->hasPermission('approve_listings');
    }

    public function reject(User $user, Listing $listing): bool
    {
        return $user->hasPermission('approve_listings');
    }

    public function report(User $user, Listing $listing): bool
    {
        return $user->id !== $listing->user_id;
    }

    public function manageTier(User $user, Listing $listing): bool
    {
        return $user->hasPermission('review_premium_listings');
    }

    public function reviewGoldCandidate(User $user, Listing $listing): bool
    {
        return $user->hasPermission('review_gold_listings');
    }
}
