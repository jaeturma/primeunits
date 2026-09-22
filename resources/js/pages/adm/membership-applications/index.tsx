import { FormEvent } from 'react';
import { Head, router, useForm } from '@inertiajs/react';

type Application = {
    id: number;
    type: string;
    target_level: string;
    status: string;
    status_label: string;
    user: { id: number; name: string; email: string } | null;
    store_name: string | null;
    applicant_notes: string | null;
    reviewer: string | null;
    document_count: number;
    submitted_at: string | null;
};

export default function AdminMembershipApplicationsIndex({
    applications,
    filters,
    types,
    statuses,
}: {
    applications: { data: Application[] };
    filters: { type: string; status: string };
    types: string[];
    statuses: string[];
}) {
    function approve(id: number) {
        router.post(`/adm/membership-applications/${id}/approve`);
    }

    function requestInfo(id: number) {
        const notes = prompt('What additional information is required?');
        if (notes) {
            router.post(`/adm/membership-applications/${id}/request-info`, {
                notes,
            });
        }
    }

    function reject(id: number) {
        const reason = prompt('Rejection reason');
        if (reason) {
            router.post(`/adm/membership-applications/${id}/reject`, {
                reason,
            });
        }
    }

    function suspend(id: number) {
        const reason = prompt('Suspension reason');
        if (reason) {
            router.post(`/adm/membership-applications/${id}/suspend`, {
                reason,
            });
        }
    }

    const inviteForm = useForm({
        email: '',
        type: 'buyer',
        target_level: 'silver',
        reviewer_notes: '',
    });

    function invite(e: FormEvent) {
        e.preventDefault();
        inviteForm.post('/adm/membership-applications/invite', {
            preserveScroll: true,
            onSuccess: () => inviteForm.reset(),
        });
    }

    return (
        <>
            <Head title="Membership Application Review" />
            <div className="p-4">
                <h1 className="text-2xl font-semibold">
                    Membership Application Review
                </h1>
                <p className="mt-1 text-sm text-muted-foreground">
                    Silver and Gold buyer/seller access is granted only by
                    invitation. Inviting a user does not change their
                    access — they must accept it from their account
                    settings.
                </p>

                <form
                    onSubmit={invite}
                    className="mt-4 grid gap-3 rounded-lg border p-4 sm:grid-cols-4"
                >
                    <input
                        type="email"
                        required
                        placeholder="User email"
                        className="h-9 rounded-md border px-3 text-sm sm:col-span-2"
                        value={inviteForm.data.email}
                        onChange={(e) =>
                            inviteForm.setData('email', e.target.value)
                        }
                    />
                    <select
                        className="h-9 rounded-md border px-2 text-sm"
                        value={inviteForm.data.type}
                        onChange={(e) =>
                            inviteForm.setData('type', e.target.value)
                        }
                    >
                        <option value="buyer">Buyer</option>
                        <option value="seller">Seller</option>
                    </select>
                    <select
                        className="h-9 rounded-md border px-2 text-sm"
                        value={inviteForm.data.target_level}
                        onChange={(e) =>
                            inviteForm.setData(
                                'target_level',
                                e.target.value,
                            )
                        }
                    >
                        <option value="silver">Silver</option>
                        <option value="gold">Gold</option>
                    </select>
                    <input
                        type="text"
                        placeholder="Note to the invitee (optional)"
                        className="h-9 rounded-md border px-3 text-sm sm:col-span-3"
                        value={inviteForm.data.reviewer_notes}
                        onChange={(e) =>
                            inviteForm.setData(
                                'reviewer_notes',
                                e.target.value,
                            )
                        }
                    />
                    <button
                        type="submit"
                        disabled={inviteForm.processing}
                        className="h-9 rounded-md bg-primary px-3 text-sm font-medium text-primary-foreground disabled:opacity-50"
                    >
                        Send invitation
                    </button>
                    {inviteForm.errors.email && (
                        <p className="text-xs text-destructive sm:col-span-4">
                            {inviteForm.errors.email}
                        </p>
                    )}
                </form>

                <div className="mt-4 flex flex-wrap gap-2">
                    <a
                        href="/adm/membership-applications"
                        className={`rounded-full px-3 py-1 text-xs font-medium ${!filters.type && !filters.status ? 'bg-primary text-primary-foreground' : 'border'}`}
                    >
                        All
                    </a>
                    {types.map((type) => (
                        <a
                            key={type}
                            href={`/adm/membership-applications?type=${type}`}
                            className={`rounded-full px-3 py-1 text-xs font-medium capitalize ${filters.type === type ? 'bg-primary text-primary-foreground' : 'border'}`}
                        >
                            {type}
                        </a>
                    ))}
                    {statuses.map((status) => (
                        <a
                            key={status}
                            href={`/adm/membership-applications?status=${status}`}
                            className={`rounded-full px-3 py-1 text-xs font-medium ${filters.status === status ? 'bg-primary text-primary-foreground' : 'border'}`}
                        >
                            {status.replace('_', ' ')}
                        </a>
                    ))}
                </div>

                <div className="mt-6 grid gap-3">
                    {applications.data.map((application) => (
                        <div
                            key={application.id}
                            className="rounded-lg border p-4"
                        >
                            <div className="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p className="font-medium capitalize">
                                        {application.type} —{' '}
                                        {application.target_level} —{' '}
                                        {application.user?.name ??
                                            'Unknown user'}
                                    </p>
                                    <p className="text-sm text-muted-foreground">
                                        {application.user?.email}
                                        {application.store_name
                                            ? ` · ${application.store_name}`
                                            : ''}
                                    </p>
                                    <p className="mt-1 text-xs text-muted-foreground">
                                        Status: {application.status_label} ·{' '}
                                        {application.document_count}{' '}
                                        document(s)
                                        {application.reviewer
                                            ? ` · Reviewed by ${application.reviewer}`
                                            : ''}
                                    </p>
                                    {application.applicant_notes && (
                                        <p className="mt-2 text-xs text-muted-foreground">
                                            "{application.applicant_notes}"
                                        </p>
                                    )}
                                </div>
                                <div className="flex shrink-0 flex-wrap gap-2">
                                    {application.status !== 'approved' && (
                                        <button
                                            onClick={() =>
                                                approve(application.id)
                                            }
                                            className="rounded bg-primary px-3 py-2 text-sm text-primary-foreground"
                                        >
                                            Approve
                                        </button>
                                    )}
                                    <button
                                        onClick={() =>
                                            requestInfo(application.id)
                                        }
                                        className="rounded border px-3 py-2 text-sm"
                                    >
                                        Request Info
                                    </button>
                                    {application.status !== 'rejected' && (
                                        <button
                                            onClick={() =>
                                                reject(application.id)
                                            }
                                            className="rounded border px-3 py-2 text-sm"
                                        >
                                            Reject
                                        </button>
                                    )}
                                    {application.status === 'approved' && (
                                        <button
                                            onClick={() =>
                                                suspend(application.id)
                                            }
                                            className="rounded border border-destructive px-3 py-2 text-sm text-destructive"
                                        >
                                            Suspend
                                        </button>
                                    )}
                                </div>
                            </div>
                        </div>
                    ))}
                    {applications.data.length === 0 && (
                        <p className="text-sm text-muted-foreground">
                            No applications found.
                        </p>
                    )}
                </div>
            </div>
        </>
    );
}

AdminMembershipApplicationsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Membership Applications',
            href: '/adm/membership-applications',
        },
    ],
};
