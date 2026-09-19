import { Head, Link, router, usePage } from '@inertiajs/react';
import {
    ArrowRight,
    BadgeCheck,
    Bike,
    Car,
    ChevronLeft,
    ChevronRight,
    Compass,
    Construction,
    Filter,
    Key,
    LayoutGrid,
    MapPin,
    Package,
    Search,
    Tag,
    Tractor,
    Truck,
    Zap,
} from 'lucide-react';
import { useEffect, useState } from 'react';
import type { ComponentType, FormEvent, ReactNode } from 'react';
import { dashboard, login } from '@/routes';

type Category = {
    id: number;
    name: string;
    slug: string;
    icon: string | null;
    classification_field: { id: number; name: string; label: string } | null;
};

type ClassificationOption = {
    value: string;
    label: string;
    count: number;
};

type RentalTypeOption = {
    value: string;
    label: string;
};

const categoryIcons: Record<string, ComponentType<{ className?: string }>> = {
    car: Car,
    bike: Bike,
    truck: Truck,
    tractor: Tractor,
    construction: Construction,
    zap: Zap,
    package: Package,
};

function CategoryIcon({
    icon,
    className,
}: {
    icon: string | null;
    className?: string;
}) {
    const Icon = (icon && categoryIcons[icon]) || LayoutGrid;

    return <Icon className={className} />;
}

type Brand = {
    name: string;
    logo: string;
};

type ListingCard = {
    id: number;
    title: string;
    description: string | null;
    price: string;
    condition: string;
    brand: string | null;
    model: string | null;
    year_model: number | null;
    province: string | null;
    municipality: string | null;
    image_url: string | null;
    is_featured: boolean;
    category: { name: string; slug: string };
};

type LandingContent = {
    hero_badge: string;
    hero_title: string;
    hero_subtitle: string;
    search_title: string;
    featured_title: string;
    featured_subtitle: string;
    results_title: string;
    results_subtitle: string;
    budget_title: string;
    seller_cta_title: string;
    seller_cta_body: string;
    seller_cta_button: string;
};

type LandingAd = {
    id: number;
    title: string;
    category: string;
    body: string;
    cta_label: string | null;
    cta_url: string | null;
    image_url: string | null;
    accent_color: string;
};

type Filters = {
    q: string;
    category: string;
    brand: string;
    classification: string;
    condition: string;
    min_price: string;
    max_price: string;
    fuel_type: string;
    max_mileage: string;
    region: string;
    province: string;
    municipality: string;
};

type BrandGroup =
    | 'vehicle'
    | 'truck'
    | 'motorcycle'
    | 'threeWheel'
    | 'eBike'
    | 'equipment'
    | 'farm';

type SearchOptions = {
    brands: Brand[];
    brandGroups: Record<BrandGroup, Brand[]>;
    vehicleTypes: string[];
    equipmentTypes: string[];
    motorcycleTypes: string[];
    conditions: Array<{ value: string; label: string }>;
    fuelTypes: string[];
    regions: string[];
    provinces: string[];
    municipalities: string[];
    locationOptions: {
        regions: Array<{ id: number; name: string }>;
        provinces: Array<{
            id: number;
            name: string;
            region_id: number;
            region_name: string | null;
        }>;
        municipalities: Array<{
            id: number;
            name: string;
            region_id: number;
            region_name: string | null;
            province_id: number | null;
            province_name: string | null;
        }>;
    };
};

type Props = {
    landing: LandingContent;
    ads: LandingAd[];
    filters: Filters;
    categories: Category[];
    categoryBrandGroups: Record<string, BrandGroup[]>;
    rentalTypes: RentalTypeOption[];
    marketplaceListings: ListingCard[];
    featuredListings: ListingCard[];
    miniListings: ListingCard[];
    searchOptions: SearchOptions;
};

const priceRanges = [
    ['Under PHP 300K', '300000'],
    ['Under PHP 500K', '500000'],
    ['Under PHP 1M', '1000000'],
    ['Under PHP 2M', '2000000'],
];

const brandTabs = [
    { key: 'vehicle', label: 'Cars' },
    { key: 'truck', label: 'Trucks' },
    { key: 'motorcycle', label: 'Motorcycle' },
    { key: 'threeWheel', label: '3-Wheel' },
    { key: 'eBike', label: 'E-Bikes' },
    { key: 'equipment', label: 'Light to Heavy' },
    { key: 'farm', label: 'Farm' },
] as const;

const financingMenuItems = [
    { title: 'Brand New', href: '/financing?type=brand-new' },
    { title: 'Used Cars', href: '/financing?type=used-cars' },
    { title: 'Sangla OR/CR', href: '/financing?type=sangla-or-cr' },
];

type SearchMode = 'buy' | 'rent' | 'browse';

