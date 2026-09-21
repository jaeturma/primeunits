<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'rental_booking_id',
    'drone_compliance_notice_id',
    'notice_version',
    'notice_text',
    'acknowledged_at',
    'ip_address',
])]
class DroneComplianceAcknowledgement extends Model
{
    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<RentalBooking, $this>
     */
    public function rentalBooking(): BelongsTo
    {
        return $this->belongsTo(RentalBooking::class);
    }

    /**
     * @return BelongsTo<DroneComplianceNotice, $this>
     */
    public function notice(): BelongsTo
    {
        return $this->belongsTo(DroneComplianceNotice::class, 'drone_compliance_notice_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'acknowledged_at' => 'datetime',
        ];
    }
}
