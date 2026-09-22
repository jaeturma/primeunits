import { Head, router } from '@inertiajs/react';
import { useState } from 'react';

type ListingRow = {
    id: number;
    title: string;
    category: string | null;
    seller: string | null;
    marketplace_tier: string;
    tier_label: string;
    visibility_level: string;
    visibility_label: string;
    is_gold_candidate: boolean;
    status: string;
    promotional_type: string;
    promo_label: string;
    promoted_until: string | null;
};

function TierEditor({ listing }: { listing: ListingRow }) {
    const [tier, setTier] = useState(listing.marketplace_tier);
    const [visibility, setVisibility] = useState(listing.visibility_level);
    const [promotionalType, setPromotionalType] = useState(
        listing.promotional_type,
    );
    const [promotedUntil, setPromotedUntil] = useState(
        listing.promoted_until ? listing.promoted_until.slice(0, 10) : '',
    );

    function save() {
        router.put(
            `/adm/listing-tiers/${listing.id}`,
            {
                marketplace_tier: tier,
                visibility_level: visibility,
                promotional_type: promotionalType,
                promoted_until: promotedUntil || null,
            },
            { preserveScroll: true },
        );
    }

    function approveGold() {
        router.post(
            `/adm/listing-tiers/${listing.id}/approve-gold`,
            {},
            { preserveScroll: true },
        );
    }

    return (
        <div className="rounded-lg border p-4">
            <div className="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <p className="font-medium">{listing.title}</p>
                    <p className="text-sm text-muted-foreground">
                        {listing.category} · {listing.seller} · {listing.status}
                    </p>
                </div>
                {listing.is_gold_candidate && (
                    <span className="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700">
                        Gold candidate
                    </span>
                )}
            </div>
            <div className="mt-3 flex flex-wrap items-center gap-3">
                <select
                    className="h-9 rounded-md border px-2 text-sm"
                    value={tier}
                    onChange={(e) => setTier(e.target.value)}
                >
                    <option value="regular">Regular</option>
                    <option value="silver">Silver</option>
                    <option value="gold">Gold</option>
                    <option value="gold_enterprise">Gold Enterprise</option>
                </select>
                <select
                    className="h-9 rounded-md border px-2 text-sm"
                    value={visibility}
                    onChange={(e) => setVisibility(e.target.value)}
                >
                    <option value="public">Public</option>
                    <option value="public_preview">Public Preview</option>
                    <option value="silver_exclusive">Silver Exclusive</option>
                    <option value="gold_exclusive">Gold Exclusive</option>
                    <option value="verified_buyer_only">
                        Verified Buyer Only
                    </option>
                    <option value="invitation_only">Invitation Only</option>
                </select>
                <select
                    className="h-9 rounded-md border px-2 text-sm"
                    value={promotionalType}
                    onChange={(e) => setPromotionalType(e.target.value)}
                >
                    <option value="NONE">Not sponsored</option>
                    <option value="SL">Sponsored</option>
                    <option value="PL">Prime</option>
                </select>
                <input
                    type="date"
                    className="h-9 rounded-md border px-2 text-sm"
                    value={promotedUntil}
                    onChange={(e) => setPromotedUntil(e.target.value)}
                    aria-label="Sponsored until"
                />
                <button
                    onClick={save}
                    className="rounded bg-primary px-3 py-1.5 text-xs font-medium text-primary-foreground"
                >
                    Save
                </button>
                {listing.is_gold_candidate && (
                    <button
                        onClick={approveGold}
                        className="rounded border px-3 py-1.5 text-xs font-medium"
                    >
                        Approve as Gold
                    </button>
                )}
            </div>
        </div>
    );
}

export default function ListingTiersIndex({
    listings,
    filters,
}: {
    listings: { data: ListingRow[] };
    filters: { tier: string; gold_candidates: boolean };
    tiers: string[];
    visibilityLevels: string[];
}) {
    return (
        <>
            <Head title="Listing Marketplace Tiers" />
            <div className="p-4">
                <h1 className="text-2xl font-semibold">
                    Listing Marketplace Tiers &amp; Visibility
                </h1>

                <div className="mt-4 flex flex-wrap gap-2">
                    <a
                        href="/adm/listing-tiers"
                        className={`rounded-full px-3 py-1 text-xs font-medium ${!filters.tier && !filters.gold_candidates ? 'bg-primary text-primary-foreground' : 'border'}`}
                    >
                        All
                    </a>
                    <a
                        href="/adm/listing-tiers?gold_candidates=1"
                        className={`rounded-full px-3 py-1 text-xs font-medium ${filters.gold_candidates ? 'bg-primary text-primary-foreground' : 'border'}`}
                    >
                        Gold candidates
                    </a>
                </div>

                <div className="mt-6 grid gap-3">
                    {listings.data.map((listing) => (
                        <TierEditor key={listing.id} listing={listing} />
                    ))}
                    {listings.data.length === 0 && (
                        <p className="text-sm text-muted-foreground">
                            No listings found.
                        </p>
                    )}
                </div>
            </div>
        </>
    );
}

ListingTiersIndex.layout = {
    breadcrumbs: [{ title: 'Listing Tiers', href: '/adm/listing-tiers' }],
};
