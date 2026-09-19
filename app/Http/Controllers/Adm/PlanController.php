<?php

namespace App\Http\Controllers\Adm;

use App\Http\Requests\StorePlanRequest;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
                    'price' => $plan->price,
                    'duration_days' => $plan->duration_days,
                    'features' => $plan->features ?? [],
                    'is_active' => $plan->is_active,
                ]),
            'types' => [
                ['value' => Plan::TypeBoost, 'label' => 'Boost'],
                ['value' => Plan::TypeSubscription, 'label' => 'Subscription'],
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
