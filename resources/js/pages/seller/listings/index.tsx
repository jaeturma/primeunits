import { Head, Link, router } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';

type ListingCard = {
    id: number;
    title: string;
    price: string;
    condition: string;
    brand: string | null;
    model: string | null;
    status_label: string;
    rejected_reason: string | null;
    image_url: string;
    category: { name: string };
};

export default function SellerListings({
    listings,
    managementPath,
}: {
    listings: ListingCard[];
    managementPath: string;
}) {
    return (
        <>
            <Head title="My Listings" />
            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div className="flex items-center justify-between gap-3">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-normal">
                            My Listings
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Track review status for your submitted listings.
                        </p>
                    </div>
                    <Link
                        href={`${managementPath}/create`}
                        className="h-9 rounded-md bg-primary px-3 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                    >
                        New listing
                    </Link>
                </div>

                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    {listings.map((listing) => (
                        <article key={listing.id} className="rounded-lg border">
                            <div className="aspect-video overflow-hidden rounded-t-lg bg-muted">
                                <img
                                    src={listing.image_url}
                                    alt=""
                                    className="h-full w-full object-cover"
                                />
                            </div>
                            <div className="space-y-3 p-4">
                                <div className="flex items-start justify-between gap-2">
                                    <h2 className="font-medium">
                                        {listing.title}
                                    </h2>
                                    <Badge variant="secondary">
                                        {listing.status_label}
                                    </Badge>
                                </div>
                                <p className="text-sm">
                                    PHP {Number(listing.price).toLocaleString()}
                                </p>
                                <p className="text-sm text-muted-foreground">
                                    {listing.category.name} ·{' '}
                                    {[listing.brand, listing.model]
                                        .filter(Boolean)
                                        .join(' ')}
                                </p>
                                {listing.rejected_reason && (
                                    <p className="text-sm text-destructive">
                                        {listing.rejected_reason}
                                    </p>
                                )}
                                <div className="flex gap-2 border-t pt-3">
                                    <Link
                                        href={`${managementPath}/${listing.id}/edit`}
                                        className="rounded-md border px-3 py-1.5 text-xs font-medium hover:bg-muted"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        type="button"
                                        onClick={() => {
                                            if (confirm('Delete this unit?')) {
                                                router.delete(`${managementPath}/${listing.id}`);
                                            }
                                        }}
                                        className="rounded-md border border-destructive/30 px-3 py-1.5 text-xs font-medium text-destructive hover:bg-destructive/10"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </article>
                    ))}
                </div>
            </div>
        </>
    );
}

SellerListings.layout = {
    breadcrumbs: [
        {
            title: 'My Listings',
            href: '/seller/listings',
        },
    ],
};
