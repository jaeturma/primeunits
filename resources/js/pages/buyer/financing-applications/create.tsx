import { FormEvent, useState } from 'react';
import { Head, useForm } from '@inertiajs/react';

type Product = {
    id: number;
    name: string;
    type_label: string;
    min_amount: string;
    max_amount: string;
    interest_rate_min: string;
    interest_rate_max: string;
    min_term_months: number;
    max_term_months: number;
};

type Partner = {
    id: number;
    company_name: string;
    products: Product[];
};

export default function CreateFinancingApplication({
    partners,
    employment_types,
    listing,
    user,
}: {
    partners: Partner[];
    employment_types: Record<string, string>;
    listing: { id: number; title: string; price: string } | null;
    user: { name: string };
}) {
    const [selectedPartnerId, setSelectedPartnerId] = useState('');
    const selectedPartner = partners.find(
        (p) => p.id === Number(selectedPartnerId),
    );

    const { data, setData, post, processing, errors } = useForm({
        financing_partner_id: '',
        financing_product_id: '',
        listing_id: listing ? String(listing.id) : '',
        full_name: user.name,
        contact_number: '',
        employment_type: '',
        monthly_income: '',
        unit_price: listing ? listing.price : '',
        requested_amount: '',
        down_payment: '',
        preferred_term_months: '36',
        notes: '',
        attachments: [] as File[],
    });

    function handlePartnerChange(value: string) {
        setSelectedPartnerId(value);
        setData((prev) => ({
            ...prev,
            financing_partner_id: value,
            financing_product_id: '',
        }));
    }

    function submit(e: FormEvent<HTMLFormElement>) {
        e.preventDefault();
        post('/buyer/financing-applications', { forceFormData: true });
    }

    const product = selectedPartner?.products.find(
        (p) => p.id === Number(data.financing_product_id),
    );

    return (
        <>
            <Head title="Apply for Financing" />
            <div className="mx-auto w-full max-w-2xl p-4">
                <div className="mb-6">
                    <h1 className="text-2xl font-semibold tracking-normal">
                        Apply for Financing
                    </h1>
                    {listing && (
                        <p className="mt-1 text-sm text-muted-foreground">
                            For: {listing.title}
                        </p>
                    )}
                </div>

                <form onSubmit={submit} className="grid gap-6">
                    <section className="grid gap-4 rounded-lg border p-5">
                        <h2 className="font-medium">
                            Select Financing Partner
                        </h2>

                        <Field
                            label="Financing Partner"
                            required
                            error={errors.financing_partner_id}
                        >
                            <select
                                value={selectedPartnerId}
                                onChange={(e) =>
                                    handlePartnerChange(e.target.value)
                                }
                                required
                                className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                            >
                                <option value="">Select partner</option>
                                {partners.map((p) => (
                                    <option key={p.id} value={p.id}>
                                        {p.company_name}
                                    </option>
                                ))}
                            </select>
                        </Field>

                        {selectedPartner && (
                            <Field
                                label="Financing Product"
                                required
                                error={errors.financing_product_id}
                            >
                                <select
                                    value={data.financing_product_id}
                                    onChange={(e) =>
                                        setData(
                                            'financing_product_id',
                                            e.target.value,
                                        )
                                    }
                                    required
                                    className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                >
                                    <option value="">Select product</option>
                                    {selectedPartner.products.map((p) => (
                                        <option key={p.id} value={p.id}>
                                            {p.name} ({p.type_label})
                                        </option>
                                    ))}
                                </select>
                            </Field>
                        )}

                        {product && (
                            <div className="grid grid-cols-3 gap-2 rounded-md bg-muted/50 p-3 text-xs">
                                <div>
                                    <span className="text-muted-foreground">
                                        Amount:{' '}
                                    </span>
                                    PHP{' '}
                                    {Number(
                                        product.min_amount,
                                    ).toLocaleString()}{' '}
                                    –{' '}
                                    {Number(
                                        product.max_amount,
                                    ).toLocaleString()}
                                </div>
                                <div>
                                    <span className="text-muted-foreground">
                                        Rate:{' '}
                                    </span>
                                    {product.interest_rate_min}% –{' '}
                                    {product.interest_rate_max}%
                                </div>
                                <div>
                                    <span className="text-muted-foreground">
                                        Term:{' '}
                                    </span>
                                    {product.min_term_months} –{' '}
                                    {product.max_term_months} months
                                </div>
                            </div>
                        )}
                    </section>

                    <section className="grid gap-4 rounded-lg border p-5">
                        <h2 className="font-medium">Personal Information</h2>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <Field
                                label="Full Name"
                                required
                                error={errors.full_name}
                            >
                                <input
                                    type="text"
                                    value={data.full_name}
                                    onChange={(e) =>
                                        setData('full_name', e.target.value)
                                    }
                                    required
                                    className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                />
                            </Field>
                            <Field
                                label="Contact Number"
                                required
                                error={errors.contact_number}
                            >
                                <input
                                    type="tel"
                                    value={data.contact_number}
                                    onChange={(e) =>
                                        setData(
                                            'contact_number',
                                            e.target.value,
                                        )
                                    }
                                    required
                                    className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                    placeholder="+63 9XX XXX XXXX"
                                />
                            </Field>
                        </div>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <Field
                                label="Employment Type"
                                required
                                error={errors.employment_type}
                            >
                                <select
                                    value={data.employment_type}
                                    onChange={(e) =>
                                        setData(
                                            'employment_type',
                                            e.target.value,
                                        )
                                    }
                                    required
                                    className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                >
                                    <option value="">Select type</option>
                                    {Object.entries(employment_types).map(
                                        ([k, v]) => (
                                            <option key={k} value={k}>
                                                {v}
                                            </option>
                                        ),
                                    )}
                                </select>
                            </Field>
                            <Field
                                label="Monthly Income (PHP)"
                                error={errors.monthly_income}
                            >
                                <input
                                    type="number"
                                    value={data.monthly_income}
                                    onChange={(e) =>
                                        setData(
                                            'monthly_income',
                                            e.target.value,
                                        )
                                    }
                                    min="0"
                                    step="0.01"
                                    className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                    placeholder="0.00"
                                />
                            </Field>
                        </div>
                    </section>

                    <section className="grid gap-4 rounded-lg border p-5">
                        <h2 className="font-medium">Loan Details</h2>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <Field
                                label="Unit Price (PHP)"
                                required
                                error={errors.unit_price}
                            >
                                <input
                                    type="number"
                                    value={data.unit_price}
                                    onChange={(e) =>
                                        setData('unit_price', e.target.value)
                                    }
                                    required
                                    min="1"
                                    step="0.01"
                                    className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                />
                            </Field>
                            <Field
                                label="Down Payment (PHP)"
                                error={errors.down_payment}
                            >
                                <input
                                    type="number"
                                    value={data.down_payment}
                                    onChange={(e) =>
                                        setData('down_payment', e.target.value)
                                    }
                                    min="0"
                                    step="0.01"
                                    className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                    placeholder="Optional"
                                />
                            </Field>
                        </div>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <Field
                                label="Requested Loan Amount (PHP)"
                                required
                                error={errors.requested_amount}
                            >
                                <input
                                    type="number"
                                    value={data.requested_amount}
                                    onChange={(e) =>
                                        setData(
                                            'requested_amount',
                                            e.target.value,
                                        )
                                    }
                                    required
                                    min="1"
                                    step="0.01"
                                    className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                />
                            </Field>
                            <Field
                                label="Preferred Term (months)"
                                required
                                error={errors.preferred_term_months}
                            >
                                <input
                                    type="number"
                                    value={data.preferred_term_months}
                                    onChange={(e) =>
                                        setData(
                                            'preferred_term_months',
                                            e.target.value,
                                        )
                                    }
                                    required
                                    min={product?.min_term_months ?? 1}
                                    max={product?.max_term_months ?? 360}
                                    className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                />
                            </Field>
                        </div>
                        <Field
                            label="Notes / Special Requests"
                            error={errors.notes}
                        >
                            <textarea
                                value={data.notes}
                                onChange={(e) =>
                                    setData('notes', e.target.value)
                                }
                                className="min-h-20 w-full rounded-md border bg-background px-3 py-2 text-sm"
                                placeholder="Any additional information..."
                            />
                        </Field>
                        <Field
                            label="PDF Attachments"
                            error={errors.attachments as string}
                        >
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
                            <span className="text-xs text-muted-foreground">
                                Delivery receipts, transfer receipts, and
                                supporting PDFs. Max 5MB per file.
                            </span>
                        </Field>
                    </section>

                    <div className="flex gap-3">
                        <button
                            type="submit"
                            disabled={processing}
                            className="h-10 rounded-md bg-primary px-6 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50"
                        >
                            {processing
                                ? 'Submitting...'
                                : 'Submit Application'}
                        </button>
                        <a
                            href="/buyer/financing-applications"
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

CreateFinancingApplication.layout = {
    breadcrumbs: [
        {
            title: 'Financing Applications',
            href: '/buyer/financing-applications',
        },
        { title: 'Apply', href: '/buyer/financing-applications/apply' },
    ],
};
