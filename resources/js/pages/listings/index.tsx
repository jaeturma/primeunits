import { Head, Link, router } from '@inertiajs/react';
import {
    Filter,
    MapPin,
    Search,
    SlidersHorizontal,
    Truck,
    X,
} from 'lucide-react';
import type { FormEvent } from 'react';
import { useEffect, useState } from 'react';

type Category = {
    id: number;
    name: string;
    slug: string;
    classification_field: { id: number; name: string; label: string } | null;
};

type ClassificationOption = {
    value: string;
    label: string;
    count: number;
};

type ListingCard = {
    id: number;
    title: string;
    slug: string;
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

type Props = {
    listings: {
        data: ListingCard[];
        current_page: number;
        last_page: number;
        total: number;
        per_page: number;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    filters: Filters;
    categories: Category[];
    brandsByCategory: Record<string, string[]>;
    locationOptions: {
        provinces: string[];
        municipalities: Array<{ name: string; province_name: string | null }>;
    };
    conditions: Array<{ value: string; label: string }>;
};

const conditionLabel: Record<string, string> = {
    brand_new: 'Brand new',
    surplus: 'Surplus',
    used: 'Used',
};

export default function PublicListings({
    listings,
    filters,
    categories,
    brandsByCategory,
    locationOptions,
    conditions,
}: Props) {
    const [form, setForm] = useState<Filters>(filters);
    const [filtersOpen, setFiltersOpen] = useState(false);
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

    function cleaned() {
        return Object.fromEntries(
            Object.entries(form).filter(([, v]) => v !== ''),
        );
    }

    function submit(e: FormEvent) {
        e.preventDefault();
        router.get('/listings', cleaned(), {
            preserveScroll: true,
            preserveState: true,
        });
    }

    function clearAll() {
        const empty: Filters = {
            q: '',
            category: '',
            brand: '',
            classification: '',
            condition: '',
            min_price: '',
            max_price: '',
            fuel_type: '',
            max_mileage: '',
            region: '',
            province: '',
            municipality: '',
        };
        setForm(empty);
        router.get('/listings', {}, { preserveScroll: true });
    }

    const availableBrands = form.category
        ? (brandsByCategory[form.category] ?? [])
        : [];

    const availableMunicipalities = form.province
        ? locationOptions.municipalities
              .filter((m) => m.province_name === form.province)
              .map((m) => m.name)
        : [];

    const hasFilters = Object.values(filters).some((v) => v !== '');

    return (
        <>
            <Head title="Listings" />
            <div className="mx-auto flex w-full max-w-7xl flex-col gap-4 p-4 lg:p-6">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">
                            Listings
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            {listings.total.toLocaleString()} approved{' '}
                            {listings.total === 1 ? 'listing' : 'listings'}
                        </p>
                    </div>
                    <button
                        type="button"
                        onClick={() => setFiltersOpen((o) => !o)}
                        className="flex items-center gap-2 self-start rounded-md border px-3 py-2 text-sm font-medium hover:bg-accent sm:self-auto lg:hidden"
                    >
                        <SlidersHorizontal className="size-4" />
                        Filters
                        {hasFilters && (
                            <span className="flex size-5 items-center justify-center rounded-full bg-emerald-600 text-[10px] font-bold text-white">
                                {
                                    Object.values(filters).filter(
                                        (v) => v !== '',
                                    ).length
                                }
                            </span>
                        )}
                    </button>
                </div>

                <div className="grid gap-6 lg:grid-cols-[260px_1fr]">
                    <aside
                        className={`${filtersOpen ? 'block' : 'hidden'} rounded-lg border bg-card p-4 lg:block`}
                    >
                        <form onSubmit={submit} className="space-y-5">
                            <div className="flex items-center justify-between">
                                <span className="flex items-center gap-2 text-sm font-semibold">
                                    <Filter className="size-4" />
                                    Filters
                                </span>
                                {hasFilters && (
                                    <button
                                        type="button"
                                        onClick={clearAll}
                                        className="flex items-center gap-1 text-xs text-muted-foreground hover:text-foreground"
                                    >
                                        <X className="size-3" />
                                        Clear all
                                    </button>
                                )}
                            </div>

                            <div className="space-y-1">
                                <label className="text-xs font-medium text-muted-foreground">
                                    Keyword
                                </label>
                                <div className="relative">
                                    <Search className="absolute top-1/2 left-3 size-3.5 -translate-y-1/2 text-muted-foreground" />
                                    <input
                                        type="text"
                                        value={form.q}
                                        onChange={(e) =>
                                            updateField('q', e.target.value)
                                        }
                                        placeholder="Search listings..."
                                        className="h-9 w-full rounded-md border bg-background pr-3 pl-8 text-sm outline-none focus:ring-1 focus:ring-emerald-600"
                                    />
                                </div>
                            </div>

                            <FilterSelect
                                label="Category"
                                value={form.category}
                                onChange={(v) => updateField('category', v)}
                                options={categories.map((c) => ({
                                    label: c.name,
                                    value: c.slug,
                                }))}
                            />

                            {selectedCategory &&
                                classificationOptions.length > 0 && (
                                    <FilterSelect
                                        label={
                                            classificationField?.label ?? 'Type'
                                        }
                                        value={form.classification}
                                        onChange={(v) =>
                                            updateField('classification', v)
                                        }
                                        options={classificationOptions.map(
                                            (option) => ({
                                                label:
                                                    option.count > 0
                                                        ? `${option.label} (${option.count})`
                                                        : option.label,
                                                value: option.value,
                                            }),
                                        )}
                                        placeholder={`Any ${(classificationField?.label ?? 'type').toLowerCase()}`}
                                    />
                                )}

                            <FilterSelect
                                label="Brand"
                                value={form.brand}
                                onChange={(v) => updateField('brand', v)}
                                options={availableBrands.map((b) => ({
                                    label: b,
                                    value: b,
                                }))}
                                disabled={!form.category}
                                placeholder={
                                    form.category
                                        ? 'Any brand'
                                        : 'Select category first'
                                }
                            />

                            <FilterSelect
                                label="Condition"
                                value={form.condition}
                                onChange={(v) => updateField('condition', v)}
                                options={conditions.map((c) => ({
                                    label: c.label,
                                    value: c.value,
                                }))}
                            />

                            <div className="space-y-1">
                                <label className="text-xs font-medium text-muted-foreground">
                                    Price range (PHP)
                                </label>
                                <div className="flex gap-2">
                                    <input
                                        type="number"
                                        value={form.min_price}
                                        onChange={(e) =>
                                            updateField(
                                                'min_price',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="Min"
                                        min="0"
                                        className="h-9 w-full rounded-md border bg-background px-3 text-sm outline-none focus:ring-1 focus:ring-emerald-600"
                                    />
                                    <input
                                        type="number"
                                        value={form.max_price}
                                        onChange={(e) =>
                                            updateField(
                                                'max_price',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="Max"
                                        min="0"
                                        className="h-9 w-full rounded-md border bg-background px-3 text-sm outline-none focus:ring-1 focus:ring-emerald-600"
                                    />
                                </div>
                            </div>

                            <div className="space-y-2">
                                <label className="flex items-center gap-1 text-xs font-medium text-muted-foreground">
                                    <MapPin className="size-3" />
                                    Location
                                </label>
                                <FilterSelect
                                    label="Province"
                                    value={form.province}
                                    onChange={(v) => updateField('province', v)}
                                    options={locationOptions.provinces.map(
                                        (p) => ({ label: p, value: p }),
                                    )}
                                />
                                <FilterSelect
                                    label="Municipality / City"
                                    value={form.municipality}
                                    onChange={(v) =>
                                        updateField('municipality', v)
                                    }
                                    options={availableMunicipalities.map(
                                        (m) => ({ label: m, value: m }),
                                    )}
                                    disabled={!form.province}
                                    placeholder={
                                        form.province
                                            ? 'Any'
                                            : 'Select province first'
                                    }
                                />
                            </div>

                            <button
                                type="submit"
                                className="h-9 w-full rounded-md bg-emerald-600 text-sm font-semibold text-white hover:bg-emerald-700"
                            >
                                Apply filters
                            </button>
                        </form>
                    </aside>

                    <main className="min-w-0 space-y-4">
                        {hasFilters && (
                            <ActiveFilterChips
                                filters={filters}
                                categories={categories}
                            />
                        )}

                        {listings.data.length === 0 ? (
                            <div className="rounded-lg border border-dashed py-16 text-center">
                                <Truck className="mx-auto size-10 text-muted-foreground/40" />
                                <p className="mt-3 text-sm font-medium text-muted-foreground">
                                    No listings found
                                </p>
                                <p className="mt-1 text-xs text-muted-foreground">
                                    Try adjusting your filters
                                </p>
                            </div>
                        ) : (
                            <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                                {listings.data.map((listing) => (
                                    <ListingCard
                                        key={listing.id}
                                        listing={listing}
                                    />
                                ))}
                            </div>
                        )}

                        {listings.last_page > 1 && (
                            <Pagination links={listings.links} />
                        )}
                    </main>
                </div>
            </div>
        </>
    );
}

function FilterSelect({
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
        <div className="space-y-1">
            <label className="text-xs font-medium text-muted-foreground">
                {label}
            </label>
            <select
                value={value}
                onChange={(e) => onChange(e.target.value)}
                disabled={disabled}
                className={`h-9 w-full rounded-md border px-3 text-sm outline-none ${
                    disabled
                        ? 'cursor-not-allowed border-border bg-muted text-muted-foreground'
                        : 'bg-background focus:ring-1 focus:ring-emerald-600'
                }`}
            >
                <option value="">{placeholder}</option>
                {options.map((opt) => (
                    <option key={opt.value} value={opt.value}>
                        {opt.label}
                    </option>
                ))}
            </select>
        </div>
    );
}

function ActiveFilterChips({
    filters,
    categories,
}: {
    filters: Filters;
    categories: Category[];
}) {
    const chips: Array<{ key: keyof Filters; label: string }> = [];

    if (filters.q) {
        chips.push({ key: 'q', label: `"${filters.q}"` });
    }

    if (filters.category) {
        const cat = categories.find((c) => c.slug === filters.category);
        chips.push({ key: 'category', label: cat?.name ?? filters.category });
    }

    if (filters.classification) {
        chips.push({
            key: 'classification',
            label: filters.classification,
        });
    }

    if (filters.brand) {
        chips.push({ key: 'brand', label: filters.brand });
    }

    if (filters.condition) {
        chips.push({
            key: 'condition',
            label: conditionLabel[filters.condition] ?? filters.condition,
        });
    }

    if (filters.min_price) {
        chips.push({
            key: 'min_price',
            label: `Min PHP ${Number(filters.min_price).toLocaleString()}`,
        });
    }

    if (filters.max_price) {
        chips.push({
            key: 'max_price',
            label: `Max PHP ${Number(filters.max_price).toLocaleString()}`,
        });
    }

    if (filters.province) {
        chips.push({ key: 'province', label: filters.province });
    }

    if (filters.municipality) {
        chips.push({ key: 'municipality', label: filters.municipality });
    }

    function remove(key: keyof Filters) {
        const next = { ...filters, [key]: '' };

        if (key === 'category') {
            next.brand = '';
            next.classification = '';
        }

        if (key === 'province') {
            next.municipality = '';
        }

        router.get(
            '/listings',
            Object.fromEntries(
                Object.entries(next).filter(([, v]) => v !== ''),
            ),
            { preserveScroll: true },
        );
    }

    return (
        <div className="flex flex-wrap gap-2">
            {chips.map((chip) => (
                <span
                    key={chip.key}
                    className="flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700"
                >
                    {chip.label}
                    <button type="button" onClick={() => remove(chip.key)}>
                        <X className="size-3" />
                    </button>
                </span>
            ))}
        </div>
    );
}

function ListingCard({ listing }: { listing: ListingCard }) {
    return (
        <Link
            href={`/listings/${listing.slug ?? listing.id}`}
            className="group overflow-hidden rounded-lg border bg-card transition-shadow hover:shadow-md"
        >
            <div className="aspect-video overflow-hidden bg-muted">
                {listing.image_url ? (
                    <img
                        src={listing.image_url}
                        alt={listing.title}
                        className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                    />
                ) : (
                    <div className="flex h-full items-center justify-center text-muted-foreground/40">
                        <Truck className="size-10" />
                    </div>
                )}
            </div>
            <div className="space-y-2 p-4">
                <div className="flex items-center justify-between gap-2 text-xs text-muted-foreground">
                    <span>{listing.category.name}</span>
                    {listing.is_featured && (
                        <span className="rounded bg-emerald-50 px-2 py-0.5 font-medium text-emerald-700">
                            Featured
                        </span>
                    )}
                </div>
                <h2 className="line-clamp-2 min-h-10 text-sm leading-5 font-semibold">
                    {listing.title}
                </h2>
                <p className="font-semibold text-emerald-700">
                    PHP {Number(listing.price).toLocaleString()}
                </p>
                <p className="text-xs text-muted-foreground">
                    {listing.year_model ? `${listing.year_model} · ` : ''}
                    {conditionLabel[listing.condition] ?? listing.condition}
                    {listing.brand ? ` · ${listing.brand}` : ''}
                </p>
                {(listing.municipality || listing.province) && (
                    <p className="flex items-center gap-1 text-xs text-muted-foreground">
                        <MapPin className="size-3" />
                        {listing.municipality}
                        {listing.municipality && listing.province ? ', ' : ''}
                        {listing.province}
                    </p>
                )}
            </div>
        </Link>
    );
}

function Pagination({
    links,
}: {
    links: Array<{ url: string | null; label: string; active: boolean }>;
}) {
    return (
        <div className="flex flex-wrap items-center justify-center gap-1">
            {links.map((link, i) => {
                if (!link.url) {
                    return (
                        <span
                            key={i}
                            className="inline-flex h-9 min-w-9 items-center justify-center rounded-md px-3 text-sm text-muted-foreground"
                            dangerouslySetInnerHTML={{ __html: link.label }}
                        />
                    );
                }

                return (
                    <Link
                        key={i}
                        href={link.url}
                        className={`inline-flex h-9 min-w-9 items-center justify-center rounded-md px-3 text-sm font-medium transition-colors ${
                            link.active
                                ? 'bg-emerald-600 text-white'
                                : 'border hover:bg-accent'
                        }`}
                        dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                );
            })}
        </div>
    );
}
