<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'listing_id',
    'user_id',
    'status',
    'message',
    'reviewer_id',
    'reviewed_at',
    'rejection_reason',
    'expires_at',
])]
class ListingAccessRequest extends Model
{
    public const StatusPending = 'pending';

    public const StatusApproved = 'approved';

    public const StatusRejected = 'rejected';

    public const StatusRevoked = 'revoked';

    public const StatusExpired = 'expired';

    /**
     * @return BelongsTo<Listing, $this>
     */
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function isGrantedAndUnexpired(): bool
    {
        return $this->status === self::StatusApproved
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::StatusApproved => 'Approved',
            self::StatusRejected => 'Rejected',
            self::StatusRevoked => 'Revoked',
            self::StatusExpired => 'Expired',
            default => 'Pending',
        };
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }
}
