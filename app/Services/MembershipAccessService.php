<?php

namespace App\Services;

use App\Models\DealerProfile;
use App\Models\MembershipAccess;
use App\Models\MembershipApplication;
use App\Models\Plan;
use App\Models\Subscription;
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
    public function __construct(private NotificationService $notifications) {}

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

    /**
     * Reflects real Subscription expiry onto MembershipAccess.buyer_access_status,
     * so a lapsed Silver/Gold membership actually loses premium access instead
     * of relying only on Subscription::isActive()'s on-the-fly check (which
     * nothing but this sync ever turns into an access change). Mirrors the
     * ExpireDronePilotCredentials command's rationale: the stored status
     * should reflect reality, not just be checkable on demand.
     *
     * A subscription past `ends_at` but still inside `grace_ends_at` moves to
     * Grace Period on the Subscription only — access is retained during
     * grace. Access is downgraded only once grace has also passed, and only
     * when buyer_access_level still matches the lapsed plan's tier (an admin
     * may have already changed it independently).
     *
     * @return array{grace: int, expired: int}
     */
    public function syncExpiredMemberships(): array
    {
        $counts = ['grace' => 0, 'expired' => 0];

        Subscription::query()
            ->whereIn('status', [Subscription::StatusActive, Subscription::StatusGracePeriod])
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', now())
            ->whereHas('plan', fn ($query) => $query->where('type', Plan::TypeMembership)->where('tier', '!=', Plan::TierRegular))
            ->with(['user.membershipAccess', 'plan'])
            ->chunkById(100, function ($subscriptions) use (&$counts): void {
                foreach ($subscriptions as $subscription) {
                    // Check the grace window by date, not Subscription::isInGracePeriod():
                    // that method also returns true purely because status is
                    // already "grace_period", which would keep a subscription
                    // stuck there forever once grace_ends_at itself has passed.
                    $stillInGraceWindow = $subscription->grace_ends_at !== null && $subscription->grace_ends_at->isFuture();

                    if ($stillInGraceWindow) {
                        if ($subscription->status !== Subscription::StatusGracePeriod) {
                            $subscription->update(['status' => Subscription::StatusGracePeriod]);
                            $counts['grace']++;
                        }

                        continue;
                    }

                    $subscription->update(['status' => Subscription::StatusExpired]);

                    $access = $subscription->user?->membershipAccess;

                    if ($access !== null
                        && $access->buyer_access_level === $subscription->plan->tier
                        && $access->buyer_access_status === MembershipAccess::StatusActive
                    ) {
                        $access->update(['buyer_access_status' => MembershipAccess::StatusExpired]);

                        $this->notifications->send(
                            user: $subscription->user,
                            event: 'membership.expired',
                            title: 'Membership expired',
                            message: "Your PrimeUnits {$subscription->plan->name} membership has expired. Renew to keep access to premium listings.",
                            url: '/settings/membership',
                        );
                    }

                    $counts['expired']++;
                }
            });

        return $counts;
    }
}
