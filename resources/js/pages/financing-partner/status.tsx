import { Head, Link } from '@inertiajs/react';

type Partner = {
    company_name: string;
    status: string;
    status_label: string;
    rejected_reason: string | null;
    verified_at: string | null;
};

export default function FinancingPartnerStatus({
    partner,
}: {
    partner: Partner;
}) {
    const statusConfig: Record<
        string,
        { color: string; icon: string; message: string }
    > = {
        pending: {
            color: 'border-yellow-200 bg-yellow-50',
            icon: '⏳',
            message:
                'Your application is under review. We will notify you once a decision has been made.',
        },
        verified: {
            color: 'border-green-200 bg-green-50',
            icon: '✅',
            message:
                'Your company has been verified! You can now add financing products and receive applications.',
        },
        rejected: {
            color: 'border-red-200 bg-red-50',
            icon: '❌',
            message:
                'Your application was not approved. Please see the reason below.',
        },
    };

    const config = statusConfig[partner.status] ?? statusConfig.pending;

    return (
        <>
            <Head title="Financing Partner Status" />
            <div className="mx-auto w-full max-w-xl p-4">
                <h1 className="mb-6 text-2xl font-semibold tracking-normal">
                    Partner Application Status
                </h1>

                <div className={`rounded-lg border p-6 ${config.color}`}>
                    <div className="flex items-center gap-3">
                        <span className="text-2xl">{config.icon}</span>
                        <div>
                            <p className="font-semibold">
                                {partner.company_name}
                            </p>
                            <p className="text-sm">{partner.status_label}</p>
                        </div>
                    </div>
                    <p className="mt-3 text-sm">{config.message}</p>

                    {partner.rejected_reason && (
                        <div className="mt-4 rounded-md border border-red-300 bg-white/60 p-3 text-sm">
                            <p className="font-medium">Reason:</p>
                            <p className="mt-1">{partner.rejected_reason}</p>
                        </div>
                    )}

                    {partner.verified_at && (
                        <p className="mt-3 text-xs text-muted-foreground">
                            Verified on{' '}
                            {new Date(partner.verified_at).toLocaleDateString()}
                        </p>
                    )}
                </div>

                {partner.status === 'verified' && (
                    <div className="mt-6 grid gap-3">
                        <Link
                            href="/financing-partner/products"
                            className="block rounded-lg border bg-card p-4 transition-shadow hover:shadow-sm"
                        >
                            <p className="font-medium">Manage Products</p>
                            <p className="mt-0.5 text-sm text-muted-foreground">
                                Add and manage your financing product offerings
                            </p>
                        </Link>
                        <Link
                            href="/financing-partner/applications"
                            className="block rounded-lg border bg-card p-4 transition-shadow hover:shadow-sm"
                        >
                            <p className="font-medium">View Applications</p>
                            <p className="mt-0.5 text-sm text-muted-foreground">
                                Review financing applications from buyers
                            </p>
                        </Link>
                    </div>
                )}
            </div>
        </>
    );
}
