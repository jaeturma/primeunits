<?php

namespace Database\Seeders\Concerns;

/**
 * Shared Davao Region location list so demo listings and rental units
 * are seeded against the same real province/city names instead of two
 * copies that can drift apart.
 */
trait SeedsDavaoLocations
{
    private const string DAVAO_REGION = 'Region XI (Davao Region)';

    /**
     * @return array<int, array{region: string, province: string, municipality: string}>
     */
    private function davaoLocations(): array
    {
        return [
            ['province' => 'Davao del Sur', 'municipality' => 'City of Davao'],
            ['province' => 'Davao del Sur', 'municipality' => 'City of Davao'],
            ['province' => 'Davao del Sur', 'municipality' => 'City of Davao'],
            ['province' => 'Davao del Sur', 'municipality' => 'City of Digos'],
            ['province' => 'Davao del Sur', 'municipality' => 'Bansalan'],
            ['province' => 'Davao del Norte', 'municipality' => 'City of Tagum'],
            ['province' => 'Davao del Norte', 'municipality' => 'City of Panabo'],
            ['province' => 'Davao del Norte', 'municipality' => 'Island Garden City of Samal'],
            ['province' => 'Davao de Oro', 'municipality' => 'Compostela'],
            ['province' => 'Davao de Oro', 'municipality' => 'Nabunturan'],
            ['province' => 'Davao Oriental', 'municipality' => 'City of Mati'],
            ['province' => 'Davao Oriental', 'municipality' => 'Baganga'],
            ['province' => 'Davao Occidental', 'municipality' => 'Malita'],
        ];
    }

    /**
     * @return array{region: string, province: string, municipality: string}
     */
    private function davaoLocation(int $index): array
    {
        $locations = $this->davaoLocations();

        return [
            'region' => self::DAVAO_REGION,
            ...$locations[$index % count($locations)],
        ];
    }
}
