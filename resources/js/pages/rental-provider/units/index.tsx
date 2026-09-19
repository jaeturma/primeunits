import { Head, Link, router } from '@inertiajs/react';

type RentalUnit = {
    id: number;
    slug: string;
    provider_username: string;
    name: string;
    rental_type: string;
    rental_type_label: string;
    brand: string | null;
    model: string | null;
    price_per_day: string;
    status: string;
    region: string | null;
    municipality: string | null;
    views_count: number;
    image_url: string | null;
};

export default function RentalProviderUnits({
    units,
}: {
    units: RentalUnit[];
}) {
    const statusColors: Record<string, string> = {
        pending: 'bg-yellow-100 text-yellow-700',
        approved: 'bg-green-100 text-green-700',
        rejected: 'bg-red-100 text-red-700',
    };

    return (
        <>
            <Head title="My Rental Units" />
            <div className="mx-auto w-full max-w-5xl p-4">
                <div className="mb-6 flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-normal">
                            My Rental Units
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            {units.length} unit{units.length !== 1 ? 's' : ''}
                        </p>
                    </div>
                    <Link
                        href="/rental-provider/units/create"
                        className="inline-flex h-10 items-center rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                    >
                        + Add Unit
                    </Link>
                </div>

                {units.length === 0 ? (
                    <div className="rounded-lg border border-dashed p-12 text-center">
                        <p className="font-medium">No rental units yet</p>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Add your first rental unit to get started.
                        </p>
                        <Link
                            href="/rental-provider/units/create"
                            className="mt-4 inline-block rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                        >
                            Add Unit
                        </Link>
                    </div>
                ) : (
                    <div className="grid gap-4">
                        {units.map((unit) => (
                            <div
                                key={unit.id}
                                className="flex gap-4 rounded-lg border bg-card p-4"
                            >
                                <div className="h-20 w-32 shrink-0 overflow-hidden rounded-md bg-muted">
                                    {unit.image_url ? (
                                        <img
                                            src={unit.image_url}
                                            alt={unit.name}
                                            className="h-full w-full object-cover"
                                        />
                                    ) : (
                                        <div className="flex h-full items-center justify-center text-xs text-muted-foreground">
                                            No image
                                        </div>
                                    )}
                                </div>
                                <div className="flex flex-1 flex-wrap items-start gap-4">
                                    <div className="min-w-0 flex-1">
                                        <div className="flex flex-wrap items-center gap-2">
                                            <span
                                                className={`rounded-full px-2 py-0.5 text-xs font-medium ${statusColors[unit.status] ?? 'bg-muted text-muted-foreground'}`}
                                            >
                                                {unit.status
                                                    .charAt(0)
                                                    .toUpperCase() +
                                                    unit.status.slice(1)}
                                            </span>
                                            <span className="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700">
                                                {unit.rental_type_label}
                                            </span>
                                        </div>
                                        <h3 className="mt-1 font-medium">
                                            {unit.name}
                                        </h3>
                                        {(unit.brand || unit.model) && (
                                            <p className="text-sm text-muted-foreground">
                                                {[unit.brand, unit.model]
                                                    .filter(Boolean)
                                                    .join(' ')}
                                            </p>
                                        )}
                                        {unit.municipality && (
                                            <p className="mt-0.5 text-xs text-muted-foreground">
                                                {unit.municipality}
                                            </p>
                                        )}
                                    </div>
                                    <div className="shrink-0 text-right">
                                        <p className="font-semibold">
                                            PHP{' '}
                                            {Number(
                                                unit.price_per_day,
                                            ).toLocaleString()}
                                            <span className="text-sm font-normal text-muted-foreground">
                                                /day
                                            </span>
                                        </p>
                                        <p className="mt-0.5 text-xs text-muted-foreground">
                                            {unit.views_count} views
                                        </p>
                                        {unit.status === 'approved' && (
                                            <Link
                                                href={`/rentals/${unit.provider_username}/${unit.slug}`}
                                                className="mt-2 inline-block text-xs text-primary hover:underline"
                                            >
                                                View listing
                                            </Link>
                                        )}
                                        <button
                                            type="button"
                                            onClick={() => {
                                                if (confirm('Delete this rental unit?')) {
                                                    router.delete(`/rental-provider/units/${unit.id}`);
                                                }
                                            }}
                                            className="mt-2 ml-3 text-xs text-destructive hover:underline"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}

RentalProviderUnits.layout = {
    breadcrumbs: [{ title: 'My Rental Units', href: '/rental-provider/units' }],
};
