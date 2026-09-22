<?php

namespace App\Http\Controllers;

use App\Models\DealerProfile;
use App\Models\MembershipApplication;
use App\Models\MembershipApplicationDocument;
use App\Models\MembershipApplicationLog;
use App\Services\MembershipAccessService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MembershipApplicationController extends Controller
{
    public function index(Request $request): Response
    {
        $applications = $request->user()->membershipApplications()
            ->with('documents')
            ->latest()
            ->get()
            ->map(fn (MembershipApplication $a): array => $this->serialize($a));

        return Inertia::render('membership/applications/index', [
            'applications' => $applications,
        ]);
    }

    public function store(Request $request, NotificationService $notifications): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', Rule::in([
                MembershipApplication::TypeBuyer,
                MembershipApplication::TypeSeller,
                MembershipApplication::TypeStore,
            ])],
            // Silver/Gold buyer access is invitation-only and is never
            // self-applied for; only the "regular" baseline is open here.
            'target_level' => ['required', Rule::in(['regular', 'silver', 'gold']), function (string $attribute, mixed $value, \Closure $fail) use ($request): void {
                if ($request->input('type') === MembershipApplication::TypeBuyer && $value !== 'regular') {
                    $fail('Silver and Gold buyer access is by invitation only and cannot be requested here.');
                }
            }],
            'applicant_notes' => ['nullable', 'string', 'max:5000'],
            'business_name' => [Rule::requiredIf($request->input('type') === MembershipApplication::TypeStore), 'nullable', 'string', 'max:255'],
            'contact_number' => [Rule::requiredIf($request->input('type') === MembershipApplication::TypeStore), 'nullable', 'string', 'max:30'],
            'documents' => ['nullable', 'array', 'max:10'],
            'documents.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'document_labels' => ['nullable', 'array'],
            'document_labels.*' => ['string', 'max:255'],
        ]);

        $dealerProfile = null;

        if ($data['type'] === MembershipApplication::TypeStore) {
            $dealerProfile = $request->user()->dealerProfile()->firstOrCreate([], [
                'business_name' => $data['business_name'] ?? $request->user()->name."'s Store",
                'slug' => Str::slug(($data['business_name'] ?? $request->user()->name.'-store').'-'.$request->user()->id),
                'contact_number' => $data['contact_number'] ?? '',
                'status' => DealerProfile::StatusPending,
                'store_tier' => DealerProfile::StoreTierRegular,
                'store_tier_status' => DealerProfile::StoreTierStatusPending,
            ]);
        }

        $application = MembershipApplication::query()->create([
            'user_id' => $request->user()->id,
            'type' => $data['type'],
            'target_level' => $data['target_level'],
            'dealer_profile_id' => $dealerProfile?->id,
            'status' => MembershipApplication::StatusPendingReview,
            'applicant_notes' => $data['applicant_notes'] ?? null,
            'submitted_at' => now(),
        ]);

        foreach ($request->file('documents', []) as $index => $file) {
            $application->documents()->create([
                'label' => $request->input("document_labels.{$index}", $file->getClientOriginalName()),
                'path' => $file->store('membership-applications', 'local'),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        MembershipApplicationLog::query()->create([
            'membership_application_id' => $application->id,
            'actor_id' => $request->user()->id,
            'action' => MembershipApplicationLog::ActionSubmitted,
            'from_status' => null,
            'to_status' => MembershipApplication::StatusPendingReview,
        ]);

        $notifications->send(
            user: $request->user(),
            event: 'membership_application.submitted',
            title: 'Application submitted',
            message: ucfirst($data['type'])." application for {$data['target_level']} level submitted for review.",
            url: '/membership/applications',
        );

        return back()->with('success', 'Application submitted for review.');
    }

    public function withdraw(Request $request, MembershipApplication $membershipApplication): RedirectResponse
    {
        Gate::authorize('withdraw', $membershipApplication);

        $from = $membershipApplication->status;
        $membershipApplication->update(['status' => MembershipApplication::StatusWithdrawn]);

        MembershipApplicationLog::query()->create([
            'membership_application_id' => $membershipApplication->id,
            'actor_id' => $request->user()->id,
            'action' => MembershipApplicationLog::ActionWithdrawn,
            'from_status' => $from,
            'to_status' => MembershipApplication::StatusWithdrawn,
        ]);

        return back()->with('success', 'Application withdrawn.');
    }

    public function accept(Request $request, MembershipApplication $membershipApplication, MembershipAccessService $access): RedirectResponse
    {
        Gate::authorize('respond', $membershipApplication);

        $membershipApplication->update([
            'status' => MembershipApplication::StatusApproved,
            'reviewed_at' => now(),
        ]);

        $access->grantApplication($membershipApplication);

        MembershipApplicationLog::query()->create([
            'membership_application_id' => $membershipApplication->id,
            'actor_id' => $request->user()->id,
            'action' => MembershipApplicationLog::ActionAccepted,
            'from_status' => MembershipApplication::StatusInvited,
            'to_status' => MembershipApplication::StatusApproved,
        ]);

        return back()->with('success', 'Invitation accepted.');
    }

    public function decline(Request $request, MembershipApplication $membershipApplication): RedirectResponse
    {
        Gate::authorize('respond', $membershipApplication);

        $membershipApplication->update([
            'status' => MembershipApplication::StatusDeclined,
            'reviewed_at' => now(),
        ]);

        MembershipApplicationLog::query()->create([
            'membership_application_id' => $membershipApplication->id,
            'actor_id' => $request->user()->id,
            'action' => MembershipApplicationLog::ActionDeclined,
            'from_status' => MembershipApplication::StatusInvited,
            'to_status' => MembershipApplication::StatusDeclined,
        ]);

        return back()->with('success', 'Invitation declined.');
    }

    public function document(Request $request, MembershipApplicationDocument $document): StreamedResponse
    {
        Gate::authorize('view', $document->application);

        abort_unless(Storage::disk('local')->exists($document->path), 404);

        return Storage::disk('local')->response($document->path);
    }

    /**
     * @return array<string, mixed>
     */
    private function serialize(MembershipApplication $application): array
    {
        return [
            'id' => $application->id,
            'type' => $application->type,
            'target_level' => $application->target_level,
            'status' => $application->status,
            'status_label' => $application->statusLabel(),
            'applicant_notes' => $application->applicant_notes,
            'applicant_visible_notes' => $application->applicant_visible_notes,
            'rejection_reason' => $application->rejection_reason,
            'submitted_at' => $application->submitted_at?->toISOString(),
            'reviewed_at' => $application->reviewed_at?->toISOString(),
            'documents' => $application->documents->map(fn (MembershipApplicationDocument $d): array => [
                'id' => $d->id,
                'label' => $d->label,
            ]),
            'is_open' => $application->isOpen(),
            'is_pending_acceptance' => $application->isPendingAcceptance(),
        ];
    }
}
