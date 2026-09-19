<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

#[Fillable([
    'listing_id',
    'buyer_id',
    'seller_id',
    'reference_code',
    'status',
    'message',
    'contacted_at',
    'negotiated_at',
    'closed_at',
])]
class Lead extends Model
{
    public const StatusInquiry = 'inquiry';

    public const StatusContacted = 'contacted';

    public const StatusNegotiating = 'negotiating';

    public const StatusReserved = 'reserved';

    public const StatusClosed = 'closed';

    public const StatusCancelled = 'cancelled';

    public static function generateReferenceCode(): string
    {
        do {
            $code = 'PU-'.Str::upper(Str::random(7));
        } while (self::query()->where('reference_code', $code)->exists());

        return $code;
    }

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
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * @return HasOne<Transaction, $this>
     */
    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class);
    }

    /**
     * @return HasMany<LeadMessage, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(LeadMessage::class)->orderBy('created_at')->orderBy('id');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::StatusContacted => 'Contacted',
            self::StatusNegotiating => 'Negotiating',
            self::StatusReserved => 'Reserved',
            self::StatusClosed => 'Closed',
            self::StatusCancelled => 'Cancelled',
            default => 'Inquiry',
        };
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'contacted_at' => 'datetime',
            'negotiated_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }
}
