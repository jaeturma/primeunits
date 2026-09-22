<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Standalone demo data: representative Regular, Silver, and Gold
 * listings, including at least one example of every listing visibility
 * level (Public, Public Preview, Silver Exclusive, Gold Exclusive,
 * Verified Buyer Only, Invitation Only).
 *
 * Requires CategorySeeder, MembershipDemoUserSeeder, and PremiumStoreSeeder
 * to have run first. Rice harvester rental and agricultural drone service
 * demo listings for the Regular tier are seeded separately by
 * RiceHarvesterListingSeeder / AgriculturalDroneListingSeeder (not
 * duplicated here).
 */
class PremiumListingSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([CategorySeeder::class, MembershipDemoUserSeeder::class, PremiumStoreSeeder::class]);

        $regular = User::query()->where('email', 'regular.demo@primeunits.test')->firstOrFail();
        $silverSeller = User::query()->where('email', 'silver.seller@primeunits.test')->firstOrFail();
        $silverStore = User::query()->where('email', 'silver.store@primeunits.test')->firstOrFail();
        $goldSeller = User::query()->where('email', 'gold.seller@primeunits.test')->firstOrFail();
        $goldStore = User::query()->where('email', 'gold.store@primeunits.test')->firstOrFail();

        $cars = Category::query()->where('slug', 'cars')->firstOrFail();
        $motorcycles = Category::query()->where('slug', 'motorcycles')->firstOrFail();
        $watercraft = Category::query()->where('slug', 'watercraft-marine-vessels')->firstOrFail();
        $aircraft = Category::query()->where('slug', 'aircraft')->firstOrFail();

        // ── Regular ──────────────────────────────────────────────────
        $this->listing($regular, $motorcycles, [
            'title' => 'Standard Commuter Motorcycle',
            'description' => 'Reliable everyday commuter motorcycle, well-maintained.',
            'price' => 85000,
            'condition' => Listing::ConditionUsed,
            'brand' => 'Honda',
            'model' => 'Click 160i',
        ], [
            'marketplace_tier' => Listing::TierRegular,
            'visibility_level' => Listing::VisibilityPublic,
            'seller_capacity' => Listing::CapacityPrivateOwner,
        ], ['motorcycle_type' => 'Naked / Street']);

        $this->listing($regular, $cars, [
            'title' => 'Family SUV in Excellent Condition',
            'description' => 'Spacious 7-seater family SUV, single owner.',
            'price' => 1150000,
            'condition' => Listing::ConditionUsed,
            'brand' => 'Toyota',
            'model' => 'Fortuner',
        ], [
            'marketplace_tier' => Listing::TierRegular,
            'visibility_level' => Listing::VisibilityPublic,
            'seller_capacity' => Listing::CapacityPrivateOwner,
        ], ['body_type' => 'SUV']);

        // ── Silver ───────────────────────────────────────────────────
        $this->listing($silverSeller, $motorcycles, [
            'title' => 'Premium Big Bike, Low Mileage',
            'description' => 'Well-maintained premium big bike from a verified enthusiast seller.',
            'price' => 950000,
            'condition' => Listing::ConditionUsed,
            'brand' => 'Ducati',
            'model' => 'Monster 937',
        ], [
            'marketplace_tier' => Listing::TierSilver,
            'visibility_level' => Listing::VisibilityPublic,
            'seller_capacity' => Listing::CapacityPrivateOwner,
        ], ['motorcycle_type' => 'Big Bike']);

        $this->listing($silverStore, $cars, [
            'title' => 'High-End Luxury SUV, Dealer Certified',
            'description' => 'Fully loaded luxury SUV, dealer-certified pre-owned.',
            'price' => 4200000,
            'condition' => Listing::ConditionUsed,
            'brand' => 'BMW',
            'model' => 'X5',
        ], [
            'marketplace_tier' => Listing::TierSilver,
            'visibility_level' => Listing::VisibilityVerifiedBuyerOnly,
            'seller_capacity' => Listing::CapacityAuthorizedDealer,
        ], ['body_type' => 'SUV']);

        $this->listing($silverStore, $cars, [
            'title' => 'Sports Car, Track-Ready',
            'description' => 'Performance sports car maintained to dealer standard.',
            'price' => 5800000,
            'condition' => Listing::ConditionUsed,
            'brand' => 'Porsche',
            'model' => '718 Cayman',
        ], [
            'marketplace_tier' => Listing::TierSilver,
            'visibility_level' => Listing::VisibilityPublic,
            'seller_capacity' => Listing::CapacityAuthorizedDealer,
        ], ['body_type' => 'Sports Car']);

        $this->listing($silverStore, $cars, [
            'title' => 'Armored & Security Vehicle',
            'description' => 'Professionally armored security vehicle. Protection classification available to qualified buyers only.',
            'price' => 12500000,
            'condition' => Listing::ConditionUsed,
            'brand' => 'Toyota',
            'model' => 'Land Cruiser (Armored)',
        ], [
            'marketplace_tier' => Listing::TierSilver,
            'visibility_level' => Listing::VisibilitySilverExclusive,
            'seller_capacity' => Listing::CapacityAuthorizedDealer,
            'registration_number' => 'DEMO-REG-ARM-0099',
            'public_preview_summary' => 'Armored and security vehicle available through a verified dealer. Protection specifications disclosed to qualified Silver buyers only.',
        ], ['body_type' => 'Armored & Security Vehicle']);

        $this->listing($silverSeller, $watercraft, [
            'title' => 'Personal Watercraft, Twin Set',
            'description' => 'Two matched personal watercraft units, garage-kept.',
            'price' => 780000,
            'condition' => Listing::ConditionUsed,
            'brand' => 'Yamaha',
            'model' => 'WaveRunner FX',
        ], [
            'marketplace_tier' => Listing::TierSilver,
            'visibility_level' => Listing::VisibilityPublic,
            'seller_capacity' => Listing::CapacityPrivateOwner,
        ], ['vessel_type' => 'Personal Watercraft (Jet Skis)']);

        $this->listing($silverSeller, $motorcycles, [
            'title' => 'Premium Custom Motorcycle Build',
            'description' => 'One-off premium custom build by a recognized local shop.',
            'price' => 1350000,
            'condition' => Listing::ConditionUsed,
            'brand' => 'Harley-Davidson',
            'model' => 'Custom Softail',
        ], [
            'marketplace_tier' => Listing::TierSilver,
            'visibility_level' => Listing::VisibilityPublic,
            'seller_capacity' => Listing::CapacityPrivateOwner,
        ], ['motorcycle_type' => 'Premium Custom Motorcycle']);

        // ── Gold ─────────────────────────────────────────────────────
        $this->listing($goldSeller, $motorcycles, [
            'title' => 'Rare Collector Chopper, Historically Significant',
            'description' => 'Extremely rare collector chopper with documented provenance.',
            'price' => 4500000,
            'condition' => Listing::ConditionUsed,
            'brand' => 'Custom',
            'model' => 'Collector Chopper 1967',
        ], [
            'marketplace_tier' => Listing::TierGold,
            'visibility_level' => Listing::VisibilityGoldExclusive,
            'seller_capacity' => Listing::CapacityPrivateOwner,
            'registration_number' => 'DEMO-REG-CHOP-0007',
            'confidentiality_required' => true,
            'public_preview_summary' => 'Rare, historically significant collector chopper. Full provenance and documentation available to approved Gold buyers.',
        ], ['motorcycle_type' => 'Collector Chopper']);

        $this->listing($goldStore, $aircraft, [
            'title' => 'Light Private Aircraft, Well-Equipped',
            'description' => 'Well-maintained light private aircraft with complete maintenance records.',
            'price' => 18500000,
            'condition' => Listing::ConditionUsed,
            'brand' => 'Cessna',
            'model' => '172 Skyhawk',
        ], [
            'marketplace_tier' => Listing::TierGold,
            'visibility_level' => Listing::VisibilityGoldExclusive,
            'seller_capacity' => Listing::CapacityBrokerageCompany,
            'registration_number' => 'DEMO-REG-ACFT-0012',
            'confidentiality_required' => true,
            'public_preview_summary' => 'Light private aircraft offered through a specialist aviation brokerage. Maintenance and airworthiness records available to approved Gold buyers.',
        ], ['aircraft_type' => 'Light Aircraft']);

        $this->listing($goldStore, $aircraft, [
            'title' => 'Private Jet — Price on Request',
            'description' => 'Mid-size private jet offered through a specialist aviation brokerage.',
            'price' => 0,
            'condition' => Listing::ConditionUsed,
            'brand' => 'Bombardier',
            'model' => 'Challenger 350',
        ], [
            'marketplace_tier' => Listing::TierGold,
            'visibility_level' => Listing::VisibilityInvitationOnly,
            'seller_capacity' => Listing::CapacityBrokerageCompany,
            'price_on_request' => true,
            'confidentiality_required' => true,
            'public_preview_summary' => 'Mid-size private jet. Full specification, maintenance history, and pricing available strictly to invited, approved Gold buyers.',
        ], ['aircraft_type' => 'Private Jet']);

        $this->listing($goldStore, $watercraft, [
            'title' => 'Motor Yacht, Turnkey Ready',
            'description' => 'Turnkey motor yacht offered through a specialist marine brokerage.',
            'price' => 65000000,
            'condition' => Listing::ConditionUsed,
            'brand' => 'Azimut',
            'model' => '68 Flybridge',
        ], [
            'marketplace_tier' => Listing::TierGold,
            'visibility_level' => Listing::VisibilityGoldExclusive,
            'seller_capacity' => Listing::CapacityBrokerageCompany,
            'registration_number' => 'DEMO-REG-YACHT-0021',
            'confidentiality_required' => true,
            'public_preview_summary' => 'Turnkey motor yacht. Survey, registration, and berth details available to approved Gold buyers.',
        ], ['vessel_type' => 'Motor Yacht']);

        $this->listing($goldStore, $watercraft, [
            'title' => 'Superyacht — Confidential Listing',
            'description' => 'Superyacht offered under a confidential brokerage mandate.',
            'price' => 0,
            'condition' => Listing::ConditionUsed,
            'brand' => 'Feadship',
            'model' => 'Custom 55m',
        ], [
            'marketplace_tier' => Listing::TierGold,
            'visibility_level' => Listing::VisibilityPublicPreview,
            'seller_capacity' => Listing::CapacityBrokerageCompany,
            'price_on_request' => true,
            'confidentiality_required' => true,
            'public_preview_summary' => 'Superyacht available under a confidential brokerage mandate. Approximate specification shown; owner identity, berth, and full particulars are Gold Exclusive.',
        ], ['vessel_type' => 'Superyacht']);

        $this->listing($goldStore, $watercraft, [
            'title' => 'Premium Day Boat, Gold-Approved Exception',
            'description' => 'Premium collector-grade day boat, approved as a Gold marketplace exception due to rarity and value.',
            'price' => 9800000,
            'condition' => Listing::ConditionUsed,
            'brand' => 'Riva',
            'model' => 'Aquarama Replica',
        ], [
            'marketplace_tier' => Listing::TierGold,
            'visibility_level' => Listing::VisibilityGoldExclusive,
            'seller_capacity' => Listing::CapacityBrokerageCompany,
            'confidentiality_required' => true,
            'public_preview_summary' => 'Collector-grade premium day boat, individually approved for the Gold marketplace despite its category\'s Silver default.',
        ], ['vessel_type' => 'Day Boat / Speedboat']);

        // Commercial vessel: Gold-candidate, still pending specialist review —
        // stays Listing::StatusPending so it is not publicly visible,
        // bookable, or purchasable until approved.
        $this->listing($goldStore, $watercraft, [
            'title' => 'Commercial Vessel — Pending Specialist Review',
            'description' => 'Commercial vessel submitted as a Gold candidate, awaiting specialist review before any public listing.',
            'price' => 42000000,
            'condition' => Listing::ConditionUsed,
            'brand' => 'Damen',
            'model' => 'Stan Patrol 4207',
        ], [
            'marketplace_tier' => Listing::TierSilver,
            'visibility_level' => Listing::VisibilityGoldExclusive,
            'seller_capacity' => Listing::CapacityBrokerageCompany,
            'is_gold_candidate' => true,
            'confidentiality_required' => true,
        ], ['vessel_type' => 'Commercial Vessel'], status: Listing::StatusPending);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>  $tierAttributes
     * @param  array<string, string>  $specs
     */
    private function listing(User $seller, Category $category, array $attributes, array $tierAttributes, array $specs, string $status = Listing::StatusApproved): Listing
    {
        $listing = Listing::query()->updateOrCreate(
            ['title' => $attributes['title']],
            [
                ...$attributes,
                ...$tierAttributes,
                'user_id' => $seller->id,
                'seller_profile_id' => $seller->sellerProfile?->id,
                'dealer_profile_id' => $seller->dealerProfile?->id,
                'category_id' => $category->id,
                'negotiable' => true,
                'status' => $status,
                'approved_at' => $status === Listing::StatusApproved ? now()->subDays(random_int(1, 30)) : null,
            ],
        );

        $category->loadMissing('specFields');

        foreach ($specs as $name => $value) {
            $field = $category->specFields->firstWhere('name', $name);

            if ($field !== null) {
                $listing->specValues()->updateOrCreate(
                    ['spec_field_id' => $field->id],
                    ['value' => $value],
                );
            }
        }

        return $listing;
    }
}
