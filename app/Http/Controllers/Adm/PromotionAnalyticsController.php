<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use App\Services\PromotionAnalyticsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PromotionAnalyticsController extends Controller
{
    public function index(Request $request, PromotionAnalyticsService $analytics): Response
    {
        $filters = $this->filters($request);

        return Inertia::render('adm/promotions/index', [
            'filters' => $filters,
            'summary' => $analytics->summary($filters),
            'topFeaturedListings' => $analytics->topFeaturedListings($filters),
            'topSponsoredListings' => $analytics->topSponsoredListings($filters),
        ]);
    }

    /**
     * @return array{from: string|null, to: string|null}
     */
    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        return [
            'from' => $validated['from'] ?? null,
            'to' => $validated['to'] ?? null,
        ];
    }
}
