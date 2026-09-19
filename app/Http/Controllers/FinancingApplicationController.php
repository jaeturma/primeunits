<?php

namespace App\Http\Controllers;

use App\Models\FinancingApplication;
use App\Models\FinancingPartner;
use App\Models\FinancingProduct;
use App\Models\Listing;
use App\Support\StoresResourceAttachments;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class FinancingApplicationController extends Controller
{
    use StoresResourceAttachments;

    public function index(Request $request): Response
    {
        $applications = FinancingApplication::query()
            ->where('user_id', $request->user()->id)
            ->with(['financingPartner:id,company_name,slug', 'financingProduct:id,name,product_type'])
            ->latest()
            ->paginate(15)
            ->through(fn (FinancingApplication $a) => $this->serialize($a));

        return Inertia::render('buyer/financing-applications/index', [
            'applications' => $applications,
        ]);
    }

    public function show(FinancingApplication $financingApplication): Response
    {
        abort_unless(
            request()->user()?->id === $financingApplication->user_id,
            403,
        );

        $financingApplication->load([
            'financingPartner:id,company_name,slug,contact_number,contact_email',
            'financingProduct:id,name,product_type,interest_rate_min,interest_rate_max,min_term_months,max_term_months',
            'listing:id,title,price,slug',
            'attachments',
        ]);

        return Inertia::render('buyer/financing-applications/show', [
            'application' => [
                ...$this->serialize($financingApplication),
                'notes' => $financingApplication->notes,
                'reviewer_notes' => $financingApplication->reviewer_notes,
                'monthly_income' => $financingApplication->monthly_income,
                'down_payment' => $financingApplication->down_payment,
                'estimated_monthly' => $financingApplication->estimatedMonthlyPayment(),
                'partner' => $financingApplication->financingPartner
                    ? ['company_name' => $financingApplication->financingPartner->company_name, 'contact_number' => $financingApplication->financingPartner->contact_number, 'contact_email' => $financingApplication->financingPartner->contact_email]
                    : null,
                'product' => $financingApplication->financingProduct
                    ? ['name' => $financingApplication->financingProduct->name, 'type_label' => $financingApplication->financingProduct->typeLabel(), 'interest_rate_min' => $financingApplication->financingProduct->interest_rate_min, 'interest_rate_max' => $financingApplication->financingProduct->interest_rate_max]
                    : null,
                'listing' => $financingApplication->listing
                    ? ['title' => $financingApplication->listing->title, 'price' => $financingApplication->listing->price]
                    : null,
                'attachments' => $this->serializeAttachments($financingApplication),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $partners = FinancingPartner::query()
            ->with(['products' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->where('status', FinancingPartner::StatusVerified)
            ->orderBy('company_name')
            ->get()
            ->map(fn (FinancingPartner $p) => [
                'id' => $p->id,
                'company_name' => $p->company_name,
                'products' => $p->products->map(fn (FinancingProduct $prod) => [
                    'id' => $prod->id,
                    'name' => $prod->name,
                    'type_label' => $prod->typeLabel(),
                    'min_amount' => $prod->min_amount,
                    'max_amount' => $prod->max_amount,
                    'interest_rate_min' => $prod->interest_rate_min,
                    'interest_rate_max' => $prod->interest_rate_max,
                    'min_term_months' => $prod->min_term_months,
                    'max_term_months' => $prod->max_term_months,
                ]),
            ]);

        $listing = null;
        if ($request->filled('listing_id')) {
            $l = Listing::query()->find($request->integer('listing_id'));
            if ($l) {
                $listing = ['id' => $l->id, 'title' => $l->title, 'price' => $l->price];
            }
        }

        return Inertia::render('buyer/financing-applications/create', [
            'partners' => $partners,
            'employment_types' => FinancingApplication::employmentTypes(),
            'listing' => $listing,
            'user' => ['name' => $request->user()->name],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'financing_partner_id' => ['required', 'integer', 'exists:financing_partners,id'],
            'financing_product_id' => ['required', 'integer', 'exists:financing_products,id'],
            'listing_id' => ['nullable', 'integer', 'exists:listings,id'],
            'full_name' => ['required', 'string', 'max:255'],
            'contact_number' => ['required', 'string', 'max:20'],
            'employment_type' => ['required', 'in:employed,self_employed,business_owner,others'],
            'monthly_income' => ['nullable', 'numeric', 'min:0'],
            'unit_price' => ['required', 'numeric', 'min:1'],
            'requested_amount' => ['required', 'numeric', 'min:1'],
            'down_payment' => ['nullable', 'numeric', 'min:0'],
            'preferred_term_months' => ['required', 'integer', 'min:1', 'max:360'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'attachments' => self::AttachmentRules,
            'attachments.*' => self::AttachmentFileRules,
        ]);

        $partner = FinancingPartner::query()->findOrFail($request->integer('financing_partner_id'));
        abort_unless($partner->isVerified(), 422, 'Financing partner is not active.');

        $application = FinancingApplication::query()->create([
            ...$request->only([
                'financing_partner_id', 'financing_product_id', 'listing_id',
                'full_name', 'contact_number', 'employment_type', 'monthly_income',
                'unit_price', 'requested_amount', 'down_payment', 'preferred_term_months', 'notes',
            ]),
            'user_id' => $request->user()->id,
            'reference_code' => 'FA-'.strtoupper(Str::random(7)),
            'status' => FinancingApplication::StatusSubmitted,
            'submitted_at' => now(),
        ]);

        $this->storeResourceAttachments($request, $application, 'financing/applications');

        return to_route('buyer.financing-applications.show', $application)
            ->with('success', "Application {$application->reference_code} submitted successfully.");
    }

    /** @return array<string,mixed> */
    private function serialize(FinancingApplication $a): array
    {
        return [
            'id' => $a->id,
            'reference_code' => $a->reference_code,
            'status' => $a->status,
            'status_label' => $a->statusLabel(),
            'full_name' => $a->full_name,
            'contact_number' => $a->contact_number,
            'employment_type' => $a->employment_type,
            'unit_price' => $a->unit_price,
            'requested_amount' => $a->requested_amount,
            'preferred_term_months' => $a->preferred_term_months,
            'submitted_at' => $a->submitted_at?->toISOString(),
            'reviewed_at' => $a->reviewed_at?->toISOString(),
            'created_at' => $a->created_at?->toISOString(),
            'partner_name' => $a->financingPartner?->company_name,
            'product_name' => $a->financingProduct?->name,
        ];
    }
}
