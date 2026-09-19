import { Head, Link, usePage } from '@inertiajs/react';

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
};

type Partner = {
    id: number;
    slug: string;
    company_name: string;
    contact_number: string;
    contact_email: string | null;
    description: string | null;
    website: string | null;
    region: string | null;
    province: string | null;
    municipality: string | null;
    full_address: string | null;
    logo_url: string | null;
    banner_url: string | null;
    products_count: number;
    products: Product[];
};

const typeColors: Record<string, string> = {
    term_loan: 'bg-blue-100 text-blue-700',
    installment: 'bg-green-100 text-green-700',
    lease: 'bg-purple-100 text-purple-700',
    chattel_mortgage: 'bg-orange-100 text-orange-700',
};

export default function FinancingShow({ partner }: { partner: Partner }) {
    const { auth } = usePage().props;

    return (
        <>
            <Head title={partner.company_name} />

            {partner.banner_url && (
                <div className="h-48 w-full overflow-hidden bg-muted">
                    <img
                        src={partner.banner_url}
                        alt=""
                        className="h-full w-full object-cover"
                    />
                </div>
            )}

            <div className="mx-auto w-full max-w-5xl p-4">
                <div className="mb-6 flex flex-wrap items-start gap-4">
                    <div className="h-16 w-16 shrink-0 overflow-hidden rounded-full border bg-muted">
                        {partner.logo_url ? (
                            <img
                                src={partner.logo_url}
                                alt={partner.company_name}
                                className="h-full w-full object-cover"
                            />
                        ) : (
                            <div className="flex h-full w-full items-center justify-center text-2xl font-bold text-muted-foreground">
                                {partner.company_name.charAt(0)}
                            </div>
                        )}
                    </div>
                    <div className="min-w-0 flex-1">
                        <h1 className="text-2xl font-semibold">
                            {partner.company_name}
                        </h1>
                        {partner.municipality && (
                            <p className="mt-0.5 text-sm text-muted-foreground">
                                {[
                                    partner.municipality,
                                    partner.province,
                                    partner.region,
                                ]
                                    .filter(Boolean)
                                    .join(', ')}
                            </p>
                        )}
                    </div>
                    <Link
                        href={`/buyer/financing-applications/apply`}
                        className="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                    >
                        Apply for Financing
                    </Link>
                </div>

                <div className="grid gap-6 lg:grid-cols-[1fr_300px]">
                    <div className="space-y-6">
                        {partner.description && (
                            <section className="rounded-lg border p-5">
                                <h2 className="font-medium">About</h2>
                                <p className="mt-3 text-sm whitespace-pre-line text-muted-foreground">
                                    {partner.description}
                                </p>
                            </section>
                        )}

                        <section>
                            <h2 className="mb-3 font-medium">
                                Financing Products ({partner.products.length})
                            </h2>
                            {partner.products.length === 0 ? (
                                <p className="text-sm text-muted-foreground">
                                    No products listed yet.
                                </p>
                            ) : (
                                <div className="grid gap-3">
                                    {partner.products.map((product) => (
                                        <div
                                            key={product.id}
                                            className="rounded-lg border p-4"
                                        >
                                            <div className="flex flex-wrap items-start justify-between gap-2">
                                                <div>
                                                    <div className="flex flex-wrap items-center gap-2">
                                                        <span
                                                            className={`rounded-full px-2 py-0.5 text-xs font-medium ${typeColors[product.product_type] ?? 'bg-muted text-muted-foreground'}`}
                                                        >
                                                            {product.type_label}
                                                        </span>
                                                        <h3 className="font-medium">
                                                            {product.name}
                                                        </h3>
                                                    </div>
                                                    {product.description && (
                                                        <p className="mt-1 text-sm text-muted-foreground">
                                                            {
                                                                product.description
                                                            }
                                                        </p>
                                                    )}
                                                </div>
                                            </div>
                                            <dl className="mt-3 grid grid-cols-2 gap-2 text-sm sm:grid-cols-4">
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
                        </section>
                    </div>

                    <aside className="grid h-fit gap-3 rounded-lg border p-5 text-sm">
                        <h3 className="font-medium">Contact</h3>
                        <dl className="grid gap-2">
                            <InfoItem
                                label="Phone"
                                value={partner.contact_number}
                            />
                            {partner.contact_email && (
                                <InfoItem
                                    label="Email"
                                    value={partner.contact_email}
                                />
                            )}
                            {partner.website && (
                                <div>
                                    <dt className="text-xs font-medium text-muted-foreground">
                                        Website
                                    </dt>
                                    <dd className="mt-0.5">
                                        <a
                                            href={partner.website}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="break-all text-primary hover:underline"
                                        >
                                            {partner.website}
                                        </a>
                                    </dd>
                                </div>
                            )}
                            {partner.full_address && (
                                <InfoItem
                                    label="Address"
                                    value={partner.full_address}
                                />
                            )}
                        </dl>
                        <Link
                            href="/buyer/financing-applications/apply"
                            className="mt-2 block h-10 rounded-md bg-primary text-center text-sm leading-10 font-medium text-primary-foreground hover:bg-primary/90"
                        >
                            Apply Now
                        </Link>
                    </aside>
                </div>
            </div>
        </>
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
