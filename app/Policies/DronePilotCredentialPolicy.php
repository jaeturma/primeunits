<?php

namespace App\Policies;

use App\Models\DronePilotCredential;
use App\Models\User;

class DronePilotCredentialPolicy
{
    public function view(User $user, DronePilotCredential $credential): bool
    {
        return $user->id === $credential->user_id
            || $user->hasPermission('review_drone_credentials');
    }

    public function viewDocuments(User $user, DronePilotCredential $credential): bool
    {
        return $user->id === $credential->user_id
            || $user->hasPermission('view_drone_credential_documents');
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, DronePilotCredential $credential): bool
    {
        return $user->id === $credential->user_id
            && in_array($credential->status, [
                DronePilotCredential::StatusPendingReview,
                DronePilotCredential::StatusRejected,
            ], true);
    }

    public function review(User $user, DronePilotCredential $credential): bool
    {
        return $user->hasPermission('review_drone_credentials');
    }
}
