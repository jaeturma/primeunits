<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

#[Fillable([
    'user_id',
    'seller_profile_id',
    'dealer_profile_id',
    'category_id',
    'listing_type',
    'promotional_type',
    'title',
    'slug',
    'description',
    'price',
    'negotiable',
    'condition',
    'year_model',
    'brand',
    'model',
    'region',
    'province',
    'municipality',
    'barangay',
    'status',
    'approved_at',
    'rejected_reason',
    'expires_at',
    'views_count',
    'agent_validated_by', 'agent_validated_at', 'manager_accepted_by', 'manager_accepted_at', 'approved_by', 'valid_id_file', 'or_cr_file',
])]
class Listing extends Model
{
    public const StatusPending = 'pending';

    public const StatusAgentValidated = 'agent_validated';

    public const StatusManagerAccepted = 'manager_accepted';

    public const StatusApproved = 'approved';

    public const StatusRejected = 'rejected';

    public const ConditionBrandNew = 'brand_new';

    public const ConditionUsed = 'used';

    public const ConditionSurplus = 'surplus';

    public const ConditionReconditioned = 'reconditioned';

    public const TypeFree = 'FL';

    public const TypeCommissioned = 'CL';

    public const TypeDealer = 'DL';

    public const PromoNone = 'NONE';

    public const PromoSponsored = 'SL';

    public const PromoPrime = 'PL';

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<SellerProfile, $this>
     */
    public function sellerProfile(): BelongsTo
    {
        return $this->belongsTo(SellerProfile::class);
    }

    /**
     * @return BelongsTo<DealerProfile, $this>
     */
    public function dealerProfile(): BelongsTo
    {
        return $this->belongsTo(DealerProfile::class);
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<ListingImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(ListingImage::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return MorphMany<ResourceAttachment, $this>
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(ResourceAttachment::class, 'attachable')->latest();
    }

    /**
     * @return HasMany<ListingSpecValue, $this>
     */
    public function specValues(): HasMany
    {
        return $this->hasMany(ListingSpecValue::class);
    }

    /**
     * @return HasMany<Lead, $this>
     */
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    /**
     * @return HasMany<ListingBoost, $this>
     */
    public function boosts(): HasMany
    {
        return $this->hasMany(ListingBoost::class);
    }

    public function activeBoost(): HasMany
    {
        return $this->boosts()
            ->where('is_active', true)
            ->where('ends_at', '>', now());
    }

    /**
     * @return HasMany<Favorite, $this>
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * @return HasMany<ListingView, $this>
     */
    public function views(): HasMany
    {
        return $this->hasMany(ListingView::class);
    }

    /**
     * @return HasMany<ListingReport, $this>
     */
    public function reports(): HasMany
    {
        return $this->hasMany(ListingReport::class);
    }

    public function isApproved(): bool
    {
        return $this->status === self::StatusApproved;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::StatusApproved => 'Approved',
            self::StatusAgentValidated => 'Agent Validated',
            self::StatusManagerAccepted => 'Manager Accepted',
            self::StatusRejected => 'Rejected',
            default => 'Pending',
        };
    }

    public function listingTypeLabel(): string
    {
        return match ($this->listing_type) {
            self::TypeCommissioned => 'Commissioned',
            self::TypeDealer => 'Dealer',
            default => 'Free',
        };
    }

    public function promoLabel(): string
    {
        return match ($this->promotional_type) {
            self::PromoSponsored => 'Sponsored',
            self::PromoPrime => 'Prime',
            default => 'None',
        };
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::StatusApproved);
    }

    public function scopeNotExpired(Builder $query): Builder
    {
        return $query->where(function (Builder $q): void {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Electrified powertrain values, as seeded on the `fuel_type` spec
     * field. Used to make an "electric" keyword search or the Electric
     * Vehicles category chip also surface EV-powered Cars, Motorcycles,
     * or Commercial Vehicles listings, since PrimeUnits keeps EV as its
     * own browsing category while still tracking fuel type per listing.
     *
     * @return array<int, string>
     */
    public static function electrifiedFuelTypes(): array
    {
        return ['BEV', 'HEV', 'PHEV', 'eREV', 'FCEV'];
    }

    private static function mentionsElectric(string $search): bool
    {
        return preg_match('/\b(electric|ev|hybrid)\b/i', $search) === 1;
    }

    /**
     * Shared marketplace search/filter logic used by both the homepage
     * "Find a Unit" search and the full listings catalog, so the two
     * surfaces can never drift out of sync on how a filter behaves.
     */
    public function scopeSearch(Builder $query, Request $request): Builder
    {
        return $query
            ->when($request->filled('q'), function (Builder $query) use ($request): void {
                $search = $request->string('q')->toString();

                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");

                    if (self::mentionsElectric($search)) {
                        $query->orWhereHas('specValues', fn (Builder $query) => $query
                            ->whereIn('value', self::electrifiedFuelTypes())
                            ->whereHas('specField', fn (Builder $query) => $query->where('name', 'fuel_type')));
                    }
                });
            })
            ->when($request->filled('category'), fn (Builder $query) => $query->whereHas(
                'category',
                fn (Builder $query) => $query->where('slug', $request->string('category')->toString()),
            ))
            ->when($request->filled('classification'), function (Builder $query) use ($request): void {
                $classification = $request->string('classification')->toString();

                $query->whereHas('specValues', fn (Builder $query) => $query
                    ->where('value', $classification)
                    ->whereHas('specField', fn (Builder $query) => $query->where('is_classification', true)));
            })
            ->when($request->filled('brand'), fn (Builder $query) => $query->where('brand', $request->string('brand')->toString()))
            ->when($request->filled('condition'), fn (Builder $query) => $query->where('condition', $request->string('condition')->toString()))
            ->when($request->filled('min_price'), fn (Builder $query) => $query->where('price', '>=', $request->integer('min_price')))
            ->when($request->filled('max_price'), fn (Builder $query) => $query->where('price', '<=', $request->integer('max_price')))
            ->when($request->filled('region'), fn (Builder $query) => $query->where('region', $request->string('region')->toString()))
            ->when($request->filled('province'), fn (Builder $query) => $query->where('province', $request->string('province')->toString()))
            ->when($request->filled('municipality'), fn (Builder $query) => $query->where('municipality', $request->string('municipality')->toString()))
            ->when($request->filled('fuel_type'), fn (Builder $query) => $query->whereHas(
                'specValues',
                fn (Builder $query) => $query
                    ->where('value', $request->string('fuel_type')->toString())
                    ->whereHas('specField', fn (Builder $query) => $query->where('name', 'fuel_type')),
            ))
            ->when($request->filled('max_mileage'), fn (Builder $query) => $query->whereHas(
                'specValues',
                fn (Builder $query) => $query
                    ->whereRaw('CAST(value AS DECIMAL) <= ?', [$request->integer('max_mileage')])
                    ->whereHas('specField', fn (Builder $query) => $query->where('name', 'mileage')),
            ));
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Listing $listing): void {
            if (empty($listing->slug)) {
                $listing->slug = static::generateUniqueSlug($listing->title);
            }
        });

        static::updating(function (Listing $listing): void {
            if ($listing->isDirty('title') && empty($listing->slug)) {
                $listing->slug = static::generateUniqueSlug($listing->title);
            }
        });
    }

    private static function generateUniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $count = 1;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$count}";
            $count++;
        }

        return $slug;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'expires_at' => 'datetime',
            'negotiable' => 'boolean',
            'price' => 'decimal:2',
            'views_count' => 'integer',
            'agent_validated_at' => 'datetime',
            'manager_accepted_at' => 'datetime',
        ];
    }
}
