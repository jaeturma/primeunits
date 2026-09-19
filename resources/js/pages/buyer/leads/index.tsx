import { Head, Link, router } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';

type Lead = {
    id: number;
    reference_code: string;
    status_label: string;
    listing: { id: number; title: string; price: string };
    seller: { name: string; email: string } | null;
    transaction: {
        id: number;
        status_label: string;
        agreed_price: string;
        buyer_confirmed: boolean;
    } | null;
};

export default function BuyerLeads({ leads }: { leads: Lead[] }) {
    return (
        <>
            <Head title="My Inquiries" />
            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div>
                    <h1 className="text-2xl font-semibold tracking-normal">
                        My Inquiries
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Keep your PrimeUnits reference codes handy.
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
                                        Seller: {lead.seller?.name}
                                    </p>
                                </div>
                                <Badge variant="secondary">
                                    {lead.status_label}
                                </Badge>
                            </div>
                            {lead.transaction && (
                                <div className="mt-4 flex flex-wrap items-center gap-3 text-sm">
                                    <Link
                                        href={`/transactions/${lead.transaction.id}`}
                                        className="rounded-md border px-3 py-1.5 hover:bg-accent"
                                    >
                                        View transaction
                                    </Link>
                                    {!lead.transaction.buyer_confirmed && (
                                        <button
                                            type="button"
                                            onClick={() =>
                                                router.post(
                                                    `/transactions/${lead.transaction?.id}/confirm-buyer`,
                                                )
                                            }
                                            className="rounded-md bg-primary px-3 py-1.5 font-medium text-primary-foreground"
                                        >
                                            Confirm purchase
                                        </button>
                                    )}
                                </div>
                            )}
                        </article>
                    ))}
                </div>
            </div>
        </>
    );
}

BuyerLeads.layout = {
    breadcrumbs: [{ title: 'My Inquiries', href: '/buyer/leads' }],
};
