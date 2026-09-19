import { FormEvent, useState } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';

type Region = {
    id: number;
    code: string;
    name: string;
    is_active: boolean;
};

type Province = Region & {
    region_id: number;
    region?: { id: number; name: string };
};

type Municipality = Region & {
    region_id: number;
    province_id: number | null;
    type: string;
    zip_code: string | null;
    district: string | null;
    region?: { id: number; name: string };
    province?: { id: number; name: string };
};

type Filters = {
    q: string;
    region_id: string;
    province_id: string;
};

export default function AdminLocations({
    filters,
    regions,
    provinces,
    municipalities,
    municipality_count,
}: {
    filters: Filters;
    regions: Region[];
    provinces: Province[];
    municipalities: Municipality[];
    municipality_count: number;
}) {
    return (
        <>
            <Head title="Locations" />
            <div className="grid gap-6 p-4 xl:grid-cols-[380px_1fr]">
                <div className="grid h-fit gap-6">
                    <RegionForm regions={regions} />
                    <ProvinceForm regions={regions} provinces={provinces} />
                    <MunicipalityForm
                        regions={regions}
                        provinces={provinces}
                        municipalities={municipalities}
                    />
                </div>

                <div className="grid gap-6">
                    <section className="rounded-lg border p-5">
                        <div className="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h1 className="text-xl font-semibold">
                                    Philippine locations
                                </h1>
                                <p className="mt-1 text-sm text-muted-foreground">
                                    {regions.length} regions, {provinces.length}{' '}
                                    provinces,{' '}
                                    {municipality_count.toLocaleString()} towns
                                    and cities.
                                </p>
                            </div>
                            <Badge variant="secondary">PSGC seeded</Badge>
                        </div>

                        <form
                            onSubmit={filterLocations}
                            className="mt-4 grid gap-3 md:grid-cols-[1fr_180px_180px_auto]"
                        >
                            <input
                                name="q"
                                defaultValue={filters.q}
                                placeholder="Search town, city, or code"
                                className="h-10 rounded-md border bg-background px-3 text-sm"
                            />
                            <select
                                name="region_id"
                                defaultValue={filters.region_id}
                                className="h-10 rounded-md border bg-background px-3 text-sm"
                            >
                                <option value="">All regions</option>
                                {regions.map((region) => (
                                    <option key={region.id} value={region.id}>
                                        {region.name}
                                    </option>
                                ))}
                            </select>
                            <select
                                name="province_id"
                                defaultValue={filters.province_id}
                                className="h-10 rounded-md border bg-background px-3 text-sm"
                            >
                                <option value="">All provinces</option>
                                {provinces.map((province) => (
                                    <option
                                        key={province.id}
                                        value={province.id}
                                    >
                                        {province.name}
                                    </option>
                                ))}
                            </select>
                            <button className="h-10 rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground">
                                Filter
                            </button>
                        </form>
                    </section>

                    <section className="grid gap-3">
                        {municipalities.map((municipality) => (
                            <article
                                key={municipality.id}
                                className="rounded-lg border p-4"
                            >
                                <div className="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <h2 className="font-medium">
                                            {municipality.name}
                                        </h2>
                                        <p className="mt-1 text-sm text-muted-foreground">
                                            {municipality.code} ·{' '}
                                            {municipality.type} ·{' '}
                                            {municipality.province?.name ??
                                                'Independent'}{' '}
                                            · {municipality.region?.name}
                                        </p>
                                    </div>
                                    <Badge
                                        variant={
                                            municipality.is_active
                                                ? 'secondary'
                                                : 'outline'
                                        }
                                    >
                                        {municipality.is_active
                                            ? 'active'
                                            : 'inactive'}
                                    </Badge>
                                </div>
                            </article>
                        ))}
                    </section>
                </div>
            </div>
        </>
    );
}

