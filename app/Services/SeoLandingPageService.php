<?php

namespace App\Services;

use App\Models\Listing;
use App\Models\SeoPage;
use App\Support\ResolvesListingStockImage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SeoLandingPageService
{
    use ResolvesListingStockImage;

    public function pageForCategory(string $categorySlug): ?SeoPage
    {
        return SeoPage::query()
            ->with('category:id,name,slug')
            ->where('slug', $categorySlug)
            ->whereNotNull('category_id')
            ->first();
    }

    public function pageForCategoryLocation(string $categorySlug, string $locationSlug): ?SeoPage
    {
        $page = SeoPage::query()
            ->with('category:id,name,slug')
            ->where('slug', "{$categorySlug}/{$locationSlug}")
            ->whereNotNull('category_id')
            ->first();

        if ($page !== null) {
            return $page;
        }

        $categoryPage = $this->pageForCategory($categorySlug);

        if ($categoryPage === null) {
            return null;
        }

        $locationName = Str::of($locationSlug)->replace('-', ' ')->title()->toString();

        return (new SeoPage([
            'slug' => "{$categorySlug}/{$locationSlug}",
            'title' => "{$categoryPage->category->name} for Sale in {$locationName}",
            'meta_title' => "{$categoryPage->category->name} for Sale in {$locationName} | PrimeUnits",
            'meta_description' => "Browse {$categoryPage->category->name} listings in {$locationName}. Find verified sellers, compare units, and send inquiries on PrimeUnits.",
            'content' => "Explore available {$categoryPage->category->name} in {$locationName} from verified marketplace sellers.",
            'category_id' => $categoryPage->category_id,
            'province' => $locationName,
        ]))->setRelation('category', $categoryPage->category);
    }

    /**
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    public function listings(SeoPage $page, Request $request): LengthAwarePaginator
    {
        return Listing::query()
            ->with([
                'category:id,name,slug',
                'images',
                'boosts',
                'specValues' => fn ($query) => $query
                    ->whereHas('specField', fn ($query) => $query->where('is_classification', true))
                    ->with('specField:id,name,is_classification'),
            ])
            ->where('status', Listing::StatusApproved)
            ->when($page->category_id, fn (Builder $query): Builder => $query->where('category_id', $page->category_id))
            ->when($page->region, fn (Builder $query): Builder => $query->where('region', $page->region))
            ->when($page->province, fn (Builder $query): Builder => $query->where('province', $page->province))
            ->when($page->municipality, fn (Builder $query): Builder => $query->where('municipality', $page->municipality))
            ->when($request->string('condition')->isNotEmpty(), fn (Builder $query): Builder => $query->where('condition', $request->string('condition')->toString()))
            ->when($request->string('q')->isNotEmpty(), function (Builder $query) use ($request): void {
                $search = $request->string('q')->toString();

                $query->where(function (Builder $query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('min_price'), fn (Builder $query): Builder => $query->where('price', '>=', $request->integer('min_price')))
            ->when($request->filled('max_price'), fn (Builder $query): Builder => $query->where('price', '<=', $request->integer('max_price')))
            ->withExists(['boosts as has_active_boost' => fn (Builder $query) => $query
                ->where('is_active', true)
                ->where('ends_at', '>', now())])
            ->orderByDesc('has_active_boost')
            ->latest('approved_at')
            ->paginate(12)
            ->withQueryString()
            ->through(fn (Listing $listing): array => $this->serializeCard($listing));
    }

    /**
     * @return array<int, array{label: string, value: string}>
     */
    public function filters(): array
    {
        return [
            ['value' => Listing::ConditionBrandNew, 'label' => 'Brand New'],
            ['value' => Listing::ConditionUsed, 'label' => 'Used'],
            ['value' => Listing::ConditionSurplus, 'label' => 'Surplus'],
            ['value' => Listing::ConditionReconditioned, 'label' => 'Reconditioned'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeCard(Listing $listing): array
    {
        $primaryImage = $listing->images->firstWhere('is_primary', true) ?? $listing->images->first();

        return [
            'id' => $listing->id,
            'title' => $listing->title,
            'price' => $listing->price,
            'negotiable' => $listing->negotiable,
            'condition' => $listing->condition,
            'brand' => $listing->brand,
            'model' => $listing->model,
            'region' => $listing->region,
            'province' => $listing->province,
            'municipality' => $listing->municipality,
            'is_featured' => (bool) ($listing->has_active_boost ?? $listing->boosts->contains(fn ($boost): bool => $boost->isCurrentlyActive())),
            'category' => $listing->category,
            'image_url' => $this->listingImageUrl($primaryImage, $listing),
            'created_at' => $listing->created_at?->toISOString(),
        ];
    }
}
