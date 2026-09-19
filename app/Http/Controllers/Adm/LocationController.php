<?php

namespace App\Http\Controllers\Adm;

use App\Http\Controllers\Controller;
use App\Models\Municipality;
use App\Models\Province;
use App\Models\Region;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class LocationController extends Controller
{
    public function index(Request $request): Response
    {
        $regions = Region::query()
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'is_active']);

        $provinces = Province::query()
            ->with('region:id,name')
            ->orderBy('name')
            ->get(['id', 'region_id', 'code', 'name', 'is_active']);

        $municipalities = Municipality::query()
            ->with(['region:id,name', 'province:id,name'])
            ->when($request->filled('region_id'), fn ($query) => $query->where('region_id', $request->integer('region_id')))
            ->when($request->filled('province_id'), fn ($query) => $query->where('province_id', $request->integer('province_id')))
            ->when($request->filled('q'), function ($query) use ($request): void {
                $search = $request->string('q')->toString();

                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->limit(250)
            ->get(['id', 'region_id', 'province_id', 'code', 'name', 'type', 'zip_code', 'district', 'is_active']);

        return Inertia::render('adm/locations/index', [
            'filters' => [
                'q' => $request->string('q')->toString(),
                'region_id' => $request->string('region_id')->toString(),
                'province_id' => $request->string('province_id')->toString(),
            ],
            'regions' => $regions,
            'provinces' => $provinces,
            'municipalities' => $municipalities,
            'municipality_count' => Municipality::query()->count(),
        ]);
    }

    public function storeRegion(Request $request): RedirectResponse
    {
        Region::query()->create($this->regionData($request));

        return back();
    }

    public function updateRegion(Request $request, Region $region): RedirectResponse
    {
        $region->update($this->regionData($request, $region));

        return back();
    }

    public function storeProvince(Request $request): RedirectResponse
    {
        Province::query()->create($this->provinceData($request));

        return back();
    }

    public function updateProvince(Request $request, Province $province): RedirectResponse
    {
        $province->update($this->provinceData($request, $province));

        return back();
    }

    public function storeMunicipality(Request $request): RedirectResponse
    {
        Municipality::query()->create($this->municipalityData($request));

        return back();
    }

    public function updateMunicipality(Request $request, Municipality $municipality): RedirectResponse
    {
        $municipality->update($this->municipalityData($request, $municipality));

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function regionData(Request $request, ?Region $region = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'size:10', Rule::unique('regions', 'code')->ignore($region)],
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function provinceData(Request $request, ?Province $province = null): array
    {
        return $request->validate([
            'region_id' => ['required', 'exists:regions,id'],
            'code' => ['required', 'string', 'size:10', Rule::unique('provinces', 'code')->ignore($province)],
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function municipalityData(Request $request, ?Municipality $municipality = null): array
    {
        return $request->validate([
            'region_id' => ['required', 'exists:regions,id'],
            'province_id' => ['nullable', 'exists:provinces,id'],
            'code' => ['required', 'string', 'size:10', Rule::unique('municipalities', 'code')->ignore($municipality)],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:20'],
            'zip_code' => ['nullable', 'string', 'max:20'],
            'district' => ['nullable', 'string', 'max:50'],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
