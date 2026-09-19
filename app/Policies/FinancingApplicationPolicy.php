<?php

namespace App\Policies;

use App\Models\FinancingApplication;
use App\Models\User;

class FinancingApplicationPolicy
{
    public function view(User $user, FinancingApplication $application): bool
    {
        if ($user->id === $application->user_id) {
            return true;
        }

        if ($user->financingPartner?->id === $application->financing_partner_id) {
            return true;
        }

        return $user->hasPermission('view_reports');
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function review(User $user, FinancingApplication $application): bool
    {
        return $user->financingPartner?->id === $application->financing_partner_id
            && $user->financingPartner->isVerified();
    }
}