function filterLocations(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const form = new FormData(event.currentTarget);

    router.get(
        '/adm/locations',
        {
            q: form.get('q')?.toString() ?? '',
            region_id: form.get('region_id')?.toString() ?? '',
            province_id: form.get('province_id')?.toString() ?? '',
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
}

function RegionForm({ regions }: { regions: Region[] }) {
    const [editing, setEditing] = useState<Region | null>(null);
    const form = useForm({
        code: '',
        name: '',
        is_active: true,
    });

    function load(region: Region) {
        setEditing(region);
        form.setData({
            code: region.code,
            name: region.name,
            is_active: region.is_active,
        });
    }

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        const options = {
            preserveScroll: true,
            onSuccess: () => {
                setEditing(null);
                form.reset();
            },
        };

        if (editing) {
            form.put(`/adm/locations/regions/${editing.id}`, options);
            return;
        }

        form.post('/adm/locations/regions', options);
    }

    return (
        <form onSubmit={submit} className="grid gap-3 rounded-lg border p-5">
            <h2 className="font-semibold">
                {editing ? 'Edit region' : 'Add region'}
            </h2>
            <TextInput
                label="Code"
                value={form.data.code}
                onChange={(value) => form.setData('code', value)}
                error={form.errors.code}
            />
            <TextInput
                label="Name"
                value={form.data.name}
                onChange={(value) => form.setData('name', value)}
                error={form.errors.name}
            />
            <ActiveToggle
                checked={form.data.is_active}
                onChange={(checked) => form.setData('is_active', checked)}
            />
            <button
                disabled={form.processing}
                className="h-10 rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground disabled:opacity-50"
            >
                {editing ? 'Update region' : 'Create region'}
            </button>
            <select
                onChange={(event) =>
                    load(
                        regions.find(
                            (region) =>
                                region.id === Number(event.target.value),
                        )!,
                    )
                }
                className="h-10 rounded-md border bg-background px-3 text-sm"
                defaultValue=""
            >
                <option value="" disabled>
                    Edit existing region
                </option>
                {regions.map((region) => (
                    <option key={region.id} value={region.id}>
                        {region.name}
                    </option>
                ))}
            </select>
        </form>
    );
}

function ProvinceForm({
    regions,
    provinces,
}: {
    regions: Region[];
    provinces: Province[];
}) {
    const [editing, setEditing] = useState<Province | null>(null);
    const form = useForm({
        region_id: regions[0]?.id.toString() ?? '',
        code: '',
        name: '',
        is_active: true,
    });

    function load(province: Province) {
        setEditing(province);
        form.setData({
            region_id: province.region_id.toString(),
            code: province.code,
            name: province.name,
            is_active: province.is_active,
        });
    }

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        const options = {
            preserveScroll: true,
            onSuccess: () => {
                setEditing(null);
                form.reset();
            },
        };

        if (editing) {
            form.put(`/adm/locations/provinces/${editing.id}`, options);
            return;
        }

        form.post('/adm/locations/provinces', options);
    }

    return (
        <form onSubmit={submit} className="grid gap-3 rounded-lg border p-5">
            <h2 className="font-semibold">
                {editing ? 'Edit province' : 'Add province'}
            </h2>
            <SelectInput
                label="Region"
                value={form.data.region_id}
                onChange={(value) => form.setData('region_id', value)}
                error={form.errors.region_id}
            >
                {regions.map((region) => (
                    <option key={region.id} value={region.id}>
                        {region.name}
                    </option>
                ))}
            </SelectInput>
            <TextInput
                label="Code"
                value={form.data.code}
                onChange={(value) => form.setData('code', value)}
                error={form.errors.code}
            />
            <TextInput
                label="Name"
                value={form.data.name}
                onChange={(value) => form.setData('name', value)}
                error={form.errors.name}
            />
            <ActiveToggle
                checked={form.data.is_active}
                onChange={(checked) => form.setData('is_active', checked)}
            />
            <button
                disabled={form.processing}
                className="h-10 rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground disabled:opacity-50"
            >
                {editing ? 'Update province' : 'Create province'}
            </button>
            <select
                onChange={(event) =>
                    load(
                        provinces.find(
                            (province) =>
                                province.id === Number(event.target.value),
                        )!,
                    )
                }
                className="h-10 rounded-md border bg-background px-3 text-sm"
                defaultValue=""
            >
                <option value="" disabled>
                    Edit existing province
                </option>
                {provinces.map((province) => (
                    <option key={province.id} value={province.id}>
                        {province.name}
                    </option>
                ))}
            </select>
        </form>
    );
}

