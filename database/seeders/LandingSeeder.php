<?php

namespace Database\Seeders;

use App\Models\LandingAd;
use App\Models\LandingPage;
use Illuminate\Database\Seeder;

class LandingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LandingPage::query()->updateOrCreate(
            ['key' => 'home'],
            [
                'hero_badge' => 'Verified sellers across Philippine regions',
                'hero_title' => 'Cars, motorcycles, farm machines, and heavy equipment in one serious marketplace.',
                'hero_subtitle' => 'Search by brand, classification, condition, fuel type, mileage, and location from town or city up to Luzon, Visayas, and Mindanao.',
                'search_title' => 'Find your unit',
                'featured_title' => 'Featured listings',
                'featured_subtitle' => 'Location-aware results from verified sellers',
                'results_title' => 'Search results',
                'results_subtitle' => 'Filtered listings stay on this page for buyer and guest browsing',
                'budget_title' => 'Browse by budget',
                'seller_cta_title' => 'Sell vehicles and equipment with buyer-ready workflows.',
                'seller_cta_body' => 'Add listings, track inquiries, confirm deals, and submit payments from the seller dashboard.',
                'seller_cta_button' => 'Apply as seller',
                'is_active' => true,
            ],
        );

        collect([
            [
                'title' => 'Certified Repair Shops',
                'category' => 'Service Shops',
                'body' => 'Promote inspection, repair, repainting, and preventive maintenance services to active buyers.',
                'cta_label' => 'Book service',
                'cta_url' => '/?classification=Repair%20Shops#ads',
                'image_url' => '/images/ads/repair-shop.svg',
                'accent_color' => '#2563eb',
                'sort_order' => 1,
            ],
            [
                'title' => 'Metro Motor Parts Supply',
                'category' => 'Motor Parts',
                'body' => 'Feature tires, batteries, accessories, upgrades, fluids, and fast-moving replacement parts.',
                'cta_label' => 'Find parts',
                'cta_url' => '/?classification=Motor%20Parts#ads',
                'image_url' => '/images/ads/motor-parts.svg',
                'accent_color' => '#dc2626',
                'sort_order' => 2,
            ],
            [
                'title' => 'Prime Repaint and Body Works',
                'category' => 'Repainting',
                'body' => 'Showcase repainting, panel repair, detailing, ceramic coating, and restoration services.',
                'cta_label' => 'Request estimate',
                'cta_url' => '/?classification=Repainting#ads',
                'image_url' => '/images/ads/repainting.svg',
                'accent_color' => '#7c3aed',
                'sort_order' => 3,
            ],
            [
                'title' => 'Aftermarket Performance Hub',
                'category' => 'Aftermarket',
                'body' => 'Promote lift kits, lighting, utility racks, bike accessories, and work-ready upgrades.',
                'cta_label' => 'View upgrades',
                'cta_url' => '/?classification=Aftermarket#ads',
                'image_url' => '/images/ads/aftermarket.svg',
                'accent_color' => '#0891b2',
                'sort_order' => 4,
            ],
            [
                'title' => 'Visayas Motorcycle Dealer',
                'category' => 'Motorcycle Dealer',
                'body' => 'Advertise motorcycles, scooters, three-wheelers, e-bikes, parts, financing, and service bundles.',
                'cta_label' => 'Visit dealer',
                'cta_url' => '/?classification=Motorcycle%20Dealer#ads',
                'image_url' => '/images/ads/motorcycle-dealer.svg',
                'accent_color' => '#16a34a',
                'sort_order' => 5,
            ],
            [
                'title' => 'Mindanao Heavy Equipment Dealers',
                'category' => 'Heavy Equipment Dealers',
                'body' => 'Feature loaders, excavators, forklifts, generators, rentals, and site support services.',
                'cta_label' => 'View dealers',
                'cta_url' => '/?classification=Heavy%20Equipment%20Dealers#ads',
                'image_url' => '/images/ads/heavy-equipment-dealer.svg',
                'accent_color' => '#ca8a04',
                'sort_order' => 6,
            ],
            [
                'title' => 'Davao Farm Equipment Center',
                'category' => 'Farm Equipment',
                'body' => 'Promote tractors, harvesters, implements, maintenance, spare parts, and field service.',
                'cta_label' => 'See farm units',
                'cta_url' => '/?classification=Farm%20Equipment#ads',
                'image_url' => '/images/ads/farm-equipment-dealer.svg',
                'accent_color' => '#65a30d',
                'sort_order' => 7,
            ],
        ])->each(fn (array $ad): LandingAd => LandingAd::query()->updateOrCreate(
            ['title' => $ad['title']],
            [
                ...$ad,
                'is_active' => true,
            ],
        ));
    }
}
