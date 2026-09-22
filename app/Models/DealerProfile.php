<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable([
    'user_id',
    'business_name',
    'slug',
    'accreditation_number',
    'accreditation_file',
    'logo',
    'banner',
    'contact_number',
    'email',
    'website',
    'description',
    'region',
    'province',
    'municipality',
    'barangay',
    'full_address',
    'lat',
    'lng',
    'status',
    'verified_at',
    'rejected_reason',
    'manager_validated_by', 'manager_validated_at', 'approved_by',
    'store_tier', 'store_tier_status', 'store_tier_notes',
])]
class DealerProfile extends Model
{
    public const StatusPending = 'pending';

    public const StatusVerified = 'verified';

    public const StatusManagerValidated = 'manager_validated';

    public const StatusRejected = 'rejected';

    public const StoreTierRegular = 'regular';

    public const StoreTierSilver = 'silver';

    public const StoreTierGoldProfessional = 'gold_professional';

    public const StoreTierStatusPending = 'pending';

    public const StoreTierStatusActive = 'active';

    public const StoreTierStatusSuspended = 'suspended';

    public const StoreTierStatusRejected = 'rejected';

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Listing, $this>
     */
    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    /**
     * @return MorphMany<ResourceAttachment, $this>
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(ResourceAttachment::class, 'attachable')->latest();
    }

    public function isVerified(): bool
    {
        return $this->status === self::StatusVerified;
    }

    public function hasActiveStoreTierAtLeast(string $tier): bool
    {
        if ($this->store_tier_status !== self::StoreTierStatusActive) {
            return false;
        }

        $rank = [self::StoreTierRegular => 1, self::StoreTierSilver => 2, self::StoreTierGoldProfessional => 3];

        return ($rank[$this->store_tier] ?? 0) >= ($rank[$tier] ?? 0);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::StatusVerified => 'Verified',
            self::StatusManagerValidated => 'Manager Validated',
            self::StatusRejected => 'Rejected',
            default => 'Pending',
        };
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
            'manager_validated_at' => 'datetime',
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
        ];
    }
}
