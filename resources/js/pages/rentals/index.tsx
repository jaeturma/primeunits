import { Head, Link, router } from '@inertiajs/react';
import {
    Bus,
    Car,
    Filter,
    MapPin,
    Search,
    SlidersHorizontal,
    Truck,
    User,
    UserX,
    X,
} from 'lucide-react';
import { FormEvent, useState } from 'react';

type RentalCard = {
    id: number;
    slug: string;
    provider_username: string;
    name: string;
    rental_type: string;
    rental_type_label: string;
    brand: string | null;
    model: string | null;
    year_model: number | null;
    with_driver: boolean;
    price_per_day: string;
    price_per_hour: string | null;
    province: string | null;
    municipality: string | null;
    image_url: string | null;
};

type Filters = {
    q: string;
    type: string;
    with_driver: string;
    province: string;
    municipality: string;
    max_price: string;
};

type Props = {
    rentals: {
        data: RentalCard[];
        total: number;
        last_page: number;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    rental_types: Record<string, string>;
    filters: Filters;
    locationOptions: {
        provinces: string[];
        municipalities: Array<{ name: string; province_name: string | null }>;
    };
};

const typeIcons: Record<string, typeof Car> = {
    car_rental: Car,
    van_rental: Car,
    self_drive: Car,
    chauffeur: Car,
    airport_transfer: Car,
    wedding_car: Car,
    shuttle: Bus,
    bus_rental: Bus,
    truck_rental: Truck,
    equipment_rental: Truck,
    motorcycle_rental: Car,
};

const driverModes = [
    { value: '', label: 'All modes' },
    { value: 'true', label: 'With Driver / Operator' },
    { value: 'false', label: 'Self-Drive / Renter-Operated' },
];

export default function RentalsIndex({
    rentals,
    rental_types,
    filters,
    locationOptions,
}: Props) {
    const [form, setForm] = useState<Filters>(filters);
    const [filtersOpen, setFiltersOpen] = useState(false);

    function updateField(field: keyof Filters, value: string) {
        setForm((current) => {
            if (field === 'province')
                return { ...current, province: value, municipality: '' };
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
        router.get('/rentals', cleaned(), {
            preserveScroll: true,
            preserveState: true,
        });
    }

    function clearAll() {
        const empty: Filters = {
            q: '',
            type: '',
            with_driver: '',
            province: '',
            municipality: '',
            max_price: '',
        };
        setForm(empty);
        router.get('/rentals', {}, { preserveScroll: true });
    }

    const availableMunicipalities = form.province
        ? locationOptions.municipalities
              .filter((m) => m.province_name === form.province)
              .map((m) => m.name)
        : [];

    const hasFilters = Object.values(filters).some((v) => v !== '');

    return (
        <>
            <Head title="Rental Marketplace" />
            <div className="mx-auto w-full max-w-7xl p-4 lg:p-6">
                {/* Header */}
                <div className="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">
                            Rental Marketplace
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Cars, vans, buses, trucks, farm &amp; heavy
                            equipment for rent — with or without driver
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
                    {/* Sidebar */}
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

                            {/* Keyword */}
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
                                        placeholder="e.g. bus, sedan, backhoe..."
                                        className="h-9 w-full rounded-md border bg-background pr-3 pl-8 text-sm outline-none focus:ring-1 focus:ring-emerald-600"
                                    />
                                </div>
                            </div>

                            {/* Type */}
                            <FilterSelect
                                label="Vehicle / Equipment Type"
                                value={form.type}
                                onChange={(v) => updateField('type', v)}
                                options={Object.entries(rental_types).map(
                                    ([k, v]) => ({ label: v, value: k }),
                                )}
                            />

                            {/* Driver mode */}
                            <div className="space-y-1.5">
                                <label className="text-xs font-medium text-muted-foreground">
                                    Driver / Operator
                                </label>
                                <div className="grid gap-1.5">
                                    {driverModes.map((mode) => (
                                        <button
                                            key={mode.value}
                                            type="button"
                                            onClick={() =>
                                                updateField(
                                                    'with_driver',
                                                    mode.value,
                                                )
                                            }
                                            className={`flex items-center gap-2 rounded-md border px-3 py-2 text-xs font-medium transition-colors ${
                                                form.with_driver === mode.value
                                                    ? 'border-emerald-600 bg-emerald-50 text-emerald-700'
                                                    : 'hover:bg-muted'
                                            }`}
                                        >
                                            {mode.value === 'true' && (
                                                <User className="size-3.5" />
                                            )}
                                            {mode.value === 'false' && (
                                                <UserX className="size-3.5" />
                                            )}
                                            {mode.value === '' && (
                                                <Filter className="size-3.5" />
                                            )}
                                            {mode.label}
                                        </button>
                                    ))}
                                </div>
                            </div>

                            {/* Location */}
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

                            {/* Max price */}
                            <div className="space-y-1">
                                <label className="text-xs font-medium text-muted-foreground">
                                    Max daily rate (PHP)
                                </label>
                                <input
                                    type="number"
                                    value={form.max_price}
                                    onChange={(e) =>
                                        updateField('max_price', e.target.value)
                                    }
                                    placeholder="e.g. 5000"
                                    min="0"
                                    className="h-9 w-full rounded-md border bg-background px-3 text-sm outline-none focus:ring-1 focus:ring-emerald-600"
                                />
                            </div>

                            <button
                                type="submit"
                                className="h-9 w-full rounded-md bg-emerald-600 text-sm font-semibold text-white hover:bg-emerald-700"
                            >
                                Search rentals
                            </button>
                        </form>
                    </aside>

                    {/* Main content */}
                    <main className="min-w-0 space-y-4">
                        {hasFilters && (
                            <ActiveChips
                                filters={filters}
                                rental_types={rental_types}
                            />
                        )}

                        <p className="text-sm text-muted-foreground">
                            {rentals.total.toLocaleString()} rental unit
                            {rentals.total !== 1 ? 's' : ''} available
                        </p>

                        {rentals.data.length === 0 ? (
                            <div className="rounded-lg border border-dashed py-16 text-center">
                                <Truck className="mx-auto size-10 text-muted-foreground/40" />
                                <p className="mt-3 text-sm font-medium text-muted-foreground">
                                    No rentals found
                                </p>
                                <p className="mt-1 text-xs text-muted-foreground">
                                    Try adjusting your filters
                                </p>
                            </div>
                        ) : (
                            <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                                {rentals.data.map((rental) => (
                                    <RentalCard
                                        key={rental.id}
                                        rental={rental}
                                    />
                                ))}
                            </div>
                        )}

                        {rentals.last_page > 1 && (
                            <div className="flex flex-wrap justify-center gap-1 pt-2">
                                {rentals.links.map((link, i) =>
                                    link.url ? (
                                        <Link
                                            key={i}
                                            href={link.url}
                                            className={`inline-flex h-9 min-w-9 items-center justify-center rounded-md px-3 text-sm font-medium ${link.active ? 'bg-emerald-600 text-white' : 'border hover:bg-accent'}`}
                                            dangerouslySetInnerHTML={{
                                                __html: link.label,
                                            }}
                                        />
                                    ) : (
                                        <span
                                            key={i}
                                            className="inline-flex h-9 min-w-9 items-center justify-center rounded-md px-3 text-sm text-muted-foreground"
                                            dangerouslySetInnerHTML={{
                                                __html: link.label,
                                            }}
                                        />
                                    ),
                                )}
                            </div>
                        )}
                    </main>
                </div>
            </div>
        </>
    );
}

function RentalCard({ rental }: { rental: RentalCard }) {
    const Icon = typeIcons[rental.rental_type] ?? Truck;

    return (
        <Link
            href={`/rentals/${rental.provider_username}/${rental.slug}`}
            className="group overflow-hidden rounded-lg border bg-card transition-shadow hover:shadow-md"
        >
            <div className="relative aspect-video overflow-hidden bg-muted">
                {rental.image_url ? (
                    <img
                        src={rental.image_url}
                        alt={rental.name}
                        className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                    />
                ) : (
                    <div className="flex h-full items-center justify-center text-muted-foreground/30">
                        <Icon className="size-12" />
                    </div>
                )}
                {/* Driver badge top-right */}
                <span
                    className={`absolute top-2 right-2 flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold shadow-sm ${
                        rental.with_driver
                            ? 'bg-emerald-600 text-white'
                            : 'bg-zinc-800 text-white'
                    }`}
                >
                    {rental.with_driver ? (
                        <>
                            <User className="size-3" /> With Driver
                        </>
                    ) : (
                        <>
                            <UserX className="size-3" /> Self-Drive
                        </>
                    )}
                </span>
            </div>

            <div className="space-y-2 p-4">
                <div className="flex items-center gap-2">
                    <span className="rounded-full bg-blue-100 px-2 py-0.5 text-[11px] font-medium text-blue-700">
                        {rental.rental_type_label}
                    </span>
                </div>

                <h3 className="line-clamp-2 min-h-10 text-sm leading-5 font-semibold">
                    {rental.name}
                </h3>

                {(rental.brand || rental.model) && (
                    <p className="text-xs text-muted-foreground">
                        {[rental.brand, rental.model, rental.year_model]
                            .filter(Boolean)
                            .join(' · ')}
                    </p>
                )}

                <div className="flex items-end justify-between gap-2">
                    <div>
                        <p className="text-lg font-semibold text-emerald-700">
                            PHP {Number(rental.price_per_day).toLocaleString()}
                            <span className="text-xs font-normal text-muted-foreground">
                                /day
                            </span>
                        </p>
                        {rental.price_per_hour && (
                            <p className="text-xs text-muted-foreground">
                                PHP{' '}
                                {Number(rental.price_per_hour).toLocaleString()}
                                /hr
                            </p>
                        )}
                    </div>
                    {(rental.municipality || rental.province) && (
                        <p className="flex items-center gap-1 text-xs text-muted-foreground">
                            <MapPin className="size-3 shrink-0" />
                            <span className="truncate">
                                {rental.municipality ?? rental.province}
                            </span>
                        </p>
                    )}
                </div>
            </div>
        </Link>
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
    onChange: (v: string) => void;
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

function ActiveChips({
    filters,
    rental_types,
}: {
    filters: Filters;
    rental_types: Record<string, string>;
}) {
    const chips: Array<{ key: keyof Filters; label: string }> = [];

    if (filters.q) chips.push({ key: 'q', label: `"${filters.q}"` });
    if (filters.type)
        chips.push({
            key: 'type',
            label: rental_types[filters.type] ?? filters.type,
        });
    if (filters.with_driver === 'true')
        chips.push({ key: 'with_driver', label: 'With Driver / Operator' });
    if (filters.with_driver === 'false')
        chips.push({ key: 'with_driver', label: 'Self-Drive' });
    if (filters.province)
        chips.push({ key: 'province', label: filters.province });
    if (filters.municipality)
        chips.push({ key: 'municipality', label: filters.municipality });
    if (filters.max_price)
        chips.push({
            key: 'max_price',
            label: `Max PHP ${Number(filters.max_price).toLocaleString()}/day`,
        });

    function remove(key: keyof Filters) {
        const next = { ...filters, [key]: '' };
        if (key === 'province') next.municipality = '';
        router.get(
            '/rentals',
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