export default function Welcome({
    landing,
    ads,
    filters,
    categories,
    categoryBrandGroups,
    rentalTypes,
    marketplaceListings,
    searchOptions,
}: Props) {
    const { auth } = usePage().props;
    const [form, setForm] = useState<Filters>(filters);
    const [mode, setMode] = useState<SearchMode>('buy');
    const [rentalType, setRentalType] = useState('');
    const [activeBrandTab, setActiveBrandTab] =
        useState<(typeof brandTabs)[number]['key']>('vehicle');
    const [classificationField, setClassificationField] = useState<{
        id: number;
        name: string;
        label: string;
    } | null>(null);
    const [classificationOptions, setClassificationOptions] = useState<
        ClassificationOption[]
    >([]);

    const selectedCategory = categories.find((c) => c.slug === form.category);

    useEffect(() => {
        if (!selectedCategory) {
            return;
        }

        let cancelled = false;

        fetch(`/categories/${selectedCategory.id}/classifications`)
            .then((response) => response.json())
            .then(
                (payload: {
                    field: {
                        id: number;
                        name: string;
                        label: string;
                    } | null;
                    options: ClassificationOption[];
                }) => {
                    if (cancelled) {
                        return;
                    }

                    setClassificationField(payload.field);
                    setClassificationOptions(payload.options);
                },
            );

        return () => {
            cancelled = true;
        };
    }, [selectedCategory]);

    function updateField(field: keyof Filters, value: string) {
        setForm((current) => {
            if (field === 'region') {
                return {
                    ...current,
                    region: value,
                    province: '',
                    municipality: '',
                };
            }

            if (field === 'province') {
                return { ...current, province: value, municipality: '' };
            }

            if (field === 'category') {
                return {
                    ...current,
                    category: value,
                    brand: '',
                    classification: '',
                };
            }

            return { ...current, [field]: value };
        });
    }

    function cleanedForm() {
        return Object.fromEntries(
            Object.entries(form).filter(([, value]) => value !== ''),
        );
    }

    function cleanedRentalForm() {
        return Object.fromEntries(
            Object.entries({
                q: form.q,
                type: rentalType,
                province: form.province,
                municipality: form.municipality,
                max_price: form.max_price,
            }).filter(([, value]) => value !== ''),
        );
    }

    function submitSearch(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        if (mode === 'rent') {
            router.get('/rentals', cleanedRentalForm());

            return;
        }

        router.get(mode === 'browse' ? '/listings' : '/', cleanedForm(), {
            preserveScroll: true,
            preserveState: mode !== 'browse',
        });
    }

    function applyLocation() {
        router.get('/', cleanedForm(), {
            preserveScroll: true,
            preserveState: true,
        });
    }

    const visibleBrands = form.category
        ? (() => {
              const groups = categoryBrandGroups[form.category] ?? [];
              const seen = new Set<string>();

              return groups
                  .flatMap((g) => searchOptions.brandGroups[g] ?? [])
                  .filter((b) => {
                      if (seen.has(b.name)) {
                          return false;
                      }

                      seen.add(b.name);

                      return true;
                  })
                  .sort((a, b) =>
                      a.name.localeCompare(b.name, undefined, {
                          sensitivity: 'base',
                      }),
                  );
          })()
        : searchOptions.brands;

    const hasActiveFilters = Object.values(filters).some(
        (value) => value !== '',
    );
    const marketplaceCards = buildMarketplaceCards(
        marketplaceListings,
        ads,
        !hasActiveFilters,
    );
    const filteredProvinces = searchOptions.locationOptions.provinces.filter(
        (province) => !form.region || province.region_name === form.region,
    );
    const provinceHasMatch = filteredProvinces.length > 0;
    const visibleProvinces = provinceHasMatch
        ? filteredProvinces
        : searchOptions.locationOptions.provinces;
    const filteredMunicipalities =
        searchOptions.locationOptions.municipalities.filter(
            (municipality) =>
                (!form.region ||
                    municipality.region_name === form.region ||
                    !provinceHasMatch) &&
                (!form.province ||
                    municipality.province_name === form.province),
        );

    return (
        <>
            <Head title="PrimeUnits Philippines">
                <meta
                    name="description"
                    content="Search vehicles, motorcycles, farm equipment, light equipment, and heavy equipment from verified Philippine sellers."
                />
            </Head>

            <main className="min-h-screen bg-[#f4f5f2] text-zinc-950">
                <header className="border-b border-zinc-200 bg-white">
                    <div className="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 lg:px-6">
                        <Link
                            href="/"
                            className="flex items-center gap-3 font-semibold"
                        >
                            <span className="flex size-10 items-center justify-center rounded-md bg-emerald-600 text-white">
                                <Truck className="size-5" />
                            </span>
                            <span className="text-xl">PrimeUnits</span>
                        </Link>

                        <nav className="flex items-center gap-1 text-sm">
                            <Link
                                href="/seller/apply"
                                className="hidden rounded-md px-3 py-2 font-medium text-zinc-700 hover:bg-zinc-100 md:inline-flex"
                            >
                                Sell
                            </Link>
                            <Link
                                href="/rentals"
                                className="hidden rounded-md px-3 py-2 font-medium text-zinc-700 hover:bg-zinc-100 md:inline-flex"
                            >
                                Rent
                            </Link>
                            <Link
                                href="/insurance"
                                className="hidden rounded-md px-3 py-2 font-medium text-zinc-700 hover:bg-zinc-100 md:inline-flex"
                            >
                                Insurance
                            </Link>
                            <div className="group relative hidden md:block">
                                <Link
                                    href="/financing"
                                    className="inline-flex rounded-md px-3 py-2 font-medium text-zinc-700 hover:bg-zinc-100"
                                >
                                    Financing
                                </Link>
                                <div className="invisible absolute top-full right-0 z-20 w-44 rounded-md border border-zinc-200 bg-white p-1 opacity-0 shadow-lg transition group-hover:visible group-hover:opacity-100">
                                    {financingMenuItems.map((item) => (
                                        <Link
                                            key={item.href}
                                            href={item.href}
                                            className="block rounded px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100"
                                        >
                                            {item.title}
                                        </Link>
                                    ))}
                                </div>
                            </div>
                            <Link
                                href="/contact-us"
                                className="hidden rounded-md px-3 py-2 font-medium text-zinc-700 hover:bg-zinc-100 md:inline-flex"
                            >
                                Ask
                            </Link>
                            {auth.user ? (
                                <Link
                                    href={dashboard()}
                                    className="rounded-md bg-zinc-950 px-4 py-2 font-semibold text-white"
                                >
                                    Dashboard
                                </Link>
                            ) : (
                                <Link
                                    href={login()}
                                    className="rounded-md bg-zinc-950 px-4 py-2 font-semibold text-white hover:bg-zinc-800"
                                >
                                    Login
                                </Link>
                            )}
                        </nav>
                    </div>
                </header>

                <section className="relative overflow-hidden border-b border-zinc-200 bg-zinc-950">
                    <img
                        src="/images/landing-equipment-yard.png"
                        alt="Vehicles and equipment ready for sale"
                        className="absolute inset-0 h-full w-full object-cover opacity-90"
                    />
                    <div className="absolute inset-0 bg-gradient-to-r from-zinc-950/88 via-zinc-950/58 to-zinc-950/5" />
                    <div className="relative mx-auto grid min-h-[560px] max-w-7xl gap-6 px-4 py-8 lg:grid-cols-[1fr_480px] lg:px-6">
                        <div className="flex min-h-[420px] flex-col justify-end py-8 text-white">
                            <p className="mb-3 flex items-center gap-2 text-sm font-medium text-emerald-200">
                                <BadgeCheck className="size-4" />
                                {landing.hero_badge}
                            </p>
                            <h1 className="max-w-3xl text-4xl leading-tight font-semibold tracking-normal md:text-5xl">
                                {landing.hero_title}
                            </h1>
                            <p className="mt-4 max-w-2xl text-base leading-7 text-white/78">
                                {landing.hero_subtitle}
                            </p>
                        </div>

                        <div className="self-center rounded-2xl border border-white/10 bg-white/97 p-5 shadow-2xl backdrop-blur-sm sm:p-6">
                            <div className="mb-4 flex items-center justify-between">
                                <h2 className="text-lg font-semibold tracking-tight">
                                    {landing.search_title}
                                </h2>
                                <Filter className="size-5 text-emerald-700" />
                            </div>

                            <div
                                role="tablist"
                                aria-label="Search mode"
                                className="mb-4 grid grid-cols-3 gap-1 rounded-lg bg-zinc-100 p-1"
                            >
                                {(
                                    [
                                        { key: 'buy', label: 'Buy', icon: Tag },
                                        {
                                            key: 'rent',
                                            label: 'Rent',
                                            icon: Key,
                                        },
                                        {
                                            key: 'browse',
                                            label: 'Browse',
                                            icon: Compass,
                                        },
                                    ] as const
                                ).map((tab) => (
                                    <button
                                        key={tab.key}
                                        type="button"
                                        role="tab"
                                        aria-selected={mode === tab.key}
                                        onClick={() => setMode(tab.key)}
                                        className={`flex h-9 items-center justify-center gap-1.5 rounded-md text-sm font-semibold transition ${
                                            mode === tab.key
                                                ? 'bg-white text-emerald-700 shadow-sm'
                                                : 'text-zinc-600 hover:text-zinc-950'
                                        }`}
                                    >
                                        <tab.icon className="size-3.5" />
                                        {tab.label}
                                    </button>
                                ))}
                            </div>

                            <form onSubmit={submitSearch} className="space-y-4">
                                <label className="block">
                                    <span className="mb-1 block text-xs font-medium text-zinc-600">
                                        Keyword
                                    </span>
                                    <div className="relative">
                                        <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-zinc-400" />
                                        <input
                                            value={form.q}
                                            onChange={(event) =>
                                                updateField(
                                                    'q',
                                                    event.target.value,
                                                )
                                            }
                                            className="h-11 w-full rounded-lg border border-zinc-300 bg-white pr-3 pl-9 text-sm transition outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/15"
                                            placeholder={
                                                mode === 'rent'
                                                    ? 'Van rental, wedding car, self-drive...'
                                                    : 'Toyota Fortuner, excavator, tractor...'
                                            }
                                        />
                                    </div>
                                </label>

                                {mode !== 'rent' && (
                                    <div>
                                        <span className="mb-1.5 block text-xs font-medium text-zinc-600">
                                            What are you looking for?
                                        </span>
                                        <div className="flex gap-1.5 overflow-x-auto pb-1">
                                            <CategoryChip
                                                active={form.category === ''}
                                                label="All"
                                                icon={
                                                    <LayoutGrid className="size-3.5" />
                                                }
                                                onClick={() =>
                                                    updateField('category', '')
                                                }
                                            />
                                            {categories.map((category) => (
                                                <CategoryChip
                                                    key={category.slug}
                                                    active={
                                                        form.category ===
                                                        category.slug
                                                    }
                                                    label={category.name}
                                                    icon={
                                                        <CategoryIcon
                                                            icon={category.icon}
                                                            className="size-3.5"
                                                        />
                                                    }
                                                    onClick={() =>
                                                        updateField(
                                                            'category',
                                                            category.slug,
                                                        )
                                                    }
                                                />
                                            ))}
                                            <CategoryChip
                                                active={false}
                                                label="Rentals"
                                                icon={
                                                    <Key className="size-3.5" />
                                                }
                                                onClick={() => setMode('rent')}
                                            />
                                        </div>
                                    </div>
                                )}

                                {mode === 'rent' && (
                                    <div>
                                        <span className="mb-1.5 block text-xs font-medium text-zinc-600">
                                            Rental type
                                        </span>
                                        <div className="flex gap-1.5 overflow-x-auto pb-1">
                                            <CategoryChip
                                                active={rentalType === ''}
                                                label="All"
                                                icon={
                                                    <LayoutGrid className="size-3.5" />
                                                }
                                                onClick={() =>
                                                    setRentalType('')
                                                }
                                            />
                                            {rentalTypes.map((type) => (
                                                <CategoryChip
                                                    key={type.value}
                                                    active={
                                                        rentalType ===
                                                        type.value
                                                    }
                                                    label={type.label}
                                                    onClick={() =>
                                                        setRentalType(
                                                            type.value,
                                                        )
                                                    }
                                                />
                                            ))}
                                        </div>
                                    </div>
                                )}

                                {mode !== 'rent' &&
                                    selectedCategory &&
                                    classificationOptions.length > 0 && (
                                        <div>
                                            <span className="mb-1.5 block text-xs font-medium text-zinc-600">
                                                {classificationField?.label ??
                                                    'Type'}
                                            </span>
                                            <div className="flex flex-wrap gap-1.5">
                                                {classificationOptions.map(
                                                    (option) => (
                                                        <CategoryChip
                                                            key={option.value}
                                                            active={
                                                                form.classification ===
                                                                option.value
                                                            }
                                                            label={
                                                                option.count > 0
                                                                    ? `${option.label} (${option.count})`
                                                                    : option.label
                                                            }
                                                            onClick={() =>
                                                                updateField(
                                                                    'classification',
                                                                    form.classification ===
                                                                        option.value
                                                                        ? ''
                                                                        : option.value,
                                                                )
                                                            }
                                                        />
                                                    ),
                                                )}
                                            </div>
                                        </div>
                                    )}

                                {mode !== 'rent' && (
                                    <div className="grid grid-cols-2 gap-3">
                                        <SelectField
                                            label="Brand"
                                            value={form.brand}
                                            onChange={(value) =>
                                                updateField('brand', value)
                                            }
                                            options={visibleBrands.map(
                                                (brand) => ({
                                                    label: brand.name,
                                                    value: brand.name,
                                                }),
                                            )}
                                            disabled={!form.category}
                                            placeholder={
                                                form.category
                                                    ? 'Any'
                                                    : 'Select category first'
                                            }
                                        />
                                        <SelectField
                                            label="Condition"
                                            value={form.condition}
                                            onChange={(value) =>
                                                updateField('condition', value)
                                            }
                                            options={searchOptions.conditions.map(
                                                (condition) => ({
                                                    label: condition.label,
                                                    value: condition.value,
                                                }),
                                            )}
                                        />
                                    </div>
                                )}

                                <div className="grid grid-cols-2 gap-3">
                                    <PesoField
                                        label={
                                            mode === 'rent'
                                                ? 'Min per day'
                                                : 'Min price'
                                        }
                                        value={form.min_price}
                                        onChange={(value) =>
                                            updateField('min_price', value)
                                        }
                                    />
                                    <PesoField
                                        label={
                                            mode === 'rent'
                                                ? 'Max per day'
                                                : 'Max price'
                                        }
                                        value={form.max_price}
                                        onChange={(value) =>
                                            updateField('max_price', value)
                                        }
                                    />
                                </div>

                                <div
                                    className={`grid gap-3 ${mode === 'rent' ? 'sm:grid-cols-2' : 'sm:grid-cols-3'}`}
                                >
                                    {mode !== 'rent' && (
                                        <SelectField
                                            label="Region"
                                            value={form.region}
                                            onChange={(value) =>
                                                updateField('region', value)
                                            }
                                            options={searchOptions.locationOptions.regions.map(
                                                (region) => ({
                                                    label: region.name,
                                                    value: region.name,
                                                }),
                                            )}
                                        />
                                    )}
                                    <SelectField
                                        label="Province"
                                        value={form.province}
                                        onChange={(value) =>
                                            updateField('province', value)
                                        }
                                        options={visibleProvinces.map(
                                            (province) => ({
                                                label: province.name,
                                                value: province.name,
                                            }),
                                        )}
                                    />
                                    <SelectField
                                        label="Town/City"
                                        value={form.municipality}
                                        onChange={(value) =>
                                            updateField('municipality', value)
                                        }
                                        options={
                                            form.province
                                                ? filteredMunicipalities.map(
                                                      (municipality) => ({
                                                          label: municipality.name,
                                                          value: municipality.name,
                                                      }),
                                                  )
                                                : []
                                        }
                                        disabled={!form.province}
                                        placeholder={
                                            form.province
                                                ? 'Any'
                                                : 'Select province first'
                                        }
                                    />
                                </div>

                                <div className="flex gap-2 pt-1">
                                    <button
                                        type="submit"
                                        className="inline-flex h-11 flex-1 items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700"
                                    >
                                        <Search className="size-4" />
                                        {mode === 'rent'
                                            ? 'Search rentals'
                                            : mode === 'browse'
                                              ? 'Browse all units'
                                              : 'Search'}
                                    </button>
                                    {mode === 'buy' && (
                                        <button
                                            type="button"
                                            onClick={applyLocation}
                                            className="inline-flex h-11 items-center justify-center rounded-lg border border-zinc-300 px-3 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-100"
                                            aria-label="Jump to results"
                                        >
                                            <MapPin className="size-4" />
                                        </button>
                                    )}
                                </div>
                            </form>
                        </div>
                    </div>
                </section>

                <section
                    id="results"
                    className="mx-auto max-w-7xl px-4 py-6 lg:px-6"
                >
                    <div className="grid gap-4 lg:grid-cols-[240px_1fr]">
                        <aside className="space-y-3">
                            <DiscoveryPanel
                                title="Vehicles"
                                icon={<Truck className="size-4" />}
                                items={searchOptions.vehicleTypes}
                            />
                            <DiscoveryPanel
                                title="Farm and equipment"
                                icon={<Tractor className="size-4" />}
                                items={searchOptions.equipmentTypes}
                            />
                            <DiscoveryPanel
                                title="Motorcycles"
                                icon={<Bike className="size-4" />}
                                items={searchOptions.motorcycleTypes}
                            />
                        </aside>

                        <div className="space-y-6">
                            <BrandStrip
                                activeTab={activeBrandTab}
                                brandGroups={searchOptions.brandGroups}
                                onTabChange={setActiveBrandTab}
                            />

                            <SectionHeading
                                title={
                                    hasActiveFilters
                                        ? landing.results_title
                                        : landing.featured_title
                                }
                                subtitle={
                                    hasActiveFilters
                                        ? landing.results_subtitle
                                        : '12 latest marketplace picks with one featured listing and one managed ad.'
                                }
                            />
                            <div className="grid gap-x-4 gap-y-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                                {marketplaceCards.map((card) =>
                                    card.kind === 'ad' ? (
                                        <MarketplaceAdCard
                                            key={`ad-${card.ad.id}`}
                                            ad={card.ad}
                                        />
                                    ) : (
                                        <MarketplaceListingCard
                                            key={`listing-${card.listing.id}`}
                                            listing={card.listing}
                                            featured={card.featured}
                                        />
                                    ),
                                )}
                                {marketplaceCards.length === 0 && (
                                    <EmptyListings />
                                )}
                            </div>
                        </div>
                    </div>
                </section>

                <section className="border-t border-zinc-200 bg-white">
                    <div className="mx-auto max-w-7xl px-4 py-8 lg:px-6">
                        <div>
                            <h2 className="text-xl font-semibold">
                                {landing.budget_title}
                            </h2>
                            <div className="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                                {priceRanges.map(([label, value]) => (
                                    <Link
                                        key={value}
                                        href={`/?max_price=${value}#results`}
                                        className="flex items-center justify-between rounded-md border border-zinc-200 px-4 py-3 text-sm font-medium hover:border-emerald-600 hover:text-emerald-700"
                                    >
                                        {label}
                                        <ChevronRight className="size-4" />
                                    </Link>
                                ))}
                            </div>
                        </div>
                    </div>
                </section>
                <AdvertisingCarousel ads={ads} />
                <section className="border-t border-zinc-200 bg-white">
                    <div className="mx-auto max-w-7xl px-4 py-8 lg:px-6">
                        <div className="rounded-md bg-zinc-950 p-6 text-white">
                            <h2 className="text-xl font-semibold">
                                {landing.seller_cta_title}
                            </h2>
                            <p className="mt-2 text-sm leading-6 text-white/70">
                                {landing.seller_cta_body}
                            </p>
                            <Link
                                href="/seller/apply"
                                className="mt-5 inline-flex h-10 items-center rounded-md bg-emerald-400 px-4 text-sm font-semibold text-zinc-950 hover:bg-emerald-300"
                            >
                                {landing.seller_cta_button}
                                <ArrowRight className="ml-2 size-4" />
                            </Link>
                        </div>
                    </div>
                </section>
                <Footer
                    vehicleTypes={searchOptions.vehicleTypes}
                    equipmentTypes={searchOptions.equipmentTypes}
                    motorcycleTypes={searchOptions.motorcycleTypes}
                    regions={searchOptions.regions}
                />
            </main>
        </>
    );
}

