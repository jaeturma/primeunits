import { Head, Link } from '@inertiajs/react';

type Stats = {
    total_listings: number;
    active_listings: number;
    total_views: number;
    total_leads: number;
    closed_leads: number;
    total_transactions: number;
    total_revenue: number;
    conversion_rate: number;
};

type TopListing = {
    id: number;
    title: string;
    views_count: number;
    leads_count: number;
    status: string;
    price: string;
};

type RecentLead = {
    id: number;
    reference_code: string;
    status: string;
    status_label: string;
    listing_title: string | null;
    buyer_name: string | null;
    created_at: string | null;
};

export default function SellerAnalytics({
    stats,
    top_listings,
    recent_leads,
}: {
    stats: Stats;
    top_listings: TopListing[];
    recent_leads: RecentLead[];
}) {
    return (
        <>
            <Head title="My Analytics" />
            <div className="mx-auto w-full max-w-5xl p-4">
                <div className="mb-6">
                    <h1 className="text-2xl font-semibold tracking-normal">
                        My Analytics
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Performance overview for your listings
                    </p>
                </div>

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <StatCard
                        label="Active Listings"
                        value={stats.active_listings}
                        sub={`${stats.total_listings} total`}
                    />
                    <StatCard
                        label="Total Views"
                        value={stats.total_views.toLocaleString()}
                    />
                    <StatCard
                        label="Total Leads"
                        value={stats.total_leads}
                        sub={`${stats.closed_leads} closed`}
                    />
                    <StatCard
                        label="Conversion Rate"
                        value={`${stats.conversion_rate}%`}
                        sub="leads to close"
                    />
                </div>

                <div className="mt-4 grid gap-4 sm:grid-cols-2">
                    <StatCard
                        label="Confirmed Transactions"
                        value={stats.total_transactions}
                    />
                    <StatCard
                        label="Total Revenue"
                        value={`PHP ${Number(stats.total_revenue).toLocaleString()}`}
                        sub="from confirmed transactions"
                    />
                </div>

                <div className="mt-8 grid gap-6 lg:grid-cols-2">
                    <section className="rounded-lg border p-5">
                        <h2 className="font-medium">Top Listings by Views</h2>
                        {top_listings.length === 0 ? (
                            <p className="mt-4 text-sm text-muted-foreground">
                                No listings yet.
                            </p>
                        ) : (
                            <div className="mt-4 divide-y">
                                {top_listings.map((listing) => (
                                    <div
                                        key={listing.id}
                                        className="flex items-center justify-between py-3 text-sm"
                                    >
                                        <div className="min-w-0 flex-1">
                                            <Link
                                                href={`/listings/${listing.id}`}
                                                className="line-clamp-1 font-medium hover:underline"
                                            >
                                                {listing.title}
                                            </Link>
                                            <p className="mt-0.5 text-muted-foreground">
                                                PHP{' '}
                                                {Number(
                                                    listing.price,
                                                ).toLocaleString()}{' '}
                                                · {listing.status}
                                            </p>
                                        </div>
                                        <div className="ml-4 shrink-0 text-right">
                                            <p className="font-medium">
                                                {listing.views_count.toLocaleString()}{' '}
                                                views
                                            </p>
                                            <p className="text-muted-foreground">
                                                {listing.leads_count} leads
                                            </p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                        <div className="mt-4">
                            <Link
                                href="/seller/listings"
                                className="text-sm font-medium text-primary hover:underline"
                            >
                                View all listings →
                            </Link>
                        </div>
                    </section>

                    <section className="rounded-lg border p-5">
                        <h2 className="font-medium">Recent Leads</h2>
                        {recent_leads.length === 0 ? (
                            <p className="mt-4 text-sm text-muted-foreground">
                                No leads yet.
                            </p>
                        ) : (
                            <div className="mt-4 divide-y">
                                {recent_leads.map((lead) => (
                                    <div key={lead.id} className="py-3 text-sm">
                                        <div className="flex items-start justify-between gap-2">
                                            <div className="min-w-0 flex-1">
                                                <p className="font-medium">
                                                    {lead.reference_code}
                                                </p>
                                                <p className="mt-0.5 line-clamp-1 text-muted-foreground">
                                                    {lead.listing_title}
                                                </p>
                                                <p className="mt-0.5 text-xs text-muted-foreground">
                                                    From: {lead.buyer_name}
                                                </p>
                                            </div>
                                            <LeadStatusBadge
                                                status={lead.status}
                                                label={lead.status_label}
                                            />
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                        <div className="mt-4">
                            <Link
                                href="/seller/leads"
                                className="text-sm font-medium text-primary hover:underline"
                            >
                                View all leads →
                            </Link>
                        </div>
                    </section>
                </div>
            </div>
        </>
    );
}

function StatCard({
    label,
    value,
    sub,
}: {
    label: string;
    value: string | number;
    sub?: string;
}) {
    return (
        <div className="rounded-lg border bg-card p-5">
            <p className="text-sm text-muted-foreground">{label}</p>
            <p className="mt-2 text-2xl font-bold">{value}</p>
            {sub && <p className="mt-1 text-xs text-muted-foreground">{sub}</p>}
        </div>
    );
}

function LeadStatusBadge({ status, label }: { status: string; label: string }) {
    const colors: Record<string, string> = {
        inquiry: 'bg-blue-100 text-blue-700',
        contacted: 'bg-yellow-100 text-yellow-700',
        negotiating: 'bg-orange-100 text-orange-700',
        reserved: 'bg-purple-100 text-purple-700',
        closed: 'bg-green-100 text-green-700',
        cancelled: 'bg-gray-100 text-gray-600',
    };
    return (
        <span
            className={`shrink-0 rounded-full px-2 py-0.5 text-xs font-medium ${colors[status] ?? 'bg-muted text-muted-foreground'}`}
        >
            {label}
        </span>
    );
}

SellerAnalytics.layout = {
    breadcrumbs: [{ title: 'My Analytics', href: '/seller/analytics' }],
};
