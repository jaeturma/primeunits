<?php

namespace App\Http\Controllers;

use App\Models\DealerProfile;
use App\Models\Listing;
use App\Support\ResolvesListingStockImage;
use App\Support\StoresResourceAttachments;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class DealerProfileController extends Controller
{
    use ResolvesListingStockImage;
    use StoresResourceAttachments;

    public function index(Request $request): Response
    {
        $dealers = DealerProfile::query()
            ->where('status', DealerProfile::StatusVerified)
            ->when($request->filled('q'), fn ($q) => $q->where(function ($q) use ($request): void {
                $search = $request->string('q')->toString();
                $q->where('business_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            }))
            ->when($request->filled('region'), fn ($q) => $q->where('region', $request->string('region')->toString()))
            ->orderBy('business_name')
            ->paginate(12)
            ->through(fn (DealerProfile $d) => [
                'id' => $d->id,
                'slug' => $d->slug,
                'business_name' => $d->business_name,
                'logo' => $d->logo ? Storage::disk('public')->url($d->logo) : null,
                'region' => $d->region,
                'province' => $d->province,
                'municipality' => $d->municipality,
                'contact_number' => $d->contact_number,
                'verified_at' => $d->verified_at?->toDateString(),
            ]);

        return Inertia::render('dealers/index', [
            'dealers' => $dealers,
            'filters' => [
                'q' => $request->string('q')->toString(),
                'region' => $request->string('region')->toString(),
            ],
        ]);
    }

    public function show(DealerProfile $dealerProfile): Response
    {
        abort_unless($dealerProfile->isVerified(), 404);

        $dealerProfile->load('user:id,name');

        $listings = Listing::query()
            ->with([
                'category:id,name,slug',
                'images',
                'specValues' => fn ($query) => $query
                    ->whereHas('specField', fn ($query) => $query->where('is_classification', true))
                    ->with('specField:id,name,is_classification'),
            ])
            ->where('user_id', $dealerProfile->user_id)
            ->where('listing_type', Listing::TypeDealer)
            ->where('status', Listing::StatusApproved)
            ->notExpired()
            ->withExists(['boosts as has_active_boost' => fn ($q) => $q
                ->where('is_active', true)
                ->where('ends_at', '>', now())])
            ->orderByDesc('has_active_boost')
            ->latest('approved_at')
            ->paginate(12)
            ->through(fn (Listing $l) => [
                'id' => $l->id,
                'title' => $l->title,
                'price' => $l->price,
                'condition' => $l->condition,
                'brand' => $l->brand,
                'model' => $l->model,
                'year_model' => $l->year_model,
                'is_featured' => (bool) ($l->has_active_boost ?? false),
                'category' => $l->category,
                'image_url' => $this->listingImageUrl($l->images->firstWhere('is_primary', true) ?? $l->images->first(), $l),
            ]);

        return Inertia::render('dealers/show', [
            'dealer' => [
                'id' => $dealerProfile->id,
                'slug' => $dealerProfile->slug,
                'business_name' => $dealerProfile->business_name,
                'logo' => $dealerProfile->logo ? Storage::disk('public')->url($dealerProfile->logo) : null,
                'banner' => $dealerProfile->banner ? Storage::disk('public')->url($dealerProfile->banner) : null,
                'description' => $dealerProfile->description,
                'contact_number' => $dealerProfile->contact_number,
                'email' => $dealerProfile->email,
                'website' => $dealerProfile->website,
                'region' => $dealerProfile->region,
                'province' => $dealerProfile->province,
                'municipality' => $dealerProfile->municipality,
                'full_address' => $dealerProfile->full_address,
                'verified_at' => $dealerProfile->verified_at?->toDateString(),
            ],
            'listings' => $listings,
        ]);
    }

    public function apply(Request $request): Response|RedirectResponse
    {
        $profile = $request->user()->dealerProfile;

        if ($profile && $profile->status !== DealerProfile::StatusRejected) {
            return to_route('dealer.status');
        }

        return Inertia::render('dealer/apply', [
            'profile' => $profile ? $this->serializeProfile($profile->load('attachments')) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'contact_number' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'region' => ['nullable', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'municipality' => ['nullable', 'string', 'max:255'],
            'barangay' => ['nullable', 'string', 'max:255'],
            'full_address' => ['nullable', 'string', 'max:1000'],
            'accreditation_number' => ['nullable', 'string', 'max:100'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'banner' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'accreditation_file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'attachments' => ['required', 'array', 'min:1', 'max:10'],
            'attachments.*' => self::AttachmentFileRules,
        ]);

        $data = $request->only([
            'business_name', 'contact_number', 'email', 'website', 'description',
            'region', 'province', 'municipality', 'barangay', 'full_address',
            'accreditation_number',
        ]);

        $data['slug'] = $this->generateSlug($request->string('business_name')->toString());
        $data['status'] = DealerProfile::StatusPending;

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('dealers/logos', 'public');
        }
        if ($request->hasFile('banner')) {
            $data['banner'] = $request->file('banner')->store('dealers/banners', 'public');
        }
        if ($request->hasFile('accreditation_file')) {
            $data['accreditation_file'] = $request->file('accreditation_file')->store('dealers/docs', 'public');
        }

        $profile = $request->user()->dealerProfile()->create($data);

        $this->storeResourceAttachments($request, $profile, 'dealers/attachments');

        return to_route('dealer.status');
    }

    public function status(Request $request): Response|RedirectResponse
    {
        $profile = $request->user()->dealerProfile;

        if (! $profile) {
            return to_route('dealer.apply');
        }

        return Inertia::render('dealer/status', [
            'profile' => $this->serializeProfile($profile->load('attachments')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeProfile(DealerProfile $profile): array
    {
        return [
            'id' => $profile->id,
            'slug' => $profile->slug,
            'business_name' => $profile->business_name,
            'contact_number' => $profile->contact_number,
            'email' => $profile->email,
            'website' => $profile->website,
            'description' => $profile->description,
            'region' => $profile->region,
            'province' => $profile->province,
            'municipality' => $profile->municipality,
            'full_address' => $profile->full_address,
            'accreditation_number' => $profile->accreditation_number,
            'logo' => $profile->logo ? Storage::disk('public')->url($profile->logo) : null,
            'banner' => $profile->banner ? Storage::disk('public')->url($profile->banner) : null,
            'status' => $profile->status,
            'status_label' => $profile->statusLabel(),
            'verified_at' => $profile->verified_at?->toISOString(),
            'rejected_reason' => $profile->rejected_reason,
            'attachments' => $profile->relationLoaded('attachments') ? $this->serializeAttachments($profile) : [],
        ];
    }

    private function generateSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $count = 1;

        while (DealerProfile::query()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$count}";
            $count++;
        }

        return $slug;
    }
}
