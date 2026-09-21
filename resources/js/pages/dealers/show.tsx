import { Head, Link } from '@inertiajs/react';
import { PublicFooter } from '@/components/public-footer';
import { PublicHeader } from '@/components/public-header';

type Dealer = {
    id: number;
    slug: string;
    business_name: string;
    logo: string | null;
    banner: string | null;
    description: string | null;
    contact_number: string;
    email: string | null;
    website: string | null;
    region: string | null;
    province: string | null;
    municipality: string | null;
    full_address: string | null;
    verified_at: string | null;
};

type Listing = {
    id: number;
    title: string;
    price: string;
    condition: string;
    brand: string | null;
    model: string | null;
    year_model: number | null;
    is_featured: boolean;
    category: { name: string } | null;
    image_url: string;
};

type PaginatedListings = {
    data: Listing[];
    total: number;
    last_page: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
};

export default function DealerShow({
    dealer,
    listings,
}: {
    dealer: Dealer;
    listings: PaginatedListings;
}) {
    return (
        <>
            <Head title={dealer.business_name} />
            <PublicHeader />
            <div className="mx-auto w-full max-w-6xl p-4">
                {dealer.banner && (
                    <div className="mb-6 h-48 w-full overflow-hidden rounded-xl bg-muted">
                        <img
                            src={dealer.banner}
                            alt=""
                            className="h-full w-full object-cover"
                        />
                    </div>
                )}

                <div className="mb-8 rounded-lg border p-6">
                    <div className="flex items-start gap-5">
                        <div className="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-muted text-3xl font-bold text-muted-foreground">
                            {dealer.logo ? (
                                <img
                                    src={dealer.logo}
                                    alt={dealer.business_name}
                                    className="h-full w-full object-cover"
                                />
                            ) : (
                                dealer.business_name.charAt(0).toUpperCase()
                            )}
                        </div>
                        <div className="flex-1">
                            <div className="flex flex-wrap items-center gap-2">
                                <h1 className="text-2xl font-semibold tracking-normal">
                                    {dealer.business_name}
                                </h1>
                                <span className="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">
                                    Verified Dealer
                                </span>
                            </div>
                            {dealer.description && (
                                <p className="mt-2 text-sm text-muted-foreground">
                                    {dealer.description}
                                </p>
                            )}
                            <dl className="mt-4 grid gap-3 text-sm sm:grid-cols-2 lg:grid-cols-3">
                                {dealer.contact_number && (
                                    <InfoItem
                                        label="Contact"
                                        value={dealer.contact_number}
                                    />
                                )}
                                {dealer.email && (
                                    <InfoItem
                                        label="Email"
                                        value={dealer.email}
                                    />
                                )}
                                {dealer.website && (
                                    <div>
                                        <dt className="font-medium">Website</dt>
                                        <dd className="mt-0.5 text-muted-foreground">
                                            <a
                                                href={dealer.website}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="text-primary hover:underline"
                                            >
                                                {dealer.website}
                                            </a>
                                        </dd>
                                    </div>
                                )}
                                {(dealer.municipality ||
                                    dealer.province ||
                                    dealer.region) && (
                                    <InfoItem
                                        label="Location"
                                        value={[
                                            dealer.municipality,
                                            dealer.province,
                                            dealer.region,
                                        ]
                                            .filter(Boolean)
                                            .join(', ')}
                                    />
                                )}
                                {dealer.full_address && (
                                    <InfoItem
                                        label="Address"
                                        value={dealer.full_address}
                                    />
                                )}
                                {dealer.verified_at && (
                                    <InfoItem
                                        label="Member since"
                                        value={dealer.verified_at}
                                    />
                                )}
                            </dl>
                        </div>
                    </div>
                </div>

                <div className="mb-6">
                    <h2 className="text-lg font-semibold">
                        Available Units ({listings.total})
                    </h2>
                </div>

                {listings.data.length === 0 ? (
                    <div className="rounded-lg border border-dashed p-12 text-center">
                        <p className="text-sm text-muted-foreground">
                            No available units at this time.
                        </p>
                    </div>
                ) : (
                    <>
                        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            {listings.data.map((listing) => (
                                <Link
                                    key={listing.id}
                                    href={`/listings/${listing.id}`}
                                    className="group rounded-lg border bg-card transition-shadow hover:shadow-md"
                                >
                                    <div className="relative aspect-video overflow-hidden rounded-t-lg bg-muted">
                                        <img
                                            src={listing.image_url}
                                            alt={listing.title}
                                            className="h-full w-full object-cover transition-transform group-hover:scale-105"
                                        />
                                        {listing.is_featured && (
                                            <span className="absolute top-2 left-2 rounded bg-yellow-400 px-2 py-0.5 text-[11px] font-semibold text-yellow-900">
                                                Featured
                                            </span>
                                        )}
                                    </div>
                                    <div className="p-4">
                                        <p className="text-xs text-muted-foreground">
                                            {listing.category?.name}
                                        </p>
                                        <h3 className="mt-1 line-clamp-2 leading-tight font-medium">
                                            {listing.title}
                                        </h3>
                                        <p className="mt-2 text-lg font-semibold">
                                            PHP{' '}
                                            {Number(
                                                listing.price,
                                            ).toLocaleString()}
                                        </p>
                                        <div className="mt-2 flex flex-wrap gap-2 text-xs text-muted-foreground">
                                            <span className="rounded-full bg-muted px-2 py-0.5">
                                                {listing.condition}
                                            </span>
                                            {listing.year_model && (
                                                <span className="rounded-full bg-muted px-2 py-0.5">
                                                    {listing.year_model}
                                                </span>
                                            )}
                                            {listing.brand && (
                                                <span className="rounded-full bg-muted px-2 py-0.5">
                                                    {listing.brand}
                                                </span>
                                            )}
                                        </div>
                                    </div>
                                </Link>
                            ))}
                        </div>

                        {listings.last_page > 1 && (
                            <div className="mt-8 flex justify-center gap-2">
                                {listings.links.map((link, i) =>
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

function InfoItem({ label, value }: { label: string; value: string }) {
    return (
        <div>
            <dt className="font-medium">{label}</dt>
            <dd className="mt-0.5 text-muted-foreground">{value}</dd>
        </div>
    );
}
