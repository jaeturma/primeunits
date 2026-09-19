import { Head, Link, router } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';

type Plan = {
    id: number;
    name: string;
    type: string;
    price: string;
    duration_days: number | null;
    features: string[];
};

type Payment = {
    id: number;
    status: string;
    amount: string;
} | null;

type Subscription = {
    id: number;
    status: string;
    ends_at: string | null;
    plan: Plan;
    payment: Payment;
};

type Boost = {
    id: number;
    ends_at: string | null;
    is_active: boolean;
    plan: Plan;
    listing: { id: number; title: string };
    payment: Payment;
};

type Listing = { id: number; title: string };

export default function MonetizationDashboard({
    boostPlans,
    subscriptionPlans,
    subscriptions,
    boosts,
    listings,
}: {
    boostPlans: Plan[];
    subscriptionPlans: Plan[];
    subscriptions: Subscription[];
    boosts: Boost[];
    listings: Listing[];
}) {
    function boost(listingId: number, planId: number) {
        router.post(`/seller/listings/${listingId}/boosts/${planId}`);
    }

    return (
        <>
            <Head title="Monetization" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4">
                <div>
                    <h1 className="text-2xl font-semibold tracking-normal">
                        Monetization
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Manage subscriptions, boosts, and pending payments.
                    </p>
                </div>

                <section className="grid gap-4 md:grid-cols-2">
                    <Panel title="Active subscriptions">
                        {subscriptions.map((subscription) => (
                            <div
                                key={subscription.id}
                                className="rounded-md border p-3"
                            >
                                <div className="flex justify-between gap-3">
                                    <p className="font-medium">
                                        {subscription.plan.name}
                                    </p>
                                    <Badge variant="secondary">
                                        {subscription.status}
                                    </Badge>
                                </div>
                                <p className="mt-1 text-sm text-muted-foreground">
                                    Ends{' '}
                                    {subscription.ends_at
                                        ? new Date(
                                              subscription.ends_at,
                                          ).toLocaleDateString()
                                        : 'after payment confirmation'}
                                </p>
                                {subscription.payment?.status === 'pending' && (
                                    <Link
                                        href={`/payments/${subscription.payment.id}`}
                                        className="mt-2 inline-block text-sm underline"
                                    >
                                        Complete payment
                                    </Link>
                                )}
                            </div>
                        ))}
                    </Panel>

                    <Panel title="Boosted listings">
                        {boosts.map((boost) => (
                            <div
                                key={boost.id}
                                className="rounded-md border p-3"
                            >
                                <div className="flex justify-between gap-3">
                                    <p className="font-medium">
                                        {boost.listing.title}
                                    </p>
                                    <Badge variant="secondary">
                                        {boost.is_active
                                            ? 'active'
                                            : (boost.payment?.status ??
                                              'pending')}
                                    </Badge>
                                </div>
                                <p className="mt-1 text-sm text-muted-foreground">
                                    {boost.plan.name} · expires{' '}
                                    {boost.ends_at
                                        ? new Date(
                                              boost.ends_at,
                                          ).toLocaleDateString()
                                        : 'after payment confirmation'}
                                </p>
                                {boost.payment?.status === 'pending' && (
                                    <Link
                                        href={`/payments/${boost.payment.id}`}
                                        className="mt-2 inline-block text-sm underline"
                                    >
                                        Complete payment
                                    </Link>
                                )}
                            </div>
                        ))}
                    </Panel>
                </section>

                <Panel title="Subscription plans">
                    <div className="grid gap-4 md:grid-cols-3">
                        {subscriptionPlans.map((plan) => (
                            <PlanCard key={plan.id} plan={plan}>
                                <button
                                    type="button"
                                    onClick={() =>
                                        router.post(
                                            `/seller/plans/${plan.id}/subscribe`,
                                        )
                                    }
                                    className="h-9 rounded-md bg-primary px-3 text-sm font-medium text-primary-foreground"
                                >
                                    Subscribe
                                </button>
                            </PlanCard>
                        ))}
                    </div>
                </Panel>

                <Panel title="Boost listing">
                    <div className="grid gap-4 md:grid-cols-2">
                        {listings.map((listing) => (
                            <div
                                key={listing.id}
                                className="rounded-md border p-3"
                            >
                                <p className="font-medium">{listing.title}</p>
                                <div className="mt-3 flex flex-wrap gap-2">
                                    {boostPlans.map((plan) => (
                                        <button
                                            key={plan.id}
                                            type="button"
                                            onClick={() =>
                                                boost(listing.id, plan.id)
                                            }
                                            className="rounded-md border px-3 py-1.5 text-sm hover:bg-accent"
                                        >
                                            {plan.name} · PHP{' '}
                                            {Number(
                                                plan.price,
                                            ).toLocaleString()}
                                        </button>
                                    ))}
                                </div>
                            </div>
                        ))}
                    </div>
                </Panel>
            </div>
        </>
    );
}

function Panel({
    title,
    children,
}: {
    title: string;
    children: React.ReactNode;
}) {
    return (
        <section className="rounded-lg border p-5">
            <h2 className="font-medium">{title}</h2>
            <div className="mt-4 grid gap-3">{children}</div>
        </section>
    );
}

function PlanCard({
    plan,
    children,
}: {
    plan: Plan;
    children: React.ReactNode;
}) {
    return (
        <div className="rounded-md border p-4">
            <h3 className="font-medium">{plan.name}</h3>
            <p className="mt-2 text-lg font-semibold">
                PHP {Number(plan.price).toLocaleString()}
            </p>
            <p className="text-sm text-muted-foreground">
                {plan.duration_days ?? 0} days
            </p>
            <ul className="mt-3 list-inside list-disc text-sm text-muted-foreground">
                {plan.features.map((feature) => (
                    <li key={feature}>{feature}</li>
                ))}
            </ul>
            <div className="mt-4">{children}</div>
        </div>
    );
}

MonetizationDashboard.layout = {
    breadcrumbs: [{ title: 'Monetization', href: '/seller/monetization' }],
};
