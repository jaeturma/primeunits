import { FormEvent, useState } from 'react';
import { Head, useForm } from '@inertiajs/react';

type Product = {
    id: number;
    name: string;
    product_type: string;
    type_label: string;
    description: string | null;
    min_amount: string;
    max_amount: string;
    interest_rate_min: string;
    interest_rate_max: string;
    min_term_months: number;
    max_term_months: number;
    is_active: boolean;
};

const typeColors: Record<string, string> = {
    term_loan: 'bg-blue-100 text-blue-700',
    installment: 'bg-green-100 text-green-700',
    lease: 'bg-purple-100 text-purple-700',
    chattel_mortgage: 'bg-orange-100 text-orange-700',
};

export default function FinancingPartnerProducts({
    products,
    product_types,
}: {
    products: Product[];
    product_types: Record<string, string>;
}) {
    const [showForm, setShowForm] = useState(false);
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        product_type: '',
        description: '',
        min_amount: '',
        max_amount: '',
        interest_rate_min: '',
        interest_rate_max: '',
        min_term_months: '6',
        max_term_months: '60',
    });

    function submit(e: FormEvent<HTMLFormElement>) {
        e.preventDefault();
        post('/financing-partner/products', {
            onSuccess: () => {
                setShowForm(false);
                reset();
            },
        });
    }

    return (
        <>
            <Head title="Financing Products" />
            <div className="mx-auto w-full max-w-4xl p-4">
                <div className="mb-6 flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-normal">
                            Financing Products
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            {products.length} product
                            {products.length !== 1 ? 's' : ''}
                        </p>
                    </div>
                    <button
                        onClick={() => setShowForm(!showForm)}
                        className="h-10 rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                    >
                        {showForm ? 'Cancel' : '+ Add Product'}
                    </button>
                </div>

                {showForm && (
                    <form
                        onSubmit={submit}
                        className="mb-6 grid gap-4 rounded-lg border bg-muted/30 p-5"
                    >
                        <h2 className="font-medium">New Financing Product</h2>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <Field
                                label="Product Name"
                                required
                                error={errors.name}
                            >
                                <input
                                    type="text"
                                    value={data.name}
                                    onChange={(e) =>
                                        setData('name', e.target.value)
                                    }
                                    required
                                    className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                />
                            </Field>
                            <Field
                                label="Product Type"
                                required
                                error={errors.product_type}
                            >
                                <select
                                    value={data.product_type}
                                    onChange={(e) =>
                                        setData('product_type', e.target.value)
                                    }
                                    required
                                    className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                >
                                    <option value="">Select type</option>
                                    {Object.entries(product_types).map(
                                        ([k, v]) => (
                                            <option key={k} value={k}>
                                                {v}
                                            </option>
                                        ),
                                    )}
                                </select>
                            </Field>
                        </div>
                        <Field label="Description" error={errors.description}>
                            <textarea
                                value={data.description}
                                onChange={(e) =>
                                    setData('description', e.target.value)
                                }
                                className="min-h-16 w-full rounded-md border bg-background px-3 py-2 text-sm"
                            />
                        </Field>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <Field
                                label="Min Amount (PHP)"
                                required
                                error={errors.min_amount}
                            >
                                <input
                                    type="number"
                                    value={data.min_amount}
                                    onChange={(e) =>
                                        setData('min_amount', e.target.value)
                                    }
                                    required
                                    min="0"
                                    step="0.01"
                                    className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                />
                            </Field>
                            <Field
                                label="Max Amount (PHP)"
                                required
                                error={errors.max_amount}
                            >
                                <input
                                    type="number"
                                    value={data.max_amount}
                                    onChange={(e) =>
                                        setData('max_amount', e.target.value)
                                    }
                                    required
                                    min="0"
                                    step="0.01"
                                    className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                />
                            </Field>
                            <Field
                                label="Interest Rate Min (% p.a.)"
                                required
                                error={errors.interest_rate_min}
                            >
                                <input
                                    type="number"
                                    value={data.interest_rate_min}
                                    onChange={(e) =>
                                        setData(
                                            'interest_rate_min',
                                            e.target.value,
                                        )
                                    }
                                    required
                                    min="0"
                                    max="100"
                                    step="0.01"
                                    className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                />
                            </Field>
                            <Field
                                label="Interest Rate Max (% p.a.)"
                                required
                                error={errors.interest_rate_max}
                            >
                                <input
                                    type="number"
                                    value={data.interest_rate_max}
                                    onChange={(e) =>
                                        setData(
                                            'interest_rate_max',
                                            e.target.value,
                                        )
                                    }
                                    required
                                    min="0"
                                    max="100"
                                    step="0.01"
                                    className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                />
                            </Field>
                            <Field
                                label="Min Term (months)"
                                required
                                error={errors.min_term_months}
                            >
                                <input
                                    type="number"
                                    value={data.min_term_months}
                                    onChange={(e) =>
                                        setData(
                                            'min_term_months',
                                            e.target.value,
                                        )
                                    }
                                    required
                                    min="1"
                                    className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                />
                            </Field>
                            <Field
                                label="Max Term (months)"
                                required
                                error={errors.max_term_months}
                            >
                                <input
                                    type="number"
                                    value={data.max_term_months}
                                    onChange={(e) =>
                                        setData(
                                            'max_term_months',
                                            e.target.value,
                                        )
                                    }
                                    required
                                    min="1"
                                    className="h-10 w-full rounded-md border bg-background px-3 text-sm"
                                />
                            </Field>
                        </div>
                        <button
                            type="submit"
                            disabled={processing}
                            className="h-10 rounded-md bg-primary px-6 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50"
                        >
                            {processing ? 'Saving...' : 'Add Product'}
                        </button>
                    </form>
                )}

                {products.length === 0 && !showForm ? (
                    <div className="rounded-lg border border-dashed p-12 text-center">
                        <p className="font-medium">No products yet</p>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Add financing products for buyers to apply for.
                        </p>
                    </div>
                ) : (
                    <div className="grid gap-3">
                        {products.map((product) => (
                            <div
                                key={product.id}
                                className="rounded-lg border bg-card p-4"
                            >
                                <div className="flex flex-wrap items-start gap-2">
                                    <span
                                        className={`rounded-full px-2 py-0.5 text-xs font-medium ${typeColors[product.product_type] ?? 'bg-muted'}`}
                                    >
                                        {product.type_label}
                                    </span>
                                    {!product.is_active && (
                                        <span className="rounded-full bg-muted px-2 py-0.5 text-xs text-muted-foreground">
                                            Inactive
                                        </span>
                                    )}
                                    <p className="font-medium">
                                        {product.name}
                                    </p>
                                </div>
                                {product.description && (
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        {product.description}
                                    </p>
                                )}
                                <dl className="mt-3 grid grid-cols-3 gap-3 text-sm">
                                    <InfoItem
                                        label="Loan Amount"
                                        value={`PHP ${Number(product.min_amount).toLocaleString()} – ${Number(product.max_amount).toLocaleString()}`}
                                    />
                                    <InfoItem
                                        label="Interest Rate"
                                        value={`${product.interest_rate_min}% – ${product.interest_rate_max}% p.a.`}
                                    />
                                    <InfoItem
                                        label="Term"
                                        value={`${product.min_term_months} – ${product.max_term_months} months`}
                                    />
                                </dl>
                            </div>
                        ))}
                    </div>
                )}
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

function InfoItem({ label, value }: { label: string; value: string }) {
    return (
        <div>
            <dt className="text-xs font-medium text-muted-foreground">
                {label}
            </dt>
            <dd className="mt-0.5">{value}</dd>
        </div>
    );
}

FinancingPartnerProducts.layout = {
    breadcrumbs: [
        { title: 'Partner Status', href: '/financing-partner/status' },
        { title: 'Products', href: '/financing-partner/products' },
    ],
};
