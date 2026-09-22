import { Head, router } from '@inertiajs/react';
import { BarChart3, Filter, Megaphone } from 'lucide-react';
import { useState } from 'react';
import type { FormEvent, ReactNode } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

type Summary = {
    featured_impressions: number;
    sponsored_impressions: number;
    advertisement_impressions: number;
};

type ListingRow = {
    id: number;
    title: string;
    impressions: number;
};

type Filters = {
    from: string | null;
    to: string | null;
};

type Props = {
    filters: Filters;
    summary: Summary;
    topFeaturedListings: ListingRow[];
    topSponsoredListings: ListingRow[];
};

const number = new Intl.NumberFormat();

export default function AdminPromotions({
    filters,
    summary,
    topFeaturedListings,
    topSponsoredListings,
}: Props) {
    const [form, setForm] = useState({
        from: filters.from ?? '',
        to: filters.to ?? '',
    });

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        router.get(
            '/adm/promotions',
            {
                from: form.from || undefined,
                to: form.to || undefined,
            },
            { preserveState: true, preserveScroll: true },
        );
    }

    function resetFilters() {
        router.get('/adm/promotions');
    }

    return (
        <>
            <Head title="Promotion Impressions" />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
                <div className="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-normal">
                            Promotion Impressions
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            How often Featured and Sponsored landing-feed
                            placements were actually rendered to a viewer.
                            Advertisement impressions and clicks are tracked per
                            ad on the Landing Ads page.
                        </p>
                    </div>

                    <form
                        onSubmit={submit}
                        className="grid gap-2 sm:grid-cols-2 lg:grid-cols-3"
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

                <div className="grid gap-3 md:grid-cols-3">
                    <Metric
                        label="Featured Impressions"
                        value={summary.featured_impressions}
                    />
                    <Metric
                        label="Sponsored Impressions"
                        value={summary.sponsored_impressions}
                    />
                    <Metric
                        label="Advertisement Impressions"
                        value={summary.advertisement_impressions}
                    />
                </div>

                <div className="grid gap-4 xl:grid-cols-2">
                    <Panel title="Top Featured Listings">
                        <Table
                            rows={topFeaturedListings}
                            columns={[
                                ['Listing', (row) => row.title],
                                [
                                    'Impressions',
                                    (row) => (
                                        <Badge variant="secondary">
                                            {number.format(row.impressions)}
                                        </Badge>
                                    ),
                                ],
                            ]}
                        />
                    </Panel>

                    <Panel title="Top Sponsored Listings">
                        <Table
                            rows={topSponsoredListings}
                            columns={[
                                ['Listing', (row) => row.title],
                                [
                                    'Impressions',
                                    (row) => (
                                        <Badge variant="secondary">
                                            {number.format(row.impressions)}
                                        </Badge>
                                    ),
                                ],
                            ]}
                        />
                    </Panel>
                </div>
            </div>
        </>
    );
}

function Metric({ label, value }: { label: string; value: number }) {
    return (
        <div className="rounded-lg border p-4">
            <div className="text-sm text-muted-foreground">{label}</div>
            <div className="mt-2 text-2xl font-semibold tracking-normal">
                {number.format(value)}
            </div>
        </div>
    );
}

function Panel({ title, children }: { title: string; children: ReactNode }) {
    return (
        <section className="rounded-lg border p-5">
            <div className="mb-4 flex items-center gap-2">
                {title.includes('Sponsored') ? (
                    <BarChart3 className="size-5" />
                ) : (
                    <Megaphone className="size-5" />
                )}
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

AdminPromotions.layout = {
    breadcrumbs: [{ title: 'Promotions', href: '/adm/promotions' }],
};
