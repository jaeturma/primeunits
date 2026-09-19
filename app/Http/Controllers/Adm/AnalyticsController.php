<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Listing;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    public function dashboardSummary(Request $request, AnalyticsService $analytics): Response|JsonResponse
    {
        $filters = $this->filters($request);
        $payload = [
            'filters' => $filters,
            'filterOptions' => $this->filterOptions(),
            'summary' => $analytics->dashboardSummary($filters),
            'revenue' => $analytics->revenueReport($filters),
            'conversion' => $analytics->conversionStats($filters),
            'listingStats' => $analytics->listingStats($filters),
        ];

        if ($request->expectsJson()) {
            return response()->json($payload);
        }

        return Inertia::render('adm/dashboard', $payload);
    }

    public function revenueReport(Request $request, AnalyticsService $analytics): JsonResponse
    {
        return response()->json($analytics->revenueReport($this->filters($request)));
    }

    public function listingStats(Request $request, AnalyticsService $analytics): JsonResponse
    {
        return response()->json($analytics->listingStats($this->filters($request)));
    }

    public function conversionStats(Request $request, AnalyticsService $analytics): JsonResponse
    {
        return response()->json($analytics->conversionStats($this->filters($request)));
    }

    /**
     * @return array{from: string|null, to: string|null, category_id: int|null, location: string|null}
     */
    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'location' => ['nullable', 'string', 'max:255'],
        ]);

        return [
            'from' => $validated['from'] ?? null,
            'to' => $validated['to'] ?? null,
            'category_id' => isset($validated['category_id']) ? (int) $validated['category_id'] : null,
            'location' => $validated['location'] ?? null,
        ];
    }

    /**
     * @return array{categories: list<array{id: int, name: string}>, locations: list<string>}
     */
    private function filterOptions(): array
    {
        $regions = Listing::query()
            ->whereNotNull('region')
            ->where('region', '!=', '')
            ->distinct()
            ->pluck('region');

        $provinces = Listing::query()
            ->whereNotNull('province')
            ->where('province', '!=', '')
            ->distinct()
            ->pluck('province');

        return [
            'categories' => Category::query()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Category $category): array => [
                    'id' => $category->id,
                    'name' => $category->name,
                ])
                ->all(),
            'locations' => $regions
                ->merge($provinces)
                ->unique()
                ->sort()
                ->values()
                ->all(),
        ];
    }
}
