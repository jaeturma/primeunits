<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * The reviewed upgrade/downgrade workflow that changes a user's
 * MembershipAccess levels or a store's tier. Covers buyer, seller, and
 * store applications through one shared status pipeline (matching the
 * project's existing single-table-per-workflow pattern, e.g.
 * DronePilotCredential) rather than three separate tables, while `type`
 * keeps the three flows independently queryable and reviewable.
 */
#[Fillable([
    'user_id',
    'type',
    'target_level',
    'dealer_profile_id',
    'status',
    'applicant_notes',
    'reviewer_id',
    'reviewer_notes',
    'applicant_visible_notes',
    'rejection_reason',
    'submitted_at',
    'reviewed_at',
    'expires_at',
])]
class MembershipApplication extends Model
{
    public const TypeBuyer = 'buyer';

    public const TypeSeller = 'seller';

    public const TypeStore = 'store';

    public const StatusDraft = 'draft';

    public const StatusInvited = 'invited';

    public const StatusSubmitted = 'submitted';

    public const StatusPendingReview = 'pending_review';

    public const StatusInfoRequired = 'info_required';

    public const StatusApproved = 'approved';

    public const StatusRejected = 'rejected';

    public const StatusDeclined = 'declined';

    public const StatusWithdrawn = 'withdrawn';

    public const StatusSuspended = 'suspended';

    public const StatusExpired = 'expired';

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

    /**
     * @return BelongsTo<DealerProfile, $this>
     */
    public function dealerProfile(): BelongsTo
    {
        return $this->belongsTo(DealerProfile::class);
    }

    /**
     * @return HasMany<MembershipApplicationDocument, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(MembershipApplicationDocument::class);
    }

    /**
     * @return HasMany<MembershipApplicationLog, $this>
     */
    public function logs(): HasMany
    {
        return $this->hasMany(MembershipApplicationLog::class)->latest('created_at');
    }

    public function isOpen(): bool
    {
        return in_array($this->status, [
            self::StatusDraft,
            self::StatusSubmitted,
            self::StatusPendingReview,
            self::StatusInfoRequired,
        ], true);
    }

    public function isPendingAcceptance(): bool
    {
        return $this->status === self::StatusInvited;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::StatusInvited => 'Invited',
            self::StatusSubmitted => 'Submitted',
            self::StatusPendingReview => 'Pending Review',
            self::StatusInfoRequired => 'Additional Information Required',
            self::StatusApproved => 'Approved',
            self::StatusRejected => 'Rejected',
            self::StatusDeclined => 'Declined',
            self::StatusWithdrawn => 'Withdrawn',
            self::StatusSuspended => 'Suspended',
            self::StatusExpired => 'Expired',
            default => 'Draft',
        };
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }
}
