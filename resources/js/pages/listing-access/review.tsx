import { Head, router } from '@inertiajs/react';

type AccessRequest = {
    id: number;
    status: string;
    status_label: string;
    message: string | null;
    listing: { id: number; title: string; slug: string } | null;
    user: { id: number; name: string; email: string } | null;
    created_at: string | null;
};

export default function ListingAccessReview({
    requests,
}: {
    requests: AccessRequest[];
}) {
    function approve(id: number) {
        router.post(`/listing-access/${id}/approve`);
    }

    function reject(id: number) {
        const reason = prompt('Rejection reason');
        if (reason) {
            router.post(`/listing-access/${id}/reject`, { reason });
        }
    }

    function revoke(id: number) {
        router.post(`/listing-access/${id}/revoke`);
    }

    return (
        <>
            <Head title="Restricted Listing Access Requests" />
            <div className="p-4">
                <h1 className="text-2xl font-semibold">
                    Restricted Listing Access Requests
                </h1>
                <div className="mt-6 grid gap-3">
                    {requests.map((request) => (
                        <div key={request.id} className="rounded-lg border p-4">
                            <div className="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p className="font-medium">
                                        {request.listing?.title ?? 'Listing'}{' '}
                                        — {request.user?.name}
                                    </p>
                                    <p className="text-sm text-muted-foreground">
                                        {request.user?.email} · {request.status_label}
                                    </p>
                                    {request.message && (
                                        <p className="mt-1 text-xs text-muted-foreground">
                                            "{request.message}"
                                        </p>
                                    )}
                                </div>
                                <div className="flex shrink-0 gap-2">
                                    {request.status === 'pending' && (
                                        <>
                                            <button
                                                onClick={() =>
                                                    approve(request.id)
                                                }
                                                className="rounded bg-primary px-3 py-2 text-sm text-primary-foreground"
                                            >
                                                Approve
                                            </button>
                                            <button
                                                onClick={() =>
                                                    reject(request.id)
                                                }
                                                className="rounded border px-3 py-2 text-sm"
                                            >
                                                Reject
                                            </button>
                                        </>
                                    )}
                                    {request.status === 'approved' && (
                                        <button
                                            onClick={() => revoke(request.id)}
                                            className="rounded border border-destructive px-3 py-2 text-sm text-destructive"
                                        >
                                            Revoke
                                        </button>
                                    )}
                                </div>
                            </div>
                        </div>
                    ))}
                    {requests.length === 0 && (
                        <p className="text-sm text-muted-foreground">
                            No access requests to review.
                        </p>
                    )}
                </div>
            </div>
        </>
    );
}

ListingAccessReview.layout = {
    breadcrumbs: [{ title: 'Access Requests', href: '/listing-access/review' }],
};
