<?php

namespace App\Policies;

use App\Models\DealerProfile;
use App\Models\User;

class DealerProfilePolicy
{
    public function view(?User $user, DealerProfile $dealerProfile): bool
    {
        return $dealerProfile->isVerified();
    }

    public function update(User $user, DealerProfile $dealerProfile): bool
    {
        return $user->id === $dealerProfile->user_id;
    }

    public function approve(User $user, DealerProfile $dealerProfile): bool
    {
        return $user->hasPermission('manage_dealers');
    }

    public function reject(User $user, DealerProfile $dealerProfile): bool
    {
        return $user->hasPermission('manage_dealers');
    }
}
