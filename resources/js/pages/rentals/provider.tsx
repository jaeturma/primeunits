import { Head, Link } from '@inertiajs/react';
import { MapPin, Store, Truck } from 'lucide-react';
import { PublicFooter } from '@/components/public-footer';
import { PublicHeader } from '@/components/public-header';

type Rental = {
    id: number;
    slug: string;
    provider_username: string;
    name: string;
    rental_type_label: string;
    brand: string | null;
    model: string | null;
    price_per_day: string;
    municipality: string | null;
    province: string | null;
    image_url: string | null;
};

type PaginatedRentals = {
    data: Rental[];
    total: number;
    last_page: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
};

export default function RentalProviderPortal({
    provider,
    rentals,
}: {
    provider: { name: string; username: string };
    rentals: PaginatedRentals;
}) {
    return (
        <>
            <Head title={`${provider.name} Rentals`} />
            <PublicHeader />
            <main className="mx-auto min-h-[70vh] w-full max-w-6xl px-4 py-8">
                <section className="mb-8 rounded-xl border bg-card p-6">
                    <div className="flex items-center gap-4">
                        <div className="flex size-16 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                            <Store className="size-8" />
                        </div>
                        <div>
                            <p className="text-sm font-medium text-emerald-700">Rental Provider</p>
                            <h1 className="text-2xl font-semibold">{provider.name}</h1>
                            <p className="text-sm text-muted-foreground">@{provider.username} · {rentals.total} available unit{rentals.total !== 1 ? 's' : ''}</p>
                        </div>
                    </div>
                </section>

                {rentals.data.length === 0 ? (
                    <div className="rounded-xl border border-dashed py-16 text-center text-muted-foreground">
                        <Truck className="mx-auto mb-3 size-10 opacity-40" />
                        No approved rental units are currently available.
                    </div>
                ) : (
                    <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        {rentals.data.map((rental) => (
                            <Link key={rental.id} href={`/rentals/${provider.username}/${rental.slug}`} className="overflow-hidden rounded-xl border bg-card transition-shadow hover:shadow-md">
                                <div className="aspect-video bg-muted">
                                    {rental.image_url ? <img src={rental.image_url} alt={rental.name} className="h-full w-full object-cover" /> : <div className="flex h-full items-center justify-center"><Truck className="size-10 text-muted-foreground/40" /></div>}
                                </div>
                                <div className="space-y-2 p-4">
                                    <span className="text-xs font-medium text-emerald-700">{rental.rental_type_label}</span>
                                    <h2 className="font-semibold">{rental.name}</h2>
                                    {(rental.brand || rental.model) && <p className="text-sm text-muted-foreground">{[rental.brand, rental.model].filter(Boolean).join(' ')}</p>}
                                    <div className="flex items-end justify-between gap-2">
                                        <p className="font-semibold text-emerald-700">PHP {Number(rental.price_per_day).toLocaleString()}<span className="text-xs font-normal text-muted-foreground">/day</span></p>
                                        {(rental.municipality || rental.province) && <p className="flex items-center gap-1 text-xs text-muted-foreground"><MapPin className="size-3" />{rental.municipality ?? rental.province}</p>}
                                    </div>
                                </div>
                            </Link>
                        ))}
                    </div>
                )}

                {rentals.last_page > 1 && <nav className="mt-8 flex justify-center gap-2">{rentals.links.map((link, index) => link.url ? <Link key={index} href={link.url} className={`rounded-md border px-3 py-1.5 text-sm ${link.active ? 'bg-emerald-600 text-white' : 'hover:bg-muted'}`} dangerouslySetInnerHTML={{ __html: link.label }} /> : <span key={index} className="rounded-md border px-3 py-1.5 text-sm text-muted-foreground" dangerouslySetInnerHTML={{ __html: link.label }} />)}</nav>}
            </main>
            <PublicFooter />
        </>
    );
}
