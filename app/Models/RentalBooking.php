<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'rental_unit_id',
    'renter_id',
    'provider_id',
    'rental_package_id',
    'reference_code',
    'start_date',
    'end_date',
    'pickup_address',
    'dropoff_address',
    'quoted_price',
    'status',
    'message',
    'notes',
    'confirmed_at',
    'cancelled_at',
])]
class RentalBooking extends Model
{
    public const StatusInquiry = 'inquiry';

    public const StatusConfirmed = 'confirmed';

    public const StatusActive = 'active';

    public const StatusCompleted = 'completed';

    public const StatusCancelled = 'cancelled';

    /**
     * @return BelongsTo<RentalUnit, $this>
     */
    public function rentalUnit(): BelongsTo
    {
        return $this->belongsTo(RentalUnit::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function renter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'renter_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    /**
     * @return BelongsTo<RentalPackage, $this>
     */
    public function rentalPackage(): BelongsTo
    {
        return $this->belongsTo(RentalPackage::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::StatusConfirmed => 'Confirmed',
            self::StatusActive => 'Active',
            self::StatusCompleted => 'Completed',
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
            'start_date' => 'date',
            'end_date' => 'date',
            'quoted_price' => 'decimal:2',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }
}
