<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

#[Fillable([
    'user_id',
    'category_id',
    'rental_type',
    'name',
    'slug',
    'description',
    'brand',
    'model',
    'year_model',
    'capacity',
    'with_driver',
    'price_per_day',
    'price_per_hour',
    'price_per_hectare',
    'operator_fee',
    'transportation_fee',
    'security_deposit',
    'fuel_included',
    'operator_included',
    'transportation_included',
    'minimum_area_hectares',
    'minimum_rental_duration',
    'service_coverage_area',
    'requires_verified_drone_operator',
    'allows_self_operation',
    'intended_uses',
    'drone_pilot_user_id',
    'compliance_status',
    'region',
    'province',
    'municipality',
    'barangay',
    'status',
    'approved_at',
    'rejected_reason',
    'views_count',
    'agent_validated_by', 'agent_validated_at', 'manager_accepted_by', 'manager_accepted_at', 'approved_by', 'valid_id_file', 'or_cr_file',
])]
class RentalUnit extends Model
{
    public const StatusPending = 'pending';

    public const StatusAgentValidated = 'agent_validated';

    public const StatusManagerAccepted = 'manager_accepted';

    public const StatusApproved = 'approved';

    public const StatusRejected = 'rejected';

    public const TypeCarRental = 'car_rental';

    public const TypeVanRental = 'van_rental';

    public const TypeSelfDrive = 'self_drive';

    public const TypeChauffeur = 'chauffeur';

    public const TypeAirportTransfer = 'airport_transfer';

    public const TypeWeddingCar = 'wedding_car';

    public const TypeShuttle = 'shuttle';

    public const TypeBusRental = 'bus_rental';

    public const TypeTruckRental = 'truck_rental';

    public const TypeEquipmentRental = 'equipment_rental';

    public const TypeMotorcycleRental = 'motorcycle_rental';

    public const TypeHarvesterRental = 'harvester_rental';

    public const TypeHarvesterService = 'harvester_service';

    public const TypeDroneRental = 'drone_rental';

    public const TypeDroneService = 'drone_service';

    public const ComplianceNotApplicable = 'not_applicable';

    public const ComplianceVerifiedOperatorAssigned = 'verified_operator_assigned';

    public const ComplianceSelfOperationAllowed = 'self_operation_allowed';

    public const ComplianceUnresolved = 'unresolved';

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function dronePilot(): BelongsTo
    {
        return $this->belongsTo(User::class, 'drone_pilot_user_id');
    }

    /**
     * @return HasMany<RentalUnitImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(RentalUnitImage::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return MorphMany<ResourceAttachment, $this>
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(ResourceAttachment::class, 'attachable')->latest();
    }

    /**
     * @return HasMany<RentalPackage, $this>
     */
    public function packages(): HasMany
    {
        return $this->hasMany(RentalPackage::class)->where('is_active', true);
    }

    /**
     * @return HasMany<RentalAvailability, $this>
     */
    public function availability(): HasMany
    {
        return $this->hasMany(RentalAvailability::class);
    }

    /**
     * @return HasMany<RentalBooking, $this>
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(RentalBooking::class);
    }

    public function isApproved(): bool
    {
        return $this->status === self::StatusApproved;
    }

    public static function rentalTypes(): array
    {
        return [
            self::TypeCarRental => 'Car Rental',
            self::TypeVanRental => 'Van Rental',
            self::TypeSelfDrive => 'Self Drive',
            self::TypeChauffeur => 'Chauffeur Service',
            self::TypeAirportTransfer => 'Airport Transfer',
            self::TypeWeddingCar => 'Wedding Cars',
            self::TypeShuttle => 'Shuttle Service',
            self::TypeBusRental => 'Bus Rental',
            self::TypeTruckRental => 'Truck Rental',
            self::TypeEquipmentRental => 'Equipment Rental',
            self::TypeMotorcycleRental => 'Motorcycle Rental',
            self::TypeHarvesterRental => 'Harvester Rental',
            self::TypeHarvesterService => 'Harvester Service',
            self::TypeDroneRental => 'Drone Rental',
            self::TypeDroneService => 'Drone Service',
        ];
    }

    public function isDroneRelated(): bool
    {
        return in_array($this->rental_type, [self::TypeDroneRental, self::TypeDroneService], true);
    }

    public function isHarvesterRelated(): bool
    {
        return in_array($this->rental_type, [self::TypeHarvesterRental, self::TypeHarvesterService], true);
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (RentalUnit $unit): void {
            if (empty($unit->slug)) {
                $base = Str::slug($unit->name);
                $slug = $base;
                $count = 1;
                while (static::query()->where('slug', $slug)->exists()) {
                    $slug = "{$base}-{$count}";
                    $count++;
                }
                $unit->slug = $slug;
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'with_driver' => 'boolean',
            'price_per_day' => 'decimal:2',
            'price_per_hour' => 'decimal:2',
            'price_per_hectare' => 'decimal:2',
            'operator_fee' => 'decimal:2',
            'transportation_fee' => 'decimal:2',
            'security_deposit' => 'decimal:2',
            'fuel_included' => 'boolean',
            'operator_included' => 'boolean',
            'transportation_included' => 'boolean',
            'minimum_area_hectares' => 'decimal:2',
            'requires_verified_drone_operator' => 'boolean',
            'allows_self_operation' => 'boolean',
            'views_count' => 'integer',
            'agent_validated_at' => 'datetime',
            'manager_accepted_at' => 'datetime',
        ];
    }
}
