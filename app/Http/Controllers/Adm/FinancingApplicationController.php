<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use App\Models\FinancingApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FinancingApplicationController extends Controller
{
    public function index(Request $request): Response
    {
        $applications = FinancingApplication::query()
            ->with([
                'user:id,name,email',
                'financingPartner:id,company_name',
                'financingProduct:id,name,product_type',
            ])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')->toString()))
            ->when($request->filled('partner_id'), fn ($q) => $q->where('financing_partner_id', $request->integer('partner_id')))
            ->when($request->filled('q'), function ($q) use ($request): void {
                $search = $request->string('q')->toString();
                $q->where(function ($q) use ($search): void {
                    $q->where('reference_code', 'like', "%{$search}%")
                        ->orWhere('full_name', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($q) => $q->where('email', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(25)
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
                'partner_name' => $a->financingPartner?->company_name,
                'product_name' => $a->financingProduct?->name,
                'user' => $a->user ? ['name' => $a->user->name, 'email' => $a->user->email] : null,
                'submitted_at' => $a->submitted_at?->toISOString(),
                'reviewed_at' => $a->reviewed_at?->toISOString(),
            ]);

        return Inertia::render('adm/financing/applications/index', [
            'applications' => $applications,
            'filters' => [
                'q' => $request->string('q')->toString(),
                'status' => $request->string('status')->toString(),
            ],
        ]);
    }

    public function review(Request $request, FinancingApplication $financingApplication): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:under_review,approved,rejected'],
            'reviewer_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $financingApplication->update([
            'status' => $request->string('status')->toString(),
            'reviewer_notes' => $request->string('reviewer_notes')->toString(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Application status updated.');
    }
}
