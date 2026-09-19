<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('adm/categories/index', [
            'categories' => Category::query()
                ->withCount('listings')
                ->orderBy('name')
                ->get(['id', 'name', 'slug']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Category::query()->create($this->data($request));

        return back();
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $category->update($this->data($request, $category));

        return back();
    }

    /**
     * @return array<string, string>
     */
    private function data(Request $request, ?Category $category = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($category)],
        ]);
    }
}
