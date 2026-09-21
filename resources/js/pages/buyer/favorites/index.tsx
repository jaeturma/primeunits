import { Head, Link } from '@inertiajs/react';

type Listing = {
    id: number;
    title: string;
    price: string;
    negotiable: boolean;
    condition: string;
    brand: string | null;
    model: string | null;
    category: { name: string } | null;
    image_url: string;
};

export default function Favorites({ favorites }: { favorites: Listing[] }) {
    return (
        <>
            <Head title="My Favorites" />
            <div className="mx-auto w-full max-w-5xl p-4">
                <div className="mb-6">
                    <h1 className="text-2xl font-semibold tracking-normal">
                        My Favorites
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        {favorites.length} saved listing
                        {favorites.length !== 1 ? 's' : ''}
                    </p>
                </div>

                {favorites.length === 0 ? (
                    <div className="rounded-lg border border-dashed p-12 text-center">
                        <p className="text-sm text-muted-foreground">
                            No saved listings yet.
                        </p>
                        <Link
                            href="/listings"
                            className="mt-3 inline-block text-sm font-medium text-primary hover:underline"
                        >
                            Browse listings
                        </Link>
                    </div>
                ) : (
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {favorites.map((listing) => (
                            <Link
                                key={listing.id}
                                href={`/listings/${listing.id}`}
                                className="group rounded-lg border bg-card transition-shadow hover:shadow-md"
                            >
                                <div className="aspect-video overflow-hidden rounded-t-lg bg-muted">
                                    <img
                                        src={listing.image_url}
                                        alt={listing.title}
                                        className="h-full w-full object-cover transition-transform group-hover:scale-105"
                                    />
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
                                        {Number(listing.price).toLocaleString()}
                                    </p>
                                    <div className="mt-2 flex flex-wrap gap-2 text-xs text-muted-foreground">
                                        <span className="rounded-full bg-muted px-2 py-0.5">
                                            {listing.condition}
                                        </span>
                                        {listing.negotiable && (
                                            <span className="rounded-full bg-muted px-2 py-0.5">
                                                Negotiable
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
                )}
            </div>
        </>
    );
}

Favorites.layout = {
    breadcrumbs: [{ title: 'My Favorites', href: '/buyer/favorites' }],
};
