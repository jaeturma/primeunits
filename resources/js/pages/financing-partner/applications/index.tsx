import { Head, Link, router, useForm } from '@inertiajs/react';
import { FormEvent, useState } from 'react';

type Application = {
    id: number;
    reference_code: string;
    full_name: string;
    contact_number: string;
    employment_type: string;
    unit_price: string;
    requested_amount: string;
    preferred_term_months: number;
    status: string;
    status_label: string;
    product_name: string | null;
    submitted_at: string | null;
};

type Paginated = {
    data: Application[];
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
};

const statusColors: Record<string, string> = {
    submitted: 'bg-blue-100 text-blue-700',
    under_review: 'bg-yellow-100 text-yellow-700',
    approved: 'bg-green-100 text-green-700',
    rejected: 'bg-red-100 text-red-700',
};

export default function PartnerApplications({
    applications,
    filters,
}: {
    applications: Paginated;
    filters: { status: string };
}) {
    const [reviewingId, setReviewingId] = useState<number | null>(null);
    const { data, setData, patch, processing, reset } = useForm({
        status: '',
        reviewer_notes: '',
    });

    function submitReview(e: FormEvent<HTMLFormElement>) {
        e.preventDefault();
        if (!reviewingId) return;
        patch(`/financing-partner/applications/${reviewingId}/review`, {
            onSuccess: () => {
                setReviewingId(null);
                reset();
            },
        });
    }

    return (
        <>
            <Head title="Applications" />
            <div className="mx-auto w-full max-w-5xl p-4">
                <div className="mb-6">
                    <h1 className="text-2xl font-semibold tracking-normal">
                        Financing Applications
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        {applications.total} total
                    </p>
                </div>

                <div className="mb-4 flex flex-wrap gap-2">
                    {(
                        [
                            '',
                            'submitted',
                            'under_review',
                            'approved',
                            'rejected',
                        ] as const
                    ).map((s) => (
                        <button
                            key={s}
                            onClick={() =>
                                router.get(
                                    '/financing-partner/applications',
                                    { status: s },
                                    { preserveState: true },
                                )
                            }
                            className={`h-8 rounded-md border px-3 text-xs ${filters.status === s ? 'bg-primary text-primary-foreground' : 'hover:bg-muted'}`}
                        >
                            {s === ''
                                ? 'All'
                                : s
                                      .replace('_', ' ')
                                      .replace(/\b\w/g, (c) => c.toUpperCase())}
                        </button>
                    ))}
                </div>

                {reviewingId && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                        <div className="w-full max-w-md rounded-lg bg-background p-6 shadow-xl">
                            <h3 className="font-medium">
                                Update Application Status
                            </h3>
                            <form
                                onSubmit={submitReview}
                                className="mt-4 grid gap-3"
                            >
                                <label className="grid gap-2 text-sm">
                                    <span>
                                        New Status{' '}
                                        <span className="text-destructive">
                                            *
                                        </span>
                                    </span>
                                    <select
                                        value={data.status}
                                        onChange={(e) =>
                                            setData('status', e.target.value)
                                        }
                                        required
                                        className="h-10 rounded-md border bg-background px-3 text-sm"
                                    >
                                        <option value="">Select status</option>
                                        <option value="under_review">
                                            Under Review
                                        </option>
                                        <option value="approved">
                                            Approved
                                        </option>
                                        <option value="rejected">
                                            Rejected
                                        </option>
                                    </select>
                                </label>
                                <label className="grid gap-2 text-sm">
                                    <span>Notes for Applicant</span>
                                    <textarea
                                        value={data.reviewer_notes}
                                        onChange={(e) =>
                                            setData(
                                                'reviewer_notes',
                                                e.target.value,
                                            )
                                        }
                                        className="min-h-20 rounded-md border bg-background px-3 py-2 text-sm"
                                    />
                                </label>
                                <div className="flex gap-2">
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="h-9 rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground disabled:opacity-50"
                                    >
                                        Update
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => {
                                            setReviewingId(null);
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

                {applications.data.length === 0 ? (
                    <div className="rounded-lg border border-dashed p-12 text-center">
                        <p className="text-sm text-muted-foreground">
                            No applications found.
                        </p>
                    </div>
                ) : (
                    <div className="overflow-x-auto rounded-lg border">
                        <table className="w-full text-sm">
                            <thead className="bg-muted/50">
                                <tr>
                                    {[
                                        'Reference',
                                        'Applicant',
                                        'Product',
                                        'Amount / Term',
                                        'Status',
                                        'Submitted',
                                        'Action',
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
                                {applications.data.map((app) => (
                                    <tr
                                        key={app.id}
                                        className="hover:bg-muted/30"
                                    >
                                        <td className="p-3 font-mono text-xs">
                                            {app.reference_code}
                                        </td>
                                        <td className="p-3">
                                            <p className="font-medium">
                                                {app.full_name}
                                            </p>
                                            <p className="text-xs text-muted-foreground">
                                                {app.contact_number}
                                            </p>
                                        </td>
                                        <td className="p-3 text-muted-foreground">
                                            {app.product_name}
                                        </td>
                                        <td className="p-3">
                                            <p>
                                                PHP{' '}
                                                {Number(
                                                    app.requested_amount,
                                                ).toLocaleString()}
                                            </p>
                                            <p className="text-xs text-muted-foreground">
                                                {app.preferred_term_months}{' '}
                                                months
                                            </p>
                                        </td>
                                        <td className="p-3">
                                            <span
                                                className={`rounded-full px-2 py-0.5 text-xs font-medium ${statusColors[app.status] ?? 'bg-muted'}`}
                                            >
                                                {app.status_label}
                                            </span>
                                        </td>
                                        <td className="p-3 text-xs whitespace-nowrap text-muted-foreground">
                                            {app.submitted_at
                                                ? new Date(
                                                      app.submitted_at,
                                                  ).toLocaleDateString()
                                                : '—'}
                                        </td>
                                        <td className="p-3">
                                            {app.status !== 'approved' &&
                                                app.status !== 'rejected' && (
                                                    <button
                                                        onClick={() => {
                                                            setReviewingId(
                                                                app.id,
                                                            );
                                                            setData((d) => ({
                                                                ...d,
                                                                status: '',
                                                                reviewer_notes:
                                                                    '',
                                                            }));
                                                        }}
                                                        className="rounded bg-primary px-2 py-1 text-xs font-medium text-primary-foreground hover:bg-primary/90"
                                                    >
                                                        Review
                                                    </button>
                                                )}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}

                {applications.links && applications.links.length > 3 && (
                    <div className="mt-6 flex justify-center gap-2">
                        {applications.links.map((link, i) =>
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

PartnerApplications.layout = {
    breadcrumbs: [
        { title: 'Partner Status', href: '/financing-partner/status' },
        { title: 'Applications', href: '/financing-partner/applications' },
    ],
};
