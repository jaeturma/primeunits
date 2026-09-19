<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

#[Fillable(['parent_id', 'name', 'slug', 'icon', 'image', 'commission_rate', 'sort_order', 'is_active'])]
class Category extends Model
{
    /**
     * @return BelongsTo<Category, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * @return HasMany<Category, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * @return HasMany<CategorySpecField, $this>
     */
    public function specFields(): HasMany
    {
        return $this->hasMany(CategorySpecField::class)->orderBy('sort_order');
    }

    /**
     * The single spec field ("body type", "equipment type", etc.) that
     * represents this category's buyer-facing classification.
     *
     * @return HasOne<CategorySpecField, $this>
     */
    public function classificationField(): HasOne
    {
        return $this->hasOne(CategorySpecField::class)->where('is_classification', true);
    }

    /**
     * @return HasMany<Listing, $this>
     */
    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'commission_rate' => 'decimal:2',
        ];
    }

    /**
     * The active categories, with their classification field, shaped for
     * the "Find a Unit" search UI. Kept as a single source of truth so the
     * homepage search and the full listings catalog never diverge.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public static function searchPayload(): Collection
    {
        return static::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->with('classificationField:id,category_id,name,label')
            ->get(['id', 'name', 'slug', 'icon'])
            ->map(fn (Category $category): array => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'icon' => $category->icon,
                'classification_field' => $category->classificationField ? [
                    'id' => $category->classificationField->id,
                    'name' => $category->classificationField->name,
                    'label' => $category->classificationField->label,
                ] : null,
            ]);
    }
}
