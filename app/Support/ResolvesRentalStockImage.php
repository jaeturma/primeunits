<?php

namespace App\Support;

use App\Models\RentalUnit;

/**
 * A type-appropriate stock illustration for rental units without an
 * uploaded photo yet, instead of showing the same generic image (or a
 * bare icon) for every unit regardless of what it actually is.
 */
trait ResolvesRentalStockImage
{
    private function stockImageUrl(RentalUnit $unit): string
    {
        $image = match (true) {
            $unit->rental_type === RentalUnit::TypeEquipmentRental => 'equipment',
            $unit->rental_type === RentalUnit::TypeTruckRental => 'truck',
            $unit->rental_type === RentalUnit::TypeBusRental => 'bus',
            $unit->rental_type === RentalUnit::TypeWeddingCar => 'wedding-car',
            $unit->rental_type === RentalUnit::TypeMotorcycleRental => 'motorcycle',
            $unit->rental_type === RentalUnit::TypeSelfDrive && ($unit->capacity ?? 0) <= 2 => 'motorcycle',
            in_array($unit->rental_type, [RentalUnit::TypeVanRental, RentalUnit::TypeShuttle, RentalUnit::TypeAirportTransfer], true) => 'van',
            default => 'sedan',
        };

        return "/images/vehicles/{$image}.svg";
    }
}
