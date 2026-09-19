import { FormEvent, useState } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';

type Plan = {
    id: number;
    name: string;
    type: string;
    price: string;
    duration_days: number | null;
    features: string[];
    is_active: boolean;
};

type TypeOption = { value: string; label: string };

export default function AdminPlans({
    plans,
    types,
}: {
    plans: Plan[];
    types: TypeOption[];
}) {
    const [editing, setEditing] = useState<Plan | null>(null);
    const { data, setData, post, put, processing, errors, reset } = useForm({
        name: '',
        type: types[0]?.value ?? 'boost',
        price: '',
        duration_days: '',
        features_text: '',
        is_active: true,
        features: [] as string[],
    });

    function load(plan: Plan) {
        setEditing(plan);
        setData({
            name: plan.name,
            type: plan.type,
            price: plan.price,
            duration_days: plan.duration_days?.toString() ?? '',
            features_text: plan.features.join('\n'),
            is_active: plan.is_active,
            features: plan.features,
        });
    }

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        const payload = {
            ...data,
            features: data.features_text
                .split('\n')
                .map((feature) => feature.trim())
                .filter(Boolean),
        };

        if (editing) {
            router.put(`/adm/plans/${editing.id}`, payload, {
                onSuccess: () => {
                    setEditing(null);
                    reset();
                },
            });
            return;
        }

        router.post('/adm/plans', payload, { onSuccess: () => reset() });
    }

    return (
        <>
            <Head title="Plans" />
            <div className="grid gap-6 p-4 lg:grid-cols-[380px_1fr]">
                <form
                    onSubmit={submit}
                    className="grid h-fit gap-4 rounded-lg border p-5"
                >
                    <h1 className="text-xl font-semibold">
                        {editing ? 'Edit plan' : 'Create plan'}
                    </h1>
                    <Field label="Name" error={errors.name}>
                        <input
                            value={data.name}
                            onChange={(event) =>
                                setData('name', event.target.value)
                            }
                            className="h-10 rounded-md border bg-background px-3"
                        />
                    </Field>
                    <Field label="Type" error={errors.type}>
                        <select
                            value={data.type}
                            onChange={(event) =>
                                setData('type', event.target.value)
                            }
                            className="h-10 rounded-md border bg-background px-3"
                        >
                            {types.map((type) => (
                                <option key={type.value} value={type.value}>
                                    {type.label}
                                </option>
                            ))}
                        </select>
                    </Field>
                    <Field label="Price" error={errors.price}>
                        <input
                            type="number"
                            value={data.price}
                            onChange={(event) =>
                                setData('price', event.target.value)
                            }
                            className="h-10 rounded-md border bg-background px-3"
                        />
                    </Field>
                    <Field label="Duration days" error={errors.duration_days}>
                        <input
                            type="number"
                            value={data.duration_days}
                            onChange={(event) =>
                                setData('duration_days', event.target.value)
                            }
                            className="h-10 rounded-md border bg-background px-3"
                        />
                    </Field>
                    <Field label="Features" error={errors.features}>
                        <textarea
                            value={data.features_text}
                            onChange={(event) =>
                                setData('features_text', event.target.value)
                            }
                            className="min-h-24 rounded-md border bg-background px-3 py-2"
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
                        {editing ? 'Update plan' : 'Create plan'}
                    </button>
                </form>

                <div className="grid gap-4">
                    {plans.map((plan) => (
                        <article
                            key={plan.id}
                            className="rounded-lg border p-4"
                        >
                            <div className="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <h2 className="font-medium">{plan.name}</h2>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        {plan.type} · PHP{' '}
                                        {Number(plan.price).toLocaleString()} ·{' '}
                                        {plan.duration_days ?? 0} days
                                    </p>
                                </div>
                                <Badge variant="secondary">
                                    {plan.is_active ? 'active' : 'inactive'}
                                </Badge>
                            </div>
                            <ul className="mt-3 list-inside list-disc text-sm text-muted-foreground">
                                {plan.features.map((feature) => (
                                    <li key={feature}>{feature}</li>
                                ))}
                            </ul>
                            <button
                                type="button"
                                onClick={() => load(plan)}
                                className="mt-3 rounded-md border px-3 py-1.5 text-sm hover:bg-accent"
                            >
                                Edit
                            </button>
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
    children: React.ReactNode;
}) {
    return (
        <label className="grid gap-2 text-sm">
            <span className="font-medium">{label}</span>
            {children}
            {error && <span className="text-xs text-destructive">{error}</span>}
        </label>
    );
}

AdminPlans.layout = {
    breadcrumbs: [{ title: 'Plans', href: '/adm/plans' }],
};
