<?php

namespace Database\Seeders;

use App\Models\Municipality;
use App\Models\Province;
use App\Models\Region;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regions = collect($this->json('psgc_regions.json'))->mapWithKeys(function (array $item): array {
            $region = Region::query()->updateOrCreate(
                ['code' => $item['code']],
                [
                    'name' => trim($item['name']),
                    'is_active' => true,
                ],
            );

            return [$region->code => $region];
        });

        $provinces = collect($this->json('psgc_provinces.json'))->mapWithKeys(function (array $item) use ($regions): array {
            $region = $regions->get($this->regionCodeFrom($item['code']));

            if (! $region instanceof Region) {
                return [];
            }

            $province = Province::query()->updateOrCreate(
                ['code' => $item['code']],
                [
                    'region_id' => $region->id,
                    'name' => trim($item['name']),
                    'is_active' => true,
                ],
            );

            return [$province->code => $province];
        });

        collect($this->json('psgc_municipalities.json'))->each(function (array $item) use ($regions, $provinces): void {
            $region = $regions->get($this->regionCodeFrom($item['code']));

            if (! $region instanceof Region) {
                return;
            }

            $province = $provinces->get($this->provinceCodeFrom($item['code']));

            Municipality::query()->updateOrCreate(
                ['code' => $item['code']],
                [
                    'region_id' => $region->id,
                    'province_id' => $province?->id,
                    'name' => trim($item['name']),
                    'type' => $item['type'],
                    'zip_code' => $item['zip_code'] ?: null,
                    'district' => $item['district'] ?: null,
                    'is_active' => true,
                ],
            );
        });
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function json(string $filename): array
    {
        return json_decode(File::get(database_path("seeders/data/{$filename}")), true, flags: JSON_THROW_ON_ERROR);
    }

    private function regionCodeFrom(string $code): string
    {
        return substr($code, 0, 2).'00000000';
    }

    private function provinceCodeFrom(string $code): string
    {
        return substr($code, 0, 5).'00000';
    }
}
