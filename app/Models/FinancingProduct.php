<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class FinancingProduct extends Model
{
    public const TypeTermLoan = 'term_loan';
    public const TypeInstallment = 'installment';
    public const TypeLease = 'lease';
    public const TypeChattelMortgage = 'chattel_mortgage';

    protected $fillable = [
        'financing_partner_id', 'name', 'slug', 'product_type', 'description',
        'min_amount', 'max_amount', 'interest_rate_min', 'interest_rate_max',
        'min_term_months', 'max_term_months', 'applicable_categories', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'applicable_categories' => 'array',
            'is_active' => 'boolean',
            'min_amount' => 'decimal:2',
            'max_amount' => 'decimal:2',
            'interest_rate_min' => 'decimal:2',
            'interest_rate_max' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $product): void {
            if (empty($product->slug)) {
                $product->slug = self::generateSlug($product->name);
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

    public function partner(): BelongsTo
    {
        return $this->belongsTo(FinancingPartner::class, 'financing_partner_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(FinancingApplication::class);
    }

    /** @return array<string,string> */
    public static function productTypes(): array
    {
        return [
            self::TypeTermLoan => 'Term Loan',
            self::TypeInstallment => 'Installment',
            self::TypeLease => 'Lease',
            self::TypeChattelMortgage => 'Chattel Mortgage',
        ];
    }

    public function typeLabel(): string
    {
        return self::productTypes()[$this->product_type] ?? $this->product_type;
    }
}
