import { Head, useForm, usePage } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { useState } from 'react';
import { PublicFooter } from '@/components/public-footer';
import { PublicHeader } from '@/components/public-header';
import { TierBadge } from '@/components/tier-badge';

type RestrictedListing = {
    id: number;
    slug: string;
    title: string;
    marketplace_tier: string;
    tier_label: string;
    visibility_level: string;
    visibility_label: string;
    can_view_full: false;
    can_request_access: boolean;
    has_pending_access_request: boolean;
    category: string | null;
    description: string;
    price: string | null;
    price_on_request: boolean;
    region: string | null;
    tier: string;
    seller_capacity: string | null;
    seller_capacity_label: string | null;
};

type Listing = {
    id: number;
    title: string;
    description: string | null;
    price: string | null;
    price_on_request: boolean;
    negotiable: boolean;
    is_featured: boolean;
    condition: string;
    year_model: number | null;
    brand: string | null;
    model: string | null;
    region: string | null;
    province: string | null;
    municipality: string | null;
    barangay: string | null;
    category: { name: string };
    seller: { name: string; email: string; contact_number: string } | null;
    can_view_contact: boolean;
    can_view_full: true;
    marketplace_tier: string;
    tier_label: string;
    visibility_level: string;
    visibility_label: string;
    masked_registration_number: string | null;
    is_gold_candidate: boolean;
    seller_capacity: string | null;
    seller_capacity_label: string | null;
    current_lead: {
        id: number;
        reference_code: string;
        message: string | null;
        messages: Array<{
            id: number;
            body: string;
            created_at: string | null;
            user: { id: number; name: string };
        }>;
    } | null;
    images: { id: number; url: string; is_primary: boolean }[];
    attachments: Array<{ id: number; name: string; url: string; size: number }>;
    specs: { id: number; label: string; value: string }[];
};

