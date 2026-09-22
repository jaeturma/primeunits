<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePlanRequest;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PlanController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('adm/plans/index', [
            'plans' => Plan::query()
                ->latest()
                ->get()
                ->map(fn (Plan $plan): array => [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'type' => $plan->type,
                    'tier' => $plan->tier,
                    'price' => $plan->price,
                    'duration_days' => $plan->duration_days,
                    'features' => $plan->features ?? [],
                    'is_active' => $plan->is_active,
                ]),
            'types' => [
                ['value' => Plan::TypeBoost, 'label' => 'Boost'],
                ['value' => Plan::TypeSubscription, 'label' => 'Subscription'],
                ['value' => Plan::TypeMembership, 'label' => 'Membership'],
            ],
            'tiers' => [
                ['value' => Plan::TierRegular, 'label' => 'Regular'],
                ['value' => Plan::TierSilver, 'label' => 'Silver'],
                ['value' => Plan::TierGold, 'label' => 'Gold'],
            ],
        ]);
    }

    public function store(StorePlanRequest $request): RedirectResponse
    {
        Plan::query()->create($request->validated());

        return back();
    }

    public function update(StorePlanRequest $request, Plan $plan): RedirectResponse
    {
        $plan->update($request->validated());

        return back();
    }
}
