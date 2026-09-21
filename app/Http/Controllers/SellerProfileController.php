<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSellerProfileRequest;
use App\Models\Listing;
use App\Models\SellerProfile;
use App\Support\ResolvesListingStockImage;
use App\Support\StoresResourceAttachments;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SellerProfileController extends Controller
{
    use ResolvesListingStockImage;
    use StoresResourceAttachments;

    public function apply(Request $request): Response|RedirectResponse
    {
        $profile = $request->user()->sellerProfile;

        if ($profile && ! $profile->isRejected()) {
            return to_route('seller.status');
        }

        return Inertia::render('seller/apply', [
            'profile' => $profile ? $this->serializeProfile($profile->load('attachments')) : null,
            'sellerTypes' => [
                ['value' => 'individual', 'label' => 'Individual'],
                ['value' => 'business', 'label' => 'Business'],
            ],
        ]);
    }

    public function store(StoreSellerProfileRequest $request): RedirectResponse
    {
        $profile = $request->user()->sellerProfile()->create([
            ...$request->profileData(),
            ...$request->storedFiles(),
            'status' => SellerProfile::StatusPending,
            'verified_at' => null,
            'rejected_reason' => null,
        ]);

        $this->storeResourceAttachments($request, $profile, 'sellers/attachments');

        return to_route('seller.status');
    }

    public function show(Request $request): Response|RedirectResponse
    {
        $profile = $request->user()->sellerProfile;

        if (! $profile) {
            return to_route('seller.apply');
        }

        return Inertia::render('seller/status', [
            'profile' => $this->serializeProfile($profile->load('attachments')),
        ]);
    }

    public function update(StoreSellerProfileRequest $request): RedirectResponse
    {
        $profile = $request->user()->sellerProfile;

        abort_unless($profile?->isRejected(), 403);

        $profile->update([
            ...$request->profileData(),
            ...$request->storedFiles(),
            'status' => SellerProfile::StatusPending,
            'verified_at' => null,
            'rejected_reason' => null,
        ]);

        $this->storeResourceAttachments($request, $profile, 'sellers/attachments');

        return to_route('seller.status');
    }

    public function publicShow(SellerProfile $sellerProfile): Response
    {
        abort_unless($sellerProfile->isVerified(), 404);

        $sellerProfile->load('user:id,name');

        $listings = Listing::query()
            ->with([
                'category:id,name,slug',
                'images',
                'specValues' => fn ($query) => $query
                    ->whereHas('specField', fn ($query) => $query->where('is_classification', true))
                    ->with('specField:id,name,is_classification'),
            ])
            ->where('seller_profile_id', $sellerProfile->id)
            ->where('status', Listing::StatusApproved)
            ->notExpired()
            ->withExists(['boosts as has_active_boost' => fn ($q) => $q
                ->where('is_active', true)
                ->where('ends_at', '>', now())])
            ->orderByDesc('has_active_boost')
            ->latest('approved_at')
            ->paginate(12)
            ->through(fn (Listing $listing): array => [
                'id' => $listing->id,
                'title' => $listing->title,
                'price' => $listing->price,
                'condition' => $listing->condition,
                'brand' => $listing->brand,
                'model' => $listing->model,
                'category' => $listing->category,
                'image_url' => $this->listingImageUrl($listing->images->firstWhere('is_primary', true) ?? $listing->images->first(), $listing),
                'is_featured' => (bool) ($listing->has_active_boost ?? false),
            ]);

        return Inertia::render('sellers/show', [
            'seller' => [
                'id' => $sellerProfile->id,
                'seller_type' => $sellerProfile->seller_type,
                'business_name' => $sellerProfile->business_name,
                'owner_name' => $sellerProfile->owner_name ?? $sellerProfile->user?->name,
                'contact_number' => $sellerProfile->contact_number,
                'email' => $sellerProfile->email,
                'region' => $sellerProfile->region,
                'province' => $sellerProfile->province,
                'municipality' => $sellerProfile->municipality,
                'verified_at' => $sellerProfile->verified_at?->toDateString(),
            ],
            'listings' => $listings,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeProfile(SellerProfile $profile): array
    {
        return [
            'id' => $profile->id,
            'seller_type' => $profile->seller_type,
            'business_name' => $profile->business_name,
            'owner_name' => $profile->owner_name,
            'contact_number' => $profile->contact_number,
            'email' => $profile->email,
            'region' => $profile->region,
            'province' => $profile->province,
            'municipality' => $profile->municipality,
            'barangay' => $profile->barangay,
            'full_address' => $profile->full_address,
            'permit_number' => $profile->permit_number,
            'accreditation' => $profile->accreditation,
            'representative_name' => $profile->representative_name,
            'representative_contact' => $profile->representative_contact,
            'status' => $profile->status,
            'status_label' => $profile->statusLabel(),
            'can_edit' => $profile->isRejected(),
            'verified_at' => $profile->verified_at?->toISOString(),
            'rejected_reason' => $profile->rejected_reason,
            'attachments' => $profile->relationLoaded('attachments') ? $this->serializeAttachments($profile) : [],
        ];
    }
}
