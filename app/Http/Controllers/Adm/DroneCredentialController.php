<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use App\Models\DroneCredentialAuditLog;
use App\Models\DronePilotCredential;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DroneCredentialController extends Controller
{
    public function index(Request $request): Response
    {
        $credentials = DronePilotCredential::query()
            ->with('user:id,name,email', 'reviewer:id,name')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')->toString()))
            ->latest()
            ->paginate(20)
            ->through(fn (DronePilotCredential $c): array => [
                'id' => $c->id,
                'user' => $c->user ? ['id' => $c->user->id, 'name' => $c->user->name, 'email' => $c->user->email] : null,
                'credential_type' => $c->credential_type,
                'issuing_authority' => $c->issuing_authority,
                'masked_credential_number' => $c->maskedCredentialNumber(),
                'country' => $c->country,
                'issue_date' => $c->issue_date?->toDateString(),
                'expiration_date' => $c->expiration_date?->toDateString(),
                'status' => $c->status,
                'status_label' => $c->statusLabel(),
                'is_expired' => $c->isExpired(),
                'reviewer' => $c->reviewer?->name,
                'reviewed_at' => $c->reviewed_at?->toISOString(),
                'has_front_document' => $c->front_document_path !== null,
                'has_back_document' => $c->back_document_path !== null,
                'has_supporting_document' => $c->supporting_document_path !== null,
                'created_at' => $c->created_at?->toISOString(),
            ]);

        return Inertia::render('adm/drone-credentials/index', [
            'credentials' => $credentials,
            'statuses' => [
                DronePilotCredential::StatusPendingReview,
                DronePilotCredential::StatusVerified,
                DronePilotCredential::StatusRejected,
                DronePilotCredential::StatusExpired,
                DronePilotCredential::StatusSuspended,
            ],
            'filters' => ['status' => $request->string('status')->toString()],
        ]);
    }

    public function approve(Request $request, DronePilotCredential $droneCredential): RedirectResponse
    {
        $this->transition($droneCredential, $request, DronePilotCredential::StatusVerified, DroneCredentialAuditLog::ActionApproved);

        return back()->with('success', 'Credential verified.');
    }

    public function reject(Request $request, DronePilotCredential $droneCredential): RedirectResponse
    {
        $request->validate(['reason' => ['required', 'string', 'max:1000']]);

        $this->transition($droneCredential, $request, DronePilotCredential::StatusRejected, DroneCredentialAuditLog::ActionRejected, [
            'rejection_reason' => $request->string('reason')->toString(),
        ]);

        return back()->with('success', 'Credential rejected.');
    }

    public function suspend(Request $request, DronePilotCredential $droneCredential): RedirectResponse
    {
        $request->validate(['reason' => ['required', 'string', 'max:1000']]);

        $this->transition($droneCredential, $request, DronePilotCredential::StatusSuspended, DroneCredentialAuditLog::ActionSuspended, [
            'suspension_reason' => $request->string('reason')->toString(),
        ]);

        return back()->with('success', 'Credential suspended.');
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    private function transition(DronePilotCredential $credential, Request $request, string $toStatus, string $action, array $extra = []): void
    {
        $fromStatus = $credential->status;

        $credential->update([
            ...$extra,
            'status' => $toStatus,
            'reviewer_id' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        DroneCredentialAuditLog::query()->create([
            'drone_pilot_credential_id' => $credential->id,
            'actor_id' => $request->user()->id,
            'action' => $action,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
        ]);
    }
}
