import { FormEvent, useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';

type Brand = {
    id: number;
    name: string;
    logo: string | null;
    category_group: string;
    sort_order: number;
    is_active: boolean;
};

type Group = {
    value: string;
    label: string;
};

export default function AdminBrands({
    brands,
    groups,
}: {
    brands: Brand[];
    groups: Group[];
}) {
    const [editing, setEditing] = useState<Brand | null>(null);
    const { data, setData, post, put, processing, errors, reset } = useForm({
        name: '',
        logo: '',
        category_group: groups[0]?.value ?? 'vehicle',
        sort_order: '0',
        is_active: true,
    });

    function load(brand: Brand) {
        setEditing(brand);
        setData({
            name: brand.name,
            logo: brand.logo ?? '',
            category_group: brand.category_group,
            sort_order: brand.sort_order.toString(),
            is_active: brand.is_active,
        });
    }

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        const options = {
            preserveScroll: true,
            onSuccess: () => {
                setEditing(null);
                reset();
            },
        };

        if (editing) {
            put(`/adm/brands/${editing.id}`, options);
            return;
        }

        post('/adm/brands', options);
    }

    const labelFor = (value: string) =>
        groups.find((group) => group.value === value)?.label ?? value;

    return (
        <>
            <Head title="Brands" />
            <div className="grid gap-6 p-4 lg:grid-cols-[380px_1fr]">
                <form
                    onSubmit={submit}
                    className="grid h-fit gap-4 rounded-lg border p-5"
                >
                    <h1 className="text-xl font-semibold">
                        {editing ? 'Edit brand' : 'Create brand'}
                    </h1>
                    <Field label="Name" error={errors.name}>
                        <input
                            value={data.name}
                            onChange={(event) =>
                                setData('name', event.target.value)
                            }
                            className="h-10 rounded-md border bg-background px-3"
                        />
                    </Field>
                    <Field label="Group" error={errors.category_group}>
                        <select
                            value={data.category_group}
                            onChange={(event) =>
                                setData('category_group', event.target.value)
                            }
                            className="h-10 rounded-md border bg-background px-3"
                        >
                            {groups.map((group) => (
                                <option key={group.value} value={group.value}>
                                    {group.label}
                                </option>
                            ))}
                        </select>
                    </Field>
                    <Field label="Logo path" error={errors.logo}>
                        <input
                            value={data.logo}
                            onChange={(event) =>
                                setData('logo', event.target.value)
                            }
                            className="h-10 rounded-md border bg-background px-3"
                        />
                    </Field>
                    <Field label="Sort order" error={errors.sort_order}>
                        <input
                            type="number"
                            value={data.sort_order}
                            onChange={(event) =>
                                setData('sort_order', event.target.value)
                            }
                            className="h-10 rounded-md border bg-background px-3"
                        />
                    </Field>
                    <label className="flex items-center gap-2 text-sm">
                        <input
                            type="checkbox"
                            checked={data.is_active}
                            onChange={(event) =>
                                setData('is_active', event.target.checked)
                            }
                        />
                        Active
                    </label>
                    <button
                        disabled={processing}
                        className="h-10 rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground disabled:opacity-50"
                    >
                        {editing ? 'Update brand' : 'Create brand'}
                    </button>
                </form>

                <div className="grid gap-3">
                    {brands.map((brand) => (
                        <article
                            key={brand.id}
                            className="rounded-lg border p-4"
                        >
                            <div className="flex items-start justify-between gap-3">
                                <div className="flex gap-3">
                                    {brand.logo && (
                                        <img
                                            src={brand.logo}
                                            alt=""
                                            className="h-10 w-16 rounded border object-contain p-1"
                                        />
                                    )}
                                    <div>
                                        <h2 className="font-medium">
                                            {brand.name}
                                        </h2>
                                        <p className="mt-1 text-sm text-muted-foreground">
                                            {labelFor(brand.category_group)} ·
                                            Sort {brand.sort_order}
                                        </p>
                                    </div>
                                </div>
                                <Badge
                                    variant={
                                        brand.is_active
                                            ? 'secondary'
                                            : 'outline'
                                    }
                                >
                                    {brand.is_active ? 'active' : 'inactive'}
                                </Badge>
                            </div>
                            <button
                                type="button"
                                onClick={() => load(brand)}
                                className="mt-3 rounded-md border px-3 py-1.5 text-sm hover:bg-accent"
                            >
                                Edit
                            </button>
                        </article>
                    ))}
                </div>
            </div>
        </>
    );
}

function Field({
    label,
    error,
    children,
}: {
    label: string;
    error?: string;
    children: React.ReactNode;
}) {
    return (
        <label className="grid gap-2 text-sm">
            <span className="font-medium">{label}</span>
            {children}
            {error && <span className="text-xs text-destructive">{error}</span>}
        </label>
    );
}

AdminBrands.layout = {
    breadcrumbs: [{ title: 'Brands', href: '/adm/brands' }],
};
