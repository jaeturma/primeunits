import { Head, Link, router, useForm } from '@inertiajs/react';

type Booking = {
    id: number;
    reference_code: string;
    status: string;
    status_label: string;
    start_date: string | null;
    end_date: string | null;
    quoted_price: string | null;
    message: string | null;
    unit: { id: number; name: string } | null;
    renter: { id: number; name: string } | null;
    created_at: string | null;
};

type PaginatedBookings = {
    data: Booking[];
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
};

export default function ProviderBookings({
    bookings,
}: {
    bookings: PaginatedBookings;
}) {
    const statusColors: Record<string, string> = {
        inquiry: 'bg-blue-100 text-blue-700',
        confirmed: 'bg-green-100 text-green-700',
        active: 'bg-purple-100 text-purple-700',
        completed: 'bg-muted text-muted-foreground',
        cancelled: 'bg-red-100 text-red-700',
    };

    function updateStatus(booking: Booking, status: 'confirmed' | 'cancelled') {
        const label = status === 'confirmed' ? 'Confirm' : 'Cancel';
        if (!confirm(`${label} booking ${booking.reference_code}?`)) return;
        router.patch(
            `/rental-provider/bookings/${booking.id}/status`,
            { status },
            { preserveScroll: true },
        );
    }

    return (
        <>
            <Head title="Bookings" />
            <div className="mx-auto w-full max-w-5xl p-4">
                <div className="mb-6">
                    <h1 className="text-2xl font-semibold tracking-normal">
                        Booking Inquiries
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        {bookings.total} total bookings
                    </p>
                </div>

                {bookings.data.length === 0 ? (
                    <div className="rounded-lg border border-dashed p-12 text-center">
                        <p className="text-sm text-muted-foreground">
                            No booking inquiries yet.
                        </p>
                    </div>
                ) : (
                    <div className="grid gap-3">
                        {bookings.data.map((booking) => (
                            <div
                                key={booking.id}
                                className="rounded-lg border bg-card p-4"
                            >
                                <div className="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <div className="flex flex-wrap items-center gap-2">
                                            <span
                                                className={`rounded-full px-2 py-0.5 text-xs font-medium ${statusColors[booking.status] ?? 'bg-muted'}`}
                                            >
                                                {booking.status_label}
                                            </span>
                                            <span className="font-mono text-xs text-muted-foreground">
                                                {booking.reference_code}
                                            </span>
                                        </div>
                                        <p className="mt-1 font-medium">
                                            {booking.unit?.name}
                                        </p>
                                        <p className="text-sm text-muted-foreground">
                                            Renter: {booking.renter?.name}
                                        </p>
                                        {(booking.start_date ||
                                            booking.end_date) && (
                                            <p className="text-sm text-muted-foreground">
                                                {booking.start_date &&
                                                    new Date(
                                                        booking.start_date,
                                                    ).toLocaleDateString()}
                                                {booking.end_date &&
                                                    ` – ${new Date(booking.end_date).toLocaleDateString()}`}
                                            </p>
                                        )}
                                        {booking.message && (
                                            <p className="mt-2 text-sm text-muted-foreground italic">
                                                "{booking.message}"
                                            </p>
                                        )}
                                        {booking.created_at && (
                                            <p className="mt-1 text-xs text-muted-foreground">
                                                Received{' '}
                                                {new Date(
                                                    booking.created_at,
                                                ).toLocaleDateString()}
                                            </p>
                                        )}
                                    </div>
                                    {booking.status === 'inquiry' && (
                                        <div className="flex shrink-0 gap-2">
                                            <button
                                                onClick={() =>
                                                    updateStatus(
                                                        booking,
                                                        'confirmed',
                                                    )
                                                }
                                                className="rounded bg-green-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-green-700"
                                            >
                                                Confirm
                                            </button>
                                            <button
                                                onClick={() =>
                                                    updateStatus(
                                                        booking,
                                                        'cancelled',
                                                    )
                                                }
                                                className="rounded border px-3 py-1.5 text-xs font-medium hover:bg-muted"
                                            >
                                                Decline
                                            </button>
                                        </div>
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>
                )}

                {bookings.links && bookings.links.length > 3 && (
                    <div className="mt-6 flex justify-center gap-2">
                        {bookings.links.map((link, i) =>
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
            </div>
        </>
    );
}

ProviderBookings.layout = {
    breadcrumbs: [
        { title: 'Booking Inquiries', href: '/rental-provider/bookings' },
    ],
};
