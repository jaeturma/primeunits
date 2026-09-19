<?php

namespace App\Policies;

use App\Models\SellerProfile;
use App\Models\User;

class SellerProfilePolicy
{
    public function view(User $user, SellerProfile $sellerProfile): bool
    {
        return $user->id === $sellerProfile->user_id
            || $user->hasPermission('verify_sellers');
    }

    public function update(User $user, SellerProfile $sellerProfile): bool
    {
        return $user->id === $sellerProfile->user_id;
    }

    public function approve(User $user, SellerProfile $sellerProfile): bool
    {
        return $user->hasPermission('verify_sellers');
    }

    public function reject(User $user, SellerProfile $sellerProfile): bool
    {
        return $user->hasPermission('verify_sellers');
    }
}
