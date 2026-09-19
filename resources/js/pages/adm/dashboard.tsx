import { Head, router } from '@inertiajs/react';
import { BarChart3, CircleDollarSign, Filter, TrendingUp } from 'lucide-react';
import { useState } from 'react';
import type { FormEvent, ReactNode } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

type Summary = {
    total_users: number;
    total_sellers: number;
    total_buyers: number;
    total_listings: number;
    active_listings: number;
    total_leads: number;
    total_transactions: number;
    conversion_rate: number;
};

type Revenue = {
    total_commission: number;
    total_paid_commission: number;
    pending_commission: number;
    total_payments_received: number;
};

type Conversion = {
    total_leads: number;
    total_transactions: number;
    leads_to_transactions_rate: number;
    average_time_to_close_hours: number;
};

type ListingStats = {
    listings_per_category: Array<{ id: number; name: string; total: number }>;
    top_listings: Array<{ id: number; title: string; leads_count: number }>;
    top_sellers: Array<{
        id: number;
        name: string;
        closed_transactions: number;
        closed_value: number;
    }>;
};

type Filters = {
    from: string | null;
    to: string | null;
    category_id: number | null;
    location: string | null;
};

type Props = {
    filters: Filters;
    filterOptions: {
        categories: Array<{ id: number; name: string }>;
        locations: string[];
    };
    summary: Summary;
    revenue: Revenue;
    conversion: Conversion;
    listingStats: ListingStats;
};

const number = new Intl.NumberFormat();
const money = new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
    maximumFractionDigits: 0,
});

