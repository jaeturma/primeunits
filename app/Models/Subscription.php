<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

#[Fillable(['user_id', 'plan_id', 'starts_at', 'ends_at', 'grace_ends_at', 'status'])]
class Subscription extends Model
{
    public const StatusTrial = 'trial';

    public const StatusPendingPayment = 'pending_payment';

    public const StatusPending = 'pending';

    public const StatusPendingReview = 'pending_review';

    public const StatusActive = 'active';

    public const StatusGracePeriod = 'grace_period';

    public const StatusExpired = 'expired';

    public const StatusSuspended = 'suspended';

    public const StatusCancelled = 'cancelled';

    public const StatusBanned = 'banned';

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Plan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * @return MorphOne<Payment, $this>
     */
    public function payment(): MorphOne
    {
        return $this->morphOne(Payment::class, 'payable');
    }

    public function isActive(): bool
    {
        return in_array($this->status, [self::StatusActive, self::StatusTrial, self::StatusGracePeriod], true)
            && ($this->ends_at === null || $this->ends_at->isFuture() || $this->isInGracePeriod());
    }

    public function isInGracePeriod(): bool
    {
        return $this->status === self::StatusGracePeriod
            || ($this->grace_ends_at !== null && $this->grace_ends_at->isFuture());
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::StatusTrial => 'Trial',
            self::StatusPendingPayment => 'Pending Payment',
            self::StatusPendingReview => 'Pending Review',
            self::StatusActive => 'Active',
            self::StatusGracePeriod => 'Grace Period',
            self::StatusExpired => 'Expired',
            self::StatusSuspended => 'Suspended',
            self::StatusCancelled => 'Cancelled',
            self::StatusBanned => 'Banned',
            default => 'Pending',
        };
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'grace_ends_at' => 'datetime',
        ];
    }
}
