<?php

namespace Database\Seeders;

use App\Models\LandingAd;
use Illuminate\Database\Seeder;

/**
 * Standalone demo data: advertisement placements eligible for the landing
 * feed's "Advertisement" slot (distinct from the always-on advertising
 * carousel seeded by LandingSeeder). Fictional partners only.
 *
 * Idempotent via updateOrCreate on the stable `title`. Not wired into
 * DatabaseSeeder — run explicitly alongside PrimeUnitsLandingExperienceSeeder.
 */
class LandingAdvertisementSeeder extends Seeder
{
    public function run(): void
    {
        collect([
            [
                'title' => 'PrimeUnits Protect — Vehicle Insurance (Demo)',
                'category' => 'Insurance',
                'body' => 'Comprehensive coverage for cars, motorcycles, and fleet vehicles. Fictional demo partner offer.',
                'cta_label' => 'Get a quote',
                'cta_url' => '/insurance',
                'image_url' => '/images/ads/repair-shop.svg',
                'accent_color' => '#2563eb',
                'sort_order' => 1,
                'show_in_feed' => true,
                'target_marketplace_mode' => null,
                'starts_at' => now()->subDays(10),
                'ends_at' => now()->addDays(90),
            ],
            [
                'title' => 'Silver Circle Concierge Detailing (Demo)',
                'category' => 'Premium Services',
                'body' => 'Mobile detailing and inspection concierge for Silver-tier vehicle owners. Fictional demo partner offer.',
                'cta_label' => 'Book a slot',
                'cta_url' => '/contact-us',
                'image_url' => '/images/ads/repainting.svg',
                'accent_color' => '#64748b',
                'sort_order' => 2,
                'show_in_feed' => true,
                'target_marketplace_mode' => 'silver',
                'starts_at' => now()->subDays(5),
                'ends_at' => now()->addDays(60),
            ],
            [
                'title' => 'Meridian Private Client Brokerage (Demo)',
                'category' => 'Gold Concierge',
                'body' => 'Introductions to specialist aviation and marine brokers for approved Gold buyers. Fictional demo partner offer.',
                'cta_label' => 'Request an introduction',
                'cta_url' => '/contact-us',
                'image_url' => '/images/ads/aftermarket.svg',
                'accent_color' => '#b45309',
                'sort_order' => 3,
                'show_in_feed' => true,
                'target_marketplace_mode' => 'gold',
                'starts_at' => now()->subDays(2),
                'ends_at' => now()->addDays(45),
            ],
            [
                'title' => 'PrimeUnits Fleet Financing Desk (Demo)',
                'category' => 'Financing',
                'body' => 'Fleet and equipment financing for growing operators, arranged through PrimeUnits partner lenders. Fictional demo partner offer.',
                'cta_label' => 'See financing options',
                'cta_url' => '/financing',
                'image_url' => '/images/ads/farm-equipment-dealer.svg',
                'accent_color' => '#16a34a',
                'sort_order' => 4,
                'show_in_feed' => true,
                'target_marketplace_mode' => null,
                'starts_at' => null,
                'ends_at' => null,
            ],
        ])->each(fn (array $ad): LandingAd => LandingAd::query()->updateOrCreate(
            ['title' => $ad['title']],
            [
                ...$ad,
                'is_active' => true,
                'review_status' => LandingAd::ReviewApproved,
            ],
        ));
    }
}
