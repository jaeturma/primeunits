import { Head, Link } from '@inertiajs/react';

type Application = {
    id: number;
    reference_code: string;
    status: string;
    status_label: string;
    full_name: string;
    unit_price: string;
    requested_amount: string;
    preferred_term_months: number;
    partner_name: string | null;
    product_name: string | null;
    submitted_at: string | null;
    created_at: string | null;
};

type Paginated = {
    data: Application[];
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
};

const statusColors: Record<string, string> = {
    submitted: 'bg-blue-100 text-blue-700',
    under_review: 'bg-yellow-100 text-yellow-700',
    approved: 'bg-green-100 text-green-700',
    rejected: 'bg-red-100 text-red-700',
};

export default function BuyerFinancingApplications({
    applications,
}: {
    applications: Paginated;
}) {
    return (
        <>
            <Head title="My Financing Applications" />
            <div className="mx-auto w-full max-w-4xl p-4">
                <div className="mb-6 flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-normal">
                            Financing Applications
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            {applications.total} application
                            {applications.total !== 1 ? 's' : ''}
                        </p>
                    </div>
                    <Link
                        href="/buyer/financing-applications/apply"
                        className="inline-flex h-10 items-center rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                    >
                        + New Application
                    </Link>
                </div>

                {applications.data.length === 0 ? (
                    <div className="rounded-lg border border-dashed p-12 text-center">
                        <p className="font-medium">No applications yet</p>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Apply for vehicle financing from our partner
                            institutions.
                        </p>
                        <Link
                            href="/buyer/financing-applications/apply"
                            className="mt-4 inline-block rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                        >
                            Apply Now
                        </Link>
                    </div>
                ) : (
                    <div className="grid gap-3">
                        {applications.data.map((app) => (
                            <Link
                                key={app.id}
                                href={`/buyer/financing-applications/${app.id}`}
                                className="block rounded-lg border bg-card p-4 transition-shadow hover:shadow-sm"
                            >
                                <div className="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <div className="flex flex-wrap items-center gap-2">
                                            <span
                                                className={`rounded-full px-2 py-0.5 text-xs font-medium ${statusColors[app.status] ?? 'bg-muted'}`}
                                            >
                                                {app.status_label}
                                            </span>
                                            <span className="font-mono text-xs text-muted-foreground">
                                                {app.reference_code}
                                            </span>
                                        </div>
                                        <p className="mt-1 font-medium">
                                            {app.partner_name}
                                        </p>
                                        {app.product_name && (
                                            <p className="text-sm text-muted-foreground">
                                                {app.product_name}
                                            </p>
                                        )}
                                        {app.submitted_at && (
                                            <p className="mt-1 text-xs text-muted-foreground">
                                                Submitted{' '}
                                                {new Date(
                                                    app.submitted_at,
                                                ).toLocaleDateString()}
                                            </p>
                                        )}
                                    </div>
                                    <div className="shrink-0 text-right">
                                        <p className="font-semibold">
                                            PHP{' '}
                                            {Number(
                                                app.requested_amount,
                                            ).toLocaleString()}
                                        </p>
                                        <p className="text-sm text-muted-foreground">
                                            {app.preferred_term_months} months
                                        </p>
                                    </div>
                                </div>
                            </Link>
                        ))}
                    </div>
                )}

                {applications.links && applications.links.length > 3 && (
                    <div className="mt-6 flex justify-center gap-2">
                        {applications.links.map((link, i) =>
                            link.url ? (
                                <Link
                                    key={i}
                                    href={link.url}
                                    className={`rounded-md border px-3 py-1.5 text-sm ${link.active ? 'bg-primary text-primary-foreground' : 'hover:bg-muted'}`}
                                    dangerouslySetInnerHTML={{
                                        __html: link.label,
                                    }}
                                />
                            ) : (
                                <span
                                    key={i}
                                    className="rounded-md border px-3 py-1.5 text-sm text-muted-foreground"
                                    dangerouslySetInnerHTML={{
                                        __html: link.label,
                                    }}
                                />
                            ),
                        )}
                    </div>
                )}
            </div>
        </>
    );
}

BuyerFinancingApplications.layout = {
    breadcrumbs: [
        {
            title: 'Financing Applications',
            href: '/buyer/financing-applications',
        },
    ],
};
