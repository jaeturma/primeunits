<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreListingRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\CategorySpecField;
use App\Models\Favorite;
use App\Models\Lead;
use App\Models\Listing;
use App\Models\ListingSpecValue;
use App\Models\ListingView;
use App\Models\Municipality;
use App\Models\Province;
use App\Models\Region;
use App\Support\ResolvesListingStockImage;
use App\Support\StoresResourceAttachments;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ListingController extends Controller
{
    use ResolvesListingStockImage;
    use StoresResourceAttachments;

    public function index(Request $request): Response
    {
        $listings = Listing::query()
            ->with([
                'category:id,name,slug',
                'images',
                'boosts',
                'specValues' => fn ($query) => $query
                    ->whereHas('specField', fn ($query) => $query->where('is_classification', true))
                    ->with('specField:id,name,is_classification'),
            ])
            ->where('status', Listing::StatusApproved)
            ->withExists(['boosts as has_active_boost' => fn ($query) => $query
                ->where('is_active', true)
                ->where('ends_at', '>', now())])
            ->search($request)
            ->orderByDesc('has_active_boost')
            ->latest('approved_at')
            ->paginate(12)
            ->through(fn (Listing $listing): array => $this->serializeCard($listing));

        return Inertia::render('listings/index', [
            'listings' => $listings,
            'filters' => [
                'q' => $request->string('q')->toString(),
                'category' => $request->string('category')->toString(),
                'brand' => $request->string('brand')->toString(),
                'classification' => $request->string('classification')->toString(),
                'condition' => $request->string('condition')->toString(),
                'min_price' => $request->string('min_price')->toString(),
                'max_price' => $request->string('max_price')->toString(),
                'fuel_type' => $request->string('fuel_type')->toString(),
                'max_mileage' => $request->string('max_mileage')->toString(),
                'region' => $request->string('region')->toString(),
                'province' => $request->string('province')->toString(),
                'municipality' => $request->string('municipality')->toString(),
            ],
            'categories' => Category::searchPayload(),
            'categoryBrandGroups' => config('primeunits.category_brand_groups'),
            'brandsByCategory' => Listing::query()
                ->where('status', Listing::StatusApproved)
                ->whereNotNull('brand')
                ->join('categories', 'listings.category_id', '=', 'categories.id')
                ->select('categories.slug as category_slug', 'listings.brand')
                ->distinct()
                ->orderBy('listings.brand')
                ->get()
                ->groupBy('category_slug')
                ->map(fn ($items) => $items->pluck('brand')->values()->all())
                ->all(),
            'locationOptions' => [
                'provinces' => Province::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->pluck('name'),
                'municipalities' => Municipality::query()
                    ->with('province:id,name')
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(['id', 'province_id', 'name'])
                    ->map(fn (Municipality $municipality): array => [
                        'name' => $municipality->name,
                        'province_name' => $municipality->province?->name,
                    ]),
            ],
            'conditions' => $this->conditions(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('seller/listings/create', [
            'managementPath' => $this->managementPath(request()),
            'categories' => Category::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name', 'slug']),
            'categoryBrandGroups' => config('primeunits.category_brand_groups'),
            'conditions' => $this->conditions(),
            'brands' => Brand::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'category_group']),
            'locationOptions' => $this->locationOptions(),
        ]);
    }

    public function edit(Request $request, Listing $listing): Response
    {
        abort_unless($listing->user_id === $request->user()->id, 403);

        $listing->load(['category:id,name,slug', 'attachments', 'images', 'specValues.specField:id,name,label,type,is_classification']);

        return Inertia::render('seller/listings/edit', [
            'managementPath' => $this->managementPath($request),
            'listing' => [
                'id' => $listing->id,
                'title' => $listing->title,
                'category_id' => $listing->category_id,
                'description' => $listing->description ?? '',
                'price' => (string) $listing->price,
                'negotiable' => $listing->negotiable,
                'condition' => $listing->condition,
                'year_model' => (string) ($listing->year_model ?? ''),
                'brand' => $listing->brand ?? '',
                'model' => $listing->model ?? '',
                'region' => $listing->region ?? '',
                'province' => $listing->province ?? '',
                'municipality' => $listing->municipality ?? '',
                'barangay' => $listing->barangay ?? '',
                'listing_type' => $listing->listing_type ?? Listing::TypeFree,
                'specs' => $listing->specValues->mapWithKeys(fn ($sv) => [
                    (string) $sv->spec_field_id => $sv->value,
                ])->all(),
                'images' => $listing->images->map(fn ($img) => [
                    'id' => $img->id,
                    'url' => $this->listingImageUrl($img, $listing),
                    'is_primary' => $img->is_primary,
                    'sort_order' => $img->sort_order,
                ]),
                'attachments' => $this->serializeAttachments($listing),
            ],
            'categories' => Category::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name', 'slug']),
            'categoryBrandGroups' => config('primeunits.category_brand_groups'),
            'conditions' => $this->conditions(),
            'brands' => Brand::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'category_group']),
            'locationOptions' => $this->locationOptions(),
        ]);
    }

    public function store(StoreListingRequest $request): RedirectResponse
    {
        $user = $request->user();
        $category = Category::query()->with('specFields')->findOrFail($request->integer('category_id'));

        DB::transaction(function () use ($request, $user, $category): void {
            $listing = Listing::query()->create([
                ...$request->listingData(),
                'user_id' => $user->id,
                'seller_profile_id' => $user->sellerProfile?->id,
                'dealer_profile_id' => $user->dealerProfile?->id,
                'listing_type' => $user->dealerProfile?->isVerified()
                    ? Listing::TypeDealer
                    : Listing::TypeFree,
                'status' => Listing::StatusPending,
                'approved_at' => null,
                'rejected_reason' => null,
                'agent_validated_by' => null,
                'agent_validated_at' => null,
                'manager_accepted_by' => null,
                'manager_accepted_at' => null,
                'approved_by' => null,
                ...$request->storedIdentityDocuments(),
            ]);

            foreach ($request->specsForCategory($category) as $fieldId => $value) {
                $listing->specValues()->create([
                    'spec_field_id' => $fieldId,
                    'value' => $value,
                ]);
            }

            foreach ($request->storedImages() as $index => $path) {
                $listing->images()->create([
                    'path' => $path,
                    'is_primary' => $index === 0,
                    'sort_order' => $index,
                ]);
            }

            $this->storeResourceAttachments($request, $listing, 'listings/attachments');
        });

        return redirect()->to($this->managementPath($request));
    }

    public function show(Request $request, Listing $listing): Response
    {
        abort_unless($listing->isApproved(), 404);

        $listing->load([
            'category:id,name,slug',
            'images',
            'attachments',
            'sellerProfile.user:id,name,email',
            'dealerProfile.user:id,name,email',
            'specValues.specField:id,name,label,type,is_classification',
        ]);

        $user = $request->user();
        $ip = $request->ip();
        $alreadyViewed = ListingView::query()
            ->where('listing_id', $listing->id)
            ->where(function ($q) use ($user, $ip): void {
                if ($user) {
                    $q->where('user_id', $user->id);
                } else {
                    $q->where('ip_address', $ip)->whereNull('user_id');
                }
            })
            ->where('viewed_at', '>=', now()->subHours(6))
            ->exists();

        if (! $alreadyViewed) {
            ListingView::query()->create([
                'listing_id' => $listing->id,
                'user_id' => $user?->id,
                'ip_address' => $ip,
                'viewed_at' => now(),
            ]);
            $listing->increment('views_count');
        }

        $isFavorited = $user
            ? Favorite::query()->where('user_id', $user->id)->where('listing_id', $listing->id)->exists()
            : false;

        return Inertia::render('listings/show', [
            'listing' => $this->serializeDetail($listing, $isFavorited),
        ]);
    }

    public function myListings(Request $request): Response
    {
        $listings = $request->user()
            ->listings()
            ->with([
                'category:id,name,slug',
                'images',
                'specValues' => fn ($query) => $query
                    ->whereHas('specField', fn ($query) => $query->where('is_classification', true))
                    ->with('specField:id,name,is_classification'),
            ])
            ->latest()
            ->get()
            ->map(fn (Listing $listing): array => $this->serializeCard($listing));

        return Inertia::render('seller/listings/index', [
            'listings' => $listings,
            'managementPath' => $this->managementPath($request),
        ]);
    }

    public function update(StoreListingRequest $request, Listing $listing): RedirectResponse
    {
        $category = Category::query()->with('specFields')->findOrFail($request->integer('category_id'));

        DB::transaction(function () use ($request, $listing, $category): void {
            $listing->update([
                ...$request->listingData(),
                'status' => Listing::StatusPending,
                'approved_at' => null,
                'rejected_reason' => null,
                'agent_validated_by' => null,
                'agent_validated_at' => null,
                'manager_accepted_by' => null,
                'manager_accepted_at' => null,
                'approved_by' => null,
                ...$request->storedIdentityDocuments(),
            ]);

            $listing->specValues()->delete();

            foreach ($request->specsForCategory($category) as $fieldId => $value) {
                $listing->specValues()->create([
                    'spec_field_id' => $fieldId,
                    'value' => $value,
                ]);
            }

            foreach ($request->storedImages() as $index => $path) {
                $listing->images()->create([
                    'path' => $path,
                    'is_primary' => ! $listing->images()->where('is_primary', true)->exists() && $index === 0,
                    'sort_order' => (int) $listing->images()->max('sort_order') + $index + 1,
                ]);
            }

            $this->storeResourceAttachments($request, $listing, 'listings/attachments');
        });

        return redirect()->to($this->managementPath($request));
    }

    public function destroy(Request $request, Listing $listing): RedirectResponse
    {
        abort_unless($listing->user_id === $request->user()->id, 403);

        $listing->delete();

        return redirect()->to($this->managementPath($request));
    }

    public function renew(Request $request, Listing $listing): RedirectResponse
    {
        abort_unless($listing->user_id === $request->user()->id, 403);
        abort_unless($listing->isApproved(), 403);

        $days = config('primeunits.listing_expiry_days', 30);

        $listing->update([
            'expires_at' => now()->addDays($days),
        ]);

        return back()->with('success', 'Listing renewed successfully.');
    }

    public function specFields(Category $category): JsonResponse
    {
        return response()->json([
            'fields' => $category->specFields()
                ->orderBy('id')
                ->get()
                ->map(fn (CategorySpecField $field): array => [
                    'id' => $field->id,
                    'name' => $field->name,
                    'label' => $field->label,
                    'type' => $field->type,
                    'options' => $field->options ?? [],
                    'required' => $field->required,
                ]),
        ]);
    }

    /**
     * Classification options for a category (e.g. Body Type for Cars,
     * Equipment Type for Heavy Equipment), each annotated with how many
     * approved listings currently match it so the "Find a Unit" search
     * can show real listing counts instead of a static list.
     */
    public function classifications(Category $category): JsonResponse
    {
        $field = $category->classificationField()->first();

        if (! $field instanceof CategorySpecField) {
            return response()->json(['field' => null, 'options' => []]);
        }

        $counts = ListingSpecValue::query()
            ->where('spec_field_id', $field->id)
            ->whereHas('listing', fn ($query) => $query->where('status', Listing::StatusApproved))
            ->select('value', DB::raw('count(*) as aggregate'))
            ->groupBy('value')
            ->pluck('aggregate', 'value');

        return response()->json([
            'field' => [
                'id' => $field->id,
                'name' => $field->name,
                'label' => $field->label,
            ],
            'options' => collect($field->options ?? [])
                ->map(fn (string $option): array => [
                    'value' => $option,
                    'label' => $option,
                    'count' => (int) ($counts[$option] ?? 0),
                ])
                ->values(),
        ]);
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function conditions(): array
    {
        return [
            ['value' => Listing::ConditionBrandNew, 'label' => 'Brand New'],
            ['value' => Listing::ConditionUsed, 'label' => 'Used'],
            ['value' => Listing::ConditionSurplus, 'label' => 'Surplus'],
        ];
    }

    private function managementPath(Request $request): string
    {
        return $request->user()?->hasRole('dealer') ? '/dealer/units' : '/seller/listings';
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeCard(Listing $listing): array
    {
        $primaryImage = $listing->images->firstWhere('is_primary', true) ?? $listing->images->first();

        return [
            'id' => $listing->id,
            'title' => $listing->title,
            'slug' => $listing->slug,
            'price' => $listing->price,
            'negotiable' => $listing->negotiable,
            'condition' => $listing->condition,
            'brand' => $listing->brand,
            'model' => $listing->model,
            'status' => $listing->status,
            'status_label' => $listing->statusLabel(),
            'listing_type' => $listing->listing_type,
            'listing_type_label' => $listing->listingTypeLabel(),
            'promotional_type' => $listing->promotional_type,
            'promo_label' => $listing->promoLabel(),
            'is_featured' => (bool) ($listing->has_active_boost ?? $listing->boosts?->contains(fn ($boost): bool => $boost->is_active && $boost->ends_at?->isFuture())),
            'rejected_reason' => $listing->rejected_reason,
            'expires_at' => $listing->expires_at?->toISOString(),
            'is_expired' => $listing->isExpired(),
            'views_count' => $listing->views_count,
            'category' => $listing->category,
            'image_url' => $this->listingImageUrl($primaryImage, $listing),
            'created_at' => $listing->created_at?->toISOString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeDetail(Listing $listing, bool $isFavorited = false): array
    {
        $user = request()->user();
        $ownerProfile = $listing->dealerProfile ?? $listing->sellerProfile;
        $currentLead = $user ? Lead::query()
            ->with('messages.user:id,name')
            ->where('listing_id', $listing->id)
            ->where(function ($query) use ($user, $listing): void {
                $query->where('buyer_id', $user->id)
                    ->orWhere(function ($query) use ($user, $listing): void {
                        $query->where('seller_id', $user->id)
                            ->where('seller_id', $listing->user_id);
                    });
            })
            ->whereNotIn('status', [Lead::StatusClosed, Lead::StatusCancelled])
            ->latest()
            ->first() : null;
        $canViewContact = $currentLead !== null || $user?->id === $listing->user_id || $user?->hasPermission('approve_listings') === true;

        return [
            ...$this->serializeCard($listing),
            'is_favorited' => $isFavorited,
            'description' => $listing->description,
            'year_model' => $listing->year_model,
            'region' => $listing->region,
            'province' => $listing->province,
            'municipality' => $listing->municipality,
            'barangay' => $canViewContact ? $listing->barangay : null,
            'can_view_contact' => $canViewContact,
            'seller' => $canViewContact && $ownerProfile ? [
                'name' => $ownerProfile->user->name,
                'email' => $ownerProfile->user->email,
                'contact_number' => $ownerProfile->contact_number,
            ] : null,
            'current_lead' => $currentLead ? [
                'id' => $currentLead->id,
                'reference_code' => $currentLead->reference_code,
                'message' => $currentLead->message,
                'messages' => $currentLead->messages->map(fn ($message): array => [
                    'id' => $message->id,
                    'body' => $message->body,
                    'created_at' => $message->created_at?->toISOString(),
                    'user' => [
                        'id' => $message->user->id,
                        'name' => $message->user->name,
                    ],
                ]),
            ] : null,
            'images' => $listing->images->isNotEmpty()
                ? $listing->images->map(fn ($image): array => [
                    'id' => $image->id,
                    'url' => $this->listingImageUrl($image, $listing),
                    'is_primary' => $image->is_primary,
                ])
                : [['id' => 0, 'url' => $this->stockListingImageUrl($listing), 'is_primary' => true]],
            'attachments' => $this->serializeAttachments($listing),
            'specs' => $listing->specValues->map(fn (ListingSpecValue $value): array => [
                'id' => $value->id,
                'name' => $value->specField->name,
                'label' => $value->specField->label,
                'value' => $value->value,
            ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function locationOptions(): array
    {
        return [
            'regions' => Region::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
            'provinces' => Province::query()
                ->with('region:id,name')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'region_id', 'name'])
                ->map(fn (Province $province): array => [
                    'id' => $province->id,
                    'name' => $province->name,
                    'region_id' => $province->region_id,
                    'region_name' => $province->region?->name,
                ]),
            'municipalities' => Municipality::query()
                ->with(['region:id,name', 'province:id,name'])
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'region_id', 'province_id', 'name'])
                ->map(fn (Municipality $municipality): array => [
                    'id' => $municipality->id,
                    'name' => $municipality->name,
                    'region_id' => $municipality->region_id,
                    'region_name' => $municipality->region?->name,
                    'province_id' => $municipality->province_id,
                    'province_name' => $municipality->province?->name,
                ]),
        ];
    }
}
