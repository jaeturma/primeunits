import { Head, Link, router } from '@inertiajs/react';
import { Search, SlidersHorizontal } from 'lucide-react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import { PublicFooter } from '@/components/public-footer';
import { PublicHeader } from '@/components/public-header';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

type ListingCard = {
    id: number;
    title: string;
    price: string;
    condition: string;
    brand: string | null;
    model: string | null;
    region: string | null;
    province: string | null;
    municipality: string | null;
    image_url: string;
    is_featured: boolean;
    category: { name: string };
};

type Props = {
    seoPage: {
        slug: string;
        title: string;
        meta_title: string;
        meta_description: string;
        content: string | null;
        canonical_url: string;
        location: string | null;
        category: { id: number; name: string; slug: string } | null;
    };
    filters: {
        q: string;
        condition: string;
        min_price: string;
        max_price: string;
    };
    conditions: Array<{ value: string; label: string }>;
    listings: { data: ListingCard[] };
};

export default function SeoLanding({
    seoPage,
    filters,
    conditions,
    listings,
}: Props) {
    const [form, setForm] = useState(filters);
    const heroImage = listings.data[0]?.image_url;

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        router.get(
            `/${seoPage.slug}`,
            {
                q: form.q || undefined,
                condition: form.condition || undefined,
                min_price: form.min_price || undefined,
                max_price: form.max_price || undefined,
            },
            { preserveScroll: true, preserveState: true },
        );
    }

    return (
        <>
            <Head title={seoPage.meta_title}>
                <meta name="description" content={seoPage.meta_description} />
                <link rel="canonical" href={seoPage.canonical_url} />
                <meta property="og:title" content={seoPage.meta_title} />
                <meta
                    property="og:description"
                    content={seoPage.meta_description}
                />
            </Head>
            <PublicHeader />

            <main className="bg-background">
                <section className="relative min-h-[390px] overflow-hidden bg-zinc-950 text-white">
                    {heroImage && (
                        <img
                            src={heroImage}
                            alt=""
                            className="absolute inset-0 h-full w-full object-cover opacity-45"
                        />
                    )}
                    <div className="absolute inset-0 bg-black/45" />
                    <div className="relative mx-auto flex min-h-[390px] max-w-6xl flex-col justify-end gap-5 px-4 py-10">
                        <div className="max-w-3xl">
                            <p className="text-sm font-medium text-white/75">
                                PrimeUnits Marketplace
                            </p>
                            <h1 className="mt-3 text-4xl font-semibold tracking-normal md:text-5xl">
                                {seoPage.title}
                            </h1>
                            <p className="mt-4 max-w-2xl text-base text-white/80">
                                {seoPage.meta_description}
                            </p>
                        </div>

                        <form
                            onSubmit={submit}
                            className="grid grid-cols-1 gap-2 bg-background/95 p-3 text-foreground shadow-lg backdrop-blur md:grid-cols-[1.4fr_1fr_1fr_1fr_auto]"
                        >
                            <div className="relative">
                                <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    value={form.q}
                                    onChange={(event) =>
                                        setForm({
                                            ...form,
                                            q: event.target.value,
                                        })
                                    }
                                    className="pl-9"
                                    placeholder="Search brand, model, or unit"
                                />
                            </div>
                            <Select
                                value={form.condition || 'all'}
                                onValueChange={(value) =>
                                    setForm({
                                        ...form,
                                        condition: value === 'all' ? '' : value,
                                    })
                                }
                            >
                                <SelectTrigger className="w-full">
                                    <SelectValue placeholder="Condition" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">
                                        Any condition
                                    </SelectItem>
                                    {conditions.map((condition) => (
                                        <SelectItem
                                            key={condition.value}
                                            value={condition.value}
                                        >
                                            {condition.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <Input
                                type="number"
                                value={form.min_price}
                                onChange={(event) =>
                                    setForm({
                                        ...form,
                                        min_price: event.target.value,
                                    })
                                }
                                placeholder="Min price"
                            />
                            <Input
                                type="number"
                                value={form.max_price}
                                onChange={(event) =>
                                    setForm({
                                        ...form,
                                        max_price: event.target.value,
                                    })
                                }
                                placeholder="Max price"
                            />
                            <Button type="submit">
                                <SlidersHorizontal className="size-4" />
                                Filter
                            </Button>
                        </form>
                    </div>
                </section>

                <section className="mx-auto max-w-6xl px-4 py-10">
                    {seoPage.content && (
                        <p className="mb-7 max-w-3xl text-sm leading-6 text-muted-foreground">
                            {seoPage.content}
                        </p>
                    )}

                    <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        {listings.data.map((listing) => (
                            <Link
                                key={listing.id}
                                href={`/listings/${listing.id}`}
                                className="overflow-hidden rounded-lg border bg-background hover:bg-accent/40"
                            >
                                <div className="aspect-video bg-muted">
                                    <img
                                        src={listing.image_url}
                                        alt=""
                                        className="h-full w-full object-cover"
                                    />
                                </div>
                                <div className="space-y-2 p-4">
                                    <p className="text-xs text-muted-foreground">
                                        {listing.category.name}
                                        {listing.province &&
                                            ` in ${listing.province}`}
                                        {listing.is_featured && ' · Featured'}
                                    </p>
                                    <h2 className="font-medium">
                                        {listing.title}
                                    </h2>
                                    <p className="text-sm">
                                        PHP{' '}
                                        {Number(listing.price).toLocaleString()}
                                    </p>
                                </div>
                            </Link>
                        ))}
                    </div>

                    {listings.data.length === 0 && (
                        <div className="rounded-lg border p-8 text-center">
                            <h2 className="font-medium">No listings found</h2>
                            <p className="mt-2 text-sm text-muted-foreground">
                                Try changing the filters or checking nearby
                                locations.
                            </p>
                        </div>
                    )}
                </section>
            </main>
            <PublicFooter />
        </>
    );
}
