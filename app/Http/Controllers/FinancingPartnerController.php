<?php

namespace App\Http\Controllers;

use App\Models\FinancingApplication;
use App\Models\FinancingPartner;
use App\Models\FinancingProduct;
use App\Support\StoresResourceAttachments;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class FinancingPartnerController extends Controller
{
    use StoresResourceAttachments;

    public function apply(Request $request): Response|RedirectResponse
    {
        $existing = $request->user()->financingPartner;

        if ($existing) {
            return to_route('financing-partner.status');
        }

        return Inertia::render('financing-partner/apply');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'registration_number' => ['nullable', 'string', 'max:100'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'license_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'description' => ['nullable', 'string', 'max:5000'],
            'website' => ['nullable', 'url', 'max:255'],
            'contact_number' => ['required', 'string', 'max:20'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'banner' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'region' => ['nullable', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'municipality' => ['nullable', 'string', 'max:255'],
            'full_address' => ['nullable', 'string', 'max:1000'],
            'attachments' => self::AttachmentRules,
            'attachments.*' => self::AttachmentFileRules,
        ]);

        $data = $request->only([
            'company_name', 'registration_number', 'license_number', 'description',
            'website', 'contact_number', 'contact_email', 'region', 'province', 'municipality', 'full_address',
        ]);

        $data['user_id'] = $request->user()->id;
        $data['status'] = FinancingPartner::StatusPending;

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('financing/logos', 'public');
        }
        if ($request->hasFile('banner')) {
            $data['banner'] = $request->file('banner')->store('financing/banners', 'public');
        }
        if ($request->hasFile('license_file')) {
            $data['license_file'] = $request->file('license_file')->store('financing/documents', 'public');
        }

        $partner = FinancingPartner::query()->create($data);

        $this->storeResourceAttachments($request, $partner, 'financing/partner-attachments');

        return to_route('financing-partner.status');
    }

    public function status(Request $request): Response|RedirectResponse
    {
        $partner = $request->user()->financingPartner;

        if (! $partner) {
            return to_route('financing-partner.apply');
        }

        return Inertia::render('financing-partner/status', [
            'partner' => [
                'company_name' => $partner->company_name,
                'status' => $partner->status,
                'status_label' => $partner->statusLabel(),
                'rejected_reason' => $partner->rejected_reason,
                'verified_at' => $partner->verified_at?->toISOString(),
            ],
        ]);
    }

    public function products(Request $request): Response
    {
        $partner = $request->user()->financingPartner;
        abort_unless($partner?->isVerified(), 403);

        $products = $partner->products()
            ->orderBy('sort_order')
            ->get()
            ->map(fn (FinancingProduct $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'product_type' => $p->product_type,
                'type_label' => $p->typeLabel(),
                'min_amount' => $p->min_amount,
                'max_amount' => $p->max_amount,
                'interest_rate_min' => $p->interest_rate_min,
                'interest_rate_max' => $p->interest_rate_max,
                'min_term_months' => $p->min_term_months,
                'max_term_months' => $p->max_term_months,
                'is_active' => $p->is_active,
                'description' => $p->description,
            ]);

        return Inertia::render('financing-partner/products/index', [
            'products' => $products,
            'product_types' => FinancingProduct::productTypes(),
        ]);
    }

    public function storeProduct(Request $request): RedirectResponse
    {
        $partner = $request->user()->financingPartner;
        abort_unless($partner?->isVerified(), 403);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'product_type' => ['required', 'in:term_loan,installment,lease,chattel_mortgage'],
            'description' => ['nullable', 'string', 'max:3000'],
            'min_amount' => ['required', 'numeric', 'min:0'],
            'max_amount' => ['required', 'numeric', 'gte:min_amount'],
            'interest_rate_min' => ['required', 'numeric', 'min:0', 'max:100'],
            'interest_rate_max' => ['required', 'numeric', 'gte:interest_rate_min', 'max:100'],
            'min_term_months' => ['required', 'integer', 'min:1'],
            'max_term_months' => ['required', 'integer', 'gte:min_term_months'],
        ]);

        $partner->products()->create($request->only([
            'name', 'product_type', 'description', 'min_amount', 'max_amount',
            'interest_rate_min', 'interest_rate_max', 'min_term_months', 'max_term_months',
        ]));

        return back()->with('success', 'Product added.');
    }

    public function applications(Request $request): Response
    {
        $partner = $request->user()->financingPartner;
        abort_unless($partner?->isVerified(), 403);

        $applications = FinancingApplication::query()
            ->where('financing_partner_id', $partner->id)
            ->with('financingProduct:id,name')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')->toString()))
            ->latest()
            ->paginate(20)
            ->through(fn (FinancingApplication $a) => [
                'id' => $a->id,
                'reference_code' => $a->reference_code,
                'full_name' => $a->full_name,
                'contact_number' => $a->contact_number,
                'employment_type' => $a->employment_type,
                'unit_price' => $a->unit_price,
                'requested_amount' => $a->requested_amount,
                'preferred_term_months' => $a->preferred_term_months,
                'status' => $a->status,
                'status_label' => $a->statusLabel(),
                'product_name' => $a->financingProduct?->name,
                'submitted_at' => $a->submitted_at?->toISOString(),
            ]);

        return Inertia::render('financing-partner/applications/index', [
            'applications' => $applications,
            'filters' => ['status' => $request->string('status')->toString()],
        ]);
    }

    public function reviewApplication(Request $request, FinancingApplication $financingApplication): RedirectResponse
    {
        $partner = $request->user()->financingPartner;
        abort_unless(
            $partner?->isVerified() && $partner->id === $financingApplication->financing_partner_id,
            403,
        );

        $request->validate([
            'status' => ['required', 'in:under_review,approved,rejected'],
            'reviewer_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $financingApplication->update([
            'status' => $request->string('status')->toString(),
            'reviewer_notes' => $request->string('reviewer_notes')->toString(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Application updated.');
    }
}
