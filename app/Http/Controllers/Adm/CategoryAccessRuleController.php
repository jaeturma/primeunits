<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryAccessRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CategoryAccessRuleController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('adm/category-access-rules/index', [
            'categories' => Category::query()
                ->with('accessRule')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
                ->map(fn (Category $category): array => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'rule' => $category->accessRule ? [
                        'default_marketplace_tier' => $category->accessRule->default_marketplace_tier,
                        'min_buyer_access' => $category->accessRule->min_buyer_access,
                        'min_seller_access' => $category->accessRule->min_seller_access,
                        'manual_review_required' => $category->accessRule->manual_review_required,
                        'public_preview_allowed' => $category->accessRule->public_preview_allowed,
                        'verified_buyer_required' => $category->accessRule->verified_buyer_required,
                        'ownership_documents_required' => $category->accessRule->ownership_documents_required,
                        'category_credentials_required' => $category->accessRule->category_credentials_required,
                        'proof_of_funds_allowed' => $category->accessRule->proof_of_funds_allowed,
                        'confidentiality_required' => $category->accessRule->confidentiality_required,
                    ] : null,
                ]),
            'levels' => ['regular', 'silver', 'gold'],
        ]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $request->validate([
            'default_marketplace_tier' => ['required', Rule::in(['regular', 'silver', 'gold', 'gold_enterprise'])],
            'min_buyer_access' => ['required', Rule::in(['regular', 'silver', 'gold'])],
            'min_seller_access' => ['required', Rule::in(['regular', 'silver', 'gold'])],
            'manual_review_required' => ['boolean'],
            'public_preview_allowed' => ['boolean'],
            'verified_buyer_required' => ['boolean'],
            'ownership_documents_required' => ['boolean'],
            'category_credentials_required' => ['boolean'],
            'proof_of_funds_allowed' => ['boolean'],
            'confidentiality_required' => ['boolean'],
        ]);

        CategoryAccessRule::query()->updateOrCreate(
            ['category_id' => $category->id],
            $data,
        );

        return back()->with('success', 'Category access rule updated.');
    }
}
