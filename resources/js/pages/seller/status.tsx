import { Head, Link } from '@inertiajs/react';

type SellerProfile = {
    seller_type: string;
    business_name: string | null;
    owner_name: string | null;
    contact_number: string;
    email: string | null;
    full_address: string | null;
    status_label: string;
    verified_at: string | null;
    rejected_reason: string | null;
    can_edit: boolean;
};

export default function SellerStatus({ profile }: { profile: SellerProfile }) {
    return (
        <>
            <Head title="Seller Status" />

            <div className="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4">
                <div>
                    <h1 className="text-2xl font-semibold tracking-normal">
                        Seller Verification
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        {profile.status_label}
                    </p>
                </div>

                <div className="rounded-lg border p-5">
                    <dl className="grid gap-4 text-sm md:grid-cols-2">
                        <Info label="Seller type" value={profile.seller_type} />
                        <Info
                            label="Business name"
                            value={profile.business_name}
                        />
                        <Info label="Owner name" value={profile.owner_name} />
                        <Info
                            label="Contact number"
                            value={profile.contact_number}
                        />
                        <Info label="Email" value={profile.email} />
                        <Info
                            label="Verified at"
                            value={
                                profile.verified_at
                                    ? new Date(
                                          profile.verified_at,
                                      ).toLocaleDateString()
                                    : null
                            }
                        />
                    </dl>

                    {profile.full_address && (
                        <div className="mt-4 text-sm">
                            <p className="font-medium">Address</p>
                            <p className="mt-1 text-muted-foreground">
                                {profile.full_address}
                            </p>
                        </div>
                    )}

                    {profile.rejected_reason && (
                        <div className="mt-4 rounded-md border border-destructive/40 p-3 text-sm">
                            <p className="font-medium">Review notes</p>
                            <p className="mt-1 text-muted-foreground">
                                {profile.rejected_reason}
                            </p>
                        </div>
                    )}

                    {profile.can_edit && (
                        <Link
                            href="/seller/apply"
                            className="mt-5 inline-flex h-9 items-center rounded-md bg-primary px-3 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                        >
                            Edit application
                        </Link>
                    )}
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

SellerStatus.layout = {
    breadcrumbs: [
        {
            title: 'Seller Status',
            href: '/seller/status',
        },
    ],
};
