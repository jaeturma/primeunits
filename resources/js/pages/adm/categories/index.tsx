import { FormEvent, useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';

type Category = {
    id: number;
    name: string;
    slug: string;
    listings_count: number;
};

export default function AdminCategories({
    categories,
}: {
    categories: Category[];
}) {
    const [editing, setEditing] = useState<Category | null>(null);
    const { data, setData, post, put, processing, errors, reset } = useForm({
        name: '',
        slug: '',
    });

    function load(category: Category) {
        setEditing(category);
        setData({ name: category.name, slug: category.slug });
    }

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        const options = {
            preserveScroll: true,
            onSuccess: () => {
                setEditing(null);
                reset();
            },
        };

        if (editing) {
            put(`/adm/categories/${editing.id}`, options);
            return;
        }

        post('/adm/categories', options);
    }

    return (
        <>
            <Head title="Categories" />
            <div className="grid gap-6 p-4 lg:grid-cols-[360px_1fr]">
                <form
                    onSubmit={submit}
                    className="grid h-fit gap-4 rounded-lg border p-5"
                >
                    <h1 className="text-xl font-semibold">
                        {editing ? 'Edit category' : 'Create category'}
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
                    <Field label="Slug" error={errors.slug}>
                        <input
                            value={data.slug}
                            onChange={(event) =>
                                setData('slug', event.target.value)
                            }
                            className="h-10 rounded-md border bg-background px-3"
                        />
                    </Field>
                    <button
                        disabled={processing}
                        className="h-10 rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground disabled:opacity-50"
                    >
                        {editing ? 'Update category' : 'Create category'}
                    </button>
                </form>

                <div className="grid gap-3">
                    {categories.map((category) => (
                        <article
                            key={category.id}
                            className="rounded-lg border p-4"
                        >
                            <div className="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <h2 className="font-medium">
                                        {category.name}
                                    </h2>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        {category.slug}
                                    </p>
                                </div>
                                <Badge variant="secondary">
                                    {category.listings_count} listings
                                </Badge>
                            </div>
                            <button
                                type="button"
                                onClick={() => load(category)}
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

AdminCategories.layout = {
    breadcrumbs: [{ title: 'Categories', href: '/adm/categories' }],
};
