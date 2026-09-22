import { Head, Link, router, useForm } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';

type AppNotification = {
    id: string;
    title: string;
    message: string;
    event: string;
    url: string | null;
    read_at: string | null;
    created_at: string | null;
};

type Preferences = {
    database_enabled: boolean;
    email_enabled: boolean;
    sms_enabled: boolean;
};

export default function NotificationsIndex({
    notifications,
    preferences,
}: {
    notifications: { data: AppNotification[] };
    preferences: Preferences;
}) {
    const { data, setData, put, processing } =
        useForm<Preferences>(preferences);

    function savePreferences() {
        put('/notifications/preferences', { preserveScroll: true });
    }

    return (
        <>
            <Head title="Notifications" />
            <div className="grid grid-cols-1 gap-6 p-4 lg:grid-cols-[1fr_320px]">
                <section className="grid gap-4">
                    <div className="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h1 className="text-2xl font-semibold tracking-normal">
                                Notifications
                            </h1>
                            <p className="mt-1 text-sm text-muted-foreground">
                                Updates from inquiries, listings, transactions,
                                and payments.
                            </p>
                        </div>
                        <button
                            type="button"
                            onClick={() =>
                                router.patch('/notifications/read-all')
                            }
                            className="rounded-md border px-3 py-1.5 text-sm hover:bg-accent"
                        >
                            Mark all read
                        </button>
                    </div>

                    {notifications.data.map((notification) => (
                        <article
                            key={notification.id}
                            className="rounded-lg border p-4"
                        >
                            <div className="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <div className="flex flex-wrap items-center gap-2">
                                        <h2 className="font-medium">
                                            {notification.title}
                                        </h2>
                                        {!notification.read_at && (
                                            <Badge variant="secondary">
                                                New
                                            </Badge>
                                        )}
                                    </div>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        {notification.message}
                                    </p>
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    {notification.created_at
                                        ? new Date(
                                              notification.created_at,
                                          ).toLocaleString()
                                        : ''}
                                </p>
                            </div>
                            <div className="mt-4 flex flex-wrap gap-2">
                                {notification.url && (
                                    <Link
                                        href={notification.url}
                                        className="rounded-md border px-3 py-1.5 text-sm hover:bg-accent"
                                    >
                                        Open
                                    </Link>
                                )}
                                {!notification.read_at && (
                                    <button
                                        type="button"
                                        onClick={() =>
                                            router.patch(
                                                `/notifications/${notification.id}/read`,
                                                {},
                                                { preserveScroll: true },
                                            )
                                        }
                                        className="rounded-md border px-3 py-1.5 text-sm hover:bg-accent"
                                    >
                                        Mark read
                                    </button>
                                )}
                            </div>
                        </article>
                    ))}
                </section>

                <aside className="h-fit rounded-lg border p-5">
                    <h2 className="font-medium">Preferences</h2>
                    <div className="mt-4 grid gap-3 text-sm">
                        <Toggle
                            label="In-app"
                            checked={data.database_enabled}
                            onChange={(checked) =>
                                setData('database_enabled', checked)
                            }
                        />
                        <Toggle
                            label="Email"
                            checked={data.email_enabled}
                            onChange={(checked) =>
                                setData('email_enabled', checked)
                            }
                        />
                        <Toggle
                            label="SMS ready"
                            checked={data.sms_enabled}
                            onChange={(checked) =>
                                setData('sms_enabled', checked)
                            }
                        />
                    </div>
                    <button
                        type="button"
                        disabled={processing}
                        onClick={savePreferences}
                        className="mt-5 h-9 rounded-md bg-primary px-3 text-sm font-medium text-primary-foreground disabled:opacity-50"
                    >
                        Save preferences
                    </button>
                </aside>
            </div>
        </>
    );
}

function Toggle({
    label,
    checked,
    onChange,
}: {
    label: string;
    checked: boolean;
    onChange: (checked: boolean) => void;
}) {
    return (
        <label className="flex items-center justify-between gap-3">
            <span>{label}</span>
            <input
                type="checkbox"
                checked={checked}
                onChange={(event) => onChange(event.target.checked)}
            />
        </label>
    );
}

NotificationsIndex.layout = {
    breadcrumbs: [{ title: 'Notifications', href: '/notifications' }],
};
