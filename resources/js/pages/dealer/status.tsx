import { Head, Link } from '@inertiajs/react';

type DealerProfile = {
    id: number;
    slug: string;
    business_name: string;
    contact_number: string;
    email: string | null;
    status: string;
    status_label: string;
    verified_at: string | null;
    rejected_reason: string | null;
};

export default function DealerStatus({ profile }: { profile: DealerProfile }) {
    return (
        <>
            <Head title="Dealer Application Status" />
            <div className="mx-auto w-full max-w-2xl p-4">
                <h1 className="text-2xl font-semibold tracking-normal">
                    Dealer Application
                </h1>

                <div className="mt-6 rounded-lg border p-6">
                    <div className="flex items-start justify-between">
                        <div>
                            <p className="font-medium">
                                {profile.business_name}
                            </p>
                            <p className="mt-1 text-sm text-muted-foreground">
                                {profile.contact_number}
                            </p>
                        </div>
                        <StatusBadge
                            status={profile.status}
                            label={profile.status_label}
                        />
                    </div>

                    {profile.status === 'pending' && (
                        <p className="mt-4 text-sm text-muted-foreground">
                            Your application is under review. Our team will
                            verify your dealership within 2–3 business days.
                        </p>
                    )}

                    {profile.status === 'verified' && (
                        <div className="mt-4">
                            <p className="text-sm text-green-700">
                                Your dealership has been verified on{' '}
                                {profile.verified_at}.
                            </p>
                            <Link
                                href={`/dealers/${profile.slug}`}
                                className="mt-3 inline-flex h-9 items-center rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground"
                            >
                                View Public Profile
                            </Link>
                        </div>
                    )}

                    {profile.status === 'rejected' && (
                        <div className="mt-4">
                            <p className="text-sm font-medium text-destructive">
                                Rejection reason:
                            </p>
                            <p className="mt-1 rounded-md bg-destructive/10 p-3 text-sm text-destructive">
                                {profile.rejected_reason}
                            </p>
                            <Link
                                href="/dealer/apply"
                                className="mt-4 inline-flex h-9 items-center rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground"
                            >
                                Reapply
                            </Link>
                        </div>
                    )}
                </div>
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
            className={`rounded-full px-3 py-1 text-xs font-medium ${colors[status] ?? 'bg-muted text-muted-foreground'}`}
        >
            {label}
        </span>
    );
}

DealerStatus.layout = {
    breadcrumbs: [{ title: 'Dealer Status', href: '/dealer/status' }],
};
