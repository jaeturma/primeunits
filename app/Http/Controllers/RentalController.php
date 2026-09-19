<?php

namespace App\Http\Controllers;

use App\Models\Municipality;
use App\Models\Province;
use App\Models\RentalBooking;
use App\Models\RentalUnit;
use App\Models\User;
use App\Support\StoresResourceAttachments;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class RentalController extends Controller
{
    use StoresResourceAttachments;

    public function index(Request $request): Response
    {
        $withDriver = $request->filled('with_driver') ? $request->boolean('with_driver') : null;

        $rentals = RentalUnit::query()
            ->with(['images', 'user:id,name,username'])
            ->where('status', RentalUnit::StatusApproved)
            ->when($request->filled('q'), fn ($q) => $q->where(function ($q) use ($request): void {
                $search = $request->string('q')->toString();
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            }))
            ->when($request->filled('type'), fn ($q) => $q->where('rental_type', $request->string('type')->toString()))
            ->when($withDriver !== null, fn ($q) => $q->where('with_driver', $withDriver))
            ->when($request->filled('province'), fn ($q) => $q->where('province', $request->string('province')->toString()))
            ->when($request->filled('municipality'), fn ($q) => $q->where('municipality', $request->string('municipality')->toString()))
            ->when($request->filled('max_price'), fn ($q) => $q->where('price_per_day', '<=', $request->integer('max_price')))
            ->latest('approved_at')
            ->paginate(12)
            ->through(fn (RentalUnit $r) => $this->serializeCard($r));

        return Inertia::render('rentals/index', [
            'rentals' => $rentals,
            'rental_types' => RentalUnit::rentalTypes(),
            'filters' => [
                'q' => $request->string('q')->toString(),
                'type' => $request->string('type')->toString(),
                'with_driver' => $request->filled('with_driver') ? $request->string('with_driver')->toString() : '',
                'province' => $request->string('province')->toString(),
                'municipality' => $request->string('municipality')->toString(),
                'max_price' => $request->string('max_price')->toString(),
            ],
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
                    ->map(fn (Municipality $m): array => [
                        'name' => $m->name,
                        'province_name' => $m->province?->name,
                    ]),
            ],
        ]);
    }

    public function provider(string $provider): Response|RedirectResponse
    {
        $providerUser = User::query()->where('username', $provider)->first();

        if (! $providerUser) {
            $legacyUnit = RentalUnit::query()
                ->with('user:id,username')
                ->where('slug', $provider)
                ->where('status', RentalUnit::StatusApproved)
                ->firstOrFail();

            return to_route('rentals.show', [$legacyUnit->user->username, $legacyUnit]);
        }

        $rentals = $providerUser->rentalUnits()
            ->with(['images', 'user:id,name,username'])
            ->where('status', RentalUnit::StatusApproved)
            ->latest('approved_at')
            ->paginate(12)
            ->through(fn (RentalUnit $unit): array => $this->serializeCard($unit));

        abort_if($rentals->total() === 0 && ! $providerUser->hasRole('rental_provider'), 404);

        return Inertia::render('rentals/provider', [
            'provider' => [
                'name' => $providerUser->name,
                'username' => $providerUser->username,
            ],
            'rentals' => $rentals,
        ]);
    }

    public function show(User $provider, RentalUnit $rentalUnit): Response
    {
        abort_unless($rentalUnit->isApproved() && $rentalUnit->user_id === $provider->id, 404);

        $rentalUnit->load(['attachments', 'images', 'packages', 'user:id,name']);
        $rentalUnit->increment('views_count');

        $availability = $rentalUnit->availability()
            ->where('date', '>=', now()->toDateString())
            ->where('date', '<=', now()->addMonths(3)->toDateString())
            ->get(['date', 'is_available'])
            ->mapWithKeys(fn ($a) => [$a->date->toDateString() => $a->is_available]);

        return Inertia::render('rentals/show', [
            'rental' => [
                ...$this->serializeCard($rentalUnit),
                'description' => $rentalUnit->description,
                'capacity' => $rentalUnit->capacity,
                'provider' => ['name' => $rentalUnit->user?->name],
                'packages' => $rentalUnit->packages->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'duration_type' => $p->duration_type,
                    'duration_value' => $p->duration_value,
                    'price' => $p->price,
                    'inclusions' => $p->inclusions,
                ]),
                'availability' => $availability,
                'images' => $rentalUnit->images->map(fn ($img) => [
                    'id' => $img->id,
                    'url' => Storage::disk('public')->url($img->path),
                    'is_primary' => $img->is_primary,
                ]),
                'attachments' => $this->serializeAttachments($rentalUnit),
            ],
        ]);
    }

    public function legacyShow(RentalUnit $rentalUnit): RedirectResponse
    {
        abort_unless($rentalUnit->isApproved(), 404);

        return to_route('rentals.show', [
            'provider' => $rentalUnit->user->username,
            'rentalUnit' => $rentalUnit,
        ]);
    }

    public function myUnits(Request $request): Response
    {
        $units = $request->user()
            ->rentalUnits()
            ->with('images')
            ->latest()
            ->get()
            ->map(fn (RentalUnit $r) => $this->serializeCard($r));

        return Inertia::render('rental-provider/units/index', [
            'units' => $units,
        ]);
    }

    public function destroy(Request $request, RentalUnit $rentalUnit): RedirectResponse
    {
        abort_unless($rentalUnit->user_id === $request->user()->id, 403);

        $rentalUnit->delete();

        return to_route('rental-provider.units.index')->with('success', 'Rental unit deleted.');
    }

    public function create(): Response
    {
        return Inertia::render('rental-provider/units/create', [
            'rental_types' => RentalUnit::rentalTypes(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'rental_type' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'brand' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'year_model' => ['nullable', 'integer', 'min:1950', 'max:'.((int) date('Y') + 1)],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'with_driver' => ['boolean'],
            'price_per_day' => ['required', 'numeric', 'min:0'],
            'price_per_hour' => ['nullable', 'numeric', 'min:0'],
            'region' => ['nullable', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'municipality' => ['nullable', 'string', 'max:255'],
            'barangay' => ['nullable', 'string', 'max:255'],
            'images' => ['array', 'max:10'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'attachments' => self::AttachmentRules,
            'attachments.*' => self::AttachmentFileRules,
            'valid_id_file' => [Rule::requiredIf(fn (): bool => ! $request->user()->rentalProfile?->isApproved()), 'nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'or_cr_file' => [Rule::requiredIf(fn (): bool => ! $request->user()->rentalProfile?->isApproved()), 'nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        DB::transaction(function () use ($request): void {
            $unit = RentalUnit::query()->create([
                ...$request->only([
                    'rental_type', 'name', 'description', 'brand', 'model',
                    'year_model', 'capacity', 'with_driver', 'price_per_day', 'price_per_hour',
                    'region', 'province', 'municipality', 'barangay',
                ]),
                'user_id' => $request->user()->id,
                'status' => RentalUnit::StatusPending,
                'valid_id_file' => $request->hasFile('valid_id_file') ? $request->file('valid_id_file')->store('rentals/identity', 'public') : null,
                'or_cr_file' => $request->hasFile('or_cr_file') ? $request->file('or_cr_file')->store('rentals/identity', 'public') : null,
            ]);

            foreach ($request->file('images', []) as $index => $image) {
                $unit->images()->create([
                    'path' => $image->store('rentals/images', 'public'),
                    'is_primary' => $index === 0,
                    'sort_order' => $index,
                ]);
            }

            $this->storeResourceAttachments($request, $unit, 'rentals/attachments');
        });

        return to_route('rental-provider.units.index');
    }

    public function myBookings(Request $request): Response
    {
        $bookings = RentalBooking::query()
            ->where('provider_id', $request->user()->id)
            ->with('rentalUnit:id,name,slug', 'renter:id,name')
            ->latest()
            ->paginate(20)
            ->through(fn (RentalBooking $b) => [
                'id' => $b->id,
                'reference_code' => $b->reference_code,
                'status' => $b->status,
                'status_label' => $b->statusLabel(),
                'start_date' => $b->start_date?->toDateString(),
                'end_date' => $b->end_date?->toDateString(),
                'quoted_price' => $b->quoted_price,
                'message' => $b->message,
                'unit' => $b->rentalUnit ? ['id' => $b->rentalUnit->id, 'name' => $b->rentalUnit->name] : null,
                'renter' => $b->renter ? ['id' => $b->renter->id, 'name' => $b->renter->name] : null,
                'created_at' => $b->created_at?->toISOString(),
            ]);

        return Inertia::render('rental-provider/bookings/index', [
            'bookings' => $bookings,
        ]);
    }

    public function submitBooking(Request $request, RentalUnit $rentalUnit): RedirectResponse
    {
        abort_unless($rentalUnit->isApproved(), 404);

        $request->validate([
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'rental_package_id' => ['nullable', 'integer', 'exists:rental_packages,id'],
            'pickup_address' => ['nullable', 'string', 'max:500'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $code = 'RB-'.strtoupper(Str::random(8));

        RentalBooking::query()->create([
            'rental_unit_id' => $rentalUnit->id,
            'renter_id' => $request->user()->id,
            'provider_id' => $rentalUnit->user_id,
            'rental_package_id' => $request->input('rental_package_id'),
            'reference_code' => $code,
            'start_date' => $request->string('start_date')->toString(),
            'end_date' => $request->string('end_date')->toString(),
            'pickup_address' => $request->string('pickup_address')->toString(),
            'message' => $request->string('message')->toString(),
            'status' => RentalBooking::StatusInquiry,
        ]);

        return back()->with('booking_code', $code);
    }

    public function updateBookingStatus(Request $request, RentalBooking $rentalBooking): RedirectResponse
    {
        abort_unless($rentalBooking->provider_id === $request->user()->id, 403);

        $request->validate(['status' => ['required', 'in:confirmed,cancelled']]);

        $data = ['status' => $request->string('status')->toString()];

        if ($data['status'] === RentalBooking::StatusConfirmed) {
            $data['confirmed_at'] = now();
        } elseif ($data['status'] === RentalBooking::StatusCancelled) {
            $data['cancelled_at'] = now();
        }

        $rentalBooking->update($data);

        return back()->with('success', 'Booking status updated.');
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeCard(RentalUnit $unit): array
    {
        $primaryImage = $unit->images->firstWhere('is_primary', true) ?? $unit->images->first();

        return [
            'id' => $unit->id,
            'slug' => $unit->slug,
            'provider_username' => $unit->user?->username ?? $unit->user()->value('username'),
            'name' => $unit->name,
            'rental_type' => $unit->rental_type,
            'rental_type_label' => RentalUnit::rentalTypes()[$unit->rental_type] ?? $unit->rental_type,
            'brand' => $unit->brand,
            'model' => $unit->model,
            'year_model' => $unit->year_model,
            'with_driver' => $unit->with_driver,
            'price_per_day' => $unit->price_per_day,
            'price_per_hour' => $unit->price_per_hour,
            'status' => $unit->status,
            'region' => $unit->region,
            'province' => $unit->province,
            'municipality' => $unit->municipality,
            'views_count' => $unit->views_count,
            'image_url' => $primaryImage ? Storage::disk('public')->url($primaryImage->path) : null,
        ];
    }
}
