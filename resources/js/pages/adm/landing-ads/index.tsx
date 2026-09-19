import { Head, router, useForm } from '@inertiajs/react';
import type { FormEvent, ReactNode } from 'react';
import { useState } from 'react';
import { Badge } from '@/components/ui/badge';

type LandingAd = {
    id: number;
    title: string;
    category: string;
    body: string;
    cta_label: string | null;
    cta_url: string | null;
    image_url: string | null;
    accent_color: string;
    sort_order: number;
    is_active: boolean;
};

export default function AdminLandingAds({ ads }: { ads: LandingAd[] }) {
    const [editing, setEditing] = useState<LandingAd | null>(null);
    const [imagePreview, setImagePreview] = useState<string | null>(null);
    const { data, setData, post, processing, errors, reset } = useForm({
        _method: 'POST',
        title: '',
        category: 'Service Shops',
        body: '',
        cta_label: '',
        cta_url: '',
        image_url: '',
        image_file: null as File | null,
        accent_color: '#059669',
        sort_order: '0',
        is_active: true,
    });

    function load(ad: LandingAd) {
        setEditing(ad);
        setData({
            _method: 'PUT',
            title: ad.title,
            category: ad.category,
            body: ad.body,
            cta_label: ad.cta_label ?? '',
            cta_url: ad.cta_url ?? '',
            image_url: ad.image_url ?? '',
            image_file: null,
            accent_color: ad.accent_color,
            sort_order: ad.sort_order.toString(),
            is_active: ad.is_active,
        });
        setImagePreview(ad.image_url);
    }

    function clear() {
        setEditing(null);
        setImagePreview(null);
        reset();
    }

    function setImage(file: File | null) {
        setData('image_file', file);

        if (file) {
            setImagePreview(URL.createObjectURL(file));
            return;
        }

        setImagePreview(data.image_url || null);
    }

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        if (editing) {
            post(`/adm/landing-ads/${editing.id}`, {
                forceFormData: true,
                preserveScroll: true,
                onSuccess: clear,
            });
            return;
        }

        post('/adm/landing-ads', {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: clear,
        });
    }

    function remove(ad: LandingAd) {
        router.delete(`/adm/landing-ads/${ad.id}`, { preserveScroll: true });
    }

    return (
        <>
            <Head title="Landing Ads" />
            <div className="grid gap-6 p-4 lg:grid-cols-[380px_1fr]">
                <form
                    onSubmit={submit}
                    className="grid h-fit gap-4 rounded-lg border p-5"
                >
                    <h1 className="text-xl font-semibold">
                        {editing ? 'Edit ad' : 'Create ad'}
                    </h1>
                    <Field label="Title" error={errors.title}>
                        <input
                            value={data.title}
                            onChange={(event) =>
                                setData('title', event.target.value)
                            }
                            className="h-10 rounded-md border bg-background px-3"
                        />
                    </Field>
                    <Field label="Category" error={errors.category}>
                        <input
                            value={data.category}
                            onChange={(event) =>
                                setData('category', event.target.value)
                            }
                            className="h-10 rounded-md border bg-background px-3"
                            placeholder="Repair Shops"
                        />
                    </Field>
                    <Field label="Body" error={errors.body}>
                        <textarea
                            value={data.body}
                            onChange={(event) =>
                                setData('body', event.target.value)
                            }
                            className="min-h-24 rounded-md border bg-background px-3 py-2"
                        />
                    </Field>
                    <div className="grid gap-4 sm:grid-cols-2">
                        <Field label="CTA label" error={errors.cta_label}>
                            <input
                                value={data.cta_label}
                                onChange={(event) =>
                                    setData('cta_label', event.target.value)
                                }
                                className="h-10 rounded-md border bg-background px-3"
                            />
                        </Field>
                        <Field label="CTA URL" error={errors.cta_url}>
                            <input
                                value={data.cta_url}
                                onChange={(event) =>
                                    setData('cta_url', event.target.value)
                                }
                                className="h-10 rounded-md border bg-background px-3"
                            />
                        </Field>
                    </div>
                    <Field label="Image URL" error={errors.image_url}>
                        <input
                            value={data.image_url}
                            onChange={(event) => {
                                setData('image_url', event.target.value);
                                if (!data.image_file) {
                                    setImagePreview(event.target.value || null);
                                }
                            }}
                            className="h-10 rounded-md border bg-background px-3"
                            placeholder="/images/landing-equipment-yard.png"
                        />
                    </Field>
                    <Field label="Upload photo" error={errors.image_file}>
                        <input
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            onChange={(event) =>
                                setImage(event.target.files?.[0] ?? null)
                            }
                            className="rounded-md border bg-background px-3 py-2 text-sm"
                        />
                    </Field>
                    {imagePreview && (
                        <img
                            src={imagePreview}
                            alt="Ad photo preview"
                            className="aspect-[16/9] w-full rounded-md border object-cover"
                        />
                    )}
                    <div className="grid gap-4 sm:grid-cols-2">
                        <Field label="Accent color" error={errors.accent_color}>
                            <input
                                type="color"
                                value={data.accent_color}
                                onChange={(event) =>
                                    setData('accent_color', event.target.value)
                                }
                                className="h-10 rounded-md border bg-background px-3"
                            />
                        </Field>
                        <Field label="Sort order" error={errors.sort_order}>
                            <input
                                type="number"
                                value={data.sort_order}
                                onChange={(event) =>
                                    setData('sort_order', event.target.value)
                                }
                                className="h-10 rounded-md border bg-background px-3"
                            />
                        </Field>
                    </div>
                    <label className="flex items-center gap-2 text-sm">
                        <input
                            type="checkbox"
                            checked={data.is_active}
                            onChange={(event) =>
                                setData('is_active', event.target.checked)
                            }
                        />
                        Active
                    </label>
                    <div className="flex gap-2">
                        <button
                            disabled={processing}
                            className="h-10 flex-1 rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground disabled:opacity-50"
                        >
                            {editing ? 'Update ad' : 'Create ad'}
                        </button>
                        {editing && (
                            <button
                                type="button"
                                onClick={clear}
                                className="h-10 rounded-md border px-4 text-sm font-medium hover:bg-accent"
                            >
                                Cancel
                            </button>
                        )}
                    </div>
                </form>

                <div className="grid gap-4">
                    {ads.map((ad) => (
                        <article key={ad.id} className="rounded-lg border p-4">
                            <div className="grid gap-4 md:grid-cols-[180px_1fr]">
                                <div className="overflow-hidden rounded-md border bg-muted">
                                    {ad.image_url ? (
                                        <img
                                            src={ad.image_url}
                                            alt={ad.title}
                                            className="aspect-[16/10] w-full object-cover"
                                        />
                                    ) : (
                                        <div className="flex aspect-[16/10] items-center justify-center px-4 text-center text-xs text-muted-foreground">
                                            No photo
                                        </div>
                                    )}
                                </div>
                                <div>
                                    <div className="flex flex-wrap items-start justify-between gap-3">
                                        <div>
                                            <p className="text-xs text-muted-foreground">
                                                {ad.category}
                                            </p>
                                            <h2 className="mt-1 font-medium">
                                                {ad.title}
                                            </h2>
                                            <p className="mt-1 text-sm text-muted-foreground">
                                                {ad.body}
                                            </p>
                                        </div>
                                        <Badge variant="secondary">
                                            {ad.is_active
                                                ? 'active'
                                                : 'inactive'}
                                        </Badge>
                                    </div>
                                    <div className="mt-3 flex flex-wrap items-center gap-2 text-sm">
                                        <span className="rounded-md border px-2 py-1">
                                            Order {ad.sort_order}
                                        </span>
                                        <span
                                            className="rounded-md border px-2 py-1"
                                            style={{ color: ad.accent_color }}
                                        >
                                            {ad.accent_color}
                                        </span>
                                    </div>
                                    <div className="mt-3 flex gap-2">
                                        <button
                                            type="button"
                                            onClick={() => load(ad)}
                                            className="rounded-md border px-3 py-1.5 text-sm hover:bg-accent"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => remove(ad)}
                                            className="rounded-md border px-3 py-1.5 text-sm text-destructive hover:bg-accent"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </article>
                    ))}
                </div>
            </div>
        </>
    );
}

function Field({
    label,
    error,
    children,
}: {
    label: string;
    error?: string;
    children: ReactNode;
}) {
    return (
        <label className="grid gap-2 text-sm">
            <span className="font-medium">{label}</span>
            {children}
            {error && <span className="text-xs text-destructive">{error}</span>}
        </label>
    );
}

AdminLandingAds.layout = {
    breadcrumbs: [{ title: 'Landing Ads', href: '/adm/landing-ads' }],
};
