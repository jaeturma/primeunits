<?php

namespace App\Http\Controllers;

use App\Models\SeoPage;
use App\Services\SeoLandingPageService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SeoController extends Controller
{
    public function category(Request $request, string $category, SeoLandingPageService $landingPages): Response
    {
        $page = $landingPages->pageForCategory($category);

        abort_unless($page instanceof SeoPage, 404);

        return $this->render($request, $page, $landingPages);
    }

    public function categoryLocation(Request $request, string $category, string $location, SeoLandingPageService $landingPages): Response
    {
        $page = $landingPages->pageForCategoryLocation($category, $location);

        abort_unless($page instanceof SeoPage, 404);

        return $this->render($request, $page, $landingPages);
    }

    private function render(Request $request, SeoPage $page, SeoLandingPageService $landingPages): Response
    {
        return Inertia::render('seo/landing', [
            'seoPage' => [
                'slug' => $page->slug,
                'title' => $page->title,
                'meta_title' => $page->meta_title,
                'meta_description' => $page->meta_description,
                'content' => $page->content,
                'canonical_url' => $request->url(),
                'category' => $page->category ? [
                    'id' => $page->category->id,
                    'name' => $page->category->name,
                    'slug' => $page->category->slug,
                ] : null,
                'location' => $page->municipality ?? $page->province ?? $page->region,
            ],
            'filters' => [
                'q' => $request->string('q')->toString(),
                'condition' => $request->string('condition')->toString(),
                'min_price' => $request->string('min_price')->toString(),
                'max_price' => $request->string('max_price')->toString(),
            ],
            'conditions' => $landingPages->filters(),
            'listings' => $landingPages->listings($page, $request),
        ]);
    }
}
