<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\SeoPage;
use Illuminate\Database\Seeder;

class SeoPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pages = [
            'cars' => [
                'slug' => 'cars',
                'label' => 'Cars',
                'locations' => ['cebu' => ['province' => 'Cebu'], 'davao' => ['province' => 'Davao del Sur']],
            ],
            'agricultural-equipment' => [
                'slug' => 'agricultural-equipment',
                'label' => 'Agricultural Equipment',
                'locations' => ['davao' => ['province' => 'Davao del Sur'], 'mindanao' => ['region' => 'Region XI']],
            ],
            'heavy-equipment' => [
                'slug' => 'heavy-equipment',
                'label' => 'Heavy Equipment',
                'locations' => ['cebu' => ['province' => 'Cebu'], 'davao' => ['province' => 'Davao del Sur']],
            ],
            'motorcycles' => [
                'slug' => 'motorcycles',
                'label' => 'Motorcycles',
                'locations' => ['cebu' => ['province' => 'Cebu'], 'manila' => ['province' => 'Metro Manila']],
            ],
        ];

        foreach ($pages as $categorySlug => $page) {
            $category = Category::query()->where('slug', $categorySlug)->firstOrFail();

            SeoPage::query()->updateOrCreate(
                ['slug' => $page['slug']],
                [
                    'title' => "{$page['label']} for Sale in the Philippines",
                    'meta_title' => "{$page['label']} for Sale | PrimeUnits Philippines",
                    'meta_description' => "Browse {$page['label']} listings from verified PrimeUnits sellers across the Philippines.",
                    'content' => "Find available {$page['label']} from verified sellers, compare prices, and send inquiries directly through PrimeUnits.",
                    'category_id' => $category->id,
                    'region' => null,
                    'province' => null,
                    'municipality' => null,
                ],
            );

            foreach ($page['locations'] as $locationSlug => $location) {
                $locationName = $location['province'] ?? $location['region'];

                SeoPage::query()->updateOrCreate(
                    ['slug' => "{$page['slug']}/{$locationSlug}"],
                    [
                        'title' => "{$page['label']} for Sale in {$locationName}",
                        'meta_title' => "{$page['label']} for Sale in {$locationName} | PrimeUnits",
                        'meta_description' => "Browse {$page['label']} listings in {$locationName}. Find verified sellers, compare units, and send inquiries on PrimeUnits.",
                        'content' => "Explore {$page['label']} available in {$locationName}, including featured listings and seller-verified units.",
                        'category_id' => $category->id,
                        'region' => $location['region'] ?? null,
                        'province' => $location['province'] ?? null,
                        'municipality' => null,
                    ],
                );
            }
        }
    }
}