function SelectField({
    label,
    value,
    onChange,
    options,
    disabled = false,
    placeholder = 'Any',
}: {
    label: string;
    value: string;
    onChange: (value: string) => void;
    options: Array<{ label: string; value: string }>;
    disabled?: boolean;
    placeholder?: string;
}) {
    return (
        <label className="block">
            <span className="mb-1 block text-xs font-medium text-zinc-600">
                {label}
            </span>
            <select
                value={value}
                onChange={(event) => onChange(event.target.value)}
                disabled={disabled}
                className={`h-10 w-full rounded-md border px-3 text-sm capitalize outline-none ${
                    disabled
                        ? 'cursor-not-allowed border-zinc-200 bg-zinc-100 text-zinc-400'
                        : 'border-zinc-300 bg-white focus:border-emerald-600'
                }`}
            >
                <option value="">{placeholder}</option>
                {options.map((option) => (
                    <option key={option.value} value={option.value}>
                        {option.label}
                    </option>
                ))}
            </select>
        </label>
    );
}

function CategoryChip({
    label,
    icon,
    active,
    onClick,
}: {
    label: string;
    icon?: ReactNode;
    active: boolean;
    onClick: () => void;
}) {
    return (
        <button
            type="button"
            onClick={onClick}
            aria-pressed={active}
            className={`flex h-9 shrink-0 items-center gap-1.5 rounded-full border px-3 text-xs font-semibold whitespace-nowrap transition ${
                active
                    ? 'border-emerald-600 bg-emerald-600 text-white shadow-sm'
                    : 'border-zinc-200 bg-white text-zinc-700 hover:border-emerald-600 hover:text-emerald-700'
            }`}
        >
            {icon}
            {label}
        </button>
    );
}

