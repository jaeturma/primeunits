<?php

namespace App\Support;

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
