<?php

namespace Database\Seeders;

use App\Models\DroneComplianceAcknowledgement;
use App\Models\DroneComplianceNotice;
use App\Models\RentalBooking;
use App\Models\RentalPackage;
use App\Models\RentalUnit;
use App\Models\User;
use App\Services\DroneBookingComplianceService;
use App\Services\RentalPricingService;
use Illuminate\Database\Seeder;

/**
 * Standalone demo data: a confirmed per-hectare harvesting service booking,
 * a pending agricultural drone spraying booking with the verified pilot,
 * and a completed harvester rental — using the same decimal-safe pricing
 * service the live booking flow uses, so the demo totals are realistic.
 *
 * Requires RiceHarvesterListingSeeder, AgriculturalDroneListingSeeder,
 * FarmEquipmentDemoUserSeeder, and DroneComplianceNoticeSeeder to have run
 * first.
 */
class FarmRentalBookingSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RiceHarvesterListingSeeder::class,
            AgriculturalDroneListingSeeder::class,
            DroneComplianceNoticeSeeder::class,
        ]);

        $pricing = app(RentalPricingService::class);
        $droneCompliance = app(DroneBookingComplianceService::class);
        $farmer = User::query()->where('email', 'farmer.demo@primeunits.test')->firstOrFail();

        $this->confirmedHarvestingServiceBooking($pricing, $farmer);
        $this->pendingDroneSprayingBooking($pricing, $droneCompliance, $farmer);
        $this->completedHarvesterRentalBooking($pricing, $farmer);
    }

    private function confirmedHarvestingServiceBooking(RentalPricingService $pricing, User $farmer): void
    {
        $unit = RentalUnit::query()->where('name', 'Complete Rice Harvesting Service per Hectare')->firstOrFail();
        $areaHectares = 3.5;

        $amounts = $pricing->calculate($unit, [
            'pricing_unit' => RentalPackage::DurationPerHectare,
            'area_hectares' => $areaHectares,
            'with_operator' => true,
            'with_transportation' => true,
        ]);

        RentalBooking::query()->updateOrCreate(
            ['reference_code' => 'RB-DEMO-HARVEST-001'],
            [
                'rental_unit_id' => $unit->id,
                'renter_id' => $farmer->id,
                'provider_id' => $unit->user_id,
                'start_date' => now()->addDays(4)->toDateString(),
                'pickup_address' => 'Barangay Poblacion, Nabunturan, Davao de Oro',
                'message' => 'Requesting harvesting service for our 3.5-hectare rice field.',
                'status' => RentalBooking::StatusConfirmed,
                'confirmed_at' => now()->subDay(),
                'area_hectares' => $areaHectares,
                'pricing_unit' => RentalPackage::DurationPerHectare,
                'quoted_price' => $amounts['total_amount'],
                ...$amounts,
            ],
        );
    }

    private function pendingDroneSprayingBooking(RentalPricingService $pricing, DroneBookingComplianceService $droneCompliance, User $farmer): void
    {
        $unit = RentalUnit::query()->where('name', 'Agricultural Drone Spraying Service with Verified Pilot')->firstOrFail();
        $areaHectares = 2.0;

        $decision = $droneCompliance->resolveOperator($unit, $farmer, selfOperate: false);

        $amounts = $pricing->calculate($unit, [
            'pricing_unit' => RentalPackage::DurationPerHectare,
            'area_hectares' => $areaHectares,
            'with_operator' => true,
            'with_transportation' => true,
        ]);

        $notice = DroneComplianceNotice::active();

        $booking = RentalBooking::query()->updateOrCreate(
            ['reference_code' => 'RB-DEMO-DRONESPRAY-002'],
            [
                'rental_unit_id' => $unit->id,
                'renter_id' => $farmer->id,
                'provider_id' => $unit->user_id,
                'start_date' => now()->addDays(9)->toDateString(),
                'pickup_address' => 'Barangay San Vicente, City of Panabo, Davao del Norte',
                'message' => 'Requesting drone spraying for 2 hectares of corn.',
                'status' => RentalBooking::StatusInquiry,
                'area_hectares' => $areaHectares,
                'pricing_unit' => RentalPackage::DurationPerHectare,
                'quoted_price' => $amounts['total_amount'],
                'drone_pilot_user_id' => $decision['drone_pilot_user_id'],
                'operator_verification_snapshot' => $decision['snapshot'],
                'compliance_acknowledged_at' => now()->subHours(2),
                'compliance_notice_version' => $notice?->version,
                ...$amounts,
            ],
        );

        if ($notice !== null) {
            DroneComplianceAcknowledgement::query()->updateOrCreate(
                ['rental_booking_id' => $booking->id, 'user_id' => $farmer->id],
                [
                    'drone_compliance_notice_id' => $notice->id,
                    'notice_version' => $notice->version,
                    'notice_text' => $notice->body,
                    'acknowledged_at' => now()->subHours(2),
                    'ip_address' => '127.0.0.1',
                ],
            );
        }
    }

    private function completedHarvesterRentalBooking(RentalPricingService $pricing, User $farmer): void
    {
        $unit = RentalUnit::query()->where('name', 'Modern Rice Combine Harvester with Operator')->firstOrFail();

        $amounts = $pricing->calculate($unit, [
            'pricing_unit' => RentalPackage::DurationDaily,
            'start_date' => now()->subDays(20)->toDateString(),
            'end_date' => now()->subDays(19)->toDateString(),
            'with_operator' => true,
        ]);

        RentalBooking::query()->updateOrCreate(
            ['reference_code' => 'RB-DEMO-HARVESTER-003'],
            [
                'rental_unit_id' => $unit->id,
                'renter_id' => $farmer->id,
                'provider_id' => $unit->user_id,
                'start_date' => now()->subDays(20)->toDateString(),
                'end_date' => now()->subDays(19)->toDateString(),
                'pickup_address' => 'Barangay Magugpo, City of Tagum, Davao del Norte',
                'message' => 'Two-day harvester rental, completed.',
                'status' => RentalBooking::StatusCompleted,
                'confirmed_at' => now()->subDays(21),
                'pricing_unit' => RentalPackage::DurationDaily,
                'quoted_price' => $amounts['total_amount'],
                ...$amounts,
            ],
        );
    }
}
