import { Head, Link } from '@inertiajs/react';

type Application = {
    id: number;
    reference_code: string;
    status: string;
    status_label: string;
    full_name: string;
    contact_number: string;
    employment_type: string;
    unit_price: string;
    requested_amount: string;
    down_payment: string | null;
    monthly_income: string | null;
    preferred_term_months: number;
    notes: string | null;
    reviewer_notes: string | null;
    estimated_monthly: number | null;
    submitted_at: string | null;
    reviewed_at: string | null;
    partner_name: string | null;
    product_name: string | null;
    partner: {
        company_name: string;
        contact_number: string;
        contact_email: string | null;
    } | null;
    product: {
        name: string;
        type_label: string;
        interest_rate_min: string;
        interest_rate_max: string;
    } | null;
    listing: { title: string; price: string } | null;
    attachments: Array<{ id: number; name: string; url: string; size: number }>;
};

const statusColors: Record<string, string> = {
    submitted: 'bg-blue-100 text-blue-700',
    under_review: 'bg-yellow-100 text-yellow-700',
    approved: 'bg-green-100 text-green-700',
    rejected: 'bg-red-100 text-red-700',
};

export default function FinancingApplicationShow({
    application,
}: {
    application: Application;
}) {
    return (
        <>
            <Head title={`Application ${application.reference_code}`} />
            <div className="mx-auto w-full max-w-3xl p-4">
                <div className="mb-6 flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <div className="flex items-center gap-2">
                            <span
                                className={`rounded-full px-2 py-0.5 text-xs font-medium ${statusColors[application.status] ?? 'bg-muted'}`}
                            >
                                {application.status_label}
                            </span>
                            <span className="font-mono text-sm text-muted-foreground">
                                {application.reference_code}
                            </span>
                        </div>
                        <h1 className="mt-1 text-xl font-semibold">
                            Financing Application
                        </h1>
                        {application.submitted_at && (
                            <p className="mt-0.5 text-sm text-muted-foreground">
                                Submitted{' '}
                                {new Date(
                                    application.submitted_at,
                                ).toLocaleDateString()}
                            </p>
                        )}
                    </div>
                    <Link
                        href="/buyer/financing-applications"
                        className="text-sm text-primary hover:underline"
                    >
                        ← All Applications
                    </Link>
                </div>

                {application.reviewer_notes && (
                    <div
                        className={`mb-4 rounded-md border p-4 text-sm ${application.status === 'approved' ? 'border-green-200 bg-green-50' : application.status === 'rejected' ? 'border-red-200 bg-red-50' : 'border-yellow-200 bg-yellow-50'}`}
                    >
                        <p className="font-medium">Partner Notes</p>
                        <p className="mt-1">{application.reviewer_notes}</p>
                        {application.reviewed_at && (
                            <p className="mt-1 text-xs text-muted-foreground">
                                Reviewed{' '}
                                {new Date(
                                    application.reviewed_at,
                                ).toLocaleDateString()}
                            </p>
                        )}
                    </div>
                )}

                <div className="grid gap-5">
                    <Section title="Financing Partner">
                        <InfoItem
                            label="Company"
                            value={
                                application.partner?.company_name ??
                                application.partner_name ??
                                '—'
                            }
                        />
                        <InfoItem
                            label="Product"
                            value={`${application.product?.name ?? application.product_name ?? '—'} (${application.product?.type_label ?? ''})`}
                        />
                        {application.product && (
                            <InfoItem
                                label="Interest Rate"
                                value={`${application.product.interest_rate_min}% – ${application.product.interest_rate_max}% p.a.`}
                            />
                        )}
                        {application.partner?.contact_number && (
                            <InfoItem
                                label="Contact"
                                value={application.partner.contact_number}
                            />
                        )}
                        {application.partner?.contact_email && (
                            <InfoItem
                                label="Email"
                                value={application.partner.contact_email}
                            />
                        )}
                    </Section>

                    {application.listing && (
                        <Section title="Unit">
                            <InfoItem
                                label="Title"
                                value={application.listing.title}
                            />
                            <InfoItem
                                label="Price"
                                value={`PHP ${Number(application.listing.price).toLocaleString()}`}
                            />
                        </Section>
                    )}

                    <Section title="Personal Information">
                        <InfoItem
                            label="Full Name"
                            value={application.full_name}
                        />
                        <InfoItem
                            label="Contact"
                            value={application.contact_number}
                        />
                        <InfoItem
                            label="Employment"
                            value={application.employment_type.replace(
                                '_',
                                ' ',
                            )}
                        />
                        {application.monthly_income && (
                            <InfoItem
                                label="Monthly Income"
                                value={`PHP ${Number(application.monthly_income).toLocaleString()}`}
                            />
                        )}
                    </Section>

                    <Section title="Loan Details">
                        <InfoItem
                            label="Unit Price"
                            value={`PHP ${Number(application.unit_price).toLocaleString()}`}
                        />
                        <InfoItem
                            label="Requested Amount"
                            value={`PHP ${Number(application.requested_amount).toLocaleString()}`}
                        />
                        {application.down_payment && (
                            <InfoItem
                                label="Down Payment"
                                value={`PHP ${Number(application.down_payment).toLocaleString()}`}
                            />
                        )}
                        <InfoItem
                            label="Term"
                            value={`${application.preferred_term_months} months`}
                        />
                        {application.estimated_monthly && (
                            <InfoItem
                                label="Est. Monthly Payment"
                                value={`PHP ${Number(application.estimated_monthly).toLocaleString()}`}
                            />
                        )}
                    </Section>

                    {application.notes && (
                        <Section title="Notes">
                            <p className="text-sm whitespace-pre-line text-muted-foreground">
                                {application.notes}
                            </p>
                        </Section>
                    )}

                    {application.attachments.length > 0 && (
                        <Section title="Attachments">
                            {application.attachments.map((attachment) => (
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
                        </Section>
                    )}
                </div>
            </div>
        </>
    );
}

function Section({
    title,
    children,
}: {
    title: string;
    children: React.ReactNode;
}) {
    return (
        <section className="rounded-lg border p-5">
            <h2 className="mb-3 font-medium">{title}</h2>
            <dl className="grid gap-2 text-sm sm:grid-cols-2">{children}</dl>
        </section>
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

FinancingApplicationShow.layout = {
    breadcrumbs: [
        {
            title: 'Financing Applications',
            href: '/buyer/financing-applications',
        },
        { title: 'Application Detail', href: '#' },
    ],
};
