import { FormEvent, useState } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';

type SellerProfile = {
    id: number;
    user: {
        id: number | null;
        name: string | null;
        email: string | null;
    };
    seller_type: string;
    business_name: string | null;
    owner_name: string | null;
    contact_number: string;
    email: string | null;
    region: string | null;
    province: string | null;
    municipality: string | null;
    barangay: string | null;
    full_address: string | null;
    permit_number: string | null;
    accreditation: string | null;
    representative_name: string | null;
    representative_contact: string | null;
    status: string;
    status_label: string;
    can_approve: boolean;
    can_reject: boolean;
    rejected_reason: string | null;
    files: Record<string, string | null>;
};

type StatusOption = {
    value: string;
    label: string;
};

export default function AdminSellersIndex({
    profiles,
    filters,
    statuses,
}: {
    profiles: SellerProfile[];
    filters: { status: string };
    statuses: StatusOption[];
}) {
    const [rejectingId, setRejectingId] = useState<number | null>(null);
    const { data, setData, post, processing, errors, reset } = useForm({
        rejected_reason: '',
    });

    function filter(status: string) {
        router.get(
            '/adm/sellers',
            { status: status || undefined },
            { preserveState: true, replace: true },
        );
    }

    function approve(profile: SellerProfile) {
        router.post(`/adm/sellers/${profile.id}/approve`, undefined, {
            preserveScroll: true,
        });
    }

    function reject(event: FormEvent<HTMLFormElement>, profile: SellerProfile) {
        event.preventDefault();

        post(`/adm/sellers/${profile.id}/reject`, {
            preserveScroll: true,
            onSuccess: () => {
                reset();
                setRejectingId(null);
            },
        });
    }

    return (
        <>
            <Head title="Seller Applications" />

            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
                <div className="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-normal">
                            Seller Applications
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Review seller submissions and verification files.
                        </p>
                    </div>
                    <label className="grid gap-2 text-sm">
                        <span className="font-medium">Status</span>
                        <select
                            value={filters.status}
                            onChange={(event) => filter(event.target.value)}
                            className="h-10 rounded-md border bg-background px-3 text-sm"
                        >
                            {statuses.map((status) => (
                                <option key={status.value} value={status.value}>
                                    {status.label}
                                </option>
                            ))}
                        </select>
                    </label>
                </div>

                <div className="grid gap-4">
                    {profiles.map((profile) => (
                        <article
                            key={profile.id}
                            className="rounded-lg border p-5"
                        >
                            <div className="flex flex-wrap items-start justify-between gap-4">
                                <div>
                                    <div className="flex flex-wrap items-center gap-2">
                                        <h2 className="font-semibold">
                                            {profile.business_name ||
                                                profile.owner_name ||
                                                profile.user.name}
                                        </h2>
                                        <Badge variant="secondary">
                                            {profile.status_label}
                                        </Badge>
                                    </div>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        {profile.user.email} ·{' '}
                                        {profile.contact_number}
                                    </p>
                                </div>
                                <div className="flex gap-2">
                                    {profile.can_approve && (
                                        <button
                                            type="button"
                                            onClick={() => approve(profile)}
                                            className="h-9 rounded-md bg-primary px-3 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                                        >
                                            Approve
                                        </button>
                                    )}
                                    {profile.can_reject && (
                                        <button
                                            type="button"
                                            onClick={() =>
                                                setRejectingId(profile.id)
                                            }
                                            className="h-9 rounded-md border px-3 text-sm font-medium hover:bg-accent"
                                        >
                                            Reject
                                        </button>
                                    )}
                                </div>
                            </div>

                            <dl className="mt-5 grid gap-4 text-sm md:grid-cols-3">
                                <Info
                                    label="Seller type"
                                    value={profile.seller_type}
                                />
                                <Info
                                    label="Owner"
                                    value={profile.owner_name}
                                />
                                <Info label="Email" value={profile.email} />
                                <Info
                                    label="Permit"
                                    value={profile.permit_number}
                                />
                                <Info
                                    label="Accreditation"
                                    value={profile.accreditation}
                                />
                                <Info
                                    label="Representative"
                                    value={profile.representative_name}
                                />
                            </dl>

                            <div className="mt-4 text-sm">
                                <p className="font-medium">Address</p>
                                <p className="mt-1 text-muted-foreground">
                                    {[
                                        profile.full_address,
                                        profile.barangay,
                                        profile.municipality,
                                        profile.province,
                                        profile.region,
                                    ]
                                        .filter(Boolean)
                                        .join(', ') || 'Not set'}
                                </p>
                            </div>

                            <div className="mt-4 flex flex-wrap gap-2 text-sm">
                                {Object.entries(profile.files).map(
                                    ([field, url]) =>
                                        url && (
                                            <a
                                                key={field}
                                                href={url}
                                                target="_blank"
                                                rel="noreferrer"
                                                className="rounded-md border px-3 py-1.5 hover:bg-accent"
                                            >
                                                {field.replaceAll('_', ' ')}
                                            </a>
                                        ),
                                )}
                            </div>

                            {profile.rejected_reason && (
                                <div className="mt-4 rounded-md border p-3 text-sm">
                                    <p className="font-medium">Reject reason</p>
                                    <p className="mt-1 text-muted-foreground">
                                        {profile.rejected_reason}
                                    </p>
                                </div>
                            )}

                            {rejectingId === profile.id && (
                                <form
                                    onSubmit={(event) => reject(event, profile)}
                                    className="mt-4 grid gap-3"
                                >
                                    <textarea
                                        value={data.rejected_reason}
                                        onChange={(event) =>
                                            setData(
                                                'rejected_reason',
                                                event.target.value,
                                            )
                                        }
                                        className="min-h-24 rounded-md border bg-background px-3 py-2 text-sm"
                                        placeholder="Reason for rejection"
                                    />
                                    {errors.rejected_reason && (
                                        <p className="text-xs text-destructive">
                                            {errors.rejected_reason}
                                        </p>
                                    )}
                                    <div className="flex gap-2">
                                        <button
                                            type="submit"
                                            disabled={processing}
                                            className="h-9 rounded-md bg-destructive px-3 text-sm font-medium text-white disabled:opacity-50"
                                        >
                                            Submit rejection
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => setRejectingId(null)}
                                            className="h-9 rounded-md border px-3 text-sm font-medium hover:bg-accent"
                                        >
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            )}
                        </article>
                    ))}
                </div>
            </div>
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

AdminSellersIndex.layout = {
    breadcrumbs: [
        {
            title: 'Seller Applications',
            href: '/adm/sellers',
        },
    ],
};