function MunicipalityForm({
    regions,
    provinces,
    municipalities,
}: {
    regions: Region[];
    provinces: Province[];
    municipalities: Municipality[];
}) {
    const [editing, setEditing] = useState<Municipality | null>(null);
    const form = useForm({
        region_id: regions[0]?.id.toString() ?? '',
        province_id: '',
        code: '',
        name: '',
        type: 'Mun',
        zip_code: '',
        district: '',
        is_active: true,
    });

    function load(municipality: Municipality) {
        setEditing(municipality);
        form.setData({
            region_id: municipality.region_id.toString(),
            province_id: municipality.province_id?.toString() ?? '',
            code: municipality.code,
            name: municipality.name,
            type: municipality.type,
            zip_code: municipality.zip_code ?? '',
            district: municipality.district ?? '',
            is_active: municipality.is_active,
        });
    }

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        const options = {
            preserveScroll: true,
            onSuccess: () => {
                setEditing(null);
                form.reset();
            },
        };

        if (editing) {
            form.put(`/adm/locations/municipalities/${editing.id}`, options);
            return;
        }

        form.post('/adm/locations/municipalities', options);
    }

    return (
        <form onSubmit={submit} className="grid gap-3 rounded-lg border p-5">
            <h2 className="font-semibold">
                {editing ? 'Edit town/city' : 'Add town/city'}
            </h2>
            <SelectInput
                label="Region"
                value={form.data.region_id}
                onChange={(value) => form.setData('region_id', value)}
                error={form.errors.region_id}
            >
                {regions.map((region) => (
                    <option key={region.id} value={region.id}>
                        {region.name}
                    </option>
                ))}
            </SelectInput>
            <SelectInput
                label="Province"
                value={form.data.province_id}
                onChange={(value) => form.setData('province_id', value)}
                error={form.errors.province_id}
            >
                <option value="">Independent / NCR</option>
                {provinces.map((province) => (
                    <option key={province.id} value={province.id}>
                        {province.name}
                    </option>
                ))}
            </SelectInput>
            <TextInput
                label="Code"
                value={form.data.code}
                onChange={(value) => form.setData('code', value)}
                error={form.errors.code}
            />
            <TextInput
                label="Name"
                value={form.data.name}
                onChange={(value) => form.setData('name', value)}
                error={form.errors.name}
            />
            <SelectInput
                label="Type"
                value={form.data.type}
                onChange={(value) => form.setData('type', value)}
                error={form.errors.type}
            >
                <option value="Mun">Municipality</option>
                <option value="City">City</option>
                <option value="SGU">Special geographic unit</option>
            </SelectInput>
            <TextInput
                label="ZIP code"
                value={form.data.zip_code}
                onChange={(value) => form.setData('zip_code', value)}
                error={form.errors.zip_code}
            />
            <TextInput
                label="District"
                value={form.data.district}
                onChange={(value) => form.setData('district', value)}
                error={form.errors.district}
            />
            <ActiveToggle
                checked={form.data.is_active}
                onChange={(checked) => form.setData('is_active', checked)}
            />
            <button
                disabled={form.processing}
                className="h-10 rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground disabled:opacity-50"
            >
                {editing ? 'Update town/city' : 'Create town/city'}
            </button>
            <select
                onChange={(event) =>
                    load(
                        municipalities.find(
                            (municipality) =>
                                municipality.id === Number(event.target.value),
                        )!,
                    )
                }
                className="h-10 rounded-md border bg-background px-3 text-sm"
                defaultValue=""
            >
                <option value="" disabled>
                    Edit visible town/city
                </option>
                {municipalities.map((municipality) => (
                    <option key={municipality.id} value={municipality.id}>
                        {municipality.name}
                    </option>
                ))}
            </select>
        </form>
    );
}

function TextInput({
    label,
    value,
    onChange,
    error,
}: {
    label: string;
    value: string;
    onChange: (value: string) => void;
    error?: string;
}) {
    return (
        <label className="grid gap-2 text-sm">
            <span className="font-medium">{label}</span>
            <input
                value={value}
                onChange={(event) => onChange(event.target.value)}
                className="h-10 rounded-md border bg-background px-3"
            />
            {error && <span className="text-xs text-destructive">{error}</span>}
        </label>
    );
}

function SelectInput({
    label,
    value,
    onChange,
    error,
    children,
}: {
    label: string;
    value: string;
    onChange: (value: string) => void;
    error?: string;
    children: React.ReactNode;
}) {
    return (
        <label className="grid gap-2 text-sm">
            <span className="font-medium">{label}</span>
            <select
                value={value}
                onChange={(event) => onChange(event.target.value)}
                className="h-10 rounded-md border bg-background px-3"
            >
                {children}
            </select>
            {error && <span className="text-xs text-destructive">{error}</span>}
        </label>
    );
}

function ActiveToggle({
    checked,
    onChange,
}: {
    checked: boolean;
    onChange: (checked: boolean) => void;
}) {
    return (
        <label className="flex items-center gap-2 text-sm">
            <input
                type="checkbox"
                checked={checked}
                onChange={(event) => onChange(event.target.checked)}
            />
            Active
        </label>
    );
}

AdminLocations.layout = {
    breadcrumbs: [{ title: 'Locations', href: '/adm/locations' }],
};
