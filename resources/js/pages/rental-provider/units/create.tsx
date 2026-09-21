import { FormEvent } from 'react';
import { Head, useForm } from '@inertiajs/react';

const FARM_RENTAL_TYPES = [
    'harvester_rental',
    'harvester_service',
    'drone_rental',
    'drone_service',
];
const DRONE_RENTAL_TYPES = ['drone_rental', 'drone_service'];

export default function CreateRentalUnit({
    rental_types,
    farm_categories = [],
    verified_drone_pilots = [],
}: {
    rental_types: Record<string, string>;
    farm_categories?: { id: number; name: string }[];
    verified_drone_pilots?: { id: number; name: string }[];
}) {
    const currentYear = new Date().getFullYear();

    const { data, setData, post, processing, errors } = useForm({
        rental_type: '',
        name: '',
        description: '',
        brand: '',
        model: '',
        year_model: '',
        capacity: '',
        price_per_day: '',
        price_per_hour: '',
        region: '',
        province: '',
        municipality: '',
        barangay: '',
        images: [] as File[],
        attachments: [] as File[],
        valid_id_file: null as File | null,
        or_cr_file: null as File | null,
        category_id: '',
        price_per_hectare: '',
        operator_fee: '',
        transportation_fee: '',
        security_deposit: '',
        fuel_included: '' as '' | '1' | '0',
        operator_included: false,
        transportation_included: false,
        minimum_area_hectares: '',
        minimum_rental_duration: '',
        service_coverage_area: '',
        requires_verified_drone_operator: false,
        allows_self_operation: true,
        intended_uses: '',
        drone_pilot_user_id: '',
    });

    const isFarmType = FARM_RENTAL_TYPES.includes(data.rental_type);
    const isDroneType = DRONE_RENTAL_TYPES.includes(data.rental_type);

    function submit(e: FormEvent<HTMLFormElement>) {
        e.preventDefault();
        post('/rental-provider/units', { forceFormData: true });
    }

    return (
        <>
            <Head title="Add Rental Unit" />
            <div className="mx-auto w-full max-w-2xl p-4">
                <div className="mb-6">
                    <h1 className="text-2xl font-semibold tracking-normal">
                        Add Rental Unit
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Your unit will be reviewed before going live.
                    </p>
                </div>

                <form onSubmit={submit} className="grid gap-6">
                    <section className="grid gap-4 rounded-lg border p-5">
                        <h2 className="font-medium">Unit Information</h2>

                        <Field
                            label="Rental Type"
                            required
                            error={errors.rental_type}
                        >
                            <select
                                value={data.rental_type}
                                onChange={(e) =>
                                    setData('rental_type', e.target.value)
                                }
                                required
                                className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                            >
                                <option value="">Select type</option>
                                {Object.entries(rental_types).map(([k, v]) => (
                                    <option key={k} value={k}>
                                        {v}
                                    </option>
                                ))}
                            </select>
                        </Field>

                        <Field label="Unit Name" required error={errors.name}>
                            <input
                                type="text"
                                value={data.name}
                                onChange={(e) =>
                                    setData('name', e.target.value)
                                }
                                required
                                className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                placeholder="e.g. Toyota HiAce Van"
                            />
                        </Field>

                        <Field label="Description" error={errors.description}>
                            <textarea
                                value={data.description}
                                onChange={(e) =>
                                    setData('description', e.target.value)
                                }
                                className="min-h-24 w-full rounded-md border bg-background px-3 py-2 text-sm"
                                placeholder="Describe your unit, features, and conditions..."
                            />
                        </Field>

                        <div className="grid gap-4 sm:grid-cols-3">
                            <Field label="Brand" error={errors.brand}>
                                <input
                                    type="text"
                                    value={data.brand}
                                    onChange={(e) =>
                                        setData('brand', e.target.value)
                                    }
                                    className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                    placeholder="e.g. Toyota"
                                />
                            </Field>
                            <Field label="Model" error={errors.model}>
                                <input
                                    type="text"
                                    value={data.model}
                                    onChange={(e) =>
                                        setData('model', e.target.value)
                                    }
                                    className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                    placeholder="e.g. HiAce"
                                />
                            </Field>
                            <Field label="Year" error={errors.year_model}>
                                <input
                                    type="number"
                                    value={data.year_model}
                                    onChange={(e) =>
                                        setData('year_model', e.target.value)
                                    }
                                    min="1950"
                                    max={currentYear + 1}
                                    className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                    placeholder={String(currentYear)}
                                />
                            </Field>
                        </div>

                        <Field
                            label="Passenger Capacity"
                            error={errors.capacity}
                        >
                            <input
                                type="number"
                                value={data.capacity}
                                onChange={(e) =>
                                    setData('capacity', e.target.value)
                                }
                                min="1"
                                className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                placeholder="e.g. 12"
                            />
                        </Field>
                    </section>

                    <section className="grid gap-4 rounded-lg border p-5">
                        <h2 className="font-medium">Pricing</h2>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <Field
                                label="Price Per Day (PHP)"
                                required
                                error={errors.price_per_day}
                            >
                                <input
                                    type="number"
                                    value={data.price_per_day}
                                    onChange={(e) =>
                                        setData('price_per_day', e.target.value)
                                    }
                                    required
                                    min="0"
                                    step="0.01"
                                    className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                    placeholder="0.00"
                                />
                            </Field>
                            <Field
                                label="Price Per Hour (PHP)"
                                error={errors.price_per_hour}
                            >
                                <input
                                    type="number"
                                    value={data.price_per_hour}
                                    onChange={(e) =>
                                        setData(
                                            'price_per_hour',
                                            e.target.value,
                                        )
                                    }
                                    min="0"
                                    step="0.01"
                                    className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                    placeholder="Optional"
                                />
                            </Field>
                        </div>
                    </section>

                    {isFarmType && (
                        <section className="grid gap-4 rounded-lg border p-5">
                            <h2 className="font-medium">
                                Farm Equipment Details
                            </h2>

                            <Field
                                label="Category"
                                required
                                error={errors.category_id}
                            >
                                <select
                                    value={data.category_id}
                                    onChange={(e) =>
                                        setData('category_id', e.target.value)
                                    }
                                    required
                                    className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                >
                                    <option value="">Select category</option>
                                    {farm_categories.map((c) => (
                                        <option key={c.id} value={c.id}>
                                            {c.name}
                                        </option>
                                    ))}
                                </select>
                            </Field>

                            <div className="grid gap-4 sm:grid-cols-2">
                                <Field
                                    label="Rate per Hectare (PHP)"
                                    error={errors.price_per_hectare}
                                >
                                    <input
                                        type="number"
                                        value={data.price_per_hectare}
                                        onChange={(e) =>
                                            setData(
                                                'price_per_hectare',
                                                e.target.value,
                                            )
                                        }
                                        min="0"
                                        step="0.01"
                                        className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                        placeholder="Optional"
                                    />
                                </Field>
                                <Field
                                    label="Minimum Area (hectares)"
                                    error={errors.minimum_area_hectares}
                                >
                                    <input
                                        type="number"
                                        value={data.minimum_area_hectares}
                                        onChange={(e) =>
                                            setData(
                                                'minimum_area_hectares',
                                                e.target.value,
                                            )
                                        }
                                        min="0"
                                        step="0.01"
                                        className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                        placeholder="Optional"
                                    />
                                </Field>
                                <Field
                                    label="Operator Fee (PHP)"
                                    error={errors.operator_fee}
                                >
                                    <input
                                        type="number"
                                        value={data.operator_fee}
                                        onChange={(e) =>
                                            setData(
                                                'operator_fee',
                                                e.target.value,
                                            )
                                        }
                                        min="0"
                                        step="0.01"
                                        className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                        placeholder="Optional"
                                    />
                                </Field>
                                <Field
                                    label="Transportation Fee (PHP)"
                                    error={errors.transportation_fee}
                                >
                                    <input
                                        type="number"
                                        value={data.transportation_fee}
                                        onChange={(e) =>
                                            setData(
                                                'transportation_fee',
                                                e.target.value,
                                            )
                                        }
                                        min="0"
                                        step="0.01"
                                        className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                        placeholder="Optional"
                                    />
                                </Field>
                                <Field
                                    label="Security Deposit (PHP)"
                                    error={errors.security_deposit}
                                >
                                    <input
                                        type="number"
                                        value={data.security_deposit}
                                        onChange={(e) =>
                                            setData(
                                                'security_deposit',
                                                e.target.value,
                                            )
                                        }
                                        min="0"
                                        step="0.01"
                                        className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                        placeholder="Optional"
                                    />
                                </Field>
                                <Field
                                    label="Fuel/Consumables"
                                    error={errors.fuel_included}
                                >
                                    <select
                                        value={data.fuel_included}
                                        onChange={(e) =>
                                            setData(
                                                'fuel_included',
                                                e.target
                                                    .value as typeof data.fuel_included,
                                            )
                                        }
                                        className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                    >
                                        <option value="">Unspecified</option>
                                        <option value="1">Included</option>
                                        <option value="0">Excluded</option>
                                    </select>
                                </Field>
                            </div>

                            <div className="grid gap-3 sm:grid-cols-2">
                                <label className="flex items-center gap-2 text-sm">
                                    <input
                                        type="checkbox"
                                        checked={data.operator_included}
                                        onChange={(e) =>
                                            setData(
                                                'operator_included',
                                                e.target.checked,
                                            )
                                        }
                                    />
                                    Operator included in base rate
                                </label>
                                <label className="flex items-center gap-2 text-sm">
                                    <input
                                        type="checkbox"
                                        checked={
                                            data.transportation_included
                                        }
                                        onChange={(e) =>
                                            setData(
                                                'transportation_included',
                                                e.target.checked,
                                            )
                                        }
                                    />
                                    Transportation included in base rate
                                </label>
                            </div>

                            <Field
                                label="Minimum Rental Duration"
                                error={errors.minimum_rental_duration}
                            >
                                <input
                                    type="text"
                                    value={data.minimum_rental_duration}
                                    onChange={(e) =>
                                        setData(
                                            'minimum_rental_duration',
                                            e.target.value,
                                        )
                                    }
                                    className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                    placeholder="e.g. 1 day, 2 hectares"
                                />
                            </Field>

                            <Field
                                label="Service Coverage Area"
                                error={errors.service_coverage_area}
                            >
                                <textarea
                                    value={data.service_coverage_area}
                                    onChange={(e) =>
                                        setData(
                                            'service_coverage_area',
                                            e.target.value,
                                        )
                                    }
                                    className="min-h-20 w-full rounded-md border bg-background px-3 py-2 text-sm"
                                    placeholder="Municipalities or provinces served"
                                />
                            </Field>

                            <Field
                                label="Intended Uses"
                                error={errors.intended_uses}
                            >
                                <textarea
                                    value={data.intended_uses}
                                    onChange={(e) =>
                                        setData(
                                            'intended_uses',
                                            e.target.value,
                                        )
                                    }
                                    className="min-h-20 w-full rounded-md border bg-background px-3 py-2 text-sm"
                                    placeholder="e.g. Crop spraying, fertilizer application, field mapping"
                                />
                            </Field>

                            {isDroneType && (
                                <div className="grid gap-3 rounded-md border border-dashed p-4">
                                    <p className="text-sm font-medium">
                                        Drone Operator Compliance
                                    </p>
                                    <label className="flex items-center gap-2 text-sm">
                                        <input
                                            type="checkbox"
                                            checked={
                                                data.requires_verified_drone_operator
                                            }
                                            onChange={(e) =>
                                                setData(
                                                    'requires_verified_drone_operator',
                                                    e.target.checked,
                                                )
                                            }
                                        />
                                        Require a verified pilot credential
                                        for this listing
                                    </label>
                                    <label className="flex items-center gap-2 text-sm">
                                        <input
                                            type="checkbox"
                                            checked={
                                                data.allows_self_operation
                                            }
                                            onChange={(e) =>
                                                setData(
                                                    'allows_self_operation',
                                                    e.target.checked,
                                                )
                                            }
                                        />
                                        Allow renter self-operation (subject
                                        to their own verified credential when
                                        required)
                                    </label>
                                    <Field
                                        label="Assigned Verified Pilot"
                                        error={errors.drone_pilot_user_id}
                                    >
                                        <select
                                            value={data.drone_pilot_user_id}
                                            onChange={(e) =>
                                                setData(
                                                    'drone_pilot_user_id',
                                                    e.target.value,
                                                )
                                            }
                                            className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                        >
                                            <option value="">
                                                None assigned yet
                                            </option>
                                            {verified_drone_pilots.map(
                                                (p) => (
                                                    <option
                                                        key={p.id}
                                                        value={p.id}
                                                    >
                                                        {p.name}
                                                    </option>
                                                ),
                                            )}
                                        </select>
                                    </Field>
                                    <p className="text-xs text-muted-foreground">
                                        PrimeUnits does not issue pilot
                                        licenses and does not guarantee
                                        government approval. Credential
                                        review only confirms marketplace
                                        eligibility.
                                    </p>
                                </div>
                            )}
                        </section>
                    )}

                    <section className="grid gap-4 rounded-lg border p-5">
                        <h2 className="font-medium">Location</h2>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <Field label="Region" error={errors.region}>
                                <input
                                    type="text"
                                    value={data.region}
                                    onChange={(e) =>
                                        setData('region', e.target.value)
                                    }
                                    className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                />
                            </Field>
                            <Field label="Province" error={errors.province}>
                                <input
                                    type="text"
                                    value={data.province}
                                    onChange={(e) =>
                                        setData('province', e.target.value)
                                    }
                                    className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                />
                            </Field>
                            <Field
                                label="Municipality/City"
                                error={errors.municipality}
                            >
                                <input
                                    type="text"
                                    value={data.municipality}
                                    onChange={(e) =>
                                        setData('municipality', e.target.value)
                                    }
                                    className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                />
                            </Field>
                            <Field label="Barangay" error={errors.barangay}>
                                <input
                                    type="text"
                                    value={data.barangay}
                                    onChange={(e) =>
                                        setData('barangay', e.target.value)
                                    }
                                    className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                />
                            </Field>
                        </div>
                    </section>

                    <section className="grid gap-4 rounded-lg border p-5">
                        <h2 className="font-medium">Photos and Documents</h2>
                        <p className="text-sm text-muted-foreground">Unapproved rental owners must submit a valid government ID and the unit's OR/CR.</p>
                        <Field label="Valid government ID" error={errors.valid_id_file}>
                            <input type="file" accept="image/jpeg,image/png,application/pdf" onChange={(e) => setData('valid_id_file', e.target.files?.[0] ?? null)} className="text-sm" />
                        </Field>
                        <Field label="Valid OR/CR" error={errors.or_cr_file}>
                            <input type="file" accept="image/jpeg,image/png,application/pdf" onChange={(e) => setData('or_cr_file', e.target.files?.[0] ?? null)} className="text-sm" />
                        </Field>
                        <p className="text-sm text-muted-foreground">
                            Upload up to 10 photos. First photo will be used as
                            the cover image.
                        </p>
                        <input
                            type="file"
                            multiple
                            accept="image/jpg,image/jpeg,image/png"
                            onChange={(e) =>
                                setData(
                                    'images',
                                    Array.from(e.target.files ?? []),
                                )
                            }
                            className="text-sm file:mr-3 file:cursor-pointer file:rounded-md file:border file:px-3 file:py-1.5 file:text-sm file:font-medium"
                        />
                        {errors.images && (
                            <p className="text-xs text-destructive">
                                {errors.images}
                            </p>
                        )}
                        <div className="grid gap-2">
                            <span className="text-sm font-medium">
                                PDF attachments
                            </span>
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

                    <div className="flex gap-3">
                        <button
                            type="submit"
                            disabled={processing}
                            className="h-10 rounded-md bg-primary px-6 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50"
                        >
                            {processing ? 'Submitting...' : 'Submit Unit'}
                        </button>
                        <a
                            href="/rental-provider/units"
                            className="inline-flex h-10 items-center rounded-md border px-6 text-sm font-medium hover:bg-muted"
                        >
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </>
    );
}

function Field({
    label,
    required,
    error,
    children,
}: {
    label: string;
    required?: boolean;
    error?: string;
    children: React.ReactNode;
}) {
    return (
        <label className="grid gap-2 text-sm">
            <span className="font-medium">
                {label}
                {required && <span className="ml-1 text-destructive">*</span>}
            </span>
            {children}
            {error && <span className="text-xs text-destructive">{error}</span>}
        </label>
    );
}

CreateRentalUnit.layout = {
    breadcrumbs: [
        { title: 'My Rental Units', href: '/rental-provider/units' },
        { title: 'Add Unit', href: '/rental-provider/units/create' },
    ],
};
