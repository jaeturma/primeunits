<?php

namespace App\Http\Controllers;

use App\Models\DroneCredentialAuditLog;
use App\Models\DronePilotCredential;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DronePilotCredentialController extends Controller
{
    public function show(Request $request): Response
    {
        $credentials = $request->user()->droneCredentials()->latest()->get();

        return Inertia::render('drone-credentials/show', [
            'credentials' => $credentials->map(fn (DronePilotCredential $c): array => $this->serialize($c)),
            'is_verified' => $request->user()->hasVerifiedDroneCredential(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'credential_type' => ['required', 'string', 'max:255'],
            'issuing_authority' => ['nullable', 'string', 'max:255'],
            'credential_number' => ['nullable', 'string', 'max:255'],
            'issue_date' => ['nullable', 'date'],
            'expiration_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'country' => ['nullable', 'string', 'max:255'],
            'front_document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'back_document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'supporting_document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        ]);

        $credential = DronePilotCredential::query()->create([
            ...collect($data)->except(['front_document', 'back_document', 'supporting_document'])->all(),
            'user_id' => $request->user()->id,
            'status' => DronePilotCredential::StatusPendingReview,
            'front_document_path' => $request->hasFile('front_document') ? $request->file('front_document')->store('drone-credentials', 'local') : null,
            'back_document_path' => $request->hasFile('back_document') ? $request->file('back_document')->store('drone-credentials', 'local') : null,
            'supporting_document_path' => $request->hasFile('supporting_document') ? $request->file('supporting_document')->store('drone-credentials', 'local') : null,
        ]);

        DroneCredentialAuditLog::query()->create([
            'drone_pilot_credential_id' => $credential->id,
            'actor_id' => $request->user()->id,
            'action' => DroneCredentialAuditLog::ActionSubmitted,
            'from_status' => null,
            'to_status' => DronePilotCredential::StatusPendingReview,
        ]);

        return back()->with('success', 'Credential submitted for review.');
    }

    public function document(Request $request, DronePilotCredential $credential, string $type): StreamedResponse
    {
        Gate::authorize('viewDocuments', $credential);

        $path = match ($type) {
            'front' => $credential->front_document_path,
            'back' => $credential->back_document_path,
            'supporting' => $credential->supporting_document_path,
            default => null,
        };

        abort_if($path === null || ! Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }

    /**
     * @return array<string, mixed>
     */
    private function serialize(DronePilotCredential $c): array
    {
        return [
            'id' => $c->id,
            'credential_type' => $c->credential_type,
            'issuing_authority' => $c->issuing_authority,
            'masked_credential_number' => $c->maskedCredentialNumber(),
            'issue_date' => $c->issue_date?->toDateString(),
            'expiration_date' => $c->expiration_date?->toDateString(),
            'country' => $c->country,
            'status' => $c->status,
            'status_label' => $c->statusLabel(),
            'is_expired' => $c->isExpired(),
            'rejection_reason' => $c->rejection_reason,
            'suspension_reason' => $c->suspension_reason,
            'reviewer_notes' => $c->reviewer_notes,
            'has_front_document' => $c->front_document_path !== null,
            'has_back_document' => $c->back_document_path !== null,
            'has_supporting_document' => $c->supporting_document_path !== null,
            'created_at' => $c->created_at?->toISOString(),
        ];
    }
}
