<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'type', 'tier', 'price', 'duration_days', 'features', 'is_active'])]
class Plan extends Model
{
    public const TypeBoost = 'boost';

    public const TypeSubscription = 'subscription';

    public const TypeMembership = 'membership';

    public const TierRegular = 'regular';

    public const TierSilver = 'silver';

    public const TierGold = 'gold';

    /**
     * @return HasMany<Subscription, $this>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * @return HasMany<ListingBoost, $this>
     */
    public function listingBoosts(): HasMany
    {
        return $this->hasMany(ListingBoost::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'features' => 'array',
            'is_active' => 'boolean',
            'price' => 'decimal:2',
        ];
    }
}
