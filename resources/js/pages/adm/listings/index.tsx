import { FormEvent, useState } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';

type Listing = {
    id: number;
    title: string;
    price: string;
    condition: string;
    brand: string | null;
    model: string | null;
    status_label: string;
    rejected_reason: string | null;
    image_url: string | null;
    can_approve: boolean;
    action_label: string | null;
    can_reject: boolean;
    category: { name: string };
    seller: { name: string; email: string };
    documents: Array<{ label: string; url: string }>;
};

type StatusOption = {
    value: string;
    label: string;
};

export default function AdminListings({
    listings,
    filters,
    statuses,
}: {
    listings: Listing[];
    filters: { status: string };
    statuses: StatusOption[];
}) {
    const [rejectingId, setRejectingId] = useState<number | null>(null);
    const { data, setData, post, processing, errors, reset } = useForm({
        rejected_reason: '',
    });

    function filter(status: string) {
        router.get(
            '/adm/listings',
            { status: status || undefined },
            { preserveState: true, replace: true },
        );
    }

    function approve(listing: Listing) {
        router.post(`/adm/listings/${listing.id}/approve`, undefined, {
            preserveScroll: true,
        });
    }

    function reject(event: FormEvent<HTMLFormElement>, listing: Listing) {
        event.preventDefault();
        post(`/adm/listings/${listing.id}/reject`, {
            preserveScroll: true,
            onSuccess: () => {
                reset();
                setRejectingId(null);
            },
        });
    }

    return (
        <>
            <Head title="Listing Review" />
            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div className="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-normal">
                            Listing Review
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Approve listings before they become public.
                        </p>
                    </div>
                    <label className="grid gap-2 text-sm">
                        <span className="font-medium">Status</span>
                        <select
                            value={filters.status}
                            onChange={(event) => filter(event.target.value)}
                            className="h-10 rounded-md border bg-background px-3 text-sm"
                        >
                            {statuses.map((status) => (
                                <option key={status.value} value={status.value}>
                                    {status.label}
                                </option>
                            ))}
                        </select>
                    </label>
                </div>

                <div className="grid gap-4">
                    {listings.map((listing) => (
                        <article
                            key={listing.id}
                            className="rounded-lg border p-4"
                        >
                            <div className="flex flex-wrap gap-4">
                                <div className="h-28 w-40 overflow-hidden rounded-md bg-muted">
                                    {listing.image_url && (
                                        <img
                                            src={listing.image_url}
                                            alt=""
                                            className="h-full w-full object-cover"
                                        />
                                    )}
                                </div>
                                <div className="min-w-0 flex-1">
                                    <div className="flex flex-wrap items-center gap-2">
                                        <h2 className="font-medium">
                                            {listing.title}
                                        </h2>
                                        <Badge variant="secondary">
                                            {listing.status_label}
                                        </Badge>
                                    </div>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        {listing.category.name} ·{' '}
                                        {listing.seller.name} ·{' '}
                                        {listing.seller.email}
                                    </p>
                                    <p className="mt-2 text-sm">
                                        PHP{' '}
                                        {Number(listing.price).toLocaleString()}
                                    </p>
                                    {listing.rejected_reason && (
                                        <p className="mt-2 text-sm text-destructive">
                                            {listing.rejected_reason}
                                        </p>
                                    )}
                                    <div className="mt-2 flex flex-wrap gap-2">{listing.documents.map(document => <a key={document.url} href={document.url} target="_blank" rel="noreferrer" className="text-xs text-primary underline">{document.label}</a>)}</div>
                                </div>
                                <div className="flex gap-2">
                                    {listing.can_approve && (
                                        <button
                                            type="button"
                                            onClick={() => approve(listing)}
                                            className="h-9 rounded-md bg-primary px-3 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                                        >
                                            {listing.action_label}
                                        </button>
                                    )}
                                    {listing.can_reject && (
                                        <button
                                            type="button"
                                            onClick={() =>
                                                setRejectingId(listing.id)
                                            }
                                            className="h-9 rounded-md border px-3 text-sm font-medium hover:bg-accent"
                                        >
                                            Reject
                                        </button>
                                    )}
                                </div>
                            </div>

                            {rejectingId === listing.id && (
                                <form
                                    onSubmit={(event) => reject(event, listing)}
                                    className="mt-4 grid gap-3"
                                >
                                    <textarea
                                        value={data.rejected_reason}
                                        onChange={(event) =>
                                            setData(
                                                'rejected_reason',
                                                event.target.value,
                                            )
                                        }
                                        className="min-h-24 rounded-md border bg-background px-3 py-2 text-sm"
                                        placeholder="Reason for rejection"
                                    />
                                    {errors.rejected_reason && (
                                        <p className="text-xs text-destructive">
                                            {errors.rejected_reason}
                                        </p>
                                    )}
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="h-9 w-fit rounded-md bg-destructive px-3 text-sm font-medium text-white disabled:opacity-50"
                                    >
                                        Submit rejection
                                    </button>
                                </form>
                            )}
                        </article>
                    ))}
                </div>
            </div>
        </>
    );
}

AdminListings.layout = {
    breadcrumbs: [
        {
            title: 'Listing Review',
            href: '/adm/listings',
        },
    ],
};
