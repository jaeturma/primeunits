<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CategoryAccessRule;
use Illuminate\Database\Seeder;

/**
 * Standalone demo data: the default marketplace tier and review
 * requirements per category. These are defaults only — individual
 * listings can still be manually assigned a different tier (e.g. a rare
 * collector chopper approved as Gold within the Motorcycles category).
 *
 * Requires CategorySeeder to have run first.
 */
class MarketplaceAccessRuleSeeder extends Seeder
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private const array RULES = [
        'cars' => ['default_marketplace_tier' => 'regular', 'min_buyer_access' => 'regular', 'min_seller_access' => 'regular'],
        'motorcycles' => ['default_marketplace_tier' => 'regular', 'min_buyer_access' => 'regular', 'min_seller_access' => 'regular'],
        'commercial-vehicles' => ['default_marketplace_tier' => 'regular', 'min_buyer_access' => 'regular', 'min_seller_access' => 'regular'],
        'agricultural-equipment' => ['default_marketplace_tier' => 'regular', 'min_buyer_access' => 'regular', 'min_seller_access' => 'regular'],
        'heavy-equipment' => ['default_marketplace_tier' => 'regular', 'min_buyer_access' => 'regular', 'min_seller_access' => 'regular'],
        'electric-vehicles' => ['default_marketplace_tier' => 'regular', 'min_buyer_access' => 'regular', 'min_seller_access' => 'regular'],
        'other-units' => ['default_marketplace_tier' => 'regular', 'min_buyer_access' => 'regular', 'min_seller_access' => 'regular'],
        'watercraft-marine-vessels' => [
            'default_marketplace_tier' => 'silver',
            'min_buyer_access' => 'silver',
            'min_seller_access' => 'silver',
            'manual_review_required' => true,
            'ownership_documents_required' => true,
            'proof_of_funds_allowed' => true,
        ],
        'aircraft' => [
            'default_marketplace_tier' => 'gold',
            'min_buyer_access' => 'gold',
            'min_seller_access' => 'gold',
            'manual_review_required' => true,
            'verified_buyer_required' => true,
            'ownership_documents_required' => true,
            'category_credentials_required' => true,
            'proof_of_funds_allowed' => true,
            'confidentiality_required' => true,
        ],
    ];

    public function run(): void
    {
        $this->call([CategorySeeder::class]);

        foreach (self::RULES as $slug => $attributes) {
            $category = Category::query()->where('slug', $slug)->first();

            if ($category === null) {
                continue;
            }

            CategoryAccessRule::query()->updateOrCreate(
                ['category_id' => $category->id],
                $attributes,
            );
        }
    }
}
