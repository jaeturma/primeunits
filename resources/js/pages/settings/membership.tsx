import { Head, Link, router } from '@inertiajs/react';
import Heading from '@/components/heading';
import { edit } from '@/routes/membership';

type Access = {
    identity_verification_level: string;
    buyer_access_level: string;
    buyer_access_status: string;
    seller_access_level: string;
    seller_access_status: string;
    current_mode: string;
    available_modes: string[];
};

type Invitation = {
    id: number;
    type: string;
    target_level: string;
    reviewer_notes: string | null;
};

export default function MembershipSettings({
    access,
    pendingInvitations,
}: {
    access: Access;
    pendingInvitations: Invitation[];
}) {
    function accept(id: number) {
        router.post(`/membership/applications/${id}/accept`, {}, { preserveScroll: true });
    }

    function decline(id: number) {
        router.post(`/membership/applications/${id}/decline`, {}, { preserveScroll: true });
    }

    function switchMode(mode: string) {
        router.post('/settings/membership/mode', { mode }, { preserveScroll: true });
    }

    const hasElevatedAccess = access.available_modes.length > 1;

    return (
        <>
            <Head title="Membership settings" />

            <h1 className="sr-only">Membership settings</h1>

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="Account access"
                    description="Your buyer access, seller authorization, and marketplace mode"
                />

                <div className="grid gap-4 rounded-lg border p-4 sm:grid-cols-2">
                    <div>
                        <p className="text-xs font-medium text-muted-foreground">
                            Buyer access
                        </p>
                        <p className="text-sm font-medium capitalize">
                            {access.buyer_access_level}{' '}
                            <span className="font-normal text-muted-foreground">
                                ({access.buyer_access_status})
                            </span>
                        </p>
                    </div>
                    <div>
                        <p className="text-xs font-medium text-muted-foreground">
                            Seller authorization
                        </p>
                        <p className="text-sm font-medium capitalize">
                            {access.seller_access_level}{' '}
                            <span className="font-normal text-muted-foreground">
                                ({access.seller_access_status})
                            </span>
                        </p>
                    </div>
                </div>

                {pendingInvitations.length > 0 && (
                    <div className="space-y-3">
                        <Heading
                            variant="small"
                            title="Pending invitations"
                            description="Optional — accepting or declining does not affect your current access until you respond"
                        />
                        {pendingInvitations.map((invitation) => (
                            <div
                                key={invitation.id}
                                className="rounded-lg border p-4"
                            >
                                <p className="text-sm font-medium capitalize">
                                    You've been invited to {invitation.target_level}{' '}
                                    {invitation.type} access
                                </p>
                                {invitation.reviewer_notes && (
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        {invitation.reviewer_notes}
                                    </p>
                                )}
                                <div className="mt-3 flex gap-2">
                                    <button
                                        onClick={() => accept(invitation.id)}
                                        className="rounded-md bg-primary px-3 py-1.5 text-xs font-medium text-primary-foreground"
                                    >
                                        Accept
                                    </button>
                                    <button
                                        onClick={() => decline(invitation.id)}
                                        className="rounded-md border px-3 py-1.5 text-xs font-medium"
                                    >
                                        Decline
                                    </button>
                                </div>
                            </div>
                        ))}
                    </div>
                )}

                {pendingInvitations.length === 0 && (
                    <p className="text-sm text-muted-foreground">
                        Silver and Gold access are available by invitation
                        only. There is nothing you need to do — if invited,
                        it will appear here.
                    </p>
                )}

                {hasElevatedAccess && (
                    <div className="space-y-2">
                        <Heading
                            variant="small"
                            title="Marketplace mode"
                            description="Choose which marketplace you browse in"
                        />
                        <div className="flex flex-wrap gap-2">
                            {access.available_modes.map((mode) => (
                                <button
                                    key={mode}
                                    onClick={() => switchMode(mode)}
                                    className={`rounded-full px-3 py-1 text-xs font-medium capitalize ${
                                        mode === access.current_mode
                                            ? 'bg-primary text-primary-foreground'
                                            : 'border'
                                    }`}
                                >
                                    {mode}
                                </button>
                            ))}
                        </div>
                    </div>
                )}

                <Link
                    href="/membership/applications"
                    className="inline-block text-sm text-muted-foreground underline"
                >
                    Manage seller or store authorization applications
                </Link>
            </div>
        </>
    );
}

MembershipSettings.layout = {
    breadcrumbs: [{ title: 'Membership settings', href: edit() }],
};
