<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadStatusRequest;
use App\Models\Lead;
use App\Models\Listing;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeadController extends Controller
{
    public function store(StoreLeadRequest $request, NotificationService $notifications): RedirectResponse
    {
        $listing = Listing::query()->findOrFail($request->integer('listing_id'));

        $message = $request->string('message')->toString() ?: 'Is this unit available?';
        $lead = Lead::query()->firstOrCreate(
            [
                'listing_id' => $listing->id,
                'buyer_id' => $request->user()->id,
                'seller_id' => $listing->user_id,
            ],
            [
                'reference_code' => Lead::generateReferenceCode(),
                'status' => Lead::StatusInquiry,
                'message' => $message,
            ],
        );

        if ($lead->wasRecentlyCreated === false) {
            $lead->update([
                'message' => $message,
                'status' => $lead->status === Lead::StatusCancelled ? Lead::StatusInquiry : $lead->status,
            ]);
        }

        $lead->messages()->create([
            'user_id' => $request->user()->id,
            'body' => $message,
        ]);

        $notifications->send(
            user: $listing->user,
            event: 'lead.created',
            title: 'New inquiry',
            message: "{$request->user()->name} inquired about {$listing->title}. Reference {$lead->reference_code}.",
            url: '/seller/leads',
        );

        return back()->with('inquiry', [
            'reference_code' => $lead->reference_code,
            'seller_name' => $listing->user->name,
        ]);
    }

    public function myLeads(Request $request): Response
    {
        $leads = $request->user()
            ->buyerLeads()
            ->with(['listing.category:id,name,slug', 'seller:id,name,email', 'transaction', 'messages.user:id,name'])
            ->latest()
            ->get()
            ->map(fn (Lead $lead): array => $this->serializeLead($lead));

        return Inertia::render('buyer/leads/index', [
            'leads' => $leads,
        ]);
    }

    public function sellerLeads(Request $request): Response
    {
        $leads = $request->user()
            ->sellerLeads()
            ->with(['listing.category:id,name,slug', 'buyer:id,name,email', 'transaction', 'messages.user:id,name'])
            ->latest()
            ->get()
            ->map(fn (Lead $lead): array => $this->serializeLead($lead));

        return Inertia::render('seller/leads/index', [
            'leads' => $leads,
            'statuses' => [
                ['value' => Lead::StatusContacted, 'label' => 'Mark contacted'],
                ['value' => Lead::StatusNegotiating, 'label' => 'Mark negotiating'],
                ['value' => Lead::StatusReserved, 'label' => 'Mark reserved'],
                ['value' => Lead::StatusCancelled, 'label' => 'Cancel'],
            ],
        ]);
    }

    public function updateStatus(UpdateLeadStatusRequest $request, Lead $lead, NotificationService $notifications): RedirectResponse
    {
        $status = $request->string('status')->toString();

        $lead->update([
            'status' => $status,
            'contacted_at' => $status === Lead::StatusContacted ? now() : $lead->contacted_at,
            'negotiated_at' => $status === Lead::StatusNegotiating ? now() : $lead->negotiated_at,
        ]);

        $notifications->send(
            user: $lead->buyer,
            event: 'lead.status_updated',
            title: 'Inquiry status updated',
            message: "Your inquiry {$lead->reference_code} is now {$lead->statusLabel()}.",
            url: '/buyer/leads',
        );

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeLead(Lead $lead): array
    {
        return [
            'id' => $lead->id,
            'reference_code' => $lead->reference_code,
            'status' => $lead->status,
            'status_label' => $lead->statusLabel(),
            'message' => $lead->message,
            'messages' => $lead->messages->map(fn ($message): array => [
                'id' => $message->id,
                'body' => $message->body,
                'created_at' => $message->created_at?->toISOString(),
                'user' => [
                    'id' => $message->user->id,
                    'name' => $message->user->name,
                ],
            ]),
            'created_at' => $lead->created_at?->toISOString(),
            'listing' => [
                'id' => $lead->listing->id,
                'title' => $lead->listing->title,
                'price' => $lead->listing->price,
                'category' => $lead->listing->category,
            ],
            'buyer' => $lead->buyer ? [
                'id' => $lead->buyer->id,
                'name' => $lead->buyer->name,
                'email' => $lead->buyer->email,
            ] : null,
            'seller' => $lead->seller ? [
                'id' => $lead->seller->id,
                'name' => $lead->seller->name,
                'email' => $lead->seller->email,
            ] : null,
            'transaction' => $lead->transaction ? [
                'id' => $lead->transaction->id,
                'status' => $lead->transaction->status,
                'status_label' => $lead->transaction->statusLabel(),
                'agreed_price' => $lead->transaction->agreed_price,
                'commission_amount' => $lead->transaction->commission_amount,
                'buyer_confirmed' => $lead->transaction->buyer_confirmed,
                'seller_confirmed' => $lead->transaction->seller_confirmed,
            ] : null,
        ];
    }
}
