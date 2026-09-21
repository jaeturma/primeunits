import { FormEvent } from 'react';
import { Head, useForm } from '@inertiajs/react';

type Credential = {
    id: number;
    credential_type: string;
    issuing_authority: string | null;
    masked_credential_number: string | null;
    issue_date: string | null;
    expiration_date: string | null;
    country: string | null;
    status: string;
    status_label: string;
    is_expired: boolean;
    rejection_reason: string | null;
    suspension_reason: string | null;
    reviewer_notes: string | null;
    created_at: string | null;
};

const statusColors: Record<string, string> = {
    verified: 'bg-emerald-100 text-emerald-700',
    pending_review: 'bg-amber-100 text-amber-700',
    rejected: 'bg-red-100 text-red-700',
    expired: 'bg-zinc-200 text-zinc-700',
    suspended: 'bg-red-100 text-red-700',
};

export default function DroneCredentials({
    credentials,
    is_verified,
}: {
    credentials: Credential[];
    is_verified: boolean;
}) {
    const form = useForm({
        credential_type: '',
        issuing_authority: '',
        credential_number: '',
        issue_date: '',
        expiration_date: '',
        country: '',
        front_document: null as File | null,
        back_document: null as File | null,
        supporting_document: null as File | null,
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        form.post('/drone-credentials', {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => form.reset(),
        });
    }

    return (
        <>
            <Head title="Drone Pilot Credentials" />
            <div className="mx-auto max-w-2xl p-4">
                <h1 className="text-2xl font-semibold">
                    Drone Pilot Credentials
                </h1>
                <p className="mt-1 text-sm text-muted-foreground">
                    Submit your remote pilot credential for review.
                    PrimeUnits does not issue pilot licenses and does not
                    guarantee government approval — this review only
                    confirms marketplace eligibility.
                </p>

                {is_verified && (
                    <div className="mt-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                        You currently hold a Verified Drone Operator status.
                    </div>
                )}

                <div className="mt-6 grid gap-3">
                    {credentials.map((c) => (
                        <div key={c.id} className="rounded-lg border p-4">
                            <div className="flex items-center justify-between gap-2">
                                <p className="font-medium">
                                    {c.credential_type}
                                </p>
                                <span
                                    className={`rounded-full px-2.5 py-0.5 text-xs font-semibold ${statusColors[c.status] ?? 'bg-zinc-100 text-zinc-700'}`}
                                >
                                    {c.status_label}
                                    {c.is_expired &&
                                        c.status === 'verified' &&
                                        ' (expired)'}
                                </span>
                            </div>
                            <p className="mt-1 text-sm text-muted-foreground">
                                {c.issuing_authority ?? 'Issuing authority not specified'}
                                {c.country ? ` · ${c.country}` : ''}
                                {c.masked_credential_number
                                    ? ` · #${c.masked_credential_number}`
                                    : ''}
                            </p>
                            {c.expiration_date && (
                                <p className="mt-1 text-xs text-muted-foreground">
                                    Expires {c.expiration_date}
                                </p>
                            )}
                            {c.rejection_reason && (
                                <p className="mt-2 text-xs text-destructive">
                                    Rejected: {c.rejection_reason}
                                </p>
                            )}
                            {c.suspension_reason && (
                                <p className="mt-2 text-xs text-destructive">
                                    Suspended: {c.suspension_reason}
                                </p>
                            )}
                            {c.reviewer_notes && (
                                <p className="mt-2 text-xs text-muted-foreground">
                                    Reviewer notes: {c.reviewer_notes}
                                </p>
                            )}
                        </div>
                    ))}
                    {credentials.length === 0 && (
                        <p className="text-sm text-muted-foreground">
                            No credentials submitted yet.
                        </p>
                    )}
                </div>

                <form
                    onSubmit={submit}
                    className="mt-8 grid gap-4 rounded-lg border p-5"
                >
                    <h2 className="font-medium">Submit a New Credential</h2>
                    <input
                        required
                        placeholder="Credential type (e.g. Remote Pilot Certificate)"
                        className="h-10 rounded-md border px-3 text-sm"
                        value={form.data.credential_type}
                        onChange={(e) =>
                            form.setData('credential_type', e.target.value)
                        }
                    />
                    <input
                        placeholder="Issuing authority"
                        className="h-10 rounded-md border px-3 text-sm"
                        value={form.data.issuing_authority}
                        onChange={(e) =>
                            form.setData('issuing_authority', e.target.value)
                        }
                    />
                    <input
                        placeholder="Credential / license number"
                        className="h-10 rounded-md border px-3 text-sm"
                        value={form.data.credential_number}
                        onChange={(e) =>
                            form.setData('credential_number', e.target.value)
                        }
                    />
                    <input
                        placeholder="Country / jurisdiction"
                        className="h-10 rounded-md border px-3 text-sm"
                        value={form.data.country}
                        onChange={(e) =>
                            form.setData('country', e.target.value)
                        }
                    />
                    <div className="grid grid-cols-2 gap-3">
                        <label className="grid gap-1 text-xs">
                            Issue date
                            <input
                                type="date"
                                className="h-10 rounded-md border px-3 text-sm"
                                value={form.data.issue_date}
                                onChange={(e) =>
                                    form.setData('issue_date', e.target.value)
                                }
                            />
                        </label>
                        <label className="grid gap-1 text-xs">
                            Expiration date
                            <input
                                type="date"
                                className="h-10 rounded-md border px-3 text-sm"
                                value={form.data.expiration_date}
                                onChange={(e) =>
                                    form.setData(
                                        'expiration_date',
                                        e.target.value,
                                    )
                                }
                            />
                        </label>
                    </div>
                    <label className="grid gap-2 text-sm">
                        Front of document
                        <input
                            type="file"
                            accept="image/jpeg,image/png,application/pdf"
                            onChange={(e) =>
                                form.setData(
                                    'front_document',
                                    e.target.files?.[0] ?? null,
                                )
                            }
                        />
                    </label>
                    <label className="grid gap-2 text-sm">
                        Back of document
                        <input
                            type="file"
                            accept="image/jpeg,image/png,application/pdf"
                            onChange={(e) =>
                                form.setData(
                                    'back_document',
                                    e.target.files?.[0] ?? null,
                                )
                            }
                        />
                    </label>
                    <label className="grid gap-2 text-sm">
                        Supporting document (PDF)
                        <input
                            type="file"
                            accept="application/pdf"
                            onChange={(e) =>
                                form.setData(
                                    'supporting_document',
                                    e.target.files?.[0] ?? null,
                                )
                            }
                        />
                    </label>
                    <p className="text-xs text-muted-foreground">
                        Documents are stored privately and are only visible
                        to you and authorized reviewers.
                    </p>
                    <button
                        disabled={form.processing}
                        className="h-10 rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground disabled:opacity-50"
                    >
                        {form.processing ? 'Submitting…' : 'Submit for review'}
                    </button>
                </form>
            </div>
        </>
    );
}

DroneCredentials.layout = {
    breadcrumbs: [{ title: 'Drone Credentials', href: '/drone-credentials' }],
};