function PesoField({
    label,
    value,
    onChange,
}: {
    label: string;
    value: string;
    onChange: (value: string) => void;
}) {
    const displayValue =
        value !== '' && !Number.isNaN(Number(value))
            ? Number(value).toLocaleString('en-PH')
            : value;

    return (
        <label className="block">
            <span className="mb-1 block text-xs font-medium text-zinc-600">
                {label}
            </span>
            <div className="relative">
                <span className="absolute top-1/2 left-3 -translate-y-1/2 text-sm font-medium text-zinc-400">
                    ₱
                </span>
                <input
                    inputMode="numeric"
                    value={displayValue}
                    onChange={(event) =>
                        onChange(event.target.value.replace(/[^0-9]/g, ''))
                    }
                    placeholder="Any"
                    className="h-10 w-full rounded-md border border-zinc-300 bg-white pr-3 pl-7 text-sm outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/15"
                />
            </div>
        </label>
    );
}

function DiscoveryPanel({
    title,
    icon,
    items,
}: {
    title: string;
    icon: ReactNode;
    items: string[];
}) {
    return (
        <div className="rounded-md border border-zinc-200 bg-white p-4">
            <h2 className="flex items-center gap-2 text-sm font-semibold">
                {icon}
                {title}
            </h2>
            <div className="mt-3 flex flex-wrap gap-2">
                {items.map((item) => (
                    <Link
                        key={item}
                        href={`/?classification=${encodeURIComponent(item)}#results`}
                        className="rounded-md bg-zinc-100 px-2.5 py-1.5 text-xs font-medium text-zinc-700 hover:bg-emerald-50 hover:text-emerald-700"
                    >
                        {item}
                    </Link>
                ))}
            </div>
        </div>
    );
}

