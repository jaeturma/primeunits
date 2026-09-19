import { Head, Link, router, useForm } from '@inertiajs/react';
import { FormEvent, useState } from 'react';

type Dealer = {
    id: number;
    slug: string;
    business_name: string;
    contact_number: string;
    email: string | null;
    status: string;
    status_label: string;
    region: string | null;
    accreditation_number: string | null;
    user: { id: number; name: string; email: string } | null;
    verified_at: string | null;
    rejected_reason: string | null;
    created_at: string | null;
    action_label: string | null;
    documents: Array<{ label: string; url: string }>;
};

type PaginatedDealers = {
    data: Dealer[];
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
};

export default function AdminDealers({
    dealers,
    filters,
}: {
    dealers: PaginatedDealers;
    filters: { status: string; q: string };
}) {
    const [q, setQ] = useState(filters.q);
    const [rejectingId, setRejectingId] = useState<number | null>(null);
    const { data, setData, post, processing, reset } = useForm({ reason: '' });

    function search(e: FormEvent<HTMLFormElement>) {
        e.preventDefault();
        router.get(
            '/adm/dealers',
            { q, status: filters.status },
            { preserveState: true },
        );
    }

    function approve(dealer: Dealer) {
        if (!confirm(`Approve ${dealer.business_name}?`)) return;
        router.post(
            `/adm/dealers/${dealer.id}/approve`,
            {},
            { preserveState: true },
        );
    }

    function submitReject(e: FormEvent<HTMLFormElement>) {
        e.preventDefault();
        if (!rejectingId) return;
        post(`/adm/dealers/${rejectingId}/reject`, {
            onSuccess: () => {
                setRejectingId(null);
                reset();
            },
        });
    }

    return (
        <>
            <Head title="Manage Dealers" />
            <div className="mx-auto w-full max-w-6xl p-4">
                <div className="mb-6">
                    <h1 className="text-2xl font-semibold tracking-normal">
                        Dealer Management
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        {dealers.total} total dealers
                    </p>
                </div>

                <div className="mb-6 flex flex-wrap gap-3">
                    <form onSubmit={search} className="flex gap-2">
                        <input
                            type="text"
                            value={q}
                            onChange={(e) => setQ(e.target.value)}
                            placeholder="Search dealers..."
                            className="h-9 rounded-md border bg-background px-3 text-sm"
                        />
                        <button
                            type="submit"
                            className="h-9 rounded-md bg-primary px-3 text-sm font-medium text-primary-foreground"
                        >
                            Search
                        </button>
                    </form>
                    {(['', 'pending', 'verified', 'rejected'] as const).map(
                        (s) => (
                            <button
                                key={s}
                                onClick={() =>
                                    router.get(
                                        '/adm/dealers',
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
                            <h3 className="font-medium">
                                Reject Dealer Application
                            </h3>
                            <form
                                onSubmit={submitReject}
                                className="mt-4 grid gap-3"
                            >
                                <label className="grid gap-2 text-sm">
                                    <span>Rejection reason</span>
                                    <textarea
                                        value={data.reason}
                                        onChange={(e) =>
                                            setData('reason', e.target.value)
                                        }
                                        required
                                        className="min-h-24 rounded-md border bg-background px-3 py-2 text-sm"
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
                                    'Business',
                                    'User',
                                    'Location',
                                    'Accreditation',
                                    'Status',
                                    'Submitted',
                                    'Actions',
                                ].map((h) => (
                                    <th
                                        key={h}
                                        className="p-3 text-left font-medium"
                                    >
                                        {h}
                                    </th>
                                ))}
                            </tr>
                        </thead>
                        <tbody className="divide-y">
                            {dealers.data.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={7}
                                        className="p-6 text-center text-muted-foreground"
                                    >
                                        No dealers found.
                                    </td>
                                </tr>
                            ) : (
                                dealers.data.map((dealer) => (
                                    <tr
                                        key={dealer.id}
                                        className="hover:bg-muted/30"
                                    >
                                        <td className="p-3">
                                            <p className="font-medium">
                                                {dealer.business_name}
                                            </p>
                                            <p className="text-xs text-muted-foreground">
                                                {dealer.contact_number}
                                            </p>
                                        </td>
                                        <td className="p-3">
                                            <p>{dealer.user?.name}</p>
                                            <p className="text-xs text-muted-foreground">
                                                {dealer.user?.email}
                                            </p>
                                        </td>
                                        <td className="p-3 text-muted-foreground">
                                            {dealer.region}
                                        </td>
                                        <td className="p-3 text-muted-foreground">
                                            <p>{dealer.accreditation_number ?? '—'}</p>
                                            <div className="flex flex-col">{dealer.documents.map(document => <a key={document.url} href={document.url} target="_blank" rel="noreferrer" className="text-xs text-primary underline">{document.label}</a>)}</div>
                                        </td>
                                        <td className="p-3">
                                            <StatusBadge
                                                status={dealer.status}
                                                label={dealer.status_label}
                                            />
                                        </td>
                                        <td className="p-3 whitespace-nowrap text-muted-foreground">
                                            {dealer.created_at
                                                ? new Date(
                                                      dealer.created_at,
                                                  ).toLocaleDateString()
                                                : '—'}
                                        </td>
                                        <td className="p-3">
                                            <div className="flex gap-2">
                                                {dealer.action_label && (
                                                    <>
                                                        <button
                                                            onClick={() =>
                                                                approve(dealer)
                                                            }
                                                            className="rounded bg-green-600 px-2 py-1 text-xs font-medium text-white hover:bg-green-700"
                                                        >
                                                            {dealer.action_label}
                                                        </button>
                                                        <button
                                                            onClick={() =>
                                                                setRejectingId(
                                                                    dealer.id,
                                                                )
                                                            }
                                                            className="rounded bg-red-600 px-2 py-1 text-xs font-medium text-white hover:bg-red-700"
                                                        >
                                                            Reject
                                                        </button>
                                                    </>
                                                )}
                                                {dealer.status ===
                                                    'verified' && (
                                                    <Link
                                                        href={`/dealers/${dealer.slug}`}
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

                {dealers.links && (
                    <div className="mt-6 flex justify-center gap-2">
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
            </div>
        </>
    );
}

function StatusBadge({ status, label }: { status: string; label: string }) {
    const colors: Record<string, string> = {
        pending: 'bg-yellow-100 text-yellow-700',
        verified: 'bg-green-100 text-green-700',
        rejected: 'bg-red-100 text-red-700',
    };
    return (
        <span
            className={`rounded-full px-2 py-0.5 text-xs font-medium ${colors[status] ?? 'bg-muted text-muted-foreground'}`}
        >
            {label}
        </span>
    );
}

AdminDealers.layout = {
    breadcrumbs: [{ title: 'Dealer Management', href: '/adm/dealers' }],
};
