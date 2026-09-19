<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable([
    'user_id',
    'seller_type',
    'business_name',
    'owner_name',
    'contact_number',
    'email',
    'region',
    'province',
    'municipality',
    'barangay',
    'full_address',
    'permit_number',
    'permit_file',
    'accreditation',
    'accreditation_file',
    'representative_name',
    'representative_contact',
    'representative_id_file',
    'status',
    'verified_at',
    'rejected_reason',
    'valid_id_file',
    'selfie_file',
])]
class SellerProfile extends Model
{
    public const StatusPending = 'pending';

    public const StatusVerified = 'verified';

    public const StatusRejected = 'rejected';

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

    public function isRejected(): bool
    {
        return $this->status === self::StatusRejected;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::StatusVerified => 'Verified Seller',
            self::StatusRejected => 'Rejected',
            default => 'Under review',
        };
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
        ];
    }
}