function BrandStrip({
    activeTab,
    brandGroups,
    onTabChange,
}: {
    activeTab: (typeof brandTabs)[number]['key'];
    brandGroups: SearchOptions['brandGroups'];
    onTabChange: (tab: (typeof brandTabs)[number]['key']) => void;
}) {
    const brands = brandGroups[activeTab];

    return (
        <section className="rounded-md border border-zinc-200 bg-white p-4">
            <div className="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <h2 className="font-semibold">Popular brands</h2>
                <div className="flex gap-1 overflow-x-auto rounded-md bg-zinc-100 p-1">
                    {brandTabs.map((tab) => (
                        <button
                            key={tab.key}
                            type="button"
                            onClick={() => onTabChange(tab.key)}
                            className={`h-9 rounded-md px-3 text-sm font-semibold whitespace-nowrap transition ${
                                activeTab === tab.key
                                    ? 'bg-white text-emerald-700 shadow-sm'
                                    : 'text-zinc-600 hover:text-zinc-950'
                            }`}
                        >
                            {tab.label}
                        </button>
                    ))}
                </div>
            </div>
            <div className="grid grid-cols-3 gap-2 sm:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8">
                {brands.map((brand) => (
                    <Link
                        key={brand.name}
                        href={`/?brand=${encodeURIComponent(brand.name)}#results`}
                        className="flex min-h-20 flex-col items-center justify-center gap-2 rounded-md border border-zinc-200 px-2 py-3 text-center text-xs font-medium text-zinc-700 hover:border-emerald-600 hover:text-emerald-700"
                    >
                        <img
                            src={brand.logo}
                            alt={`${brand.name} logo`}
                            className="h-8 max-w-20 object-contain"
                        />
                        <span>{brand.name}</span>
                    </Link>
                ))}
            </div>
        </section>
    );
}

