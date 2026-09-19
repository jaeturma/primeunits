import { FormEvent, useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';

type Lead = {
    id: number;
    reference_code: string;
    status_label: string;
    listing: { id: number; title: string; price: string };
    buyer: { name: string; email: string } | null;
    transaction: {
        id: number;
        status_label: string;
        seller_confirmed: boolean;
    } | null;
};

type StatusOption = {
    value: string;
    label: string;
};

export default function SellerLeads({
    leads,
    statuses,
}: {
    leads: Lead[];
    statuses: StatusOption[];
}) {
    const [transactionLeadId, setTransactionLeadId] = useState<number | null>(
        null,
    );
    const { data, setData, post, processing, errors, reset } = useForm({
        agreed_price: '',
    });

    function createTransaction(event: FormEvent<HTMLFormElement>, lead: Lead) {
        event.preventDefault();
        post(`/seller/leads/${lead.id}/transactions`, {
            onSuccess: () => {
                reset();
                setTransactionLeadId(null);
            },
        });
    }

    return (
        <>
            <Head title="Seller Leads" />
            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div>
                    <h1 className="text-2xl font-semibold tracking-normal">
                        Seller Leads
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Manage inquiry progress and transaction creation.
                    </p>
                </div>

                <div className="grid gap-4">
                    {leads.map((lead) => (
                        <article
                            key={lead.id}
                            className="rounded-lg border p-4"
                        >
                            <div className="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p className="font-mono text-sm font-semibold">
                                        {lead.reference_code}
                                    </p>
                                    <h2 className="mt-1 font-medium">
                                        {lead.listing.title}
                                    </h2>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        Buyer: {lead.buyer?.name} ·{' '}
                                        {lead.buyer?.email}
                                    </p>
                                </div>
                                <Badge variant="secondary">
                                    {lead.status_label}
                                </Badge>
                            </div>

                            <div className="mt-4 flex flex-wrap gap-2">
                                {statuses.map((status) => (
                                    <button
                                        key={status.value}
                                        type="button"
                                        onClick={() =>
                                            router.patch(
                                                `/seller/leads/${lead.id}/status`,
                                                { status: status.value },
                                                { preserveScroll: true },
                                            )
                                        }
                                        className="rounded-md border px-3 py-1.5 text-sm hover:bg-accent"
                                    >
                                        {status.label}
                                    </button>
                                ))}
                                {lead.transaction ? (
                                    <>
                                        <Link
                                            href={`/transactions/${lead.transaction.id}`}
                                            className="rounded-md border px-3 py-1.5 text-sm hover:bg-accent"
                                        >
                                            View transaction
                                        </Link>
                                        {!lead.transaction.seller_confirmed && (
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    router.post(
                                                        `/transactions/${lead.transaction?.id}/confirm-seller`,
                                                    )
                                                }
                                                className="rounded-md bg-primary px-3 py-1.5 text-sm font-medium text-primary-foreground"
                                            >
                                                Confirm sale
                                            </button>
                                        )}
                                    </>
                                ) : (
                                    <button
                                        type="button"
                                        onClick={() =>
                                            setTransactionLeadId(lead.id)
                                        }
                                        className="rounded-md bg-primary px-3 py-1.5 text-sm font-medium text-primary-foreground"
                                    >
                                        Create transaction
                                    </button>
                                )}
                            </div>

                            {transactionLeadId === lead.id && (
                                <form
                                    onSubmit={(event) =>
                                        createTransaction(event, lead)
                                    }
                                    className="mt-4 grid max-w-sm gap-2"
                                >
                                    <input
                                        type="number"
                                        value={data.agreed_price}
                                        onChange={(event) =>
                                            setData(
                                                'agreed_price',
                                                event.target.value,
                                            )
                                        }
                                        className="h-10 rounded-md border bg-background px-3 text-sm"
                                        placeholder="Agreed price"
                                    />
                                    {errors.agreed_price && (
                                        <p className="text-xs text-destructive">
                                            {errors.agreed_price}
                                        </p>
                                    )}
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="h-9 w-fit rounded-md bg-primary px-3 text-sm font-medium text-primary-foreground disabled:opacity-50"
                                    >
                                        Save transaction
                                    </button>
                                </form>
                            )}
                        </article>
                    ))}
                </div>
            </div>
        </>
    );
}

SellerLeads.layout = {
    breadcrumbs: [{ title: 'Seller Leads', href: '/seller/leads' }],
};
