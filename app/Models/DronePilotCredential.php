<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'credential_type',
    'issuing_authority',
    'credential_number',
    'issue_date',
    'expiration_date',
    'country',
    'front_document_path',
    'back_document_path',
    'supporting_document_path',
    'status',
    'reviewer_id',
    'reviewed_at',
    'reviewer_notes',
    'rejection_reason',
    'suspension_reason',
])]
class DronePilotCredential extends Model
{
    public const StatusPendingReview = 'pending_review';

    public const StatusVerified = 'verified';

    public const StatusRejected = 'rejected';

    public const StatusExpired = 'expired';

    public const StatusSuspended = 'suspended';

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
     * @return HasMany<DroneCredentialAuditLog, $this>
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(DroneCredentialAuditLog::class)->latest('created_at');
    }

    public function isVerified(): bool
    {
        return $this->status === self::StatusVerified;
    }

    public function isExpired(): bool
    {
        if ($this->expiration_date === null) {
            return false;
        }

        return $this->expiration_date->isPast();
    }

    public function isActiveAndUnexpired(): bool
    {
        return $this->isVerified() && ! $this->isExpired();
    }

    public function maskedCredentialNumber(): ?string
    {
        if (blank($this->credential_number)) {
            return null;
        }

        $number = (string) $this->credential_number;
        $visible = min(4, strlen($number));
        $masked = str_repeat('*', max(0, strlen($number) - $visible));

        return $masked.substr($number, -$visible);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::StatusVerified => 'Verified',
            self::StatusRejected => 'Rejected',
            self::StatusExpired => 'Expired',
            self::StatusSuspended => 'Suspended',
            default => 'Pending Review',
        };
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'expiration_date' => 'date',
            'reviewed_at' => 'datetime',
        ];
    }
}