function Footer({
    vehicleTypes,
    equipmentTypes,
    motorcycleTypes,
    regions,
}: {
    vehicleTypes: string[];
    equipmentTypes: string[];
    motorcycleTypes: string[];
    regions: string[];
}) {
    return (
        <footer className="border-t border-zinc-800 bg-zinc-950 text-white">
            <div className="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:grid-cols-2 lg:grid-cols-[1.2fr_1fr_1fr_1fr_1fr] lg:px-6">
                <div>
                    <Link
                        href="/"
                        className="flex items-center gap-3 font-semibold"
                    >
                        <span className="flex size-10 items-center justify-center rounded-md bg-emerald-500 text-zinc-950">
                            <Truck className="size-5" />
                        </span>
                        <span className="text-xl">PrimeUnits</span>
                    </Link>
                    <p className="mt-4 max-w-sm text-sm leading-6 text-white/65">
                        Marketplace tools for Philippine buyers and verified
                        sellers of vehicles, motorcycles, farm machines, and
                        work-ready equipment.
                    </p>
                </div>

                <FooterColumn title="Vehicles" items={vehicleTypes} />
                <FooterColumn
                    title="Equipment"
                    items={[...equipmentTypes, ...motorcycleTypes].slice(0, 8)}
                />
                <FooterColumn title="Locations" items={regions} />
                <div>
                    <h2 className="text-sm font-semibold">Company</h2>
                    <div className="mt-3 grid gap-2">
                        {[
                            ['About Us', '/about-us'],
                            ['Contact Us', '/contact-us'],
                            ['Terms and Conditions', '/terms-and-conditions'],
                            ['Privacy Policy', '/privacy-policy'],
                            ['Copyrights', '/copyrights'],
                        ].map(([label, href]) => (
                            <Link
                                key={label}
                                href={href}
                                className="text-sm text-white/62 hover:text-white"
                            >
                                {label}
                            </Link>
                        ))}
                    </div>
                </div>
            </div>

            <div className="border-t border-white/10">
                <div className="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-4 text-xs text-white/55 sm:flex-row sm:items-center sm:justify-between lg:px-6">
                    <p>(c) 2026 PrimeUnits. All rights reserved.</p>
                    <div className="flex flex-wrap gap-4">
                        <Link href="/#results" className="hover:text-white">
                            Results
                        </Link>
                        <Link href="/seller/apply" className="hover:text-white">
                            Sell a unit
                        </Link>
                        <Link href={login()} className="hover:text-white">
                            Account
                        </Link>
                        <Link
                            href="/privacy-policy"
                            className="hover:text-white"
                        >
                            Privacy Policy
                        </Link>
                        <Link href="/copyrights" className="hover:text-white">
                            Copyrights
                        </Link>
                    </div>
                </div>
            </div>
        </footer>
    );
}

