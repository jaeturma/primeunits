<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'lead_id',
    'listing_id',
    'agreed_price',
    'commission_rate',
    'commission_amount',
    'status',
    'buyer_confirmed',
    'seller_confirmed',
    'confirmed_at',
    'proof_file',
])]
class Transaction extends Model
{
    public const StatusPending = 'pending';

    public const StatusConfirmed = 'confirmed';

    public const StatusDisputed = 'disputed';

    /**
     * @return BelongsTo<Lead, $this>
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * @return BelongsTo<Listing, $this>
     */
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    /**
     * @return HasOne<CommissionLog, $this>
     */
    public function commissionLog(): HasOne
    {
        return $this->hasOne(CommissionLog::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::StatusConfirmed => 'Confirmed',
            self::StatusDisputed => 'Disputed',
            default => 'Pending',
        };
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'agreed_price' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'buyer_confirmed' => 'boolean',
            'seller_confirmed' => 'boolean',
            'confirmed_at' => 'datetime',
        ];
    }
}
