<?php

namespace App\Services;

use App\Models\Listing;
use App\Models\RentalUnit;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ApprovalWorkflowService
{
    public function advance(Listing|RentalUnit $resource, User $reviewer): void
    {
        if ($resource->status === Listing::StatusManagerAccepted && $reviewer->hasRole(['admin', 'superadmin'])) {
            abort_unless($this->hasVerifiedOwnerOrIdentityDocuments($resource), 422, 'Approved owner profile or valid ID and OR/CR documents are required.');
        }

        $attributes = match (true) {
            $resource->status === Listing::StatusPending && $reviewer->hasRole('coordinator') => [
                'status' => Listing::StatusAgentValidated,
                'agent_validated_by' => $reviewer->id,
                'agent_validated_at' => now(),
            ],
            $resource->status === Listing::StatusAgentValidated && $reviewer->hasRole('manager') => [
                'status' => Listing::StatusManagerAccepted,
                'manager_accepted_by' => $reviewer->id,
                'manager_accepted_at' => now(),
            ],
            $resource->status === Listing::StatusManagerAccepted && $reviewer->hasRole(['admin', 'superadmin']) => [
                'status' => Listing::StatusApproved,
                'approved_by' => $reviewer->id,
                'approved_at' => now(),
                'rejected_reason' => null,
            ],
            default => throw new HttpException(403, 'This item is not ready for your approval stage.'),
        };

        $resource->update($attributes);
    }

    private function hasVerifiedOwnerOrIdentityDocuments(Listing|RentalUnit $resource): bool
    {
        if ($resource instanceof RentalUnit) {
            return $resource->user->rentalProfile?->isApproved() === true
                || (filled($resource->valid_id_file) && filled($resource->or_cr_file));
        }

        return $resource->user->sellerProfile?->isVerified() === true
            || $resource->user->dealerProfile?->isVerified() === true
            || (filled($resource->valid_id_file) && filled($resource->or_cr_file));
    }

    public function actionLabel(Model $resource, User $reviewer): ?string
    {
        return match (true) {
            $resource->status === Listing::StatusPending && $reviewer->hasRole('coordinator') => 'Validate',
            $resource->status === Listing::StatusAgentValidated && $reviewer->hasRole('manager') => 'Accept',
            $resource->status === Listing::StatusManagerAccepted && $reviewer->hasRole(['admin', 'superadmin']) => 'Approve',
            default => null,
        };
    }
}