function AdvertisingCarousel({ ads }: { ads: LandingAd[] }) {
    const [activeIndex, setActiveIndex] = useState(0);

    useEffect(() => {
        if (ads.length < 2) {
            return;
        }

        const interval = window.setInterval(() => {
            setActiveIndex((current) => (current + 1) % ads.length);
        }, 6500);

        return () => window.clearInterval(interval);
    }, [ads.length]);

    if (ads.length === 0) {
        return null;
    }

    const visibleAds = Array.from(
        { length: Math.min(ads.length, 3) },
        (_, offset) => ads[(activeIndex + offset) % ads.length],
    );

    function move(direction: -1 | 1) {
        setActiveIndex(
            (current) => (current + direction + ads.length) % ads.length,
        );
    }

    return (
        <section id="ads" className="border-t border-zinc-200 bg-[#f4f5f2]">
            <div className="mx-auto max-w-7xl px-4 py-8 lg:px-6">
                <div className="mb-4 flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <h2 className="text-xl font-semibold">
                            Services and dealer ads
                        </h2>
                        <p className="mt-1 text-sm text-zinc-600">
                            Service shops, motor parts, repair, repainting,
                            aftermarket, motorcycle dealers, heavy equipment,
                            and farm equipment partners.
                        </p>
                    </div>
                    {ads.length > 1 && (
                        <div className="flex items-center gap-2">
                            <button
                                type="button"
                                onClick={() => move(-1)}
                                className="inline-flex size-10 items-center justify-center rounded-md border border-zinc-300 bg-white text-zinc-700 shadow-sm hover:bg-zinc-100"
                                aria-label="Previous ad"
                            >
                                <ChevronLeft className="size-4" />
                            </button>
                            <button
                                type="button"
                                onClick={() => move(1)}
                                className="inline-flex size-10 items-center justify-center rounded-md border border-zinc-300 bg-white text-zinc-700 shadow-sm hover:bg-zinc-100"
                                aria-label="Next ad"
                            >
                                <ChevronRight className="size-4" />
                            </button>
                        </div>
                    )}
                </div>

                <div className="grid gap-4 md:grid-cols-3">
                    {visibleAds.map((ad) => (
                        <article
                            key={ad.id}
                            className="overflow-hidden rounded-md border border-zinc-200 bg-white shadow-sm"
                        >
                            <div className="relative aspect-[16/10] bg-zinc-100">
                                {ad.image_url ? (
                                    <img
                                        src={ad.image_url}
                                        alt={ad.title}
                                        className="h-full w-full object-cover"
                                    />
                                ) : (
                                    <div className="flex h-full items-center justify-center text-sm text-zinc-500">
                                        Photo coming soon
                                    </div>
                                )}
                                <div
                                    className="absolute inset-x-0 top-0 h-1.5"
                                    style={{ backgroundColor: ad.accent_color }}
                                />
                            </div>
                            <div className="p-5">
                                <p
                                    className="text-xs font-semibold uppercase"
                                    style={{ color: ad.accent_color }}
                                >
                                    {ad.category}
                                </p>
                                <h3 className="mt-3 min-h-14 text-lg font-semibold text-zinc-950">
                                    {ad.title}
                                </h3>
                                <p className="mt-2 min-h-20 text-sm leading-6 text-zinc-600">
                                    {ad.body}
                                </p>
                                {ad.cta_label && ad.cta_url && (
                                    <Link
                                        href={ad.cta_url}
                                        className="mt-4 inline-flex h-9 items-center rounded-md border border-zinc-300 px-3 text-sm font-semibold hover:bg-zinc-100"
                                    >
                                        {ad.cta_label}
                                        <ArrowRight className="ml-2 size-4" />
                                    </Link>
                                )}
                            </div>
                        </article>
                    ))}
                </div>

                {ads.length > 1 && (
                    <div className="mt-4 flex justify-center gap-2">
                        {ads.map((ad, index) => (
                            <button
                                key={ad.id}
                                type="button"
                                onClick={() => setActiveIndex(index)}
                                className="h-2.5 rounded-full transition-all"
                                style={{
                                    width: index === activeIndex ? 28 : 10,
                                    backgroundColor:
                                        index === activeIndex
                                            ? ads[index].accent_color
                                            : '#d4d4d8',
                                }}
                                aria-label={`Show ad ${index + 1}`}
                            />
                        ))}
                    </div>
                )}
            </div>
        </section>
    );
}

