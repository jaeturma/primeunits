<?php

namespace Database\Seeders;

use App\Models\DroneCredentialAuditLog;
use App\Models\DronePilotCredential;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Seeder;

/**
 * Standalone demo data: one verified, one pending, one expired, and one
 * rejected drone pilot credential, each with an audit trail entry, so the
 * administrative review screen and the verified-operator badge both have
 * realistic cases to display.
 *
 * Requires FarmEquipmentDemoUserSeeder to have run first.
 */
class DronePilotCredentialSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([FarmEquipmentDemoUserSeeder::class]);

        $this->credential(
            email: 'pilot.verified@primeunits.test',
            credentialType: 'Remote Pilot Certificate',
            status: DronePilotCredential::StatusVerified,
            number: 'DEMO-RPAS-VERIFIED-001',
            issueDate: now()->subYear(),
            expirationDate: now()->addYear(),
            reviewed: true,
        );

        $this->credential(
            email: 'pilot.pending@primeunits.test',
            credentialType: 'Remote Pilot Certificate',
            status: DronePilotCredential::StatusPendingReview,
            number: 'DEMO-RPAS-PENDING-002',
            issueDate: now()->subMonth(),
            expirationDate: now()->addMonths(11),
            reviewed: false,
        );

        $this->credential(
            email: 'pilot.expired@primeunits.test',
            credentialType: 'Remote Pilot Certificate',
            status: DronePilotCredential::StatusExpired,
            number: 'DEMO-RPAS-EXPIRED-003',
            issueDate: now()->subYears(2),
            expirationDate: now()->subMonths(2),
            reviewed: true,
        );
    }

    private function credential(
        string $email,
        string $credentialType,
        string $status,
        string $number,
        CarbonInterface $issueDate,
        CarbonInterface $expirationDate,
        bool $reviewed,
    ): void {
        $user = User::query()->where('email', $email)->firstOrFail();
        $reviewer = $reviewed ? User::query()->whereHas('roles', fn ($q) => $q->where('name', 'superadmin'))->first() : null;

        $credential = DronePilotCredential::query()->updateOrCreate(
            ['user_id' => $user->id, 'credential_type' => $credentialType],
            [
                'issuing_authority' => 'Demo Civil Aviation Authority',
                'credential_number' => $number,
                'issue_date' => $issueDate,
                'expiration_date' => $expirationDate,
                'country' => 'Demo Jurisdiction',
                'status' => $status,
                'reviewer_id' => $reviewer?->id,
                'reviewed_at' => $reviewed ? now()->subWeeks(2) : null,
                'reviewer_notes' => $status === DronePilotCredential::StatusVerified ? 'Documents verified against demo requirements.' : null,
            ],
        );

        DroneCredentialAuditLog::query()->updateOrCreate(
            ['drone_pilot_credential_id' => $credential->id, 'action' => DroneCredentialAuditLog::ActionSubmitted],
            [
                'actor_id' => $user->id,
                'from_status' => null,
                'to_status' => DronePilotCredential::StatusPendingReview,
            ],
        );

        if ($reviewed) {
            $action = $status === DronePilotCredential::StatusVerified
                ? DroneCredentialAuditLog::ActionApproved
                : DroneCredentialAuditLog::ActionExpired;

            DroneCredentialAuditLog::query()->updateOrCreate(
                ['drone_pilot_credential_id' => $credential->id, 'action' => $action],
                [
                    'actor_id' => $reviewer?->id,
                    'from_status' => DronePilotCredential::StatusPendingReview,
                    'to_status' => $status,
                ],
            );
        }
    }
}
