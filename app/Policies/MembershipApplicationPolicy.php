<?php

namespace App\Policies;

use App\Models\MembershipApplication;
use App\Models\User;

class MembershipApplicationPolicy
{
    public function view(User $user, MembershipApplication $application): bool
    {
        return $user->id === $application->user_id
            || $user->hasPermission('review_membership_applications');
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, MembershipApplication $application): bool
    {
        return $user->id === $application->user_id && $application->isOpen();
    }

    public function withdraw(User $user, MembershipApplication $application): bool
    {
        return $user->id === $application->user_id && $application->isOpen();
    }

    public function respond(User $user, MembershipApplication $application): bool
    {
        return $user->id === $application->user_id && $application->isPendingAcceptance();
    }

    public function invite(User $user): bool
    {
        return $user->hasPermission(['review_buyer_access', 'review_seller_access']);
    }

    public function review(User $user, MembershipApplication $application): bool
    {
        return match ($application->type) {
            MembershipApplication::TypeBuyer => $user->hasPermission('review_buyer_access'),
            MembershipApplication::TypeSeller => $user->hasPermission('review_seller_access'),
            MembershipApplication::TypeStore => $user->hasPermission('review_stores'),
            default => false,
        };
    }
}
