<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use App\Models\MembershipApplication;
use App\Models\MembershipApplicationLog;
use App\Models\User;
use App\Services\MembershipAccessService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class MembershipApplicationController extends Controller
{
    public function index(Request $request): Response
    {
        $applications = MembershipApplication::query()
            ->with(['user:id,name,email', 'dealerProfile:id,business_name', 'documents', 'reviewer:id,name'])
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')->toString()))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')->toString()))
            ->latest()
            ->paginate(20)
            ->through(fn (MembershipApplication $a): array => [
                'id' => $a->id,
                'type' => $a->type,
                'target_level' => $a->target_level,
                'status' => $a->status,
                'status_label' => $a->statusLabel(),
                'user' => $a->user ? ['id' => $a->user->id, 'name' => $a->user->name, 'email' => $a->user->email] : null,
                'store_name' => $a->dealerProfile?->business_name,
                'applicant_notes' => $a->applicant_notes,
                'reviewer' => $a->reviewer?->name,
                'document_count' => $a->documents->count(),
                'submitted_at' => $a->submitted_at?->toISOString(),
            ]);

        return Inertia::render('adm/membership-applications/index', [
            'applications' => $applications,
            'filters' => [
                'type' => $request->string('type')->toString(),
                'status' => $request->string('status')->toString(),
            ],
            'types' => [MembershipApplication::TypeBuyer, MembershipApplication::TypeSeller, MembershipApplication::TypeStore],
            'statuses' => [
                MembershipApplication::StatusInvited,
                MembershipApplication::StatusPendingReview,
                MembershipApplication::StatusInfoRequired,
                MembershipApplication::StatusApproved,
                MembershipApplication::StatusDeclined,
                MembershipApplication::StatusRejected,
                MembershipApplication::StatusSuspended,
                MembershipApplication::StatusExpired,
            ],
        ]);
    }

    /**
     * Admin-initiated invitation: the only path to Silver/Gold buyer
     * access. The invited user must still explicitly accept it from
     * their account settings — inviting never grants access by itself.
     */
    public function invite(Request $request, NotificationService $notifications): RedirectResponse
    {
        Gate::authorize('invite', MembershipApplication::class);

        $data = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'type' => ['required', Rule::in([MembershipApplication::TypeBuyer, MembershipApplication::TypeSeller])],
            'target_level' => ['required', Rule::in(['silver', 'gold'])],
            'reviewer_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $invitee = User::query()->where('email', $data['email'])->firstOrFail();

        $application = MembershipApplication::query()->updateOrCreate(
            ['user_id' => $invitee->id, 'type' => $data['type'], 'target_level' => $data['target_level']],
            [
                'status' => MembershipApplication::StatusInvited,
                'reviewer_id' => $request->user()->id,
                'reviewer_notes' => $data['reviewer_notes'] ?? null,
                'reviewed_at' => now(),
            ],
        );

        MembershipApplicationLog::query()->create([
            'membership_application_id' => $application->id,
            'actor_id' => $request->user()->id,
            'action' => MembershipApplicationLog::ActionInvited,
            'from_status' => null,
            'to_status' => MembershipApplication::StatusInvited,
        ]);

        $notifications->send(
            user: $invitee,
            event: 'membership_application.invited',
            title: 'You have been invited to upgrade your account',
            message: "PrimeUnits has invited you to {$data['target_level']} {$data['type']} access. This is optional — visit your account settings to accept or decline.",
            url: '/settings/membership',
        );

        return back()->with('success', 'Invitation sent.');
    }

    public function approve(Request $request, MembershipApplication $membershipApplication, MembershipAccessService $access, NotificationService $notifications): RedirectResponse
    {
        Gate::authorize('review', $membershipApplication);

        $this->transition($membershipApplication, $request, MembershipApplication::StatusApproved, MembershipApplicationLog::ActionApproved);

        $access->grantApplication($membershipApplication);

        $notifications->send(
            user: $membershipApplication->user,
            event: 'membership_application.approved',
            title: 'Application approved',
            message: ucfirst($membershipApplication->type)." access approved at the {$membershipApplication->target_level} level.",
            url: '/membership/applications',
        );

        return back()->with('success', 'Application approved.');
    }

    public function requestInfo(Request $request, MembershipApplication $membershipApplication, NotificationService $notifications): RedirectResponse
    {
        Gate::authorize('review', $membershipApplication);

        $request->validate(['notes' => ['required', 'string', 'max:2000']]);

        $this->transition($membershipApplication, $request, MembershipApplication::StatusInfoRequired, MembershipApplicationLog::ActionInfoRequested, [
            'applicant_visible_notes' => $request->string('notes')->toString(),
        ]);

        $notifications->send(
            user: $membershipApplication->user,
            event: 'membership_application.info_required',
            title: 'Additional information required',
            message: $request->string('notes')->toString(),
            url: '/membership/applications',
        );

        return back()->with('success', 'Requested additional information.');
    }

    public function reject(Request $request, MembershipApplication $membershipApplication, NotificationService $notifications): RedirectResponse
    {
        Gate::authorize('review', $membershipApplication);

        $request->validate(['reason' => ['required', 'string', 'max:2000']]);

        $this->transition($membershipApplication, $request, MembershipApplication::StatusRejected, MembershipApplicationLog::ActionRejected, [
            'rejection_reason' => $request->string('reason')->toString(),
        ]);

        $notifications->send(
            user: $membershipApplication->user,
            event: 'membership_application.rejected',
            title: 'Application rejected',
            message: $request->string('reason')->toString(),
            url: '/membership/applications',
        );

        return back()->with('success', 'Application rejected.');
    }

    public function suspend(Request $request, MembershipApplication $membershipApplication, MembershipAccessService $access, NotificationService $notifications): RedirectResponse
    {
        Gate::authorize('review', $membershipApplication);

        $request->validate(['reason' => ['required', 'string', 'max:2000']]);
        $reason = $request->string('reason')->toString();

        $this->transition($membershipApplication, $request, MembershipApplication::StatusSuspended, MembershipApplicationLog::ActionSuspended, [
            'reviewer_notes' => $reason,
        ]);

        $access->suspendAccess($membershipApplication->user, $membershipApplication->type, $reason);

        $notifications->send(
            user: $membershipApplication->user,
            event: 'membership_application.suspended',
            title: ucfirst($membershipApplication->type).' access suspended',
            message: $reason,
            url: '/membership/applications',
        );

        return back()->with('success', 'Access suspended.');
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    private function transition(MembershipApplication $application, Request $request, string $toStatus, string $action, array $extra = []): void
    {
        $fromStatus = $application->status;

        $application->update([
            ...$extra,
            'status' => $toStatus,
            'reviewer_id' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        MembershipApplicationLog::query()->create([
            'membership_application_id' => $application->id,
            'actor_id' => $request->user()->id,
            'action' => $action,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
        ]);
    }
}
