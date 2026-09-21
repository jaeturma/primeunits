import { Head, router } from '@inertiajs/react';

type Credential = {
    id: number;
    user: { id: number; name: string; email: string } | null;
    credential_type: string;
    issuing_authority: string | null;
    masked_credential_number: string | null;
    country: string | null;
    issue_date: string | null;
    expiration_date: string | null;
    status: string;
    status_label: string;
    is_expired: boolean;
    reviewer: string | null;
    reviewed_at: string | null;
    has_front_document: boolean;
    has_back_document: boolean;
    has_supporting_document: boolean;
    created_at: string | null;
};

export default function DroneCredentialsIndex({
    credentials,
    statuses,
    filters,
}: {
    credentials: { data: Credential[] };
    statuses: string[];
    filters: { status: string };
}) {
    function documentLink(id: number, type: string) {
        return `/drone-credentials/${id}/documents/${type}`;
    }

    return (
        <>
            <Head title="Drone Pilot Credential Review" />
            <div className="p-4">
                <h1 className="text-2xl font-semibold">
                    Drone Pilot Credential Review
                </h1>

                <div className="mt-4 flex flex-wrap gap-2">
                    <a
                        href="/adm/drone-credentials"
                        className={`rounded-full px-3 py-1 text-xs font-medium ${!filters.status ? 'bg-primary text-primary-foreground' : 'border'}`}
                    >
                        All
                    </a>
                    {statuses.map((s) => (
                        <a
                            key={s}
                            href={`/adm/drone-credentials?status=${s}`}
                            className={`rounded-full px-3 py-1 text-xs font-medium ${filters.status === s ? 'bg-primary text-primary-foreground' : 'border'}`}
                        >
                            {s.replace('_', ' ')}
                        </a>
                    ))}
                </div>

                <div className="mt-6 grid gap-3">
                    {credentials.data.map((c) => (
                        <div key={c.id} className="rounded-lg border p-4">
                            <div className="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p className="font-medium">
                                        {c.credential_type} —{' '}
                                        {c.user?.name ?? 'Unknown user'}
                                    </p>
                                    <p className="text-sm text-muted-foreground">
                                        {c.user?.email} · {c.issuing_authority ?? 'N/A'} ·{' '}
                                        {c.country ?? 'N/A'}
                                        {c.masked_credential_number
                                            ? ` · #${c.masked_credential_number}`
                                            : ''}
                                    </p>
                                    <p className="mt-1 text-xs text-muted-foreground">
                                        Status: {c.status_label}
                                        {c.is_expired && ' (expired)'}
                                        {c.expiration_date
                                            ? ` · Expires ${c.expiration_date}`
                                            : ''}
                                        {c.reviewer
                                            ? ` · Reviewed by ${c.reviewer}`
                                            : ''}
                                    </p>
                                    <div className="mt-2 flex gap-3 text-xs">
                                        {c.has_front_document && (
                                            <a
                                                className="text-primary underline"
                                                href={documentLink(
                                                    c.id,
                                                    'front',
                                                )}
                                                target="_blank"
                                                rel="noreferrer"
                                            >
                                                Front document
                                            </a>
                                        )}
                                        {c.has_back_document && (
                                            <a
                                                className="text-primary underline"
                                                href={documentLink(
                                                    c.id,
                                                    'back',
                                                )}
                                                target="_blank"
                                                rel="noreferrer"
                                            >
                                                Back document
                                            </a>
                                        )}
                                        {c.has_supporting_document && (
                                            <a
                                                className="text-primary underline"
                                                href={documentLink(
                                                    c.id,
                                                    'supporting',
                                                )}
                                                target="_blank"
                                                rel="noreferrer"
                                            >
                                                Supporting document
                                            </a>
                                        )}
                                    </div>
                                </div>
                                <div className="flex shrink-0 flex-wrap gap-2">
                                    {c.status !== 'verified' && (
                                        <button
                                            onClick={() =>
                                                router.post(
                                                    `/adm/drone-credentials/${c.id}/approve`,
                                                )
                                            }
                                            className="rounded bg-primary px-3 py-2 text-sm text-primary-foreground"
                                        >
                                            Verify
                                        </button>
                                    )}
                                    {c.status !== 'rejected' && (
                                        <button
                                            onClick={() => {
                                                const reason =
                                                    prompt(
                                                        'Rejection reason',
                                                    );
                                                if (reason) {
                                                    router.post(
                                                        `/adm/drone-credentials/${c.id}/reject`,
                                                        { reason },
                                                    );
                                                }
                                            }}
                                            className="rounded border px-3 py-2 text-sm"
                                        >
                                            Reject
                                        </button>
                                    )}
                                    {c.status === 'verified' && (
                                        <button
                                            onClick={() => {
                                                const reason =
                                                    prompt(
                                                        'Suspension reason',
                                                    );
                                                if (reason) {
                                                    router.post(
                                                        `/adm/drone-credentials/${c.id}/suspend`,
                                                        { reason },
                                                    );
                                                }
                                            }}
                                            className="rounded border border-destructive px-3 py-2 text-sm text-destructive"
                                        >
                                            Suspend
                                        </button>
                                    )}
                                </div>
                            </div>
                        </div>
                    ))}
                    {credentials.data.length === 0 && (
                        <p className="text-sm text-muted-foreground">
                            No credential submissions found.
                        </p>
                    )}
                </div>
            </div>
        </>
    );
}

DroneCredentialsIndex.layout = {
    breadcrumbs: [
        { title: 'Drone Credential Review', href: '/adm/drone-credentials' },
    ],
};
