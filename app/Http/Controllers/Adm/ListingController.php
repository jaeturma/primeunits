<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Services\ApprovalWorkflowService;
use App\Services\NotificationService;
use App\Support\ResolvesListingStockImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ListingController extends Controller
{
    use ResolvesListingStockImage;

    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in([
                Listing::StatusPending,
                Listing::StatusAgentValidated,
                Listing::StatusManagerAccepted,
                Listing::StatusApproved,
                Listing::StatusRejected,
            ])],
        ]);

        $listings = Listing::query()
            ->with([
                'category:id,name,slug',
                'user:id,name,email',
                'images',
                'attachments',
                'specValues' => fn ($query) => $query
                    ->whereHas('specField', fn ($query) => $query->where('is_classification', true))
                    ->with('specField:id,name,is_classification'),
            ])
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->latest()
            ->get()
            ->map(fn (Listing $listing): array => $this->serializeListing($listing));

        return Inertia::render('adm/listings/index', [
            'listings' => $listings,
            'filters' => [
                'status' => $filters['status'] ?? '',
            ],
            'statuses' => [
                ['value' => '', 'label' => 'All statuses'],
                ['value' => Listing::StatusPending, 'label' => 'Pending'],
                ['value' => Listing::StatusAgentValidated, 'label' => 'Agent Validated'],
                ['value' => Listing::StatusManagerAccepted, 'label' => 'Manager Accepted'],
                ['value' => Listing::StatusApproved, 'label' => 'Approved'],
                ['value' => Listing::StatusRejected, 'label' => 'Rejected'],
            ],
        ]);
    }

    public function approve(Request $request, Listing $listing, NotificationService $notifications, ApprovalWorkflowService $workflow): RedirectResponse
    {
        $workflow->advance($listing, $request->user());

        if ($listing->isApproved()) {
            $notifications->send(
                user: $listing->user,
                event: 'listing.approved',
                title: 'Listing approved',
                message: "{$listing->title} is now live.",
                url: "/listings/{$listing->id}",
            );
        }

        return back();
    }

    public function reject(Request $request, Listing $listing, NotificationService $notifications): RedirectResponse
    {
        $validated = $request->validate([
            'rejected_reason' => ['required', 'string', 'max:2000'],
        ]);

        $listing->update([
            'status' => Listing::StatusRejected,
            'approved_at' => null,
            'rejected_reason' => $validated['rejected_reason'],
        ]);

        $notifications->send(
            user: $listing->user,
            event: 'listing.rejected',
            title: 'Listing rejected',
            message: "{$listing->title} was rejected: {$validated['rejected_reason']}",
            url: '/seller/listings',
        );

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeListing(Listing $listing): array
    {
        $primaryImage = $listing->images->firstWhere('is_primary', true) ?? $listing->images->first();

        return [
            'id' => $listing->id,
            'title' => $listing->title,
            'price' => $listing->price,
            'condition' => $listing->condition,
            'brand' => $listing->brand,
            'model' => $listing->model,
            'status' => $listing->status,
            'status_label' => $listing->statusLabel(),
            'rejected_reason' => $listing->rejected_reason,
            'approved_at' => $listing->approved_at?->toISOString(),
            'category' => $listing->category,
            'seller' => [
                'name' => $listing->user->name,
                'email' => $listing->user->email,
            ],
            'image_url' => $this->listingImageUrl($primaryImage, $listing),
            'documents' => collect([
                $listing->valid_id_file ? ['label' => 'Valid ID', 'url' => Storage::disk('public')->url($listing->valid_id_file)] : null,
                $listing->or_cr_file ? ['label' => 'OR/CR', 'url' => Storage::disk('public')->url($listing->or_cr_file)] : null,
                ...$listing->attachments->map(fn ($attachment): array => ['label' => $attachment->name, 'url' => $attachment->url()]),
            ])->filter()->values(),
            'can_approve' => app(ApprovalWorkflowService::class)->actionLabel($listing, request()->user()) !== null,
            'action_label' => app(ApprovalWorkflowService::class)->actionLabel($listing, request()->user()),
            'can_reject' => $listing->status !== Listing::StatusRejected,
        ];
    }
}
