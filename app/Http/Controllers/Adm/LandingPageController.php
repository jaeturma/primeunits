<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLandingPageRequest;
use App\Models\LandingPage;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class LandingPageController extends Controller
{
    public function edit(): Response
    {
        $landing = LandingPage::query()->firstOrCreate(
            ['key' => 'home'],
            [
                'hero_title' => 'Cars, motorcycles, farm machines, and heavy equipment in one serious marketplace.',
                'hero_subtitle' => 'Search by brand, classification, condition, fuel type, mileage, and location from town or city up to Luzon, Visayas, and Mindanao.',
                'seller_cta_title' => 'Sell vehicles and equipment with buyer-ready workflows.',
                'seller_cta_body' => 'Add listings, track inquiries, confirm deals, and submit payments from the seller dashboard.',
            ],
        );

        return Inertia::render('adm/landing/index', [
            'landing' => $landing,
        ]);
    }

    public function update(StoreLandingPageRequest $request): RedirectResponse
    {
        LandingPage::query()->updateOrCreate(
            ['key' => 'home'],
            [
                ...$request->validated(),
                'key' => 'home',
            ],
        );

        return back();
    }
}