function RestrictedListingView({ listing }: { listing: RestrictedListing }) {
    const { auth } = usePage().props;
    const form = useForm({ message: '', confidentiality_acknowledged: false });

    function requestAccess(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        form.post(`/listing-access/${listing.id}/request`, {
            preserveScroll: true,
        });
    }

    return (
        <>
            <Head title={listing.title} />
            <PublicHeader />
            <main className="bg-[#f4f5f2] text-zinc-950">
                <div className="mx-auto max-w-2xl p-4 py-10">
                    <div className="rounded-lg border bg-white p-6">
                        <div className="flex flex-wrap items-center gap-2">
                            <TierBadge
                                tier={listing.marketplace_tier}
                                label={listing.tier_label}
                            />
                            <span className="rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-semibold text-zinc-700">
                                {listing.visibility_label}
                            </span>
                            {listing.seller_capacity_label && (
                                <span className="rounded-full border border-zinc-200 px-2.5 py-0.5 text-xs font-semibold text-zinc-600">
                                    {listing.seller_capacity_label}
                                </span>
                            )}
                        </div>
                        <h1 className="mt-3 text-2xl font-semibold tracking-normal">
                            {listing.title}
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            {listing.category}
                            {listing.region ? ` · ${listing.region}` : ''}
                        </p>
                        <p className="mt-4 text-xl font-semibold">
                            {listing.price_on_request || !listing.price
                                ? 'Price on Request'
                                : `PHP ${Number(listing.price).toLocaleString()}`}
                        </p>
                        <p className="mt-4 text-sm whitespace-pre-line text-muted-foreground">
                            {listing.description}
                        </p>
                        <div className="mt-6 rounded-md border border-dashed p-4 text-sm text-muted-foreground">
                            Full details, exact location, registration
                            information, and seller identity are confidential
                            and shown only to approved buyers.
                        </div>
                        {!auth.user && (
                            <p className="mt-4 text-sm text-muted-foreground">
                                Log in and complete verification to request
                                access.
                            </p>
                        )}
                        {auth.user && listing.has_pending_access_request && (
                            <p className="mt-4 text-sm text-muted-foreground">
                                Your access request is pending review.
                            </p>
                        )}
                        {auth.user &&
                            !listing.has_pending_access_request &&
                            listing.can_request_access && (
                                <form
                                    onSubmit={requestAccess}
                                    className="mt-4 grid gap-3"
                                >
                                    <textarea
                                        value={form.data.message}
                                        onChange={(e) =>
                                            form.setData(
                                                'message',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="Tell the seller why you're requesting access"
                                        className="min-h-20 rounded-md border px-3 py-2 text-sm"
                                    />
                                    <label className="flex items-start gap-2 text-xs">
                                        <input
                                            type="checkbox"
                                            checked={
                                                form.data
                                                    .confidentiality_acknowledged
                                            }
                                            onChange={(e) =>
                                                form.setData(
                                                    'confidentiality_acknowledged',
                                                    e.target.checked,
                                                )
                                            }
                                        />
                                        I agree to keep any confidential
                                        information disclosed to me private.
                                    </label>
                                    <button
                                        type="submit"
                                        disabled={form.processing}
                                        className="h-10 rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground disabled:opacity-50"
                                    >
                                        {form.processing
                                            ? 'Sending…'
                                            : 'Request Access'}
                                    </button>
                                </form>
                            )}
                        {auth.user &&
                            !listing.has_pending_access_request &&
                            !listing.can_request_access && (
                                <p className="mt-4 text-sm text-muted-foreground">
                                    Requesting access to this listing requires
                                    completed identity verification.
                                </p>
                            )}
                    </div>
                </div>
            </main>
            <PublicFooter />
        </>
    );
}

export default function PublicListingView({
    listing,
}: {
    listing: Listing | RestrictedListing;
}) {
    if (listing.can_view_full !== true) {
        return <RestrictedListingView listing={listing} />;
    }

    return <FullListingView listing={listing} />;
}

function FullListingView({ listing }: { listing: Listing }) {
    const [activeImage, setActiveImage] = useState(0);
    const primary = listing.images[activeImage] ?? listing.images[0];
    const { auth, flash } = usePage().props;
    const { data, setData, post, processing, errors } = useForm({
        listing_id: listing.id.toString(),
        message: listing.current_lead?.message ?? 'Is this unit available?',
    });

    function inquire(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        post('/leads', { preserveScroll: true });
    }

    return (
        <>
            <Head title={listing.title} />
            <PublicHeader />
            <main className="bg-[#f4f5f2] text-zinc-950">
                <div className="mx-auto grid w-full max-w-6xl grid-cols-1 gap-6 p-4 py-6 lg:grid-cols-[1.4fr_0.8fr] lg:py-8">
                    <div className="space-y-4">
                        <div className="aspect-video overflow-hidden rounded-lg border bg-muted">
                            {primary && (
                                <img
                                    src={primary.url}
                                    alt=""
                                    className="h-full w-full object-cover"
                                />
                            )}
                        </div>
                        <div className="grid grid-cols-4 gap-2">
                            {listing.images.map((image, index) => (
                                <button
                                    key={image.id}
                                    type="button"
                                    onClick={() => setActiveImage(index)}
                                    className={`overflow-hidden rounded-md border ${index === activeImage ? 'border-primary' : ''}`}
                                >
                                    <img
                                        src={image.url}
                                        alt=""
                                        className="aspect-video w-full object-cover"
                                    />
                                </button>
                            ))}
                        </div>
                        <section className="rounded-lg border bg-white p-5">
                            <h2 className="font-medium">Description</h2>
                            <p className="mt-3 text-sm whitespace-pre-line text-muted-foreground">
                                {listing.description ||
                                    'No description provided.'}
                            </p>
                        </section>
                        <section className="rounded-lg border bg-white p-5">
                            <h2 className="font-medium">Specifications</h2>
                            <dl className="mt-4 grid gap-4 text-sm md:grid-cols-2">
                                {listing.specs.map((spec) => (
                                    <div key={spec.id}>
                                        <dt className="font-medium">
                                            {spec.label}
                                        </dt>
                                        <dd className="mt-1 text-muted-foreground">
                                            {spec.value}
                                        </dd>
                                    </div>
                                ))}
                            </dl>
                        </section>
                        {listing.attachments.length > 0 && (
                            <section className="rounded-lg border bg-white p-5">
                                <h2 className="font-medium">Documents</h2>
                                <div className="mt-3 grid gap-2">
                                    {listing.attachments.map((attachment) => (
                                        <a
                                            key={attachment.id}
                                            href={attachment.url}
                                            target="_blank"
                                            rel="noreferrer"
                                            className="truncate rounded-md border px-3 py-2 text-sm hover:bg-muted"
                                        >
                                            {attachment.name}
                                        </a>
                                    ))}
                                </div>
                            </section>
                        )}
                    </div>
                    <aside className="h-fit rounded-lg border bg-white p-5">
                        {flash.inquiry && (
                            <div className="mb-4 rounded-md border border-primary/30 p-3 text-sm">
                                <p className="font-medium">Inquiry created</p>
                                <p className="mt-1 text-muted-foreground">
                                    Reference: {flash.inquiry.reference_code}
                                </p>
                                <p className="mt-1 text-muted-foreground">
                                    Contact {flash.inquiry.seller_name} and keep
                                    this reference code for tracking.
                                </p>
                            </div>
                        )}
                        <p className="text-sm text-muted-foreground">
                            {listing.category.name}
                            {listing.is_featured && ' - Featured'}
                        </p>
                        {(listing.marketplace_tier !== 'regular' ||
                            listing.seller_capacity_label) && (
                            <div className="mt-2 flex flex-wrap gap-2">
                                {listing.marketplace_tier !== 'regular' && (
                                    <TierBadge
                                        tier={listing.marketplace_tier}
                                        label={listing.tier_label}
                                    />
                                )}
                                {listing.is_gold_candidate && (
                                    <span className="rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-semibold text-zinc-700">
                                        Gold candidate — pending review
                                    </span>
                                )}
                                {listing.seller_capacity_label && (
                                    <span className="rounded-full border border-zinc-200 px-2.5 py-0.5 text-xs font-semibold text-zinc-600">
                                        {listing.seller_capacity_label}
                                    </span>
                                )}
                            </div>
                        )}
                        <h1 className="mt-1 text-2xl font-semibold tracking-normal">
                            {listing.title}
                        </h1>
                        <p className="mt-4 text-xl font-semibold">
                            {listing.price_on_request || !listing.price
                                ? 'Price on Request'
                                : `PHP ${Number(listing.price).toLocaleString()}`}
                        </p>
                        {listing.masked_registration_number && (
                            <p className="mt-1 text-sm text-muted-foreground">
                                Registration:{' '}
                                {listing.masked_registration_number}
                            </p>
                        )}
                        {listing.negotiable && (
                            <p className="mt-1 text-sm text-muted-foreground">
                                Negotiable
                            </p>
                        )}
                        <dl className="mt-5 grid gap-3 text-sm">
                            <Info label="Condition" value={listing.condition} />
                            <Info
                                label="Year"
                                value={listing.year_model?.toString() ?? null}
                            />
                            <Info
                                label="Brand / Model"
                                value={[listing.brand, listing.model]
                                    .filter(Boolean)
                                    .join(' ')}
                            />
                            <Info
                                label={
                                    listing.can_view_contact
                                        ? 'Location'
                                        : 'Area'
                                }
                                value={[
                                    listing.can_view_contact
                                        ? listing.barangay
                                        : null,
                                    listing.municipality,
                                    listing.province,
                                    listing.region,
                                ]
                                    .filter(Boolean)
                                    .join(', ')}
                            />
                            {listing.can_view_contact && listing.seller ? (
                                <>
                                    <Info
                                        label="Seller"
                                        value={listing.seller.name}
                                    />
                                    <Info
                                        label="Email"
                                        value={listing.seller.email}
                                    />
                                    <Info
                                        label="Contact"
                                        value={listing.seller.contact_number}
                                    />
                                </>
                            ) : (
                                <div className="rounded-md border border-dashed p-3 text-sm text-muted-foreground">
                                    Seller address and contact details are shown
                                    after you start a conversation.
                                </div>
                            )}
                        </dl>
                        {listing.current_lead && (
                            <section className="mt-5 rounded-md border p-3">
                                <p className="text-sm font-medium">
                                    Inquiry{' '}
                                    {listing.current_lead.reference_code}
                                </p>
                                <div className="mt-3 grid max-h-56 gap-2 overflow-y-auto">
                                    {listing.current_lead.messages.map(
                                        (message) => (
                                            <div
                                                key={message.id}
                                                className="rounded-md bg-muted p-2 text-sm"
                                            >
                                                <p className="font-medium">
                                                    {message.user.name}
                                                </p>
                                                <p className="mt-1 text-muted-foreground">
                                                    {message.body}
                                                </p>
                                            </div>
                                        ),
                                    )}
                                </div>
                            </section>
                        )}
                        {auth.user ? (
                            <form
                                onSubmit={inquire}
                                className="mt-5 grid gap-3"
                            >
                                <textarea
                                    value={data.message}
                                    onChange={(event) =>
                                        setData('message', event.target.value)
                                    }
                                    className="min-h-24 rounded-md border bg-background px-3 py-2 text-sm"
                                    placeholder="Message for the seller"
                                />
                                {errors.listing_id && (
                                    <p className="text-xs text-destructive">
                                        {errors.listing_id}
                                    </p>
                                )}
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="h-10 rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50"
                                >
                                    {processing
                                        ? 'Sending...'
                                        : listing.current_lead
                                          ? 'Send message'
                                          : 'Inquire'}
                                </button>
                            </form>
                        ) : (
                            <p className="mt-5 text-sm text-muted-foreground">
                                Log in to inquire about this listing.
                            </p>
                        )}
                    </aside>
                </div>
            </main>
            <PublicFooter />
        </>
    );
}

function Info({ label, value }: { label: string; value: string | null }) {
    return (
        <div>
            <dt className="font-medium">{label}</dt>
            <dd className="mt-1 text-muted-foreground">{value || 'Not set'}</dd>
        </div>
    );
}
