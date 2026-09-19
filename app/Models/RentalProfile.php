<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'business_name', 'slug', 'contact_number', 'email', 'business_address', 'business_registration_file', 'valid_id_file', 'status', 'manager_validated_by', 'manager_validated_at', 'approved_by', 'approved_at', 'rejected_reason'])]
class RentalProfile extends Model
{
    public const StatusPending = 'pending';
    public const StatusManagerValidated = 'manager_validated';
    public const StatusApproved = 'approved';
    public const StatusRejected = 'rejected';

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function units(): HasMany { return $this->hasMany(RentalUnit::class, 'user_id', 'user_id'); }
    public function isApproved(): bool { return $this->status === self::StatusApproved; }

    protected function casts(): array
    {
        return ['manager_validated_at' => 'datetime', 'approved_at' => 'datetime'];
    }
}