function FooterColumn({ title, items }: { title: string; items: string[] }) {
    return (
        <div>
            <h2 className="text-sm font-semibold">{title}</h2>
            <div className="mt-3 grid gap-2">
                {items.map((item) => (
                    <Link
                        key={item}
                        href={`/?classification=${encodeURIComponent(item)}#results`}
                        className="text-sm text-white/62 hover:text-white"
                    >
                        {item}
                    </Link>
                ))}
            </div>
        </div>
    );
}

function SectionHeading({
    title,
    subtitle,
}: {
    title: string;
    subtitle: string;
}) {
    return (
        <div className="flex items-end justify-between gap-4">
            <div>
                <h2 className="text-xl font-semibold">{title}</h2>
                <p className="mt-1 text-sm text-zinc-600">{subtitle}</p>
            </div>
            <Link
                href="/#results"
                className="hidden text-sm font-medium text-emerald-700 sm:inline-flex"
            >
                View all
            </Link>
        </div>
    );
}

type MarketplaceCard =
    | { kind: 'listing'; listing: ListingCard; featured: boolean }
    | { kind: 'ad'; ad: LandingAd };

function buildMarketplaceCards(
    listings: ListingCard[],
    ads: LandingAd[],
    includeAd: boolean,
): MarketplaceCard[] {
    const cards: MarketplaceCard[] = listings
        .slice(0, 11)
        .map((listing, index) => ({
            kind: 'listing',
            listing,
            featured: index === 0,
        }));

    if (includeAd && ads.length > 0) {
        cards.splice(Math.min(4, cards.length), 0, {
            kind: 'ad',
            ad: ads[0],
        });
    }

    return cards.slice(0, 12);
}

function MarketplaceListingCard({
    listing,
    featured,
}: {
    listing: ListingCard;
    featured: boolean;
}) {
    return (
        <Link href={`/listings/${listing.id}`} className="group block">
            <div className="relative overflow-hidden rounded-md bg-zinc-100">
                <MarketplaceImage listing={listing} />
                {featured && (
                    <span className="absolute top-2 left-2 rounded bg-white/95 px-2 py-1 text-xs font-semibold text-emerald-700 shadow-sm">
                        Featured
                    </span>
                )}
            </div>
            <div className="pt-2">
                <p className="text-base font-semibold text-zinc-950">
                    PHP {Number(listing.price).toLocaleString()}
                </p>
                <p className="mt-0.5 line-clamp-2 min-h-10 text-sm leading-5 text-zinc-800">
                    {listing.description || listing.title}
                </p>
                <p className="mt-1 truncate text-xs text-zinc-500">
                    {listing.municipality ?? listing.province ?? 'Philippines'}
                    {listing.municipality && listing.province
                        ? `, ${listing.province}`
                        : ''}
                </p>
            </div>
        </Link>
    );
}

function MarketplaceAdCard({ ad }: { ad: LandingAd }) {
    return (
        <article className="block">
            <div className="relative overflow-hidden rounded-md bg-zinc-100">
                {ad.image_url ? (
                    <img
                        src={ad.image_url}
                        alt={ad.title}
                        className="h-[180px] w-full object-cover"
                    />
                ) : (
                    <div className="flex h-[180px] items-center justify-center bg-zinc-100 text-sm text-zinc-500">
                        Photo coming soon
                    </div>
                )}
                <span
                    className="absolute top-2 left-2 rounded px-2 py-1 text-xs font-semibold text-white shadow-sm"
                    style={{ backgroundColor: ad.accent_color }}
                >
                    Ad
                </span>
            </div>
            <div className="pt-2">
                <p className="text-base font-semibold text-zinc-950">
                    {ad.title}
                </p>
                <p className="mt-0.5 line-clamp-2 min-h-10 text-sm leading-5 text-zinc-800">
                    {ad.body}
                </p>
                <div className="mt-1 flex items-center justify-between gap-2">
                    <p className="truncate text-xs text-zinc-500">
                        {ad.category}
                    </p>
                    {ad.cta_label && ad.cta_url && (
                        <Link
                            href={ad.cta_url}
                            className="text-xs font-semibold text-emerald-700 hover:text-emerald-800"
                        >
                            {ad.cta_label}
                        </Link>
                    )}
                </div>
            </div>
        </article>
    );
}

function MarketplaceImage({ listing }: { listing: ListingCard }) {
    if (listing.image_url) {
        return (
            <img
                src={listing.image_url}
                alt={listing.title}
                className="h-[180px] w-full object-cover transition duration-200 group-hover:scale-[1.02]"
            />
        );
    }

    return (
        <div className="flex h-[180px] items-center justify-center bg-zinc-100 text-zinc-400">
            <Truck className="size-8" />
        </div>
    );
}

function EmptyListings() {
    return (
        <div className="rounded-md border border-dashed border-zinc-300 bg-white p-8 text-center text-sm text-zinc-600 sm:col-span-2 lg:col-span-4">
            No listings match this location yet. Try another city, province, or
            region.
        </div>
    );
}
