<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use App\Models\SellerProfile;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SellerProfileController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in([
                SellerProfile::StatusPending,
                SellerProfile::StatusVerified,
                SellerProfile::StatusRejected,
            ])],
        ]);

        $profiles = SellerProfile::query()
            ->with('user:id,name,email')
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->latest()
            ->get()
            ->map(fn (SellerProfile $profile): array => $this->serializeProfile($profile));

        return Inertia::render('adm/sellers/index', [
            'profiles' => $profiles,
            'filters' => [
                'status' => $filters['status'] ?? '',
            ],
            'statuses' => [
                ['value' => '', 'label' => 'All statuses'],
                ['value' => SellerProfile::StatusPending, 'label' => 'Under review'],
                ['value' => SellerProfile::StatusVerified, 'label' => 'Verified Seller'],
                ['value' => SellerProfile::StatusRejected, 'label' => 'Rejected'],
            ],
        ]);
    }

    public function approve(SellerProfile $sellerProfile, NotificationService $notifications): RedirectResponse
    {
        $sellerProfile->update([
            'status' => SellerProfile::StatusVerified,
            'verified_at' => now(),
            'rejected_reason' => null,
        ]);

        $notifications->send(
            user: $sellerProfile->user,
            event: 'seller.verified',
            title: 'Seller profile verified',
            message: 'Your seller profile has been verified. You can now create listings.',
            url: '/seller/status',
        );

        return back();
    }

    public function reject(Request $request, SellerProfile $sellerProfile, NotificationService $notifications): RedirectResponse
    {
        $validated = $request->validate([
            'rejected_reason' => ['required', 'string', 'max:2000'],
        ]);

        $sellerProfile->update([
            'status' => SellerProfile::StatusRejected,
            'verified_at' => null,
            'rejected_reason' => $validated['rejected_reason'],
        ]);

        $notifications->send(
            user: $sellerProfile->user,
            event: 'seller.rejected',
            title: 'Seller profile needs updates',
            message: "Your seller profile was rejected: {$validated['rejected_reason']}",
            url: '/seller/status',
        );

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeProfile(SellerProfile $profile): array
    {
        return [
            'id' => $profile->id,
            'user' => [
                'id' => $profile->user?->id,
                'name' => $profile->user?->name,
                'email' => $profile->user?->email,
            ],
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
            'can_approve' => ! $profile->isVerified(),
            'can_reject' => ! $profile->isRejected(),
            'verified_at' => $profile->verified_at?->toISOString(),
            'rejected_reason' => $profile->rejected_reason,
            'files' => [
                'valid_id_file' => $this->fileUrl($profile, 'valid_id_file'),
                'selfie_file' => $this->fileUrl($profile, 'selfie_file'),
                'permit_file' => $this->fileUrl($profile, 'permit_file'),
                'accreditation_file' => $this->fileUrl($profile, 'accreditation_file'),
                'representative_id_file' => $this->fileUrl($profile, 'representative_id_file'),
            ],
        ];
    }

    private function fileUrl(SellerProfile $profile, string $field): ?string
    {
        return $profile->{$field} ? route('sellers.documents', [$profile, $field]) : null;
    }
}
