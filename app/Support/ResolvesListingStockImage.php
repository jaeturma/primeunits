<?php

namespace App\Support;

use App\Models\LandingAd;
use App\Models\Listing;
use App\Models\ListingImage;
use Illuminate\Support\Facades\Storage;

/**
 * Shared listing image resolution for the homepage search and the full
 * listings catalog, so a listing without an uploaded photo shows a
 * type-appropriate stock illustration instead of the same generic image
 * for every category.
 */
trait ResolvesListingStockImage
{
    private function listingImageUrl(?ListingImage $image, Listing $listing): string
    {
        if ($image instanceof ListingImage && Storage::disk('public')->exists($image->path)) {
            return Storage::disk('public')->url($image->path);
        }

        return $this->stockListingImageUrl($listing);
    }

    /**
     * The shared listing-card payload for the landing feed: used both for
     * the first server-rendered batch and every "Load 12 More" batch, so
     * the two can never drift out of sync on what a card shows.
     *
     * @return array<string, mixed>
     */
    private function serializeFeedListingCard(Listing $listing): array
    {
        $primaryImage = $listing->images->firstWhere('is_primary', true) ?? $listing->images->first();

        return [
            'id' => $listing->id,
            'title' => $listing->title,
            'description' => $listing->description,
            'price' => $listing->price,
            'condition' => $listing->condition,
            'brand' => $listing->brand,
            'model' => $listing->model,
            'year_model' => $listing->year_model,
            'province' => $listing->province,
            'municipality' => $listing->municipality,
            'category' => [
                'name' => $listing->category->name,
                'slug' => $listing->category->slug,
            ],
            'image_url' => $this->listingImageUrl($primaryImage, $listing),
            'marketplace_tier' => $listing->marketplace_tier,
            'tier_label' => $listing->tierLabel(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeFeedAd(LandingAd $ad): array
    {
        return [
            'id' => $ad->id,
            'title' => $ad->title,
            'category' => $ad->category,
            'body' => $ad->body,
            'cta_label' => $ad->cta_label,
            'cta_url' => $ad->cta_url,
            'image_url' => $ad->image_url,
            'accent_color' => $ad->accent_color,
        ];
    }

    private function stockListingImageUrl(Listing $listing): string
    {
        $classification = strtolower((string) $listing->specValues
            ->first(fn ($value) => $value->specField?->is_classification === true)
            ?->value);

        $image = match ($listing->category?->slug) {
            'cars' => match (true) {
                str_contains($classification, 'suv') || str_contains($classification, 'crossover') => 'suv',
                str_contains($classification, 'hatchback') => 'hatchback',
                str_contains($classification, 'pickup') => 'pickup',
                str_contains($classification, 'van') || str_contains($classification, 'mpv') => 'van',
                default => 'sedan',
            },
            'motorcycles' => 'motorcycle',
            'commercial-vehicles' => match (true) {
                str_contains($classification, 'bus') || str_contains($classification, 'minibus') || str_contains($classification, 'uv express') => 'bus',
                str_contains($classification, 'van') => 'van',
                str_contains($classification, 'tricycle') => 'motorcycle',
                default => 'truck',
            },
            'agricultural-equipment' => 'tractor',
            'heavy-equipment' => 'equipment',
            'electric-vehicles' => match (true) {
                str_contains($classification, 'motorcycle') || str_contains($classification, 'scooter') || str_contains($classification, 'tricycle') || str_contains($classification, 'e-bike') => 'motorcycle',
                str_contains($classification, 'bus') => 'bus',
                str_contains($classification, 'truck') => 'truck',
                default => 'sedan',
            },
            'other-units' => str_contains($classification, 'boat') || str_contains($classification, 'watercraft') ? 'boat' : 'generator',
            default => 'sedan',
        };

        return "/images/vehicles/{$image}.svg";
    }
}
