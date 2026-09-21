import { Head, Link, router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import type { FormEvent } from 'react';

type RentalUnit = {
    id: number;
    slug: string;
    name: string;
    rental_type: string;
    rental_type_label: string;
    brand: string | null;
    model: string | null;
    price_per_day: string;
    status: string;
    region: string | null;
    municipality: string | null;
    views_count: number;
    image_url: string;
    provider: { name: string; email: string } | null;
    created_at: string | null;
    action_label: string | null;
    documents: Array<{ label: string; url: string }>;
};

type PaginatedUnits = {
    data: RentalUnit[];
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
};

export default function AdminRentals({
    units,
    filters,
}: {
    units: PaginatedUnits;
    filters: { status: string; q: string };
}) {
    const [q, setQ] = useState(filters.q);
    const [rejectingId, setRejectingId] = useState<number | null>(null);
    const { data, setData, post, processing, reset } = useForm({ reason: '' });

    function search(e: FormEvent<HTMLFormElement>) {
        e.preventDefault();
        router.get(
            '/adm/rentals',
            { q, status: filters.status },
            { preserveState: true },
        );
    }

    function approve(unit: RentalUnit) {
        if (!confirm(`Approve "${unit.name}"?`)) return;
        router.post(
            `/adm/rentals/${unit.id}/approve`,
            {},
            { preserveScroll: true },
        );
    }

    function submitReject(e: FormEvent<HTMLFormElement>) {
        e.preventDefault();
        if (!rejectingId) return;
        post(`/adm/rentals/${rejectingId}/reject`, {
            onSuccess: () => {
                setRejectingId(null);
                reset();
            },
        });
    }

    const statusColors: Record<string, string> = {
        pending: 'bg-yellow-100 text-yellow-700',
        approved: 'bg-green-100 text-green-700',
        rejected: 'bg-red-100 text-red-700',
    };

    return (
        <>
            <Head title="Manage Rentals" />
            <div className="mx-auto w-full max-w-6xl p-4">
                <div className="mb-6">
                    <h1 className="text-2xl font-semibold tracking-normal">
                        Rental Unit Management
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        {units.total} total units
                    </p>
                </div>

                <div className="mb-6 flex flex-wrap gap-3">
                    <form onSubmit={search} className="flex gap-2">
                        <input
                            type="text"
                            value={q}
                            onChange={(e) => setQ(e.target.value)}
                            placeholder="Search units or providers..."
                            className="h-9 min-w-48 rounded-md border bg-background px-3 text-sm"
                        />
                        <button
                            type="submit"
                            className="h-9 rounded-md bg-primary px-3 text-sm font-medium text-primary-foreground"
                        >
                            Search
                        </button>
                    </form>
                    {(['', 'pending', 'approved', 'rejected'] as const).map(
                        (s) => (
                            <button
                                key={s}
                                onClick={() =>
                                    router.get(
                                        '/adm/rentals',
                                        { status: s, q },
                                        { preserveState: true },
                                    )
                                }
                                className={`h-9 rounded-md border px-3 text-sm ${filters.status === s ? 'bg-primary text-primary-foreground' : 'hover:bg-muted'}`}
                            >
                                {s === ''
                                    ? 'All'
                                    : s.charAt(0).toUpperCase() + s.slice(1)}
                            </button>
                        ),
                    )}
                </div>

                {rejectingId && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                        <div className="w-full max-w-md rounded-lg bg-background p-6 shadow-xl">
                            <h3 className="font-medium">Reject Rental Unit</h3>
                            <form
                                onSubmit={submitReject}
                                className="mt-4 grid gap-3"
                            >
                                <label className="grid gap-2 text-sm">
                                    <span>
                                        Rejection reason{' '}
                                        <span className="text-destructive">
                                            *
                                        </span>
                                    </span>
                                    <textarea
                                        value={data.reason}
                                        onChange={(e) =>
                                            setData('reason', e.target.value)
                                        }
                                        required
                                        className="min-h-24 rounded-md border bg-background px-3 py-2 text-sm"
                                        placeholder="Explain why this unit is rejected..."
                                    />
                                </label>
                                <div className="flex gap-2">
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="h-9 rounded-md bg-destructive px-4 text-sm font-medium text-destructive-foreground disabled:opacity-50"
                                    >
                                        Reject
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => {
                                            setRejectingId(null);
                                            reset();
                                        }}
                                        className="h-9 rounded-md border px-4 text-sm"
                                    >
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                )}

                <div className="overflow-x-auto rounded-lg border">
                    <table className="w-full text-sm">
                        <thead className="bg-muted/50">
                            <tr>
                                {[
                                    'Unit',
                                    'Provider',
                                    'Type',
                                    'Price/Day',
                                    'Status',
                                    'Submitted',
                                    'Actions',
                                ].map((h) => (
                                    <th
                                        key={h}
                                        className="p-3 text-left font-medium whitespace-nowrap"
                                    >
                                        {h}
                                    </th>
                                ))}
                            </tr>
                        </thead>
                        <tbody className="divide-y">
                            {units.data.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={7}
                                        className="p-6 text-center text-muted-foreground"
                                    >
                                        No rental units found.
                                    </td>
                                </tr>
                            ) : (
                                units.data.map((unit) => (
                                    <tr
                                        key={unit.id}
                                        className="hover:bg-muted/30"
                                    >
                                        <td className="p-3">
                                            <div className="flex items-center gap-3">
                                                <div className="h-10 w-14 shrink-0 overflow-hidden rounded bg-muted">
                                                    <img
                                                        src={unit.image_url}
                                                        alt=""
                                                        className="h-full w-full object-cover"
                                                    />
                                                </div>
                                                <div>
                                                    <p className="font-medium">
                                                        {unit.name}
                                                    </p>
                                                    {(unit.brand ||
                                                        unit.model) && (
                                                        <p className="text-xs text-muted-foreground">
                                                            {[
                                                                unit.brand,
                                                                unit.model,
                                                            ]
                                                                .filter(Boolean)
                                                                .join(' ')}
                                                        </p>
                                                    )}
                                                </div>
                                            </div>
                                        </td>
                                        <td className="p-3">
                                            <p>{unit.provider?.name}</p>
                                            <p className="text-xs text-muted-foreground">
                                                {unit.provider?.email}
                                            </p>
                                            <div className="flex flex-col">{unit.documents.map(document => <a key={document.url} href={document.url} target="_blank" rel="noreferrer" className="text-xs text-primary underline">{document.label}</a>)}</div>
                                        </td>
                                        <td className="p-3 whitespace-nowrap text-muted-foreground">
                                            {unit.rental_type_label}
                                        </td>
                                        <td className="p-3 font-medium">
                                            PHP{' '}
                                            {Number(
                                                unit.price_per_day,
                                            ).toLocaleString()}
                                        </td>
                                        <td className="p-3">
                                            <span
                                                className={`rounded-full px-2 py-0.5 text-xs font-medium ${statusColors[unit.status] ?? 'bg-muted'}`}
                                            >
                                                {unit.status
                                                    .charAt(0)
                                                    .toUpperCase() +
                                                    unit.status.slice(1)}
                                            </span>
                                        </td>
                                        <td className="p-3 whitespace-nowrap text-muted-foreground">
                                            {unit.created_at
                                                ? new Date(
                                                      unit.created_at,
                                                  ).toLocaleDateString()
                                                : '—'}
                                        </td>
                                        <td className="p-3">
                                            <div className="flex gap-2">
                                                {unit.action_label && (
                                                    <>
                                                        <button
                                                            onClick={() =>
                                                                approve(unit)
                                                            }
                                                            className="rounded bg-green-600 px-2 py-1 text-xs font-medium text-white hover:bg-green-700"
                                                        >
                                                            {unit.action_label}
                                                        </button>
                                                        <button
                                                            onClick={() =>
                                                                setRejectingId(
                                                                    unit.id,
                                                                )
                                                            }
                                                            className="rounded bg-red-600 px-2 py-1 text-xs font-medium text-white hover:bg-red-700"
                                                        >
                                                            Reject
                                                        </button>
                                                    </>
                                                )}
                                                {unit.status === 'approved' && (
                                                    <Link
                                                        href={`/rentals/${unit.slug}`}
                                                        className="rounded bg-muted px-2 py-1 text-xs hover:bg-muted/80"
                                                    >
                                                        View
                                                    </Link>
                                                )}
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                {units.links && units.links.length > 3 && (
                    <div className="mt-6 flex justify-center gap-2">
                        {units.links.map((link, i) =>
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

AdminRentals.layout = {
    breadcrumbs: [{ title: 'Rental Management', href: '/adm/rentals' }],
};
