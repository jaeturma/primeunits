import { FormEvent } from 'react';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';

type Transaction = {
    id: number;
    agreed_price: string;
    commission_rate: string;
    commission_amount: string;
    status_label: string;
    buyer_confirmed: boolean;
    seller_confirmed: boolean;
    proof_url: string | null;
    lead: {
        reference_code: string;
        buyer: { id: number; name: string };
        seller: { id: number; name: string };
    };
    listing: { id: number; title: string };
    commission_log: { status: string } | null;
};

export default function TransactionShow({
    transaction,
}: {
    transaction: Transaction;
}) {
    const { auth } = usePage().props;
    const { setData, post, processing, errors } = useForm<{
        proof_file: File | null;
    }>({ proof_file: null });

    const isBuyer = auth.user.id === transaction.lead.buyer.id;
    const isSeller = auth.user.id === transaction.lead.seller.id;

    function upload(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        post(`/transactions/${transaction.id}/proof`, {
            forceFormData: true,
            preserveScroll: true,
        });
    }

    return (
        <>
            <Head title={`Transaction ${transaction.lead.reference_code}`} />
            <div className="mx-auto flex w-full max-w-4xl flex-col gap-6 p-4">
                <div>
                    <p className="font-mono text-sm font-semibold">
                        {transaction.lead.reference_code}
                    </p>
                    <h1 className="mt-1 text-2xl font-semibold tracking-normal">
                        {transaction.listing.title}
                    </h1>
                </div>

                <section className="rounded-lg border p-5">
                    <div className="flex flex-wrap items-center gap-3">
                        <Badge variant="secondary">
                            {transaction.status_label}
                        </Badge>
                        <span className="text-sm text-muted-foreground">
                            Commission: PHP{' '}
                            {Number(
                                transaction.commission_amount,
                            ).toLocaleString()}{' '}
                            ({transaction.commission_rate}%)
                        </span>
                    </div>
                    <dl className="mt-5 grid gap-4 text-sm md:grid-cols-2">
                        <Info
                            label="Agreed price"
                            value={`PHP ${Number(transaction.agreed_price).toLocaleString()}`}
                        />
                        <Info
                            label="Buyer"
                            value={transaction.lead.buyer.name}
                        />
                        <Info
                            label="Seller"
                            value={transaction.lead.seller.name}
                        />
                        <Info
                            label="Commission status"
                            value={transaction.commission_log?.status ?? 'None'}
                        />
                    </dl>

                    <div className="mt-5 flex flex-wrap gap-2">
                        {isBuyer && !transaction.buyer_confirmed && (
                            <button
                                type="button"
                                onClick={() =>
                                    router.post(
                                        `/transactions/${transaction.id}/confirm-buyer`,
                                    )
                                }
                                className="h-9 rounded-md bg-primary px-3 text-sm font-medium text-primary-foreground"
                            >
                                Confirm purchase
                            </button>
                        )}
                        {isSeller && !transaction.seller_confirmed && (
                            <button
                                type="button"
                                onClick={() =>
                                    router.post(
                                        `/transactions/${transaction.id}/confirm-seller`,
                                    )
                                }
                                className="h-9 rounded-md bg-primary px-3 text-sm font-medium text-primary-foreground"
                            >
                                Confirm sale
                            </button>
                        )}
                    </div>
                </section>

                <section className="rounded-lg border p-5">
                    <h2 className="font-medium">Proof</h2>
                    {transaction.proof_url && (
                        <a
                            href={transaction.proof_url}
                            target="_blank"
                            rel="noreferrer"
                            className="mt-3 inline-block rounded-md border px-3 py-1.5 text-sm hover:bg-accent"
                        >
                            View uploaded proof
                        </a>
                    )}
                    <form onSubmit={upload} className="mt-4 grid gap-2">
                        <input
                            type="file"
                            accept="image/*,application/pdf"
                            onChange={(event) =>
                                setData(
                                    'proof_file',
                                    event.target.files?.item(0) ?? null,
                                )
                            }
                            className="rounded-md border bg-background px-3 py-2 text-sm"
                        />
                        {errors.proof_file && (
                            <p className="text-xs text-destructive">
                                {errors.proof_file}
                            </p>
                        )}
                        <button
                            type="submit"
                            disabled={processing}
                            className="h-9 w-fit rounded-md bg-primary px-3 text-sm font-medium text-primary-foreground disabled:opacity-50"
                        >
                            Upload proof
                        </button>
                    </form>
                </section>
            </div>
        </>
    );
}

function Info({ label, value }: { label: string; value: string }) {
    return (
        <div>
            <dt className="font-medium">{label}</dt>
            <dd className="mt-1 text-muted-foreground">{value}</dd>
        </div>
    );
}
