<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class FinancingPartner extends Model
{
    public const StatusPending = 'pending';
    public const StatusVerified = 'verified';
    public const StatusRejected = 'rejected';

    protected $fillable = [
        'user_id', 'company_name', 'slug', 'registration_number', 'license_number',
        'license_file', 'description', 'website', 'contact_number', 'contact_email',
        'logo', 'banner', 'region', 'province', 'municipality', 'full_address',
        'status', 'verified_at', 'rejected_reason',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $partner): void {
            if (empty($partner->slug)) {
                $partner->slug = self::generateSlug($partner->company_name);
            }
        });
    }

    private static function generateSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;
        while (self::query()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(FinancingProduct::class)->orderBy('sort_order');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(FinancingApplication::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(ResourceAttachment::class, 'attachable')->latest();
    }

    public function isVerified(): bool
    {
        return $this->status === self::StatusVerified;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::StatusVerified => 'Verified',
            self::StatusRejected => 'Rejected',
            default => 'Pending Review',
        };
    }
}
