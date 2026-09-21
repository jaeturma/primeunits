<?php

namespace App\Console\Commands;

use App\Models\DroneCredentialAuditLog;
use App\Models\DronePilotCredential;
use Illuminate\Console\Command;

/**
 * Flips verified drone pilot credentials whose expiration date has passed
 * to the Expired status and records an audit entry, so the stored status
 * (and the admin review queue) reflects reality without relying solely on
 * the on-the-fly isExpired() check used for booking-eligibility gating.
 */
class ExpireDronePilotCredentials extends Command
{
    protected $signature = 'drone-credentials:expire';

    protected $description = 'Mark verified drone pilot credentials past their expiration date as Expired';

    public function handle(): int
    {
        $expired = DronePilotCredential::query()
            ->where('status', DronePilotCredential::StatusVerified)
            ->whereNotNull('expiration_date')
            ->where('expiration_date', '<', now()->toDateString())
            ->get();

        foreach ($expired as $credential) {
            $credential->update(['status' => DronePilotCredential::StatusExpired]);

            DroneCredentialAuditLog::query()->create([
                'drone_pilot_credential_id' => $credential->id,
                'actor_id' => null,
                'action' => DroneCredentialAuditLog::ActionExpired,
                'from_status' => DronePilotCredential::StatusVerified,
                'to_status' => DronePilotCredential::StatusExpired,
                'notes' => 'Automatically expired by scheduled task.',
            ]);
        }

        $this->info("Expired {$expired->count()} drone pilot credential(s).");

        return self::SUCCESS;
    }
}
