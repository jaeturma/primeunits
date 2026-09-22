import { Link } from '@inertiajs/react';
import { useCallback, useEffect, useRef, useState } from 'react';
import { PromotionBadge } from '@/components/promotion-badge';
import { TierBadge } from '@/components/tier-badge';

export type FeedListingCard = {
    id: number;
    title: string;
    description: string | null;
    price: string;
    condition: string;
    brand: string | null;
    model: string | null;
    year_model: number | null;
    province: string | null;
    municipality: string | null;
    image_url: string;
    category: { name: string; slug: string };
    marketplace_tier: string;
    tier_label: string;
};

export type FeedAdCard = {
    id: number;
    title: string;
    category: string;
    body: string;
    cta_label: string | null;
    cta_url: string | null;
    image_url: string | null;
    accent_color: string;
};

export type FeedCard = {
    type: 'organic' | 'featured' | 'sponsored' | 'advertisement';
    listing: FeedListingCard | null;
    ad: FeedAdCard | null;
};

type FeedResponse = {
    cards: FeedCard[];
    cursor: string;
    has_more: boolean;
};

function isExternal(url: string): boolean {
    return !url.startsWith('/');
}

function xsrfToken(): string | null {
    if (typeof document === 'undefined') {
        return null;
    }

    const match = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]*)/);

    return match ? decodeURIComponent(match[1]) : null;
}

function recordImpressions(
    items: Array<{ type: string; listing_id?: number; ad_id?: number }>,
) {
    if (items.length === 0) {
        return;
    }

    const token = xsrfToken();

    fetch('/feed/impressions', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...(token ? { 'X-XSRF-TOKEN': token } : {}),
        },
        body: JSON.stringify({ items }),
        credentials: 'same-origin',
        keepalive: true,
    }).catch(() => undefined);
}

function useViewabilityTracking() {
    const seen = useRef(new Set<string>());
    const observerRef = useRef<IntersectionObserver | null>(null);

    useEffect(() => {
        observerRef.current = new IntersectionObserver(
            (entries) => {
                const newlyVisible: Array<{
                    type: string;
                    listing_id?: number;
                    ad_id?: number;
                }> = [];

                for (const entry of entries) {
                    if (!entry.isIntersecting) {
                        continue;
                    }

                    const key = entry.target.getAttribute(
                        'data-impression-key',
                    );
                    const type = entry.target.getAttribute(
                        'data-impression-type',
                    );
                    const id = entry.target.getAttribute('data-impression-id');

                    if (!key || !type || !id || seen.current.has(key)) {
                        continue;
                    }

                    seen.current.add(key);
                    observerRef.current?.unobserve(entry.target);

                    if (type === 'advertisement') {
                        newlyVisible.push({ type, ad_id: Number(id) });
                    } else {
                        newlyVisible.push({ type, listing_id: Number(id) });
                    }
                }

                recordImpressions(newlyVisible);
            },
            { threshold: 0.5 },
        );

        return () => observerRef.current?.disconnect();
    }, []);

    const observe = useCallback((node: HTMLElement | null) => {
        if (node) {
            observerRef.current?.observe(node);
        }
    }, []);

    return observe;
}

function FeedListing({
    listing,
    promotion,
    observe,
}: {
    listing: FeedListingCard;
    promotion: 'featured' | 'sponsored' | null;
    observe: (node: HTMLElement | null) => void;
}) {
    return (
        <Link href={`/listings/${listing.id}`} className="group block">
            <div
                ref={promotion ? observe : undefined}
                data-impression-key={
                    promotion ? `${promotion}-${listing.id}` : undefined
                }
                data-impression-type={promotion ?? undefined}
                data-impression-id={promotion ? listing.id : undefined}
                className="relative overflow-hidden rounded-md bg-zinc-100"
            >
                <img
                    src={listing.image_url}
                    alt={listing.title}
                    loading="lazy"
                    className="aspect-[4/3] w-full object-cover transition duration-200 group-hover:scale-[1.02]"
                />
                {promotion && <PromotionBadge kind={promotion} />}
                <div className="absolute top-2 right-2">
                    <TierBadge
                        tier={listing.marketplace_tier}
                        label={listing.tier_label}
                    />
                </div>
            </div>
            <div className="pt-2">
                <p className="text-base font-semibold text-zinc-950">
                    PHP {Number(listing.price).toLocaleString()}
                </p>
                <p className="mt-0.5 line-clamp-2 min-h-10 text-sm leading-5 text-zinc-800">
                    {listing.description || listing.title}
                </p>
                <p className="mt-1 truncate text-xs text-zinc-500">
                    {listing.municipality ?? listing.province ?? 'Philippines'}
                    {listing.municipality && listing.province
                        ? `, ${listing.province}`
                        : ''}
                </p>
            </div>
        </Link>
    );
}

