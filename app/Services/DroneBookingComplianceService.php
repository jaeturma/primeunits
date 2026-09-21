<?php

namespace App\Services;

use App\Models\DronePilotCredential;
use App\Models\RentalUnit;
use App\Models\User;

/**
 * Decides who is allowed to operate a drone booking and captures the
 * verification snapshot used for that decision, without ever persisting
 * the underlying private credential documents on the booking record.
 */
class DroneBookingComplianceService
{
    /**
     * @return array{allowed: bool, reason: ?string, drone_pilot_user_id: ?int, snapshot: ?array<string, mixed>}
     */
    public function resolveOperator(RentalUnit $unit, User $renter, bool $selfOperate): array
    {
        if (! $unit->isDroneRelated()) {
            return ['allowed' => true, 'reason' => null, 'drone_pilot_user_id' => null, 'snapshot' => null];
        }

        if ($selfOperate) {
            if (! $unit->allows_self_operation) {
                return ['allowed' => false, 'reason' => 'This listing does not permit self-operated drone bookings.', 'drone_pilot_user_id' => null, 'snapshot' => null];
            }

            if ($unit->requires_verified_drone_operator && ! $renter->hasVerifiedDroneCredential()) {
                return ['allowed' => false, 'reason' => 'Self-operation requires a valid, verified drone pilot credential.', 'drone_pilot_user_id' => null, 'snapshot' => null];
            }

            $credential = $renter->latestDroneCredential();

            return [
                'allowed' => true,
                'reason' => null,
                'drone_pilot_user_id' => $credential?->isActiveAndUnexpired() === true ? $renter->id : null,
                'snapshot' => $this->snapshot($credential, $renter),
            ];
        }

        $pilot = $unit->dronePilot;

        if ($unit->requires_verified_drone_operator) {
            if ($pilot === null) {
                return ['allowed' => false, 'reason' => 'No verified drone operator is assigned to this listing.', 'drone_pilot_user_id' => null, 'snapshot' => null];
            }

            $credential = $pilot->latestDroneCredential();

            if (! $credential?->isActiveAndUnexpired()) {
                return ['allowed' => false, 'reason' => 'The assigned drone operator credential is not currently verified.', 'drone_pilot_user_id' => null, 'snapshot' => null];
            }

            return [
                'allowed' => true,
                'reason' => null,
                'drone_pilot_user_id' => $pilot->id,
                'snapshot' => $this->snapshot($credential, $pilot),
            ];
        }

        return [
            'allowed' => true,
            'reason' => null,
            'drone_pilot_user_id' => $pilot?->id,
            'snapshot' => $pilot ? $this->snapshot($pilot->latestDroneCredential(), $pilot) : null,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function snapshot(?DronePilotCredential $credential, User $operator): ?array
    {
        if ($credential === null) {
            return null;
        }

        return [
            'operator_user_id' => $operator->id,
            'credential_status' => $credential->status,
            'credential_type' => $credential->credential_type,
            'masked_credential_number' => $credential->maskedCredentialNumber(),
            'expiration_date' => $credential->expiration_date?->toDateString(),
            'verified_at_booking' => now()->toISOString(),
        ];
    }
}
