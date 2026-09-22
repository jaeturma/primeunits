<?php

namespace App\Policies;

use App\Models\ListingAccessRequest;
use App\Models\User;

class ListingAccessRequestPolicy
{
    public function view(User $user, ListingAccessRequest $request): bool
    {
        return $user->id === $request->user_id
            || $user->id === $request->listing->user_id
            || $user->hasPermission('manage_listing_access');
    }

    public function review(User $user, ListingAccessRequest $request): bool
    {
        return $user->id === $request->listing->user_id
            || $user->hasPermission('manage_listing_access');
    }

    public function revoke(User $user, ListingAccessRequest $request): bool
    {
        return $this->review($user, $request);
    }
}
