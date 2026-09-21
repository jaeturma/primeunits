import { Head, Link } from '@inertiajs/react';
import { PublicFooter } from '@/components/public-footer';
import { PublicHeader } from '@/components/public-header';

type Seller = {
    id: number;
    seller_type: string;
    business_name: string | null;
    owner_name: string | null;
    contact_number: string;
    email: string | null;
    region: string | null;
    province: string | null;
    municipality: string | null;
    verified_at: string | null;
};

type Listing = {
    id: number;
    title: string;
    price: string;
    condition: string;
    brand: string | null;
    model: string | null;
    is_featured: boolean;
    category: { name: string } | null;
    image_url: string;
};

type PaginatedListings = {
    data: Listing[];
    current_page: number;
    last_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
};

export default function SellerShow({
    seller,
    listings,
}: {
    seller: Seller;
    listings: PaginatedListings;
}) {
    const displayName = seller.business_name ?? seller.owner_name ?? 'Seller';

    return (
        <>
            <Head title={displayName} />
            <PublicHeader />
            <div className="mx-auto w-full max-w-6xl p-4">
                <div className="mb-8 rounded-lg border p-6">
                    <div className="flex items-start gap-4">
                        <div className="flex h-16 w-16 items-center justify-center rounded-full bg-primary/10 text-2xl font-bold text-primary">
                            {displayName.charAt(0).toUpperCase()}
                        </div>
                        <div className="flex-1">
                            <div className="flex items-center gap-2">
                                <h1 className="text-2xl font-semibold tracking-normal">
                                    {displayName}
                                </h1>
                                <span className="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">
                                    Verified
                                </span>
                            </div>
                            <p className="mt-1 text-sm text-muted-foreground capitalize">
                                {seller.seller_type} Seller
                            </p>
                            {seller.verified_at && (
                                <p className="mt-1 text-xs text-muted-foreground">
                                    Member since {seller.verified_at}
                                </p>
                            )}
                            <dl className="mt-4 grid gap-2 text-sm sm:grid-cols-2">
                                {[
                                    seller.municipality,
                                    seller.province,
                                    seller.region,
                                ].filter(Boolean).length > 0 && (
                                    <div>
                                        <dt className="font-medium">
                                            Location
                                        </dt>
                                        <dd className="text-muted-foreground">
                                            {[
                                                seller.municipality,
                                                seller.province,
                                                seller.region,
                                            ]
                                                .filter(Boolean)
                                                .join(', ')}
                                        </dd>
                                    </div>
                                )}
                                <div>
                                    <dt className="font-medium">Listings</dt>
                                    <dd className="text-muted-foreground">
                                        {listings.total} active
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

                <div className="mb-6">
                    <h2 className="text-lg font-semibold">
                        Active Listings ({listings.total})
                    </h2>
                </div>

                {listings.data.length === 0 ? (
                    <div className="rounded-lg border border-dashed p-12 text-center">
                        <p className="text-sm text-muted-foreground">
                            No active listings.
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
