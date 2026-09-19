<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

class LeadPolicy
{
    public function view(User $user, Lead $lead): bool
    {
        return $user->id === $lead->buyer_id
            || $user->id === $lead->seller_id
            || $user->hasPermission('view_reports');
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function updateStatus(User $user, Lead $lead): bool
    {
        return $user->id === $lead->seller_id;
    }

    public function sendMessage(User $user, Lead $lead): bool
    {
        return $user->id === $lead->buyer_id || $user->id === $lead->seller_id;
    }

    public function createTransaction(User $user, Lead $lead): bool
    {
        return $user->id === $lead->seller_id
            && $lead->status === Lead::StatusNegotiating;
    }
}
