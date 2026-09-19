<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\CategorySpecField;
use App\Models\LandingAd;
use App\Models\LandingPage;
use App\Models\Listing;
use App\Models\ListingImage;
use App\Models\Municipality;
use App\Models\Province;
use App\Models\Region;
use App\Models\RentalUnit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class HomeController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $baseListings = $this->approvedListings($request);

        $marketplaceListings = (clone $baseListings)
            ->limit(11)
            ->get()
            ->map(fn (Listing $listing): array => $this->serializeCard($listing));

        $featuredListings = (clone $baseListings)
            ->limit(8)
            ->get()
            ->map(fn (Listing $listing): array => $this->serializeCard($listing));

        $miniListings = (clone $baseListings)
            ->offset(8)
            ->limit(6)
            ->get();

        if ($miniListings->count() < 6) {
            $miniListings = $this->approvedListings($request)
                ->limit(6)
                ->get();
        }

        return Inertia::render('welcome', [
            'canRegister' => Features::enabled(Features::registration()),
            'landing' => $this->landing(),
            'ads' => LandingAd::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('title')
                ->limit(12)
                ->get()
                ->map(fn (LandingAd $ad): array => [
                    'id' => $ad->id,
                    'title' => $ad->title,
                    'category' => $ad->category,
                    'body' => $ad->body,
                    'cta_label' => $ad->cta_label,
                    'cta_url' => $ad->cta_url,
                    'image_url' => $ad->image_url,
                    'accent_color' => $ad->accent_color,
                ]),
            'filters' => [
                'q' => $request->string('q')->toString(),
                'category' => $request->string('category')->toString(),
                'brand' => $request->string('brand')->toString(),
                'classification' => $request->string('classification')->toString(),
                'condition' => $request->string('condition')->toString(),
                'min_price' => $request->string('min_price')->toString(),
                'max_price' => $request->string('max_price')->toString(),
                'fuel_type' => $request->string('fuel_type')->toString(),
                'max_mileage' => $request->string('max_mileage')->toString(),
                'region' => $request->string('region')->toString(),
                'province' => $request->string('province')->toString(),
                'municipality' => $request->string('municipality')->toString(),
            ],
            'categories' => Category::searchPayload(),
            'categoryBrandGroups' => config('primeunits.category_brand_groups'),
            'rentalTypes' => collect(RentalUnit::rentalTypes())
                ->map(fn (string $label, string $value): array => ['value' => $value, 'label' => $label])
                ->values(),
            'marketplaceListings' => $marketplaceListings,
            'featuredListings' => $featuredListings,
            'miniListings' => $miniListings->map(fn (Listing $listing): array => $this->serializeCard($listing)),
            'searchOptions' => $this->searchOptions(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function landing(): array
    {
        $landing = LandingPage::query()
            ->where('key', 'home')
            ->where('is_active', true)
            ->first();

        if (! $landing instanceof LandingPage) {
            return [
                'hero_badge' => 'Verified sellers across Philippine regions',
                'hero_title' => 'Cars, motorcycles, farm machines, and heavy equipment in one serious marketplace.',
                'hero_subtitle' => 'Search by brand, classification, condition, fuel type, mileage, and location from town or city up to Luzon, Visayas, and Mindanao.',
                'search_title' => 'Find your unit',
                'featured_title' => 'Featured listings',
                'featured_subtitle' => 'Location-aware results from verified sellers',
                'results_title' => 'Search results',
                'results_subtitle' => 'Filtered listings stay on this page for buyer and guest browsing',
                'budget_title' => 'Browse by budget',
                'seller_cta_title' => 'Sell vehicles and equipment with buyer-ready workflows.',
                'seller_cta_body' => 'Add listings, track inquiries, confirm deals, and submit payments from the seller dashboard.',
                'seller_cta_button' => 'Apply as seller',
            ];
        }

        return $landing->only([
            'hero_badge',
            'hero_title',
            'hero_subtitle',
            'search_title',
            'featured_title',
            'featured_subtitle',
            'results_title',
            'results_subtitle',
            'budget_title',
            'seller_cta_title',
            'seller_cta_body',
            'seller_cta_button',
        ]);
    }

    private function approvedListings(Request $request): Builder
    {
        return Listing::query()
            ->with(['category:id,name,slug', 'images', 'boosts'])
            ->where('status', Listing::StatusApproved)
            ->withExists(['boosts as has_active_boost' => fn ($query) => $query
                ->where('is_active', true)
                ->where('ends_at', '>', now())])
            ->search($request)
            ->orderByDesc('has_active_boost')
            ->latest('approved_at')
            ->latest();
    }

    /**
     * @return array<string, mixed>
     */
    private function searchOptions(): array
    {
        $vehicleBrands = [
            ['name' => 'Toyota', 'logo' => '/brand-logos/toyota.svg'],
            ['name' => 'Honda', 'logo' => '/brand-logos/honda.svg'],
            ['name' => 'Mitsubishi', 'logo' => '/brand-logos/mitsubishi.svg'],
            ['name' => 'Ford', 'logo' => '/brand-logos/ford.svg'],
            ['name' => 'Hyundai', 'logo' => '/brand-logos/hyundai.svg'],
            ['name' => 'Nissan', 'logo' => '/brand-logos/nissan.svg'],
            ['name' => 'Chevrolet', 'logo' => '/brand-logos/chevrolet.svg'],
            ['name' => 'Subaru', 'logo' => '/brand-logos/subaru.svg'],
            ['name' => 'Suzuki', 'logo' => '/brand-logos/suzuki.svg'],
            ['name' => 'Kia', 'logo' => '/brand-logos/kia.svg'],
            ['name' => 'Mazda', 'logo' => '/brand-logos/mazda.svg'],
            ['name' => 'BMW', 'logo' => '/brand-logos/bmw.svg'],
            ['name' => 'Isuzu', 'logo' => '/brand-logos/isuzu.svg'],
            ['name' => 'Daewoo', 'logo' => '/brand-logos/daewoo.svg'],
            ['name' => 'BYD', 'logo' => '/brand-logos/byd.svg'],
            ['name' => 'Geely', 'logo' => '/brand-logos/geely.svg'],
            ['name' => 'MG', 'logo' => '/brand-logos/mg.svg'],
            ['name' => 'Changan', 'logo' => '/brand-logos/changan.svg'],
            ['name' => 'Chery', 'logo' => '/brand-logos/chery.svg'],
            ['name' => 'GAC', 'logo' => '/brand-logos/gac.svg'],
            ['name' => 'Tesla', 'logo' => '/brand-logos/tesla.svg'],
            ['name' => 'JMC', 'logo' => '/brand-logos/jmc.svg'],
            ['name' => 'Jeep', 'logo' => '/brand-logos/jeep.svg'],
            ['name' => 'Volkswagen', 'logo' => '/brand-logos/volkswagen.svg'],
            ['name' => 'Audi', 'logo' => '/brand-logos/audi.svg'],
            ['name' => 'Volvo', 'logo' => '/brand-logos/volvo.svg'],
            ['name' => 'Peugeot', 'logo' => '/brand-logos/peugeot.svg'],
        ];
        $motorcycleBrands = [
            ['name' => 'Yamaha', 'logo' => '/brand-logos/yamaha.svg'],
            ['name' => 'Honda', 'logo' => '/brand-logos/honda_motorcycles.svg'],
            ['name' => 'BMW', 'logo' => '/brand-logos/bmw.svg'],
            ['name' => 'Kawasaki', 'logo' => '/brand-logos/kawasaki.svg'],
            ['name' => 'KTM', 'logo' => '/brand-logos/ktm.svg'],
            ['name' => 'Ducati', 'logo' => '/brand-logos/ducati.svg'],
            ['name' => 'Kymco', 'logo' => '/brand-logos/kymco.svg'],
            ['name' => 'SYM', 'logo' => '/brand-logos/sym.svg'],
            ['name' => 'Motorstar', 'logo' => '/brand-logos/motorstar.svg'],
            ['name' => 'Rusi', 'logo' => '/brand-logos/rusi.svg'],
            ['name' => 'Suzuki', 'logo' => '/brand-logos/suzuki.svg'],
            ['name' => 'Vespa', 'logo' => '/brand-logos/vespa.svg'],
        ];
        $threeWheelBrands = [
            ['name' => 'TVS', 'logo' => '/brand-logos/tvs.svg'],
            ['name' => 'Bajaj', 'logo' => '/brand-logos/bajaj.svg'],
            ['name' => 'Piaggio', 'logo' => '/brand-logos/piaggio.svg'],
            ['name' => 'Can-Am', 'logo' => '/brand-logos/canam.svg'],
            ['name' => 'HATASU-Ebikes', 'logo' => '/brand-logos/hatasu-ebikes.svg'],
            ['name' => 'NWOW', 'logo' => '/brand-logos/nwow.svg'],
        ];
        $eBikeBrands = [
            ['name' => 'NWOW', 'logo' => '/brand-logos/nwow.svg'],
            ['name' => 'Yadea', 'logo' => '/brand-logos/yadea.svg'],
            ['name' => 'Nakto', 'logo' => '/brand-logos/nakto.svg'],
            ['name' => 'ADO', 'logo' => '/brand-logos/ado.svg'],
            ['name' => 'Supremo', 'logo' => '/brand-logos/supremo.svg'],
            ['name' => 'OEM', 'logo' => '/brand-logos/oem.svg'],
        ];
        $truckBrands = [
            ['name' => 'Isuzu', 'logo' => '/brand-logos/isuzu.svg'],
            ['name' => 'Mitsubishi', 'logo' => '/brand-logos/mitsubishi.svg'],
            ['name' => 'Hino', 'logo' => '/brand-logos/hino.svg'],
            ['name' => 'Foton', 'logo' => '/brand-logos/foton.svg'],
            ['name' => 'JMC', 'logo' => '/brand-logos/jmc.svg'],
            ['name' => 'Volvo', 'logo' => '/brand-logos/volvo.svg'],
            ['name' => 'Sinotruk', 'logo' => '/brand-logos/sinotruk.svg'],
            ['name' => 'Shacman', 'logo' => '/brand-logos/shacman.svg'],
            ['name' => 'Dongfeng', 'logo' => '/brand-logos/dongfeng.svg'],
            ['name' => 'FAW', 'logo' => '/brand-logos/faw.svg'],
            ['name' => 'HOWO', 'logo' => '/brand-logos/howo.svg'],
            ['name' => 'JAC', 'logo' => '/brand-logos/jac.svg'],
            ['name' => 'Changan', 'logo' => '/brand-logos/changan.svg'],
        ];
        $equipmentBrands = [
            ['name' => 'Komatsu', 'logo' => '/brand-logos/komatsu.svg'],
            ['name' => 'Volvo', 'logo' => '/brand-logos/volvo.svg'],
            ['name' => 'Hyundai', 'logo' => '/brand-logos/hyundai.svg'],
            ['name' => 'Caterpillar', 'logo' => '/brand-logos/caterpillar.svg'],
            ['name' => 'Hitachi', 'logo' => '/brand-logos/hitachi.svg'],
            ['name' => 'SANY', 'logo' => '/brand-logos/sany.svg'],
            ['name' => 'SDLG', 'logo' => '/brand-logos/sdlg.svg'],
            ['name' => 'XCMG', 'logo' => '/brand-logos/xcmg.svg'],
            ['name' => 'Zoomlion', 'logo' => '/brand-logos/zoomlion.svg'],
            ['name' => 'LiuGong', 'logo' => '/brand-logos/liugong.svg'],
            ['name' => 'Develon', 'logo' => '/brand-logos/develon.svg'],
            ['name' => 'Lonking', 'logo' => '/brand-logos/lonking.svg'],
            ['name' => 'Sinotruk', 'logo' => '/brand-logos/sinotruk.svg'],
            ['name' => 'Hino', 'logo' => '/brand-logos/hino.svg'],
            ['name' => 'Daewoo', 'logo' => '/brand-logos/daewoo.svg'],
            ['name' => 'Sandvik', 'logo' => '/brand-logos/sandvik.svg'],
            ['name' => 'JCB', 'logo' => '/brand-logos/jcb.svg'],
            ['name' => 'Toyota', 'logo' => '/brand-logos/toyota.svg'],
            ['name' => 'Mitsubishi', 'logo' => '/brand-logos/mitsubishi.svg'],
        ];
        $farmBrands = [
            ['name' => 'Kubota', 'logo' => '/brand-logos/kubota.svg'],
            ['name' => 'Yanmar', 'logo' => '/brand-logos/yanmar.svg'],
            ['name' => 'John Deere', 'logo' => '/brand-logos/johndeere.svg'],
            ['name' => 'LOVOL', 'logo' => '/brand-logos/lovol.svg'],
            ['name' => 'TYM', 'logo' => '/brand-logos/tym.svg'],
            ['name' => 'FitCorea', 'logo' => '/brand-logos/fitcorea.svg'],
            ['name' => 'OEM', 'logo' => '/brand-logos/oem.svg'],
            ['name' => 'Mahindra', 'logo' => '/brand-logos/mahindra.svg'],
            ['name' => 'Yamaha', 'logo' => '/brand-logos/yamaha.svg'],
            ['name' => 'Mitsubishi', 'logo' => '/brand-logos/mitsubishi.svg'],
        ];
        $managedBrandGroups = $this->managedBrandGroups();

        if ($managedBrandGroups !== []) {
            $vehicleBrands = $managedBrandGroups['vehicle'] ?? $vehicleBrands;
            $truckBrands = $managedBrandGroups['truck'] ?? $truckBrands;
            $motorcycleBrands = $managedBrandGroups['motorcycle'] ?? $motorcycleBrands;
            $threeWheelBrands = $managedBrandGroups['threeWheel'] ?? $threeWheelBrands;
            $eBikeBrands = $managedBrandGroups['eBike'] ?? $eBikeBrands;
            $equipmentBrands = $managedBrandGroups['equipment'] ?? $equipmentBrands;
            $farmBrands = $managedBrandGroups['farm'] ?? $farmBrands;
        }

        return [
            'brands' => collect([...$vehicleBrands, ...$truckBrands, ...$motorcycleBrands, ...$threeWheelBrands, ...$eBikeBrands, ...$equipmentBrands, ...$farmBrands])
                ->unique('name')
                ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
                ->values()
                ->all(),
            'brandGroups' => [
                'vehicle' => $vehicleBrands,
                'truck' => $truckBrands,
                'motorcycle' => $motorcycleBrands,
                'threeWheel' => $threeWheelBrands,
                'eBike' => $eBikeBrands,
                'equipment' => $equipmentBrands,
                'farm' => $farmBrands,
            ],
            'vehicleTypes' => $this->classificationOptions('cars'),
            'equipmentTypes' => array_values(array_unique([
                ...$this->classificationOptions('agricultural-equipment'),
                ...$this->classificationOptions('heavy-equipment'),
            ])),
            'motorcycleTypes' => $this->classificationOptions('motorcycles'),
            'conditions' => [
                ['value' => Listing::ConditionBrandNew, 'label' => 'Brand new'],
                ['value' => Listing::ConditionSurplus, 'label' => 'Good as new / surplus'],
                ['value' => Listing::ConditionUsed, 'label' => 'Used but good condition'],
            ],
            'fuelTypes' => $this->searchableFieldOptions('cars', 'fuel_type'),
            'regions' => $this->locationNames(Region::class, ['Luzon', 'Visayas', 'Mindanao'], 'region'),
            'provinces' => $this->locationNames(Province::class, ['Metro Manila'], 'province'),
            'municipalities' => $this->locationNames(Municipality::class, [], 'municipality'),
            'locationOptions' => $this->locationOptions(),
        ];
    }

    /**
     * The buyer-facing classification options for a category (e.g. Body
     * Type values for Cars), pulled straight from the same spec field
     * data sellers pick from when listing a unit, so the landing page
     * never shows a classification a listing couldn't actually have.
     *
     * @return array<int, string>
     */
    private function classificationOptions(string $categorySlug): array
    {
        $category = Category::query()->where('slug', $categorySlug)->first();

        return $category?->classificationField()->first()?->options ?? [];
    }

    /**
     * @return array<int, string>
     */
    private function searchableFieldOptions(string $categorySlug, string $fieldName): array
    {
        $field = CategorySpecField::query()
            ->whereHas('category', fn ($query) => $query->where('slug', $categorySlug))
            ->where('name', $fieldName)
            ->first();

        return $field?->options ?? [];
    }

    /**
     * @return array<string, array<int, array{name: string, logo: string}>>
     */
    private function managedBrandGroups(): array
    {
        return Brand::query()
            ->where('is_active', true)
            ->orderBy('category_group')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['name', 'logo', 'category_group'])
            ->groupBy('category_group')
            ->map(fn ($brands): array => $brands
                ->map(fn (Brand $brand): array => [
                    'name' => $brand->name,
                    'logo' => $brand->logo ?: '/brand-logos/oem.svg',
                ])
                ->values()
                ->all())
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function locationOptions(): array
    {
        return [
            'regions' => collect([
                ['id' => 0, 'name' => 'Luzon'],
                ['id' => -1, 'name' => 'Visayas'],
                ['id' => -2, 'name' => 'Mindanao'],
            ])->merge(Region::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Region $region): array => [
                    'id' => $region->id,
                    'name' => $region->name,
                ]))
                ->unique('name')
                ->values()
                ->all(),
            'provinces' => Province::query()
                ->with('region:id,name')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'region_id', 'name'])
                ->map(fn (Province $province): array => [
                    'id' => $province->id,
                    'name' => $province->name,
                    'region_id' => $province->region_id,
                    'region_name' => $province->region?->name,
                ])
                ->values()
                ->all(),
            'municipalities' => Municipality::query()
                ->with(['region:id,name', 'province:id,name'])
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'region_id', 'province_id', 'name'])
                ->map(fn (Municipality $municipality): array => [
                    'id' => $municipality->id,
                    'name' => $municipality->name,
                    'region_id' => $municipality->region_id,
                    'region_name' => $municipality->region?->name,
                    'province_id' => $municipality->province_id,
                    'province_name' => $municipality->province?->name,
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  class-string<Region|Province|Municipality>  $model
     * @param  array<int, string>  $defaults
     * @return array<int, string>
     */
    private function locationNames(string $model, array $defaults, string $listingColumn): array
    {
        $managedNames = $model::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->limit(250)
            ->pluck('name')
            ->all();

        $listingNames = Listing::query()
            ->whereNotNull($listingColumn)
            ->distinct()
            ->orderBy($listingColumn)
            ->pluck($listingColumn)
            ->all();

        return collect([...$defaults, ...$managedNames, ...$listingNames])
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeCard(Listing $listing): array
    {
        $primaryImage = $listing->images->firstWhere('is_primary', true) ?? $listing->images->first();

        return [
            'id' => $listing->id,
            'title' => $listing->title,
            'description' => $listing->description,
            'price' => $listing->price,
            'condition' => $listing->condition,
            'brand' => $listing->brand,
            'model' => $listing->model,
            'year_model' => $listing->year_model,
            'province' => $listing->province,
            'municipality' => $listing->municipality,
            'is_featured' => (bool) ($listing->has_active_boost ?? $listing->boosts->contains(fn ($boost): bool => $boost->isCurrentlyActive())),
            'category' => [
                'name' => $listing->category->name,
                'slug' => $listing->category->slug,
            ],
            'image_url' => $this->listingImageUrl($primaryImage),
        ];
    }

    private function listingImageUrl(?ListingImage $image): ?string
    {
        if (! $image instanceof ListingImage) {
            return null;
        }

        if (! Storage::disk('public')->exists($image->path)) {
            return '/images/landing-equipment-yard.png';
        }

        return Storage::disk('public')->url($image->path);
    }
}
