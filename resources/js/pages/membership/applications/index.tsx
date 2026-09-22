import { FormEvent } from 'react';
import { Head, router, useForm } from '@inertiajs/react';

type Application = {
    id: number;
    type: string;
    target_level: string;
    status: string;
    status_label: string;
    applicant_notes: string | null;
    applicant_visible_notes: string | null;
    rejection_reason: string | null;
    submitted_at: string | null;
    documents: { id: number; label: string }[];
    is_open: boolean;
};

const statusColors: Record<string, string> = {
    approved: 'bg-emerald-100 text-emerald-700',
    pending_review: 'bg-amber-100 text-amber-700',
    submitted: 'bg-amber-100 text-amber-700',
    info_required: 'bg-amber-100 text-amber-700',
    rejected: 'bg-red-100 text-red-700',
    suspended: 'bg-red-100 text-red-700',
    withdrawn: 'bg-zinc-200 text-zinc-700',
    expired: 'bg-zinc-200 text-zinc-700',
};

export default function MembershipApplicationsIndex({
    applications,
}: {
    applications: Application[];
}) {
    const form = useForm({
        type: 'buyer',
        target_level: 'silver',
        applicant_notes: '',
        documents: [] as File[],
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        form.post('/membership/applications', {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => form.reset(),
        });
    }

    function withdraw(id: number) {
        router.post(`/membership/applications/${id}/withdraw`);
    }

    return (
        <>
            <Head title="Membership Applications" />
            <div className="mx-auto max-w-2xl p-4">
                <h1 className="text-2xl font-semibold">
                    Membership Applications
                </h1>
                <p className="mt-1 text-sm text-muted-foreground">
                    Apply for Silver or Gold buyer access, seller
                    authorization, or a verified store — reviewed
                    separately by PrimeUnits.
                </p>

                <div className="mt-6 grid gap-3">
                    {applications.map((application) => (
                        <div
                            key={application.id}
                            className="rounded-lg border p-4"
                        >
                            <div className="flex items-center justify-between gap-2">
                                <p className="font-medium capitalize">
                                    {application.type} — {application.target_level}
                                </p>
                                <span
                                    className={`rounded-full px-2.5 py-0.5 text-xs font-semibold ${statusColors[application.status] ?? 'bg-zinc-100 text-zinc-700'}`}
                                >
                                    {application.status_label}
                                </span>
                            </div>
                            {application.applicant_visible_notes && (
                                <p className="mt-2 text-xs text-amber-700">
                                    {application.applicant_visible_notes}
                                </p>
                            )}
                            {application.rejection_reason && (
                                <p className="mt-2 text-xs text-destructive">
                                    {application.rejection_reason}
                                </p>
                            )}
                            {application.documents.length > 0 && (
                                <p className="mt-2 text-xs text-muted-foreground">
                                    {application.documents.length} document(s)
                                    submitted
                                </p>
                            )}
                            {application.is_open && (
                                <button
                                    onClick={() => withdraw(application.id)}
                                    className="mt-3 rounded border px-3 py-1.5 text-xs font-medium hover:bg-muted"
                                >
                                    Withdraw
                                </button>
                            )}
                        </div>
                    ))}
                    {applications.length === 0 && (
                        <p className="text-sm text-muted-foreground">
                            No applications submitted yet.
                        </p>
                    )}
                </div>

                <form
                    onSubmit={submit}
                    className="mt-8 grid gap-4 rounded-lg border p-5"
                >
                    <h2 className="font-medium">Submit a New Application</h2>
                    <div className="grid grid-cols-2 gap-3">
                        <label className="grid gap-1 text-xs">
                            Access type
                            <select
                                className="h-10 rounded-md border px-3 text-sm"
                                value={form.data.type}
                                onChange={(e) =>
                                    form.setData('type', e.target.value)
                                }
                            >
                                <option value="buyer">Buyer</option>
                                <option value="seller">Seller</option>
                                <option value="store">Store</option>
                            </select>
                        </label>
                        <label className="grid gap-1 text-xs">
                            Target level
                            <select
                                className="h-10 rounded-md border px-3 text-sm"
                                value={form.data.target_level}
                                onChange={(e) =>
                                    form.setData(
                                        'target_level',
                                        e.target.value,
                                    )
                                }
                            >
                                <option value="regular">Regular</option>
                                <option value="silver">Silver</option>
                                <option value="gold">Gold</option>
                            </select>
                        </label>
                    </div>
                    <textarea
                        placeholder="Notes for the reviewer (optional)"
                        className="min-h-20 rounded-md border p-3 text-sm"
                        value={form.data.applicant_notes}
                        onChange={(e) =>
                            form.setData('applicant_notes', e.target.value)
                        }
                    />
                    <label className="grid gap-2 text-sm">
                        Supporting documents (ID, ownership, business
                        registration, etc.)
                        <input
                            type="file"
                            multiple
                            accept="image/jpeg,image/png,application/pdf"
                            onChange={(e) =>
                                form.setData(
                                    'documents',
                                    Array.from(e.target.files ?? []),
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
                        {form.processing ? 'Submitting…' : 'Submit application'}
                    </button>
                </form>
            </div>
        </>
    );
}

MembershipApplicationsIndex.layout = {
    breadcrumbs: [
        { title: 'Membership', href: '/membership' },
        { title: 'Applications', href: '/membership/applications' },
    ],
};
