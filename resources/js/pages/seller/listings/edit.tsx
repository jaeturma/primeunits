import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { useEffect, useState } from 'react';

type Category = { id: number; name: string; slug: string };
type Condition = { value: string; label: string };
type SpecField = {
    id: number;
    name: string;
    label: string;
    type: 'text' | 'number' | 'select';
    options: string[];
    required: boolean;
};
type Brand = { id: number; name: string; category_group: string };
type LocationOptions = {
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
type ExistingImage = {
    id: number;
    url: string;
    is_primary: boolean;
    sort_order: number;
};
type Attachment = { id: number; name: string; url: string; size: number };
type ListingData = {
    id: number;
    title: string;
    category_id: number;
    description: string;
    price: string;
    negotiable: boolean;
    condition: string;
    year_model: string;
    brand: string;
    model: string;
    region: string;
    province: string;
    municipality: string;
    barangay: string;
    listing_type: string;
    specs: Record<string, string>;
    images: ExistingImage[];
    attachments: Attachment[];
};

export default function EditListing({
    listing,
    categories,
    categoryBrandGroups,
    conditions,
    brands,
    locationOptions,
    managementPath,
}: {
    listing: ListingData;
    categories: Category[];
    categoryBrandGroups: Record<string, string[]>;
    conditions: Condition[];
    brands: Brand[];
    locationOptions: LocationOptions;
    managementPath: string;
}) {
    const [specFields, setSpecFields] = useState<SpecField[]>([]);
    const [previews, setPreviews] = useState<string[]>([]);

    const { data, setData, post, processing, errors } = useForm({
        _method: 'PUT',
        title: listing.title,
        category_id: listing.category_id.toString(),
        description: listing.description,
        price: listing.price,
        negotiable: listing.negotiable,
        condition: listing.condition,
        year_model: listing.year_model,
        brand: listing.brand,
        model: listing.model,
        region: listing.region,
        province: listing.province,
        municipality: listing.municipality,
        barangay: listing.barangay,
        specs: listing.specs,
        images: [] as File[],
        attachments: [] as File[],
    });

    useEffect(() => {
        if (!data.category_id) {
            setSpecFields([]);

            return;
        }

        fetch(`/categories/${data.category_id}/spec-fields`)
            .then((r) => r.json())
            .then((payload: { fields: SpecField[] }) =>
                setSpecFields(payload.fields),
            );
    }, [data.category_id]);

    function submit(e: FormEvent<HTMLFormElement>) {
        e.preventDefault();
        post(`${managementPath}/${listing.id}`, { forceFormData: true });
    }

    function updateCategory(value: string) {
        setData((current) => ({ ...current, category_id: value, brand: '' }));
    }

    function setSpec(field: SpecField, value: string) {
        setData('specs', { ...data.specs, [field.id]: value });
    }

    function setImages(files: FileList | null) {
        const selected = Array.from(files ?? []).slice(0, 10);
        setData('images', selected);
        setPreviews(
            selected.map((f) =>
                f.type.startsWith('image/') ? URL.createObjectURL(f) : '',
            ),
        );
    }

    function moveImage(from: number, to: number) {
        const imgs = [...data.images];
        const prevs = [...previews];
        const [img] = imgs.splice(from, 1);
        const [prev] = prevs.splice(from, 1);
        imgs.splice(to, 0, img);
        prevs.splice(to, 0, prev);
        setData('images', imgs);
        setPreviews(prevs);
    }

    function updateRegion(value: string) {
        setData((c) => ({
            ...c,
            region: value,
            province: '',
            municipality: '',
        }));
    }

    function updateProvince(value: string) {
        setData((c) => ({ ...c, province: value, municipality: '' }));
    }

    const filteredProvinces = locationOptions.provinces.filter(
        (p) => !data.region || p.region_name === data.region,
    );
    const filteredMunicipalities = locationOptions.municipalities.filter(
        (m) =>
            (!data.region || m.region_name === data.region) &&
            (!data.province || m.province_name === data.province),
    );
    const selectedCategory = categories.find(
        (c) => c.id.toString() === data.category_id,
    );
    const allowedBrandGroups = selectedCategory
        ? (categoryBrandGroups[selectedCategory.slug] ?? [])
        : [];
    const categoryBrands = brands.filter((b) =>
        allowedBrandGroups.includes(b.category_group),
    );

    return (
        <>
            <Head title={`Edit: ${listing.title}`} />
            <div className="mx-auto flex w-full max-w-5xl flex-col gap-6 p-4">
                <div>
                    <h1 className="text-2xl font-semibold tracking-normal">
                        Edit Listing
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Editing will reset your listing to pending status for
                        re-approval.
                    </p>
                </div>

                {listing.images.length > 0 && (
                    <section className="rounded-lg border p-5">
                        <h2 className="font-medium">Current Images</h2>
                        <div className="mt-4 flex flex-wrap gap-3">
                            {listing.images.map((img, i) => (
                                <div
                                    key={img.id}
                                    className="relative rounded-md border bg-background p-1"
                                >
                                    <img
                                        src={img.url}
                                        alt=""
                                        className="h-24 w-24 rounded-md object-cover"
                                    />
                                    {img.is_primary && (
                                        <span className="absolute top-2 left-2 rounded bg-black/70 px-2 py-0.5 text-[11px] font-medium text-white">
                                            Cover
                                        </span>
                                    )}
                                </div>
                            ))}
                        </div>
                    </section>
                )}

                <form onSubmit={submit} className="space-y-6">
                    <section className="rounded-lg border p-5">
                        <h2 className="font-medium">Basic Info</h2>
                        <div className="mt-4 grid gap-4 md:grid-cols-2">
                            <TextField
                                label="Title"
                                value={data.title}
                                error={errors.title}
                                required
                                onChange={(v) => setData('title', v)}
                            />
                            <Field label="Category" error={errors.category_id}>
                                <select
                                    value={data.category_id}
                                    onChange={(e) =>
                                        updateCategory(e.target.value)
                                    }
                                    className="h-10 rounded-md border bg-background px-3 text-sm"
                                >
                                    {categories.map((c) => (
                                        <option key={c.id} value={c.id}>
                                            {c.name}
                                        </option>
                                    ))}
                                </select>
                            </Field>
                            <TextField
                                label="Price"
                                value={data.price}
                                error={errors.price}
                                type="number"
                                required
                                onChange={(v) => setData('price', v)}
                            />
                            <label className="flex items-center gap-2 self-end text-sm">
                                <input
                                    type="checkbox"
                                    checked={data.negotiable}
                                    onChange={(e) =>
                                        setData('negotiable', e.target.checked)
                                    }
                                />
                                Negotiable
                            </label>
                            <TextAreaField
                                label="Description"
                                value={data.description}
                                error={errors.description}
                                onChange={(v) => setData('description', v)}
                            />
                        </div>
                    </section>

                    <section className="rounded-lg border p-5">
                        <h2 className="font-medium">General Info</h2>
                        <div className="mt-4 grid gap-4 md:grid-cols-2">
                            <Field label="Condition" error={errors.condition}>
                                <select
                                    value={data.condition}
                                    onChange={(e) =>
                                        setData('condition', e.target.value)
                                    }
                                    className="h-10 rounded-md border bg-background px-3 text-sm"
                                >
                                    {conditions.map((c) => (
                                        <option key={c.value} value={c.value}>
                                            {c.label}
                                        </option>
                                    ))}
                                </select>
                            </Field>
                            <TextField
                                label="Year Model"
                                value={data.year_model}
                                error={errors.year_model}
                                type="number"
                                onChange={(v) => setData('year_model', v)}
                            />
                            <Field label="Brand" error={errors.brand}>
                                <select
                                    value={data.brand}
                                    onChange={(e) =>
                                        setData('brand', e.target.value)
                                    }
                                    className="h-10 rounded-md border bg-background px-3 text-sm"
                                >
                                    <option value="">Select brand</option>
                                    {categoryBrands.map((b) => (
                                        <option key={b.id} value={b.name}>
                                            {b.name}
                                        </option>
                                    ))}
                                </select>
                            </Field>
                            <TextField
                                label="Model"
                                value={data.model}
                                error={errors.model}
                                onChange={(v) => setData('model', v)}
                            />
                        </div>
                    </section>

                    <section className="rounded-lg border p-5">
                        <h2 className="font-medium">Location</h2>
                        <div className="mt-4 grid gap-4 md:grid-cols-2">
                            <Field label="Region" error={errors.region}>
                                <select
                                    value={data.region}
                                    onChange={(e) =>
                                        updateRegion(e.target.value)
                                    }
                                    className="h-10 rounded-md border bg-background px-3 text-sm"
                                >
                                    <option value="">Select region</option>
                                    {locationOptions.regions.map((r) => (
                                        <option key={r.id} value={r.name}>
                                            {r.name}
                                        </option>
                                    ))}
                                </select>
                            </Field>
                            <Field label="Province" error={errors.province}>
                                <select
                                    value={data.province}
                                    onChange={(e) =>
                                        updateProvince(e.target.value)
                                    }
                                    className="h-10 rounded-md border bg-background px-3 text-sm"
                                >
                                    <option value="">Select province</option>
                                    {filteredProvinces.map((p) => (
                                        <option key={p.id} value={p.name}>
                                            {p.name}
                                        </option>
                                    ))}
                                </select>
                            </Field>
                            <Field
                                label="Town/City"
                                error={errors.municipality}
                            >
                                <select
                                    value={data.municipality}
                                    onChange={(e) =>
                                        setData('municipality', e.target.value)
                                    }
                                    className="h-10 rounded-md border bg-background px-3 text-sm"
                                >
                                    <option value="">Select town/city</option>
                                    {filteredMunicipalities.map((m) => (
                                        <option key={m.id} value={m.name}>
                                            {m.name}
                                        </option>
                                    ))}
                                </select>
                            </Field>
                            <TextField
                                label="Barangay"
                                value={data.barangay}
                                error={errors.barangay}
                                onChange={(v) => setData('barangay', v)}
                            />
                        </div>
                    </section>

                    {specFields.length > 0 && (
                        <section className="rounded-lg border p-5">
                            <h2 className="font-medium">Specifications</h2>
                            <div className="mt-4 grid gap-4 md:grid-cols-2">
                                {specFields.map((field) => (
                                    <SpecInput
                                        key={field.id}
                                        field={field}
                                        value={data.specs[field.id] ?? ''}
                                        error={
                                            errors[
                                                `specs.${field.id}` as keyof typeof errors
                                            ]
                                        }
                                        onChange={(v) => setSpec(field, v)}
                                    />
                                ))}
                            </div>
                        </section>
                    )}

                    <section className="rounded-lg border p-5">
                        <h2 className="font-medium">
                            Add Images and Documents
                        </h2>
                        <p className="mt-1 text-sm text-muted-foreground">
                            New images and PDFs will be added to existing ones.
                        </p>
                        <input
                            type="file"
                            multiple
                            accept="image/jpeg,image/png"
                            onChange={(e) => setImages(e.target.files)}
                            className="mt-4 w-full rounded-md border bg-background px-3 py-2 text-sm"
                        />
                        {errors.images && (
                            <p className="mt-2 text-xs text-destructive">
                                {errors.images}
                            </p>
                        )}
                        <div className="mt-4 flex flex-wrap gap-3">
                            {previews.map((preview, index) =>
                                preview ? (
                                    <div
                                        key={preview}
                                        draggable
                                        onDragStart={(e) =>
                                            e.dataTransfer.setData(
                                                'text/plain',
                                                index.toString(),
                                            )
                                        }
                                        onDragOver={(e) => e.preventDefault()}
                                        onDrop={(e) => {
                                            e.preventDefault();
                                            moveImage(
                                                Number(
                                                    e.dataTransfer.getData(
                                                        'text/plain',
                                                    ),
                                                ),
                                                index,
                                            );
                                        }}
                                        className="relative cursor-grab rounded-md border bg-background p-1"
                                    >
                                        <img
                                            src={preview}
                                            alt=""
                                            className="h-24 w-24 rounded-md object-cover"
                                        />
                                        <span className="absolute top-2 left-2 rounded bg-black/70 px-2 py-0.5 text-[11px] font-medium text-white">
                                            {index === 0
                                                ? 'New Cover'
                                                : index + 1}
                                        </span>
                                    </div>
                                ) : null,
                            )}
                        </div>
                        {listing.attachments.length > 0 && (
                            <div className="mt-5 grid gap-2">
                                <p className="text-sm font-medium">
                                    Current PDF attachments
                                </p>
                                {listing.attachments.map((attachment) => (
                                    <a
                                        key={attachment.id}
                                        href={attachment.url}
                                        target="_blank"
                                        rel="noreferrer"
                                        className="truncate rounded-md border px-3 py-2 text-sm hover:bg-muted"
                                    >
                                        {attachment.name}
                                    </a>
                                ))}
                            </div>
                        )}
                        <div className="mt-5 grid gap-2 text-sm">
                            <span className="font-medium">PDF attachments</span>
                            <input
                                type="file"
                                multiple
                                accept="application/pdf,.pdf"
                                onChange={(e) =>
                                    setData(
                                        'attachments',
                                        Array.from(e.target.files ?? []),
                                    )
                                }
                                className="w-full rounded-md border bg-background px-3 py-2 text-sm"
                            />
                            <p className="text-xs text-muted-foreground">
                                Delivery receipts, property transfer receipts,
                                and other PDFs. Max 5MB per file.
                            </p>
                            {errors.attachments && (
                                <p className="text-xs text-destructive">
                                    {errors.attachments}
                                </p>
                            )}
                        </div>
                    </section>

                    <div className="flex items-center gap-3">
                        <button
                            type="submit"
                            disabled={processing}
                            className="inline-flex h-10 items-center rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50"
                        >
                            {processing ? 'Saving...' : 'Save Changes'}
                        </button>
                        <a
                            href={managementPath}
                            className="inline-flex h-10 items-center rounded-md border px-4 text-sm font-medium hover:bg-muted"
                        >
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </>
    );
}

function SpecInput({
    field,
    value,
    error,
    onChange,
}: {
    field: SpecField;
    value: string;
    error?: string;
    onChange: (v: string) => void;
}) {
    if (field.type === 'select') {
        return (
            <Field label={field.label} error={error}>
                <select
                    value={value}
                    required={field.required}
                    onChange={(e) => onChange(e.target.value)}
                    className="h-10 rounded-md border bg-background px-3 text-sm"
                >
                    <option value="">Select</option>
                    {field.options.map((o) => (
                        <option key={o} value={o}>
                            {o}
                        </option>
                    ))}
                </select>
            </Field>
        );
    }

    return (
        <TextField
            label={field.label}
            value={value}
            error={error}
            type={field.type === 'number' ? 'number' : 'text'}
            required={field.required}
            onChange={onChange}
        />
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

function TextField({
    label,
    value,
    error,
    type = 'text',
    required = false,
    onChange,
}: {
    label: string;
    value: string;
    error?: string;
    type?: string;
    required?: boolean;
    onChange: (v: string) => void;
}) {
    return (
        <Field label={label} error={error}>
            <input
                type={type}
                value={value}
                required={required}
                onChange={(e) => onChange(e.target.value)}
                className="h-10 rounded-md border bg-background px-3 text-sm"
            />
        </Field>
    );
}

function TextAreaField({
    label,
    value,
    error,
    onChange,
}: {
    label: string;
    value: string;
    error?: string;
    onChange: (v: string) => void;
}) {
    return (
        <Field label={label} error={error}>
            <textarea
                value={value}
                onChange={(e) => onChange(e.target.value)}
                className="min-h-24 rounded-md border bg-background px-3 py-2 text-sm md:col-span-2"
            />
        </Field>
    );
}

EditListing.layout = {
    breadcrumbs: [
        { title: 'My Listings', href: '/seller/listings' },
        { title: 'Edit Listing', href: '#' },
    ],
};
