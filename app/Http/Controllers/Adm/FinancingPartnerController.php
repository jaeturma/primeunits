<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use App\Models\FinancingPartner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class FinancingPartnerController extends Controller
{
    public function index(Request $request): Response
    {
        $partners = FinancingPartner::query()
            ->with(['user:id,name,email'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')->toString()))
            ->when($request->filled('q'), function ($q) use ($request): void {
                $search = $request->string('q')->toString();
                $q->where(function ($q) use ($search): void {
                    $q->where('company_name', 'like', "%{$search}%")
                        ->orWhere('license_number', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(20)
            ->through(fn (FinancingPartner $p) => [
                'id' => $p->id,
                'slug' => $p->slug,
                'company_name' => $p->company_name,
                'license_number' => $p->license_number,
                'contact_number' => $p->contact_number,
                'contact_email' => $p->contact_email,
                'region' => $p->region,
                'status' => $p->status,
                'status_label' => $p->statusLabel(),
                'logo_url' => $p->logo ? Storage::disk('public')->url($p->logo) : null,
                'verified_at' => $p->verified_at?->toISOString(),
                'rejected_reason' => $p->rejected_reason,
                'created_at' => $p->created_at?->toISOString(),
                'user' => $p->user ? ['name' => $p->user->name, 'email' => $p->user->email] : null,
            ]);

        return Inertia::render('adm/financing/partners/index', [
            'partners' => $partners,
            'filters' => [
                'q' => $request->string('q')->toString(),
                'status' => $request->string('status')->toString(),
            ],
        ]);
    }

    public function approve(FinancingPartner $financingPartner): RedirectResponse
    {
        $financingPartner->update([
            'status' => FinancingPartner::StatusVerified,
            'verified_at' => now(),
            'rejected_reason' => null,
        ]);

        // Assign financing_partner role to user
        $role = \App\Models\Role::query()->where('name', 'financing_partner')->first();
        if ($role) {
            $financingPartner->user->roles()->syncWithoutDetaching([$role->id]);
        }

        return back()->with('success', 'Financing partner approved.');
    }

    public function reject(Request $request, FinancingPartner $financingPartner): RedirectResponse
    {
        $request->validate(['reason' => ['required', 'string', 'max:1000']]);

        $financingPartner->update([
            'status' => FinancingPartner::StatusRejected,
            'rejected_reason' => $request->string('reason')->toString(),
        ]);

        return back()->with('success', 'Financing partner rejected.');
    }
}
