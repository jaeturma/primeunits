import { Head, useForm } from '@inertiajs/react';
import type { FormEvent, ReactNode } from 'react';

type Landing = {
    hero_badge: string;
    hero_title: string;
    hero_subtitle: string;
    search_title: string;
    featured_title: string;
    featured_subtitle: string;
    results_title: string;
    results_subtitle: string;
    budget_title: string;
    seller_cta_title: string;
    seller_cta_body: string;
    seller_cta_button: string;
    is_active: boolean;
};

export default function AdminLanding({ landing }: { landing: Landing }) {
    const { data, setData, put, processing, errors } = useForm({
        hero_badge: landing.hero_badge,
        hero_title: landing.hero_title,
        hero_subtitle: landing.hero_subtitle,
        search_title: landing.search_title,
        featured_title: landing.featured_title,
        featured_subtitle: landing.featured_subtitle,
        results_title: landing.results_title,
        results_subtitle: landing.results_subtitle,
        budget_title: landing.budget_title,
        seller_cta_title: landing.seller_cta_title,
        seller_cta_body: landing.seller_cta_body,
        seller_cta_button: landing.seller_cta_button,
        is_active: landing.is_active,
    });

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        put('/adm/landing', { preserveScroll: true });
    }

    return (
        <>
            <Head title="Landing Page" />
            <div className="mx-auto grid max-w-5xl gap-6 p-4">
                <div>
                    <h1 className="text-2xl font-semibold tracking-normal">
                        Main Landing Page
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Manage the guest and buyer landing page copy.
                    </p>
                </div>

                <form
                    onSubmit={submit}
                    className="grid gap-4 rounded-lg border p-5"
                >
                    <Field label="Hero badge" error={errors.hero_badge}>
                        <input
                            value={data.hero_badge}
                            onChange={(event) =>
                                setData('hero_badge', event.target.value)
                            }
                            className="h-10 rounded-md border bg-background px-3"
                        />
                    </Field>
                    <Field label="Hero title" error={errors.hero_title}>
                        <input
                            value={data.hero_title}
                            onChange={(event) =>
                                setData('hero_title', event.target.value)
                            }
                            className="h-10 rounded-md border bg-background px-3"
                        />
                    </Field>
                    <Field label="Hero subtitle" error={errors.hero_subtitle}>
                        <textarea
                            value={data.hero_subtitle}
                            onChange={(event) =>
                                setData('hero_subtitle', event.target.value)
                            }
                            className="min-h-24 rounded-md border bg-background px-3 py-2"
                        />
                    </Field>

                    <div className="grid gap-4 md:grid-cols-2">
                        <Field label="Search title" error={errors.search_title}>
                            <input
                                value={data.search_title}
                                onChange={(event) =>
                                    setData('search_title', event.target.value)
                                }
                                className="h-10 rounded-md border bg-background px-3"
                            />
                        </Field>
                        <Field label="Budget title" error={errors.budget_title}>
                            <input
                                value={data.budget_title}
                                onChange={(event) =>
                                    setData('budget_title', event.target.value)
                                }
                                className="h-10 rounded-md border bg-background px-3"
                            />
                        </Field>
                    </div>

                    <div className="grid gap-4 md:grid-cols-2">
                        <Field
                            label="Featured title"
                            error={errors.featured_title}
                        >
                            <input
                                value={data.featured_title}
                                onChange={(event) =>
                                    setData(
                                        'featured_title',
                                        event.target.value,
                                    )
                                }
                                className="h-10 rounded-md border bg-background px-3"
                            />
                        </Field>
                        <Field
                            label="Featured subtitle"
                            error={errors.featured_subtitle}
                        >
                            <input
                                value={data.featured_subtitle}
                                onChange={(event) =>
                                    setData(
                                        'featured_subtitle',
                                        event.target.value,
                                    )
                                }
                                className="h-10 rounded-md border bg-background px-3"
                            />
                        </Field>
                    </div>

                    <div className="grid gap-4 md:grid-cols-2">
                        <Field
                            label="Results title"
                            error={errors.results_title}
                        >
                            <input
                                value={data.results_title}
                                onChange={(event) =>
                                    setData('results_title', event.target.value)
                                }
                                className="h-10 rounded-md border bg-background px-3"
                            />
                        </Field>
                        <Field
                            label="Results subtitle"
                            error={errors.results_subtitle}
                        >
                            <input
                                value={data.results_subtitle}
                                onChange={(event) =>
                                    setData(
                                        'results_subtitle',
                                        event.target.value,
                                    )
                                }
                                className="h-10 rounded-md border bg-background px-3"
                            />
                        </Field>
                    </div>

                    <Field
                        label="Seller CTA title"
                        error={errors.seller_cta_title}
                    >
                        <input
                            value={data.seller_cta_title}
                            onChange={(event) =>
                                setData('seller_cta_title', event.target.value)
                            }
                            className="h-10 rounded-md border bg-background px-3"
                        />
                    </Field>
                    <Field
                        label="Seller CTA body"
                        error={errors.seller_cta_body}
                    >
                        <textarea
                            value={data.seller_cta_body}
                            onChange={(event) =>
                                setData('seller_cta_body', event.target.value)
                            }
                            className="min-h-24 rounded-md border bg-background px-3 py-2"
                        />
                    </Field>
                    <Field
                        label="Seller CTA button"
                        error={errors.seller_cta_button}
                    >
                        <input
                            value={data.seller_cta_button}
                            onChange={(event) =>
                                setData('seller_cta_button', event.target.value)
                            }
                            className="h-10 rounded-md border bg-background px-3"
                        />
                    </Field>

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

                    <button
                        disabled={processing}
                        className="h-10 rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground disabled:opacity-50"
                    >
                        Save landing page
                    </button>
                </form>
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

AdminLanding.layout = {
    breadcrumbs: [{ title: 'Landing Page', href: '/adm/landing' }],
};
