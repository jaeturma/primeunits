<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class BrandController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('adm/brands/index', [
            'brands' => Brand::query()
                ->orderBy('category_group')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'groups' => $this->groups(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Brand::query()->create($this->data($request));

        return back();
    }

    public function update(Request $request, Brand $brand): RedirectResponse
    {
        $brand->update($this->data($request, $brand));

        return back();
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function groups(): array
    {
        return [
            ['value' => 'vehicle', 'label' => 'Cars'],
            ['value' => 'truck', 'label' => 'Trucks'],
            ['value' => 'motorcycle', 'label' => 'Motorcycle'],
            ['value' => 'threeWheel', 'label' => '3-Wheel'],
            ['value' => 'eBike', 'label' => 'E-Bikes'],
            ['value' => 'equipment', 'label' => 'Light to Heavy'],
            ['value' => 'farm', 'label' => 'Farm'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function data(Request $request, ?Brand $brand = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('brands', 'name')->where('category_group', $request->string('category_group')->toString())->ignore($brand)],
            'logo' => ['nullable', 'string', 'max:255'],
            'category_group' => ['required', 'string', Rule::in(collect($this->groups())->pluck('value')->all())],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
