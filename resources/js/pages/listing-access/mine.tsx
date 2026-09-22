import { Head, Link } from '@inertiajs/react';

type AccessRequest = {
    id: number;
    status: string;
    status_label: string;
    message: string | null;
    rejection_reason: string | null;
    expires_at: string | null;
    listing: { id: number; title: string; slug: string } | null;
    created_at: string | null;
};

const statusColors: Record<string, string> = {
    approved: 'bg-emerald-100 text-emerald-700',
    pending: 'bg-amber-100 text-amber-700',
    rejected: 'bg-red-100 text-red-700',
    revoked: 'bg-red-100 text-red-700',
    expired: 'bg-zinc-200 text-zinc-700',
};

export default function MyListingAccessRequests({
    requests,
}: {
    requests: AccessRequest[];
}) {
    return (
        <>
            <Head title="My Access Requests" />
            <div className="mx-auto max-w-2xl p-4">
                <h1 className="text-2xl font-semibold">
                    My Restricted Listing Access Requests
                </h1>
                <div className="mt-6 grid gap-3">
                    {requests.map((request) => (
                        <div key={request.id} className="rounded-lg border p-4">
                            <div className="flex items-center justify-between gap-2">
                                <p className="font-medium">
                                    {request.listing ? (
                                        <Link
                                            href={`/listings/${request.listing.slug}`}
                                            className="hover:underline"
                                        >
                                            {request.listing.title}
                                        </Link>
                                    ) : (
                                        'Listing no longer available'
                                    )}
                                </p>
                                <span
                                    className={`rounded-full px-2.5 py-0.5 text-xs font-semibold ${statusColors[request.status] ?? 'bg-zinc-100 text-zinc-700'}`}
                                >
                                    {request.status_label}
                                </span>
                            </div>
                            {request.rejection_reason && (
                                <p className="mt-2 text-xs text-destructive">
                                    {request.rejection_reason}
                                </p>
                            )}
                            {request.expires_at && (
                                <p className="mt-2 text-xs text-muted-foreground">
                                    Expires{' '}
                                    {new Date(
                                        request.expires_at,
                                    ).toLocaleDateString()}
                                </p>
                            )}
                        </div>
                    ))}
                    {requests.length === 0 && (
                        <p className="text-sm text-muted-foreground">
                            No access requests yet.
                        </p>
                    )}
                </div>
            </div>
        </>
    );
}

MyListingAccessRequests.layout = {
    breadcrumbs: [{ title: 'My Access Requests', href: '/listing-access/mine' }],
};
