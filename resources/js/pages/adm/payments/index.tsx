import { Head, router } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';

type Payment = {
    id: number;
    amount: string;
    method: string;
    reference_number: string | null;
    status: string;
    proof_url: string | null;
    payable_type: string;
    user: { name: string; email: string };
};

type StatusOption = { value: string; label: string };

export default function AdminPayments({
    payments,
    filters,
    statuses,
}: {
    payments: Payment[];
    filters: { status: string };
    statuses: StatusOption[];
}) {
    return (
        <>
            <Head title="Payments" />
            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div className="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-normal">
                            Payments
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Approve payment proofs to activate monetization
                            features.
                        </p>
                    </div>
                    <select
                        value={filters.status}
                        onChange={(event) =>
                            router.get(
                                '/adm/payments',
                                { status: event.target.value || undefined },
                                { preserveState: true, replace: true },
                            )
                        }
                        className="h-10 rounded-md border bg-background px-3 text-sm"
                    >
                        {statuses.map((status) => (
                            <option key={status.value} value={status.value}>
                                {status.label}
                            </option>
                        ))}
                    </select>
                </div>
                <div className="grid gap-4">
                    {payments.map((payment) => (
                        <article
                            key={payment.id}
                            className="rounded-lg border p-4"
                        >
                            <div className="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <h2 className="font-medium">
                                        PHP{' '}
                                        {Number(
                                            payment.amount,
                                        ).toLocaleString()}{' '}
                                        · {payment.payable_type}
                                    </h2>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        {payment.user.name} · {payment.method} ·{' '}
                                        {payment.reference_number ||
                                            'no reference'}
                                    </p>
                                </div>
                                <Badge variant="secondary">
                                    {payment.status}
                                </Badge>
                            </div>
                            <div className="mt-4 flex flex-wrap gap-2">
                                {payment.proof_url && (
                                    <a
                                        href={payment.proof_url}
                                        target="_blank"
                                        rel="noreferrer"
                                        className="rounded-md border px-3 py-1.5 text-sm hover:bg-accent"
                                    >
                                        View proof
                                    </a>
                                )}
                                {payment.status === 'pending' && (
                                    <>
                                        <button
                                            type="button"
                                            onClick={() =>
                                                router.post(
                                                    `/adm/payments/${payment.id}/confirm`,
                                                )
                                            }
                                            className="rounded-md bg-primary px-3 py-1.5 text-sm font-medium text-primary-foreground"
                                        >
                                            Confirm
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() =>
                                                router.post(
                                                    `/adm/payments/${payment.id}/reject`,
                                                )
                                            }
                                            className="rounded-md border px-3 py-1.5 text-sm hover:bg-accent"
                                        >
                                            Reject
                                        </button>
                                    </>
                                )}
                            </div>
                        </article>
                    ))}
                </div>
            </div>
        </>
    );
}

AdminPayments.layout = {
    breadcrumbs: [{ title: 'Payments', href: '/adm/payments' }],
};
