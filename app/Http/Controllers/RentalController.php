<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\DroneComplianceAcknowledgement;
use App\Models\DroneComplianceNotice;
use App\Models\DronePilotCredential;
use App\Models\Municipality;
use App\Models\Province;
use App\Models\RentalBooking;
use App\Models\RentalPackage;
use App\Models\RentalUnit;
use App\Models\User;
use App\Services\DroneBookingComplianceService;
use App\Services\RentalPricingService;
use App\Support\ResolvesRentalStockImage;
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
    use ResolvesRentalStockImage;
    use StoresResourceAttachments;

    public function __construct(
        private readonly RentalPricingService $pricingService,
        private readonly DroneBookingComplianceService $droneComplianceService,
    ) {}

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

        $rentalUnit->load(['attachments', 'images', 'packages', 'user:id,name', 'dronePilot:id,name']);
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
                'images' => $rentalUnit->images->isNotEmpty()
                    ? $rentalUnit->images->map(fn ($img) => [
                        'id' => $img->id,
                        'url' => Storage::disk('public')->url($img->path),
                        'is_primary' => $img->is_primary,
                    ])
                    : [['id' => 0, 'url' => $this->stockImageUrl($rentalUnit), 'is_primary' => true]],
                'attachments' => $this->serializeAttachments($rentalUnit),
                'operator_fee' => $rentalUnit->operator_fee,
                'transportation_fee' => $rentalUnit->transportation_fee,
                'security_deposit' => $rentalUnit->security_deposit,
                'fuel_included' => $rentalUnit->fuel_included,
                'minimum_area_hectares' => $rentalUnit->minimum_area_hectares,
                'minimum_rental_duration' => $rentalUnit->minimum_rental_duration,
                'service_coverage_area' => $rentalUnit->service_coverage_area,
                'requires_verified_drone_operator' => $rentalUnit->requires_verified_drone_operator,
                'allows_self_operation' => $rentalUnit->allows_self_operation,
                'intended_uses' => $rentalUnit->intended_uses,
                'drone_pilot' => $rentalUnit->dronePilot ? [
                    'name' => $rentalUnit->dronePilot->name,
                    'is_verified' => $rentalUnit->dronePilot->hasVerifiedDroneCredential(),
                ] : null,
                'compliance_notice' => $rentalUnit->isDroneRelated() && ($notice = DroneComplianceNotice::active())
                    ? ['version' => $notice->version, 'body' => $notice->body]
                    : null,
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
            'farm_categories' => Category::query()
                ->where('slug', 'agricultural-equipment')
                ->get(['id', 'name']),
            'verified_drone_pilots' => User::query()
                ->whereHas('droneCredentials', fn ($q) => $q->where('status', DronePilotCredential::StatusVerified))
                ->get(['id', 'name'])
                ->filter(fn (User $u): bool => $u->hasVerifiedDroneCredential())
                ->values(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $isFarmType = in_array($request->string('rental_type')->toString(), [
            RentalUnit::TypeHarvesterRental,
            RentalUnit::TypeHarvesterService,
            RentalUnit::TypeDroneRental,
            RentalUnit::TypeDroneService,
        ], true);

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
            'category_id' => [Rule::requiredIf($isFarmType), 'nullable', 'integer', 'exists:categories,id'],
            'price_per_hectare' => ['nullable', 'numeric', 'min:0'],
            'operator_fee' => ['nullable', 'numeric', 'min:0'],
            'transportation_fee' => ['nullable', 'numeric', 'min:0'],
            'security_deposit' => ['nullable', 'numeric', 'min:0'],
            'fuel_included' => ['nullable', 'boolean'],
            'operator_included' => ['boolean'],
            'transportation_included' => ['boolean'],
            'minimum_area_hectares' => ['nullable', 'numeric', 'min:0'],
            'minimum_rental_duration' => ['nullable', 'string', 'max:255'],
            'service_coverage_area' => ['nullable', 'string', 'max:2000'],
            'requires_verified_drone_operator' => ['boolean'],
            'allows_self_operation' => ['boolean'],
            'intended_uses' => ['nullable', 'string', 'max:2000'],
            'drone_pilot_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        DB::transaction(function () use ($request, $isFarmType): void {
            $unit = RentalUnit::query()->create([
                ...$request->only([
                    'rental_type', 'name', 'description', 'brand', 'model',
                    'year_model', 'capacity', 'with_driver', 'price_per_day', 'price_per_hour',
                    'region', 'province', 'municipality', 'barangay',
                    'price_per_hectare', 'operator_fee', 'transportation_fee', 'security_deposit',
                    'fuel_included', 'operator_included', 'transportation_included',
                    'minimum_area_hectares', 'minimum_rental_duration', 'service_coverage_area',
                    'requires_verified_drone_operator', 'allows_self_operation', 'intended_uses',
                ]),
                'category_id' => $isFarmType ? $request->integer('category_id') : null,
                'drone_pilot_user_id' => $request->integer('drone_pilot_user_id') ?: null,
                'user_id' => $request->user()->id,
                'status' => RentalUnit::StatusPending,
                'valid_id_file' => $request->hasFile('valid_id_file') ? $request->file('valid_id_file')->store('rentals/identity', 'public') : null,
                'or_cr_file' => $request->hasFile('or_cr_file') ? $request->file('or_cr_file')->store('rentals/identity', 'public') : null,
            ]);

            if ($unit->isDroneRelated()) {
                $unit->update(['compliance_status' => $this->resolveComplianceStatus($unit)]);
            }

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

        $isDrone = $rentalUnit->isDroneRelated();

        $request->validate([
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'rental_package_id' => ['nullable', 'integer', 'exists:rental_packages,id'],
            'pickup_address' => ['nullable', 'string', 'max:500'],
            'message' => ['nullable', 'string', 'max:2000'],
            'pricing_unit' => ['nullable', 'string', Rule::in([
                RentalPackage::DurationHourly,
                RentalPackage::DurationDaily,
                RentalPackage::DurationPerHectare,
                RentalPackage::DurationFixedProject,
            ])],
            'area_hectares' => ['nullable', 'numeric', 'min:0'],
            'with_operator' => ['boolean'],
            'with_transportation' => ['boolean'],
            'self_operate' => ['boolean'],
            'compliance_acknowledged' => [Rule::requiredIf($isDrone), 'boolean'],
        ]);

        if ($isDrone && ! $request->boolean('compliance_acknowledged')) {
            return back()->withErrors(['compliance_acknowledged' => 'You must acknowledge the drone compliance notice before booking.']);
        }

        $requestedStart = $request->string('start_date')->toString();
        $requestedEnd = $request->filled('end_date') ? $request->string('end_date')->toString() : $requestedStart;

        $hasOverlap = RentalBooking::query()
            ->where('rental_unit_id', $rentalUnit->id)
            ->whereIn('status', [RentalBooking::StatusConfirmed, RentalBooking::StatusActive])
            ->where('start_date', '<=', $requestedEnd)
            ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', $requestedStart))
            ->exists();

        if ($hasOverlap) {
            return back()->withErrors(['start_date' => 'This unit is already booked for the selected dates.']);
        }

        $pricingUnit = $request->string('pricing_unit')->toString() ?: RentalPackage::DurationDaily;

        if ($pricingUnit === RentalPackage::DurationPerHectare) {
            $minimum = (float) ($rentalUnit->minimum_area_hectares ?? 0);
            $area = (float) $request->input('area_hectares', 0);

            if ($area <= 0 || ($minimum > 0 && $area < $minimum)) {
                return back()->withErrors(['area_hectares' => "A minimum of {$minimum} hectares is required for this service."]);
            }
        }

        $rentalPackage = $request->filled('rental_package_id')
            ? RentalPackage::query()->find($request->integer('rental_package_id'))
            : null;

        $selfOperate = $request->boolean('self_operate');
        $operatorDecision = ['allowed' => true, 'reason' => null, 'drone_pilot_user_id' => null, 'snapshot' => null];

        if ($isDrone) {
            $operatorDecision = $this->droneComplianceService->resolveOperator($rentalUnit, $request->user(), $selfOperate);

            if (! $operatorDecision['allowed']) {
                return back()->withErrors(['self_operate' => $operatorDecision['reason']]);
            }
        }

        $pricing = $this->pricingService->calculate($rentalUnit, [
            'pricing_unit' => $pricingUnit,
            'area_hectares' => $request->input('area_hectares'),
            'start_date' => $request->string('start_date')->toString(),
            'end_date' => $request->filled('end_date') ? $request->string('end_date')->toString() : null,
            'with_operator' => $isDrone ? ! $selfOperate : $request->boolean('with_operator'),
            'with_transportation' => $request->boolean('with_transportation'),
            'rental_package' => $rentalPackage,
        ]);

        $code = 'RB-'.strtoupper(Str::random(8));

        $booking = RentalBooking::query()->create([
            'rental_unit_id' => $rentalUnit->id,
            'renter_id' => $request->user()->id,
            'provider_id' => $rentalUnit->user_id,
            'rental_package_id' => $rentalPackage?->id,
            'reference_code' => $code,
            'start_date' => $request->string('start_date')->toString(),
            'end_date' => $request->filled('end_date') ? $request->string('end_date')->toString() : null,
            'pickup_address' => $request->string('pickup_address')->toString(),
            'message' => $request->string('message')->toString(),
            'status' => RentalBooking::StatusInquiry,
            'area_hectares' => $request->input('area_hectares'),
            'pricing_unit' => $pricingUnit,
            'quoted_price' => $pricing['total_amount'],
            'drone_pilot_user_id' => $operatorDecision['drone_pilot_user_id'],
            'operator_verification_snapshot' => $operatorDecision['snapshot'],
            ...$pricing,
        ]);

        if ($isDrone) {
            $notice = DroneComplianceNotice::active();
            $noticeVersion = $notice?->version ?? 'unversioned';
            $noticeText = $notice?->body ?? DroneComplianceNotice::DEFAULT_NOTICE_TEXT;

            DroneComplianceAcknowledgement::query()->create([
                'user_id' => $request->user()->id,
                'rental_booking_id' => $booking->id,
                'drone_compliance_notice_id' => $notice?->id,
                'notice_version' => $noticeVersion,
                'notice_text' => $noticeText,
                'acknowledged_at' => now(),
                'ip_address' => $request->ip(),
            ]);

            $booking->update([
                'compliance_acknowledged_at' => now(),
                'compliance_notice_version' => $noticeVersion,
            ]);
        }

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

    private function resolveComplianceStatus(RentalUnit $unit): string
    {
        if (! $unit->isDroneRelated()) {
            return RentalUnit::ComplianceNotApplicable;
        }

        $pilot = $unit->dronePilot;

        if ($pilot?->hasVerifiedDroneCredential()) {
            return RentalUnit::ComplianceVerifiedOperatorAssigned;
        }

        if ($unit->allows_self_operation && ! $unit->requires_verified_drone_operator) {
            return RentalUnit::ComplianceSelfOperationAllowed;
        }

        return RentalUnit::ComplianceUnresolved;
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
            'price_per_hectare' => $unit->price_per_hectare,
            'operator_included' => $unit->operator_included,
            'transportation_included' => $unit->transportation_included,
            'status' => $unit->status,
            'region' => $unit->region,
            'province' => $unit->province,
            'municipality' => $unit->municipality,
            'views_count' => $unit->views_count,
            'image_url' => $primaryImage ? Storage::disk('public')->url($primaryImage->path) : $this->stockImageUrl($unit),
            'is_drone_related' => $unit->isDroneRelated(),
            'compliance_status' => $unit->isDroneRelated() ? $unit->compliance_status : null,
            'has_verified_operator' => $unit->isDroneRelated()
                ? $unit->compliance_status === RentalUnit::ComplianceVerifiedOperatorAssigned
                : null,
        ];
    }
}
