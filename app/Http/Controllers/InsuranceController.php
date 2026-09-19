<?php

namespace App\Http\Controllers;

use App\Models\InsuranceCompany;
use Inertia\Inertia;
use Inertia\Response;

class InsuranceController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('insurance', [
            'companies' => InsuranceCompany::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
                ->map(fn (InsuranceCompany $company): array => [
                    'id' => $company->id,
                    'name' => $company->name,
                    'description' => $company->description,
                    'coverage_types' => $company->coverage_types,
                    'ctpl_price' => $company->ctpl_price,
                    'premium_starting_price' => $company->premium_starting_price,
                    'contact_number' => $company->contact_number,
                    'contact_email' => $company->contact_email,
                    'website' => $company->website,
                    'province' => $company->province,
                    'municipality' => $company->municipality,
                ]),
        ]);
    }
}
