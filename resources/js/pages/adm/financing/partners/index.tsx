import { Head, Link, router, useForm } from '@inertiajs/react';
import { FormEvent, useState } from 'react';

type Partner = {
    id: number;
    slug: string;
    company_name: string;
    license_number: string | null;
    contact_number: string;
    contact_email: string | null;
    region: string | null;
    status: string;
    status_label: string;
    logo_url: string | null;
    verified_at: string | null;
    rejected_reason: string | null;
    created_at: string | null;
    user: { name: string; email: string } | null;
};

type Paginated = {
    data: Partner[];
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
};

const statusColors: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-700',
    verified: 'bg-green-100 text-green-700',
    rejected: 'bg-red-100 text-red-700',
};

export default function AdminFinancingPartners({
    partners,
    filters,
}: {
    partners: Paginated;
    filters: { q: string; status: string };
}) {
    const [q, setQ] = useState(filters.q);
    const [rejectingId, setRejectingId] = useState<number | null>(null);
    const { data, setData, post, processing, reset } = useForm({ reason: '' });

    function search(e: FormEvent<HTMLFormElement>) {
        e.preventDefault();
        router.get(
            '/adm/financing/partners',
            { q, status: filters.status },
            { preserveState: true },
        );
    }

    function approve(partner: Partner) {
        if (!confirm(`Approve ${partner.company_name}?`)) return;
        router.post(
            `/adm/financing/partners/${partner.id}/approve`,
            {},
            { preserveScroll: true },
        );
    }

    function submitReject(e: FormEvent<HTMLFormElement>) {
        e.preventDefault();
        if (!rejectingId) return;
        post(`/adm/financing/partners/${rejectingId}/reject`, {
            onSuccess: () => {
                setRejectingId(null);
                reset();
            },
        });
    }

    return (
        <>
            <Head title="Financing Partners" />
            <div className="mx-auto w-full max-w-6xl p-4">
                <div className="mb-6">
                    <h1 className="text-2xl font-semibold tracking-normal">
                        Financing Partner Management
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        {partners.total} total partners
                    </p>
                </div>

                <div className="mb-6 flex flex-wrap gap-3">
                    <form onSubmit={search} className="flex gap-2">
                        <input
                            type="text"
                            value={q}
                            onChange={(e) => setQ(e.target.value)}
                            placeholder="Search..."
                            className="h-9 min-w-48 rounded-md border bg-background px-3 text-sm"
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
                                        '/adm/financing/partners',
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
                                Reject Financing Partner
                            </h3>
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
                                    'Company',
                                    'User',
                                    'License No.',
                                    'Region',
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
                            {partners.data.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={7}
                                        className="p-6 text-center text-muted-foreground"
                                    >
                                        No financing partners found.
                                    </td>
                                </tr>
                            ) : (
                                partners.data.map((partner) => (
                                    <tr
                                        key={partner.id}
                                        className="hover:bg-muted/30"
                                    >
                                        <td className="p-3">
                                            <div className="flex items-center gap-3">
                                                <div className="h-8 w-8 shrink-0 overflow-hidden rounded-full border bg-muted">
                                                    {partner.logo_url ? (
                                                        <img
                                                            src={
                                                                partner.logo_url
                                                            }
                                                            alt=""
                                                            className="h-full w-full object-cover"
                                                        />
                                                    ) : (
                                                        <div className="flex h-full w-full items-center justify-center text-xs font-bold text-muted-foreground">
                                                            {partner.company_name.charAt(
                                                                0,
                                                            )}
                                                        </div>
                                                    )}
                                                </div>
                                                <div>
                                                    <p className="font-medium">
                                                        {partner.company_name}
                                                    </p>
                                                    <p className="text-xs text-muted-foreground">
                                                        {partner.contact_number}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td className="p-3">
                                            <p>{partner.user?.name}</p>
                                            <p className="text-xs text-muted-foreground">
                                                {partner.user?.email}
                                            </p>
                                        </td>
                                        <td className="p-3 text-muted-foreground">
                                            {partner.license_number ?? '—'}
                                        </td>
                                        <td className="p-3 text-muted-foreground">
                                            {partner.region ?? '—'}
                                        </td>
                                        <td className="p-3">
                                            <span
                                                className={`rounded-full px-2 py-0.5 text-xs font-medium ${statusColors[partner.status] ?? 'bg-muted'}`}
                                            >
                                                {partner.status_label}
                                            </span>
                                        </td>
                                        <td className="p-3 text-xs whitespace-nowrap text-muted-foreground">
                                            {partner.created_at
                                                ? new Date(
                                                      partner.created_at,
                                                  ).toLocaleDateString()
                                                : '—'}
                                        </td>
                                        <td className="p-3">
                                            <div className="flex gap-2">
                                                {partner.status ===
                                                    'pending' && (
                                                    <>
                                                        <button
                                                            onClick={() =>
                                                                approve(partner)
                                                            }
                                                            className="rounded bg-green-600 px-2 py-1 text-xs font-medium text-white hover:bg-green-700"
                                                        >
                                                            Approve
                                                        </button>
                                                        <button
                                                            onClick={() =>
                                                                setRejectingId(
                                                                    partner.id,
                                                                )
                                                            }
                                                            className="rounded bg-red-600 px-2 py-1 text-xs font-medium text-white hover:bg-red-700"
                                                        >
                                                            Reject
                                                        </button>
                                                    </>
                                                )}
                                                {partner.status ===
                                                    'verified' && (
                                                    <Link
                                                        href={`/financing/${partner.slug}`}
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

                {partners.links && partners.links.length > 3 && (
                    <div className="mt-6 flex justify-center gap-2">
                        {partners.links.map((link, i) =>
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

AdminFinancingPartners.layout = {
    breadcrumbs: [
        { title: 'Financing Partners', href: '/adm/financing/partners' },
    ],
};
