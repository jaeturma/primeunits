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
    'area_hectares',
    'pricing_unit',
    'base_amount',
    'operator_fee_amount',
    'transportation_fee_amount',
    'fuel_amount',
    'deposit_amount',
    'platform_fee_amount',
    'discount_amount',
    'tax_amount',
    'total_amount',
    'drone_pilot_user_id',
    'operator_verification_snapshot',
    'compliance_acknowledged_at',
    'compliance_notice_version',
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

    /**
     * @return BelongsTo<User, $this>
     */
    public function dronePilot(): BelongsTo
    {
        return $this->belongsTo(User::class, 'drone_pilot_user_id');
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
            'area_hectares' => 'decimal:2',
            'base_amount' => 'decimal:2',
            'operator_fee_amount' => 'decimal:2',
            'transportation_fee_amount' => 'decimal:2',
            'fuel_amount' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'platform_fee_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'operator_verification_snapshot' => 'array',
            'compliance_acknowledged_at' => 'datetime',
        ];
    }
}
