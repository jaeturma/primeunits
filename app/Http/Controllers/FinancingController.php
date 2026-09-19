<?php

namespace App\Http\Controllers;

use App\Models\FinancingPartner;
use App\Models\FinancingProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class FinancingController extends Controller
{
    public function index(Request $request): Response
    {
        $partners = FinancingPartner::query()
            ->with('products')
            ->where('status', FinancingPartner::StatusVerified)
            ->when($request->filled('q'), function ($q) use ($request): void {
                $search = $request->string('q')->toString();
                $q->where(function ($q) use ($search): void {
                    $q->where('company_name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('region'), fn ($q) => $q->where('region', $request->string('region')->toString()))
            ->orderBy('company_name')
            ->paginate(12)
            ->through(fn (FinancingPartner $p) => $this->serializeCard($p));

        return Inertia::render('financing/index', [
            'partners' => $partners,
            'filters' => [
                'q' => $request->string('q')->toString(),
                'region' => $request->string('region')->toString(),
                'type' => $request->string('type')->toString(),
            ],
        ]);
    }

    public function show(FinancingPartner $financingPartner): Response
    {
        abort_unless($financingPartner->isVerified(), 404);

        $financingPartner->load(['products' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')]);

        return Inertia::render('financing/show', [
            'partner' => [
                ...$this->serializeCard($financingPartner),
                'description' => $financingPartner->description,
                'website' => $financingPartner->website,
                'contact_email' => $financingPartner->contact_email,
                'full_address' => $financingPartner->full_address,
                'banner_url' => $financingPartner->banner
                    ? Storage::disk('public')->url($financingPartner->banner)
                    : null,
                'products' => $financingPartner->products->map(fn (FinancingProduct $p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'product_type' => $p->product_type,
                    'type_label' => $p->typeLabel(),
                    'description' => $p->description,
                    'min_amount' => $p->min_amount,
                    'max_amount' => $p->max_amount,
                    'interest_rate_min' => $p->interest_rate_min,
                    'interest_rate_max' => $p->interest_rate_max,
                    'min_term_months' => $p->min_term_months,
                    'max_term_months' => $p->max_term_months,
                ]),
            ],
        ]);
    }

    /** @return array<string,mixed> */
    private function serializeCard(FinancingPartner $partner): array
    {
        return [
            'id' => $partner->id,
            'slug' => $partner->slug,
            'company_name' => $partner->company_name,
            'contact_number' => $partner->contact_number,
            'region' => $partner->region,
            'province' => $partner->province,
            'municipality' => $partner->municipality,
            'logo_url' => $partner->logo
                ? Storage::disk('public')->url($partner->logo)
                : null,
            'products_count' => $partner->products->count(),
            'status' => $partner->status,
        ];
    }
}
