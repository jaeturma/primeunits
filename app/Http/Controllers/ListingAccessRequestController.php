<?php

namespace App\Http\Controllers;

use App\Models\ConfidentialAccessLog;
use App\Models\ConfidentialityAcknowledgement;
use App\Models\ConfidentialityNotice;
use App\Models\Listing;
use App\Models\ListingAccessRequest;
use App\Services\ListingVisibilityService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ListingAccessRequestController extends Controller
{
    public function store(Request $request, Listing $listing, ListingVisibilityService $visibility, NotificationService $notifications): RedirectResponse
    {
        abort_unless($listing->isRestrictedVisibility(), 404);
        abort_unless($visibility->canRequestAccess($request->user(), $listing), 403);

        $data = $request->validate([
            'message' => ['nullable', 'string', 'max:2000'],
            'confidentiality_acknowledged' => ['required', 'accepted'],
        ]);

        $accessRequest = ListingAccessRequest::query()->updateOrCreate(
            ['listing_id' => $listing->id, 'user_id' => $request->user()->id],
            [
                'status' => ListingAccessRequest::StatusPending,
                'message' => $data['message'] ?? null,
                'reviewer_id' => null,
                'reviewed_at' => null,
                'rejection_reason' => null,
                'expires_at' => null,
            ],
        );

        $notice = ConfidentialityNotice::active();

        ConfidentialityAcknowledgement::query()->create([
            'user_id' => $request->user()->id,
            'listing_access_request_id' => $accessRequest->id,
            'confidentiality_notice_id' => $notice?->id,
            'notice_version' => $notice?->version ?? 'unversioned',
            'notice_text' => $notice?->body ?? ConfidentialityNotice::DEFAULT_NOTICE_TEXT,
            'acknowledged_at' => now(),
            'ip_address' => $request->ip(),
        ]);

        $visibility->logAccess($request->user(), $listing, ConfidentialAccessLog::ActionRequestedAccess);

        $notifications->send(
            user: $listing->user,
            event: 'listing_access.requested',
            title: 'Restricted listing access requested',
            message: "{$request->user()->name} requested access to \"{$listing->title}\".",
            url: '/listing-access/review',
        );

        return back()->with('success', 'Access request submitted.');
    }

    public function myRequests(Request $request): Response
    {
        $requests = $request->user()->listingAccessRequests()
            ->with('listing:id,title,slug,visibility_level')
            ->latest()
            ->get()
            ->map(fn (ListingAccessRequest $r): array => $this->serialize($r));

        return Inertia::render('listing-access/mine', ['requests' => $requests]);
    }

    public function review(Request $request): Response
    {
        $canManageAll = $request->user()->hasPermission('manage_listing_access');

        $requests = ListingAccessRequest::query()
            ->with('listing:id,title,slug,user_id', 'user:id,name,email')
            ->when(! $canManageAll, fn ($q) => $q->whereHas('listing', fn ($q2) => $q2->where('user_id', $request->user()->id)))
            ->latest()
            ->get()
            ->map(fn (ListingAccessRequest $r): array => $this->serialize($r));

        return Inertia::render('listing-access/review', ['requests' => $requests]);
    }

    public function approve(Request $request, ListingAccessRequest $listingAccessRequest, NotificationService $notifications): RedirectResponse
    {
        Gate::authorize('review', $listingAccessRequest);

        $listingAccessRequest->update([
            'status' => ListingAccessRequest::StatusApproved,
            'reviewer_id' => $request->user()->id,
            'reviewed_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);

        app(ListingVisibilityService::class)->logAccess($listingAccessRequest->user, $listingAccessRequest->listing, ConfidentialAccessLog::ActionAccessGranted);

        $notifications->send(
            user: $listingAccessRequest->user,
            event: 'listing_access.approved',
            title: 'Access approved',
            message: "Your access request for \"{$listingAccessRequest->listing->title}\" was approved.",
            url: "/listings/{$listingAccessRequest->listing->slug}",
        );

        return back()->with('success', 'Access approved.');
    }

    public function reject(Request $request, ListingAccessRequest $listingAccessRequest, NotificationService $notifications): RedirectResponse
    {
        Gate::authorize('review', $listingAccessRequest);

        $request->validate(['reason' => ['required', 'string', 'max:1000']]);

        $listingAccessRequest->update([
            'status' => ListingAccessRequest::StatusRejected,
            'reviewer_id' => $request->user()->id,
            'reviewed_at' => now(),
            'rejection_reason' => $request->string('reason')->toString(),
        ]);

        $notifications->send(
            user: $listingAccessRequest->user,
            event: 'listing_access.rejected',
            title: 'Access rejected',
            message: $request->string('reason')->toString(),
            url: '/listing-access/mine',
        );

        return back()->with('success', 'Access rejected.');
    }

    public function revoke(Request $request, ListingAccessRequest $listingAccessRequest, NotificationService $notifications): RedirectResponse
    {
        Gate::authorize('revoke', $listingAccessRequest);

        $listingAccessRequest->update([
            'status' => ListingAccessRequest::StatusRevoked,
            'reviewer_id' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        app(ListingVisibilityService::class)->logAccess($listingAccessRequest->user, $listingAccessRequest->listing, ConfidentialAccessLog::ActionAccessRevoked);

        $notifications->send(
            user: $listingAccessRequest->user,
            event: 'listing_access.revoked',
            title: 'Access revoked',
            message: "Your access to \"{$listingAccessRequest->listing->title}\" has been revoked.",
            url: '/listing-access/mine',
        );

        return back()->with('success', 'Access revoked.');
    }

    /**
     * @return array<string, mixed>
     */
    private function serialize(ListingAccessRequest $r): array
    {
        return [
            'id' => $r->id,
            'status' => $r->status,
            'status_label' => $r->statusLabel(),
            'message' => $r->message,
            'rejection_reason' => $r->rejection_reason,
            'expires_at' => $r->expires_at?->toISOString(),
            'listing' => $r->listing ? ['id' => $r->listing->id, 'title' => $r->listing->title, 'slug' => $r->listing->slug] : null,
            'user' => $r->user ? ['id' => $r->user->id, 'name' => $r->user->name, 'email' => $r->user->email] : null,
            'created_at' => $r->created_at?->toISOString(),
        ];
    }
}
