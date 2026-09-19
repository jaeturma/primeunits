import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { useEffect, useState } from 'react';

type Category = {
    id: number;
    name: string;
    slug: string;
};

type Condition = {
    value: string;
    label: string;
};

type SpecField = {
    id: number;
    name: string;
    label: string;
    type: 'text' | 'number' | 'select';
    options: string[];
    required: boolean;
};

type Brand = {
    id: number;
    name: string;
    category_group: string;
};

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

type ListingFormData = {
    title: string;
    category_id: string;
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
    specs: Record<string, string>;
    images: File[];
    attachments: File[];
};

export default function CreateListing({
    categories,
    categoryBrandGroups,
    conditions,
    brands,
    locationOptions,
    managementPath,
}: {
    categories: Category[];
    categoryBrandGroups: Record<string, string[]>;
    conditions: Condition[];
    brands: Brand[];
    locationOptions: LocationOptions;
    managementPath: string;
}) {
    const [specFields, setSpecFields] = useState<SpecField[]>([]);
    const [previews, setPreviews] = useState<string[]>([]);
    const { data, setData, post, processing, errors } =
        useForm<ListingFormData>({
            title: '',
            category_id: categories[0]?.id.toString() ?? '',
            description: '',
            price: '',
            negotiable: false,
            condition: conditions[0]?.value ?? 'used',
            year_model: '',
            brand: '',
            model: '',
            region: '',
            province: '',
            municipality: '',
            barangay: '',
            specs: {},
            images: [],
            attachments: [],
        });

    useEffect(() => {
        if (!data.category_id) {
            setSpecFields([]);

            return;
        }

        setData('specs', {});

        fetch(`/categories/${data.category_id}/spec-fields`)
            .then((response) => response.json())
            .then((payload: { fields: SpecField[] }) =>
                setSpecFields(payload.fields),
            );
    }, [data.category_id]);

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        post(managementPath, { forceFormData: true });
    }

    function updateCategory(value: string) {
        setData((current) => ({
            ...current,
            category_id: value,
            brand: '',
        }));
    }

    function setSpec(field: SpecField, value: string) {
        setData('specs', {
            ...data.specs,
            [field.id]: value,
        });
    }

    function setImages(files: FileList | null) {
        const selected = Array.from(files ?? []).slice(0, 10);

        setData('images', selected);
        setPreviews(
            selected.map((file) =>
                file.type.startsWith('image/') ? URL.createObjectURL(file) : '',
            ),
        );
    }

    function moveImage(from: number, to: number) {
        const nextImages = [...data.images];
        const nextPreviews = [...previews];
        const [image] = nextImages.splice(from, 1);
        const [preview] = nextPreviews.splice(from, 1);

        nextImages.splice(to, 0, image);
        nextPreviews.splice(to, 0, preview);
        setData('images', nextImages);
        setPreviews(nextPreviews);
    }

    function updateRegion(value: string) {
        setData((current) => ({
            ...current,
            region: value,
            province: '',
            municipality: '',
        }));
    }

    function updateProvince(value: string) {
        setData((current) => ({
            ...current,
            province: value,
            municipality: '',
        }));
    }

    const filteredProvinces = locationOptions.provinces.filter(
        (province) => !data.region || province.region_name === data.region,
    );
    const filteredMunicipalities = locationOptions.municipalities.filter(
        (municipality) =>
            (!data.region || municipality.region_name === data.region) &&
            (!data.province || municipality.province_name === data.province),
    );
    const selectedCategory = categories.find(
        (category) => category.id.toString() === data.category_id,
    );
    const allowedBrandGroups = selectedCategory
        ? (categoryBrandGroups[selectedCategory.slug] ?? [])
        : [];
    const categoryBrands = brands.filter((brand) =>
        allowedBrandGroups.includes(brand.category_group),
    );

    return (
        <>
            <Head title="Create Listing" />

            <div className="mx-auto flex w-full max-w-5xl flex-col gap-6 p-4">
                <div>
                    <h1 className="text-2xl font-semibold tracking-normal">
                        Create Listing
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Listings go live after admin approval.
                    </p>
                </div>

                <form onSubmit={submit} className="space-y-6">
                    <section className="rounded-lg border p-5">
                        <h2 className="font-medium">Basic Info</h2>
                        <div className="mt-4 grid gap-4 md:grid-cols-2">
                            <TextField
                                label="Title"
                                value={data.title}
                                error={errors.title}
                                required
                                onChange={(value) => setData('title', value)}
                            />
                            <Field label="Category" error={errors.category_id}>
                                <select
                                    value={data.category_id}
                                    onChange={(event) =>
                                        updateCategory(event.target.value)
                                    }
                                    className="h-10 rounded-md border bg-background px-3 text-sm"
                                >
                                    {categories.map((category) => (
                                        <option
                                            key={category.id}
                                            value={category.id}
                                        >
                                            {category.name}
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
                                onChange={(value) => setData('price', value)}
                            />
                            <label className="flex items-center gap-2 self-end text-sm">
                                <input
                                    type="checkbox"
                                    checked={data.negotiable}
                                    onChange={(event) =>
                                        setData(
                                            'negotiable',
                                            event.target.checked,
                                        )
                                    }
                                />
                                Negotiable
                            </label>
                            <TextAreaField
                                label="Description"
                                value={data.description}
                                error={errors.description}
                                onChange={(value) =>
                                    setData('description', value)
                                }
                            />
                        </div>
                    </section>

                    <section className="rounded-lg border p-5">
                        <h2 className="font-medium">General Info</h2>
                        <div className="mt-4 grid gap-4 md:grid-cols-2">
                            <Field label="Condition" error={errors.condition}>
                                <select
                                    value={data.condition}
                                    onChange={(event) =>
                                        setData('condition', event.target.value)
                                    }
                                    className="h-10 rounded-md border bg-background px-3 text-sm"
                                >
                                    {conditions.map((condition) => (
                                        <option
                                            key={condition.value}
                                            value={condition.value}
                                        >
                                            {condition.label}
                                        </option>
                                    ))}
                                </select>
                            </Field>
                            <TextField
                                label="Year model"
                                value={data.year_model}
                                error={errors.year_model}
                                type="number"
                                onChange={(value) =>
                                    setData('year_model', value)
                                }
                            />
                            <Field label="Brand" error={errors.brand}>
                                <select
                                    value={data.brand}
                                    onChange={(event) =>
                                        setData('brand', event.target.value)
                                    }
                                    className="h-10 rounded-md border bg-background px-3 text-sm"
                                >
                                    <option value="">Select brand</option>
                                    {categoryBrands.map((brand) => (
                                        <option
                                            key={brand.id}
                                            value={brand.name}
                                        >
                                            {brand.name}
                                        </option>
                                    ))}
                                </select>
                            </Field>
                            <TextField
                                label="Model"
                                value={data.model}
                                error={errors.model}
                                onChange={(value) => setData('model', value)}
                            />
                        </div>
                    </section>

                    <section className="rounded-lg border p-5">
                        <h2 className="font-medium">Location</h2>
                        <div className="mt-4 grid gap-4 md:grid-cols-2">
                            <Field label="Region" error={errors.region}>
                                <select
                                    value={data.region}
                                    onChange={(event) =>
                                        updateRegion(event.target.value)
                                    }
                                    className="h-10 rounded-md border bg-background px-3 text-sm"
                                >
                                    <option value="">Select region</option>
                                    {locationOptions.regions.map((region) => (
                                        <option
                                            key={region.id}
                                            value={region.name}
                                        >
                                            {region.name}
                                        </option>
                                    ))}
                                </select>
                            </Field>
                            <Field label="Province" error={errors.province}>
                                <select
                                    value={data.province}
                                    onChange={(event) =>
                                        updateProvince(event.target.value)
                                    }
                                    className="h-10 rounded-md border bg-background px-3 text-sm"
                                >
                                    <option value="">Select province</option>
                                    {filteredProvinces.map((province) => (
                                        <option
                                            key={province.id}
                                            value={province.name}
                                        >
                                            {province.name}
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
                                    onChange={(event) =>
                                        setData(
                                            'municipality',
                                            event.target.value,
                                        )
                                    }
                                    className="h-10 rounded-md border bg-background px-3 text-sm"
                                >
                                    <option value="">Select town/city</option>
                                    {filteredMunicipalities.map(
                                        (municipality) => (
                                            <option
                                                key={municipality.id}
                                                value={municipality.name}
                                            >
                                                {municipality.name}
                                            </option>
                                        ),
                                    )}
                                </select>
                            </Field>
                            <TextField
                                label="Barangay"
                                value={data.barangay}
                                error={errors.barangay}
                                onChange={(value) => setData('barangay', value)}
                            />
                        </div>
                    </section>

                    <section className="rounded-lg border p-5">
                        <h2 className="font-medium">Specifications</h2>
                        <div className="mt-4 grid gap-4 md:grid-cols-2">
                            {specFields.map((field) => (
                                <SpecInput
                                    key={field.id}
                                    field={field}
                                    value={data.specs[field.id] ?? ''}
                                    error={errors[`specs.${field.id}`]}
                                    onChange={(value) => setSpec(field, value)}
                                />
                            ))}
                        </div>
                    </section>

                    <section className="rounded-lg border p-5">
                        <h2 className="font-medium">Images and Documents</h2>
                        <input
                            type="file"
                            multiple
                            accept="image/jpeg,image/png"
                            onChange={(event) => setImages(event.target.files)}
                            className="mt-4 w-full rounded-md border bg-background px-3 py-2 text-sm"
                        />
                        {errors.images && (
                            <p className="mt-2 text-xs text-destructive">
                                {errors.images}
                            </p>
                        )}
                        <div className="mt-4 flex flex-wrap gap-3">
                            {previews.map(
                                (preview, index) =>
                                    preview && (
                                        <div
                                            key={preview}
                                            draggable
                                            onDragStart={(event) =>
                                                event.dataTransfer.setData(
                                                    'text/plain',
                                                    index.toString(),
                                                )
                                            }
                                            onDragOver={(event) =>
                                                event.preventDefault()
                                            }
                                            onDrop={(event) => {
                                                event.preventDefault();
                                                moveImage(
                                                    Number(
                                                        event.dataTransfer.getData(
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
                                                    ? 'Cover'
                                                    : index + 1}
                                            </span>
                                        </div>
                                    ),
                            )}
                        </div>
                        <div className="mt-5 grid gap-2 text-sm">
                            <span className="font-medium">PDF attachments</span>
                            <input
                                type="file"
                                multiple
                                accept="application/pdf,.pdf"
                                onChange={(event) =>
                                    setData(
                                        'attachments',
                                        Array.from(event.target.files ?? []),
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

                    <button
                        type="submit"
                        disabled={processing}
                        className="inline-flex h-10 items-center rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50"
                    >
                        {processing ? 'Submitting...' : 'Submit listing'}
                    </button>
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
    onChange: (value: string) => void;
}) {
    if (field.type === 'select') {
        return (
            <Field label={field.label} error={error}>
                <select
                    value={value}
                    required={field.required}
                    onChange={(event) => onChange(event.target.value)}
                    className="h-10 rounded-md border bg-background px-3 text-sm"
                >
                    <option value="">Select</option>
                    {field.options.map((option) => (
                        <option key={option} value={option}>
                            {option}
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
    onChange: (value: string) => void;
}) {
    return (
        <Field label={label} error={error}>
            <input
                type={type}
                value={value}
                required={required}
                onChange={(event) => onChange(event.target.value)}
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
    onChange: (value: string) => void;
}) {
    return (
        <Field label={label} error={error}>
            <textarea
                value={value}
                onChange={(event) => onChange(event.target.value)}
                className="min-h-24 rounded-md border bg-background px-3 py-2 text-sm md:col-span-2"
            />
        </Field>
    );
}

CreateListing.layout = {
    breadcrumbs: [
        {
            title: 'Create Listing',
            href: '/seller/listings/create',
        },
    ],
};