function FeedAd({
    ad,
    observe,
}: {
    ad: FeedAdCard;
    observe: (node: HTMLElement | null) => void;
}) {
    const external = ad.cta_url ? isExternal(ad.cta_url) : false;

    return (
        <article
            ref={observe}
            data-impression-key={`advertisement-${ad.id}`}
            data-impression-type="advertisement"
            data-impression-id={ad.id}
            className="block rounded-md border border-dashed border-zinc-300 bg-white p-2"
        >
            <div className="relative overflow-hidden rounded-md bg-zinc-100">
                {ad.image_url ? (
                    <img
                        src={ad.image_url}
                        alt={ad.title}
                        loading="lazy"
                        className="aspect-[4/3] w-full object-cover"
                    />
                ) : (
                    <div className="flex aspect-[4/3] items-center justify-center bg-zinc-100 text-sm text-zinc-500">
                        Photo coming soon
                    </div>
                )}
                <PromotionBadge kind="advertisement" />
            </div>
            <div className="pt-2">
                <p className="text-base font-semibold text-zinc-950">
                    {ad.title}
                </p>
                <p className="mt-0.5 line-clamp-2 min-h-10 text-sm leading-5 text-zinc-800">
                    {ad.body}
                </p>
                <div className="mt-1 flex items-center justify-between gap-2">
                    <p className="truncate text-xs text-zinc-500">
                        {ad.category}
                    </p>
                    {ad.cta_label && ad.cta_url && (
                        <a
                            href={`/feed/ads/${ad.id}/click?to=${encodeURIComponent(ad.cta_url)}`}
                            target={external ? '_blank' : undefined}
                            rel={
                                external
                                    ? 'sponsored noopener noreferrer'
                                    : 'sponsored'
                            }
                            className="text-xs font-semibold text-zinc-700 hover:text-zinc-950"
                        >
                            {ad.cta_label}
                            {external && (
                                <span className="sr-only">
                                    {' '}
                                    (opens in a new tab)
                                </span>
                            )}
                        </a>
                    )}
                </div>
            </div>
        </article>
    );
}

export function LandingFeed({
    initialCards,
    initialCursor,
    initialHasMore,
}: {
    initialCards: FeedCard[];
    initialCursor: string;
    initialHasMore: boolean;
}) {
    const [cards, setCards] = useState(initialCards);
    const [cursor, setCursor] = useState(initialCursor);
    const [hasMore, setHasMore] = useState(initialHasMore);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [announcement, setAnnouncement] = useState('');
    const observe = useViewabilityTracking();
    const shownListingIds = useRef(
        new Set(
            initialCards.filter((c) => c.listing).map((c) => c.listing!.id),
        ),
    );
    const shownAdIds = useRef(
        new Set(initialCards.filter((c) => c.ad).map((c) => c.ad!.id)),
    );

    async function loadMore() {
        if (loading || !hasMore) {
            return;
        }

        setLoading(true);
        setError(null);

        try {
            const response = await fetch(
                `/feed/listings?cursor=${encodeURIComponent(cursor)}`,
                { headers: { Accept: 'application/json' } },
            );

            if (!response.ok) {
                throw new Error('request failed');
            }

            const data = (await response.json()) as FeedResponse;

            const fresh = data.cards.filter((card) => {
                if (card.listing) {
                    if (shownListingIds.current.has(card.listing.id)) {
                        return false;
                    }

                    shownListingIds.current.add(card.listing.id);

                    return true;
                }

                if (card.ad) {
                    if (shownAdIds.current.has(card.ad.id)) {
                        return false;
                    }

                    shownAdIds.current.add(card.ad.id);

                    return true;
                }

                return false;
            });

            setCards((current) => [...current, ...fresh]);
            setCursor(data.cursor);
            setHasMore(data.has_more);
            setAnnouncement(
                `${fresh.length} more listing${fresh.length === 1 ? '' : 's'} loaded.`,
            );
        } catch {
            setError('Could not load more listings. Please try again.');
        } finally {
            setLoading(false);
        }
    }

    return (
        <div>
            <div className="grid grid-cols-1 gap-x-4 gap-y-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                {cards.map((card, index) => {
                    if (card.type === 'advertisement' && card.ad) {
                        return (
                            <FeedAd
                                key={`ad-${card.ad.id}`}
                                ad={card.ad}
                                observe={observe}
                            />
                        );
                    }

                    if (card.listing) {
                        return (
                            <FeedListing
                                key={`listing-${card.listing.id}-${index}`}
                                listing={card.listing}
                                promotion={
                                    card.type === 'featured' ||
                                    card.type === 'sponsored'
                                        ? card.type
                                        : null
                                }
                                observe={observe}
                            />
                        );
                    }

                    return null;
                })}
                {cards.length === 0 && (
                    <div className="rounded-md border border-dashed border-zinc-300 bg-white p-8 text-center text-sm text-zinc-600 sm:col-span-2 lg:col-span-4">
                        No listings match this location yet. Try another city,
                        province, or region.
                    </div>
                )}
            </div>

            <div role="status" aria-live="polite" className="sr-only">
                {announcement}
            </div>

            {error && (
                <p className="mt-4 text-center text-sm text-destructive">
                    {error}
                </p>
            )}

            {hasMore && (
                <div className="mt-8 flex justify-center">
                    <button
                        type="button"
                        onClick={loadMore}
                        disabled={loading}
                        aria-busy={loading}
                        className="inline-flex h-11 w-full items-center justify-center rounded-md border border-brand-border bg-white px-6 text-sm font-semibold text-brand-accent-strong shadow-sm transition hover:bg-brand-surface-muted disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
                    >
                        {loading ? 'Loading…' : 'Load 12 More'}
                    </button>
                </div>
            )}
        </div>
    );
}
