<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['version', 'body', 'is_active', 'published_at'])]
class DroneComplianceNotice extends Model
{
    public const DEFAULT_NOTICE_TEXT = 'Operation of agricultural drones may require a qualified or licensed remote pilot and authorization from the appropriate aviation or government authority. The renter and operator are responsible for complying with applicable laws, safety requirements, operating restrictions, and local regulations.';

    /**
     * @return HasMany<DroneComplianceAcknowledgement, $this>
     */
    public function acknowledgements(): HasMany
    {
        return $this->hasMany(DroneComplianceAcknowledgement::class);
    }

    public static function active(): ?self
    {
        return static::query()->where('is_active', true)->latest('published_at')->first();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'published_at' => 'datetime',
        ];
    }
}
