<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['rental_unit_id', 'name', 'duration_type', 'duration_value', 'price', 'inclusions', 'is_active'])]
class RentalPackage extends Model
{
    public const DurationHourly = 'hourly';

    public const DurationDaily = 'daily';

    public const DurationWeekly = 'weekly';

    public const DurationMonthly = 'monthly';

    public const DurationPerHectare = 'per_hectare';

    public const DurationFixedProject = 'fixed_project';

    /**
     * @return BelongsTo<RentalUnit, $this>
     */
    public function rentalUnit(): BelongsTo
    {
        return $this->belongsTo(RentalUnit::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
