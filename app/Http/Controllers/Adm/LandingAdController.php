<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLandingAdRequest;
use App\Models\LandingAd;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class LandingAdController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('adm/landing-ads/index', [
            'ads' => LandingAd::query()
                ->orderBy('sort_order')
                ->orderBy('title')
                ->get(),
        ]);
    }

    public function store(StoreLandingAdRequest $request): RedirectResponse
    {
        LandingAd::query()->create($this->adData($request));

        return back();
    }

    public function update(StoreLandingAdRequest $request, LandingAd $landingAd): RedirectResponse
    {
        $landingAd->update($this->adData($request));

        return back();
    }

    public function destroy(LandingAd $landingAd): RedirectResponse
    {
        $landingAd->delete();

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function adData(StoreLandingAdRequest $request): array
    {
        $data = $request->adData();

        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('landing-ads', 'public');

            $data['image_url'] = Storage::disk('public')->url($path);
        }

        return $data;
    }
}
