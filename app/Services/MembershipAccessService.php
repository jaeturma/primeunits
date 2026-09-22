<?php

namespace App\Services;

use App\Models\DealerProfile;
use App\Models\MembershipAccess;
use App\Models\MembershipApplication;
use App\Models\User;
use Illuminate\Validation\ValidationException;

/**
 * Applies the effect of an approved MembershipApplication onto a user's
 * MembershipAccess (or a store's tier), and validates marketplace-mode
 * switches server-side. This is the only place that is allowed to raise
 * a user's buyer_access_level or seller_access_level — approving an
 * application is the sole path, never a payment confirmation alone.
 */
class MembershipAccessService
{
    public function grantApplication(MembershipApplication $application): void
    {
        if ($application->type === MembershipApplication::TypeStore) {
            $this->grantStoreApplication($application);

            return;
        }

        $access = $application->user->membershipAccessOrDefault();

        if ($application->type === MembershipApplication::TypeBuyer) {
            $access->update([
                'buyer_access_level' => $application->target_level,
                'buyer_access_status' => MembershipAccess::StatusActive,
                'identity_verification_level' => $this->identityLevelFor($application->target_level, $access->identity_verification_level),
            ]);
        }

        if ($application->type === MembershipApplication::TypeSeller) {
            $access->update([
                'seller_access_level' => $application->target_level,
                'seller_access_status' => MembershipAccess::StatusActive,
            ]);
        }
    }

    private function grantStoreApplication(MembershipApplication $application): void
    {
        $dealerProfile = $application->dealerProfile;

        if ($dealerProfile === null) {
            return;
        }

        $storeTier = match ($application->target_level) {
            'gold' => DealerProfile::StoreTierGoldProfessional,
            'silver' => DealerProfile::StoreTierSilver,
            default => DealerProfile::StoreTierRegular,
        };

        $dealerProfile->update([
            'store_tier' => $storeTier,
            'store_tier_status' => DealerProfile::StoreTierStatusActive,
        ]);
    }

    private function identityLevelFor(string $targetLevel, string $currentLevel): string
    {
        $rank = ['none' => 0, MembershipAccess::IdentityRegular => 1, MembershipAccess::IdentityEnhanced => 2];
        $newLevel = $targetLevel === 'regular' ? MembershipAccess::IdentityRegular : MembershipAccess::IdentityEnhanced;

        return ($rank[$newLevel] ?? 0) > ($rank[$currentLevel] ?? 0) ? $newLevel : $currentLevel;
    }

    public function suspendAccess(User $user, string $type, string $reason): void
    {
        $access = $user->membershipAccessOrDefault();

        $access->update($type === MembershipApplication::TypeBuyer
            ? ['buyer_access_status' => MembershipAccess::StatusSuspended, 'is_restricted' => true, 'restricted_reason' => $reason]
            : ['seller_access_status' => MembershipAccess::StatusSuspended, 'is_restricted' => true, 'restricted_reason' => $reason]);
    }

    /**
     * Switches the user's stored marketplace mode preference after
     * verifying, server-side, that they are actually authorized for it.
     * Never trusts the browser's requested mode without this check.
     *
     * @throws ValidationException
     */
    public function switchMode(User $user, string $requestedMode): MembershipAccess
    {
        $access = $user->membershipAccessOrDefault();

        if (! in_array($requestedMode, $access->availableModes(), true)) {
            throw ValidationException::withMessages([
                'mode' => "You are not currently authorized for the {$requestedMode} marketplace.",
            ]);
        }

        $access->update(['current_mode' => $requestedMode]);

        return $access->fresh();
    }
}
