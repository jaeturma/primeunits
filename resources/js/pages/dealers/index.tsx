import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import { PublicFooter } from '@/components/public-footer';
import { PublicHeader } from '@/components/public-header';

type Dealer = {
    id: number;
    slug: string;
    business_name: string;
    logo: string | null;
    region: string | null;
    province: string | null;
    municipality: string | null;
    contact_number: string;
    verified_at: string | null;
};

type PaginatedDealers = {
    data: Dealer[];
    current_page: number;
    last_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
};

export default function DealersIndex({
    dealers,
    filters,
}: {
    dealers: PaginatedDealers;
    filters: { q: string; region: string };
}) {
    const [q, setQ] = useState(filters.q);

    function search(e: FormEvent<HTMLFormElement>) {
        e.preventDefault();
        router.get('/dealers', { q }, { preserveState: true });
    }

    return (
        <>
            <Head title="Dealers" />
            <PublicHeader />
            <div className="mx-auto w-full max-w-6xl p-4">
                <div className="mb-6">
                    <h1 className="text-2xl font-semibold tracking-normal">
                        Dealer Directory
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Browse PrimeUnits verified vehicle dealers
                    </p>
                </div>

                <form onSubmit={search} className="mb-6 flex gap-3">
                    <input
                        type="text"
                        value={q}
                        onChange={(e) => setQ(e.target.value)}
                        placeholder="Search dealers..."
                        className="h-10 flex-1 rounded-md border bg-background px-3 text-sm"
                    />
                    <button
                        type="submit"
                        className="h-10 rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground"
                    >
                        Search
                    </button>
                </form>

                {dealers.data.length === 0 ? (
                    <div className="rounded-lg border border-dashed p-12 text-center">
                        <p className="text-sm text-muted-foreground">
                            No dealers found.
                        </p>
                    </div>
                ) : (
                    <>
                        <p className="mb-4 text-sm text-muted-foreground">
                            {dealers.total} dealer
                            {dealers.total !== 1 ? 's' : ''} found
                        </p>
                        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            {dealers.data.map((dealer) => (
                                <Link
                                    key={dealer.id}
                                    href={`/dealer/${dealer.slug}`}
                                    className="group flex gap-4 rounded-lg border bg-card p-4 transition-shadow hover:shadow-md"
                                >
                                    <div className="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-muted text-xl font-bold text-muted-foreground">
                                        {dealer.logo ? (
                                            <img
                                                src={dealer.logo}
                                                alt={dealer.business_name}
                                                className="h-full w-full object-cover"
                                            />
                                        ) : (
                                            dealer.business_name
                                                .charAt(0)
                                                .toUpperCase()
                                        )}
                                    </div>
                                    <div className="min-w-0 flex-1">
                                        <div className="flex items-center gap-1.5">
                                            <h3 className="truncate font-medium">
                                                {dealer.business_name}
                                            </h3>
                                            <span className="shrink-0 rounded-full bg-green-100 px-1.5 py-0.5 text-[10px] font-medium text-green-700">
                                                Verified
                                            </span>
                                        </div>
                                        {[
                                            dealer.municipality,
                                            dealer.province,
                                            dealer.region,
                                        ].filter(Boolean).length > 0 && (
                                            <p className="mt-1 truncate text-xs text-muted-foreground">
                                                {[
                                                    dealer.municipality,
                                                    dealer.province,
                                                    dealer.region,
                                                ]
                                                    .filter(Boolean)
                                                    .join(', ')}
                                            </p>
                                        )}
                                    </div>
                                </Link>
                            ))}
                        </div>

                        {dealers.last_page > 1 && (
                            <div className="mt-8 flex justify-center gap-2">
                                {dealers.links.map((link, i) =>
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
                    </>
                )}
            </div>
            <PublicFooter />
        </>
    );
}
