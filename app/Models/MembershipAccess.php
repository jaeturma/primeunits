<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The resolved, fast-read authorization state for one user: separate
 * buyer and seller access levels, identity verification level, and
 * marketplace-mode preference. This is deliberately not the same record
 * as a membership Subscription (payment/plan/billing state) or a
 * MembershipApplication (the reviewed workflow that changes these
 * levels) — see the class docs on those models for why they stay apart.
 */
#[Fillable([
    'user_id',
    'identity_verification_level',
    'buyer_access_level',
    'buyer_access_status',
    'seller_access_level',
    'seller_access_status',
    'is_restricted',
    'restricted_reason',
    'current_mode',
])]
class MembershipAccess extends Model
{
    public const LevelNone = 'none';

    public const LevelRegular = 'regular';

    public const LevelSilver = 'silver';

    public const LevelGold = 'gold';

    public const IdentityNone = 'none';

    public const IdentityRegular = 'regular';

    public const IdentityEnhanced = 'enhanced';

    public const StatusActive = 'active';

    public const StatusSuspended = 'suspended';

    public const StatusRevoked = 'revoked';

    public const StatusExpired = 'expired';

    /**
     * Marketplace tiers in ascending order, used to rank/compare levels.
     *
     * @var array<string, int>
     */
    private const array TIER_RANK = [
        self::LevelNone => 0,
        self::LevelRegular => 1,
        self::LevelSilver => 2,
        self::LevelGold => 3,
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hasBuyerAccessAtLeast(string $level): bool
    {
        return $this->buyer_access_status === self::StatusActive
            && $this->tierRank($this->buyer_access_level) >= $this->tierRank($level);
    }

    public function hasSellerAccessAtLeast(string $level): bool
    {
        return $this->seller_access_status === self::StatusActive
            && $this->tierRank($this->seller_access_level) >= $this->tierRank($level);
    }

    /**
     * The highest marketplace mode this user may currently browse in,
     * used as the safe fallback when a preferred mode is no longer
     * authorized (e.g. after suspension or expiration).
     */
    public function highestAuthorizedMode(): string
    {
        return match (true) {
            $this->hasBuyerAccessAtLeast(self::LevelGold) => self::LevelGold,
            $this->hasBuyerAccessAtLeast(self::LevelSilver) => self::LevelSilver,
            default => self::LevelRegular,
        };
    }

    /**
     * @return array<int, string>
     */
    public function availableModes(): array
    {
        $modes = [self::LevelRegular];

        if ($this->hasBuyerAccessAtLeast(self::LevelSilver)) {
            $modes[] = self::LevelSilver;
        }

        if ($this->hasBuyerAccessAtLeast(self::LevelGold)) {
            $modes[] = self::LevelGold;
        }

        return $modes;
    }

    /**
     * The mode to actually render the app in right now: the user's
     * stored preference when still authorized, otherwise the highest
     * mode they currently qualify for.
     */
    public function effectiveMode(): string
    {
        if (in_array($this->current_mode, $this->availableModes(), true)) {
            return $this->current_mode;
        }

        return $this->highestAuthorizedMode();
    }

    private function tierRank(?string $level): int
    {
        return self::TIER_RANK[$level] ?? 0;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_restricted' => 'boolean',
        ];
    }
}
