import { Head, router } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';

type Transaction = {
    id: number;
    agreed_price: string;
    commission_amount: string;
    status_label: string;
    buyer_confirmed: boolean;
    seller_confirmed: boolean;
    lead: {
        reference_code: string;
        buyer: { name: string };
        seller: { name: string };
    };
    listing: { title: string };
    commission_log: { status: string } | null;
};

type StatusOption = { value: string; label: string };

export default function AdminTransactions({
    transactions,
    filters,
    statuses,
}: {
    transactions: Transaction[];
    filters: { status: string };
    statuses: StatusOption[];
}) {
    return (
        <>
            <Head title="Transactions" />
            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div className="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-normal">
                            Transactions
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Track commissions and payment status.
                        </p>
                    </div>
                    <select
                        value={filters.status}
                        onChange={(event) =>
                            router.get(
                                '/adm/transactions',
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
                    {transactions.map((transaction) => (
                        <article
                            key={transaction.id}
                            className="rounded-lg border p-4"
                        >
                            <div className="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p className="font-mono text-sm font-semibold">
                                        {transaction.lead.reference_code}
                                    </p>
                                    <h2 className="mt-1 font-medium">
                                        {transaction.listing.title}
                                    </h2>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        {transaction.lead.buyer.name} /{' '}
                                        {transaction.lead.seller.name}
                                    </p>
                                </div>
                                <Badge variant="secondary">
                                    {transaction.status_label}
                                </Badge>
                            </div>
                            <div className="mt-4 flex flex-wrap items-center gap-4 text-sm">
                                <span>
                                    Deal: PHP{' '}
                                    {Number(
                                        transaction.agreed_price,
                                    ).toLocaleString()}
                                </span>
                                <span>
                                    Commission: PHP{' '}
                                    {Number(
                                        transaction.commission_amount,
                                    ).toLocaleString()}
                                </span>
                                <span>
                                    Commission status:{' '}
                                    {transaction.commission_log?.status ??
                                        'none'}
                                </span>
                                {transaction.commission_log?.status ===
                                    'unpaid' && (
                                    <button
                                        type="button"
                                        onClick={() =>
                                            router.post(
                                                `/adm/transactions/${transaction.id}/mark-paid`,
                                            )
                                        }
                                        className="rounded-md bg-primary px-3 py-1.5 font-medium text-primary-foreground"
                                    >
                                        Mark as paid
                                    </button>
                                )}
                            </div>
                        </article>
                    ))}
                </div>
            </div>
        </>
    );
}

AdminTransactions.layout = {
    breadcrumbs: [{ title: 'Transactions', href: '/adm/transactions' }],
};