export default function AdminDashboard({
    filters,
    filterOptions,
    summary,
    revenue,
    conversion,
    listingStats,
}: Props) {
    const [form, setForm] = useState({
        from: filters.from ?? '',
        to: filters.to ?? '',
        category_id: filters.category_id?.toString() ?? '',
        location: filters.location ?? '',
    });

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        router.get(
            '/adm/dashboard',
            {
                from: form.from || undefined,
                to: form.to || undefined,
                category_id: form.category_id || undefined,
                location: form.location || undefined,
            },
            { preserveState: true, preserveScroll: true },
        );
    }

    function resetFilters() {
        router.get('/adm/dashboard');
    }

    return (
        <>
            <Head title="Admin Dashboard" />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
                <div className="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-normal">
                            Analytics Dashboard
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Platform, revenue, conversion, and listing
                            performance.
                        </p>
                    </div>

                    <form
                        onSubmit={submit}
                        className="grid gap-2 sm:grid-cols-2 lg:grid-cols-5"
                    >
                        <Input
                            type="date"
                            value={form.from}
                            onChange={(event) =>
                                setForm({ ...form, from: event.target.value })
                            }
                            aria-label="From date"
                        />
                        <Input
                            type="date"
                            value={form.to}
                            onChange={(event) =>
                                setForm({ ...form, to: event.target.value })
                            }
                            aria-label="To date"
                        />
                        <Select
                            value={form.category_id || 'all'}
                            onValueChange={(value) =>
                                setForm({
                                    ...form,
                                    category_id: value === 'all' ? '' : value,
                                })
                            }
                        >
                            <SelectTrigger className="w-full">
                                <SelectValue placeholder="Category" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">
                                    All categories
                                </SelectItem>
                                {filterOptions.categories.map((category) => (
                                    <SelectItem
                                        key={category.id}
                                        value={category.id.toString()}
                                    >
                                        {category.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <Select
                            value={form.location || 'all'}
                            onValueChange={(value) =>
                                setForm({
                                    ...form,
                                    location: value === 'all' ? '' : value,
                                })
                            }
                        >
                            <SelectTrigger className="w-full">
                                <SelectValue placeholder="Location" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">
                                    All locations
                                </SelectItem>
                                {filterOptions.locations.map((location) => (
                                    <SelectItem key={location} value={location}>
                                        {location}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <div className="flex gap-2">
                            <Button type="submit" className="flex-1">
                                <Filter className="size-4" />
                                Apply
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={resetFilters}
                            >
                                Clear
                            </Button>
                        </div>
                    </form>
                </div>

                <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                    <Metric label="Users" value={summary.total_users} />
                    <Metric label="Sellers" value={summary.total_sellers} />
                    <Metric label="Buyers" value={summary.total_buyers} />
                    <Metric label="Listings" value={summary.total_listings} />
                    <Metric
                        label="Active Listings"
                        value={summary.active_listings}
                    />
                    <Metric label="Leads" value={summary.total_leads} />
                    <Metric
                        label="Transactions"
                        value={summary.total_transactions}
                    />
                    <Metric
                        label="Conversion"
                        value={`${summary.conversion_rate}%`}
                    />
                </div>

                <div className="grid gap-4 xl:grid-cols-3">
                    <section className="rounded-lg border p-5 xl:col-span-2">
                        <div className="flex items-center gap-2">
                            <CircleDollarSign className="size-5" />
                            <h2 className="font-medium">Revenue</h2>
                        </div>
                        <div className="mt-4 grid gap-3 sm:grid-cols-2">
                            <Metric
                                label="Total Commission"
                                value={money.format(revenue.total_commission)}
                            />
                            <Metric
                                label="Paid Commission"
                                value={money.format(
                                    revenue.total_paid_commission,
                                )}
                            />
                            <Metric
                                label="Pending Commission"
                                value={money.format(revenue.pending_commission)}
                            />
                            <Metric
                                label="Payments Received"
                                value={money.format(
                                    revenue.total_payments_received,
                                )}
                            />
                        </div>
                    </section>

                    <section className="rounded-lg border p-5">
                        <div className="flex items-center gap-2">
                            <TrendingUp className="size-5" />
                            <h2 className="font-medium">Conversion</h2>
                        </div>
                        <dl className="mt-4 space-y-4">
                            <Detail
                                label="Leads to transactions"
                                value={`${conversion.leads_to_transactions_rate}%`}
                            />
                            <Detail
                                label="Average time to close"
                                value={`${conversion.average_time_to_close_hours}h`}
                            />
                            <Detail
                                label="Tracked leads"
                                value={number.format(conversion.total_leads)}
                            />
                        </dl>
                    </section>
                </div>

                <div className="grid gap-4 xl:grid-cols-3">
                    <Panel title="Listings Per Category">
                        <Table
                            rows={listingStats.listings_per_category}
                            columns={[
                                ['Category', (row) => row.name],
                                ['Listings', (row) => number.format(row.total)],
                            ]}
                        />
                    </Panel>

                    <Panel title="Top Listings">
                        <Table
                            rows={listingStats.top_listings}
                            columns={[
                                ['Listing', (row) => row.title],
                                [
                                    'Leads',
                                    (row) => (
                                        <Badge variant="secondary">
                                            {number.format(row.leads_count)}
                                        </Badge>
                                    ),
                                ],
                            ]}
                        />
                    </Panel>

                    <Panel title="Top Sellers">
                        <Table
                            rows={listingStats.top_sellers}
                            columns={[
                                ['Seller', (row) => row.name],
                                [
                                    'Closed',
                                    (row) =>
                                        number.format(row.closed_transactions),
                                ],
                                [
                                    'Value',
                                    (row) => money.format(row.closed_value),
                                ],
                            ]}
                        />
                    </Panel>
                </div>
            </div>
        </>
    );
}

function Metric({ label, value }: { label: string; value: number | string }) {
    return (
        <div className="rounded-lg border p-4">
            <div className="text-sm text-muted-foreground">{label}</div>
            <div className="mt-2 text-2xl font-semibold tracking-normal">
                {typeof value === 'number' ? number.format(value) : value}
            </div>
        </div>
    );
}

function Detail({ label, value }: { label: string; value: string }) {
    return (
        <div className="flex items-center justify-between gap-3">
            <dt className="text-sm text-muted-foreground">{label}</dt>
            <dd className="font-medium">{value}</dd>
        </div>
    );
}

function Panel({ title, children }: { title: string; children: ReactNode }) {
    return (
        <section className="rounded-lg border p-5">
            <div className="mb-4 flex items-center gap-2">
                <BarChart3 className="size-5" />
                <h2 className="font-medium">{title}</h2>
            </div>
            {children}
        </section>
    );
}

function Table<T extends { id: number | string }>({
    rows,
    columns,
}: {
    rows: T[];
    columns: Array<[string, (row: T) => ReactNode]>;
}) {
    if (rows.length === 0) {
        return <p className="text-sm text-muted-foreground">No data found.</p>;
    }

    return (
        <div className="overflow-hidden rounded-md border">
            <table className="w-full text-sm">
                <thead className="bg-muted/50">
                    <tr>
                        {columns.map(([label]) => (
                            <th
                                key={label}
                                className="px-3 py-2 text-left font-medium"
                            >
                                {label}
                            </th>
                        ))}
                    </tr>
                </thead>
                <tbody>
                    {rows.map((row) => (
                        <tr key={row.id} className="border-t">
                            {columns.map(([label, render]) => (
                                <td key={label} className="px-3 py-2">
                                    {render(row)}
                                </td>
                            ))}
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}

AdminDashboard.layout = {
    breadcrumbs: [
        {
            title: 'Admin Dashboard',
            href: '/adm/dashboard',
        },
    ],
};
