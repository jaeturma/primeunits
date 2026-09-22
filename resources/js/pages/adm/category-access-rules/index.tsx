import { Head, router } from '@inertiajs/react';
import { useState } from 'react';

type Rule = {
    default_marketplace_tier: string;
    min_buyer_access: string;
    min_seller_access: string;
    manual_review_required: boolean;
    public_preview_allowed: boolean;
    verified_buyer_required: boolean;
    ownership_documents_required: boolean;
    category_credentials_required: boolean;
    proof_of_funds_allowed: boolean;
    confidentiality_required: boolean;
};

type CategoryRow = {
    id: number;
    name: string;
    slug: string;
    rule: Rule | null;
};

const defaultRule: Rule = {
    default_marketplace_tier: 'regular',
    min_buyer_access: 'regular',
    min_seller_access: 'regular',
    manual_review_required: false,
    public_preview_allowed: true,
    verified_buyer_required: false,
    ownership_documents_required: false,
    category_credentials_required: false,
    proof_of_funds_allowed: false,
    confidentiality_required: false,
};

const booleanFields: Array<{ key: keyof Rule; label: string }> = [
    { key: 'manual_review_required', label: 'Manual review required' },
    { key: 'public_preview_allowed', label: 'Public preview allowed' },
    { key: 'verified_buyer_required', label: 'Verified buyer required' },
    {
        key: 'ownership_documents_required',
        label: 'Ownership documents required',
    },
    {
        key: 'category_credentials_required',
        label: 'Category credentials required',
    },
    { key: 'proof_of_funds_allowed', label: 'Proof of funds may be requested' },
    { key: 'confidentiality_required', label: 'Confidentiality required' },
];

function RuleRow({ category }: { category: CategoryRow }) {
    const [rule, setRule] = useState<Rule>(category.rule ?? defaultRule);

    function save() {
        router.put(`/adm/category-access-rules/${category.id}`, rule, {
            preserveScroll: true,
        });
    }

    return (
        <div className="rounded-lg border p-4">
            <p className="font-medium">{category.name}</p>
            <div className="mt-3 grid gap-3 sm:grid-cols-3">
                <label className="grid gap-1 text-xs">
                    Default marketplace tier
                    <select
                        className="h-9 rounded-md border px-2 text-sm"
                        value={rule.default_marketplace_tier}
                        onChange={(e) =>
                            setRule({
                                ...rule,
                                default_marketplace_tier: e.target.value,
                            })
                        }
                    >
                        <option value="regular">Regular</option>
                        <option value="silver">Silver</option>
                        <option value="gold">Gold</option>
                        <option value="gold_enterprise">
                            Gold Enterprise
                        </option>
                    </select>
                </label>
                <label className="grid gap-1 text-xs">
                    Minimum buyer access
                    <select
                        className="h-9 rounded-md border px-2 text-sm"
                        value={rule.min_buyer_access}
                        onChange={(e) =>
                            setRule({
                                ...rule,
                                min_buyer_access: e.target.value,
                            })
                        }
                    >
                        <option value="regular">Regular</option>
                        <option value="silver">Silver</option>
                        <option value="gold">Gold</option>
                    </select>
                </label>
                <label className="grid gap-1 text-xs">
                    Minimum seller access
                    <select
                        className="h-9 rounded-md border px-2 text-sm"
                        value={rule.min_seller_access}
                        onChange={(e) =>
                            setRule({
                                ...rule,
                                min_seller_access: e.target.value,
                            })
                        }
                    >
                        <option value="regular">Regular</option>
                        <option value="silver">Silver</option>
                        <option value="gold">Gold</option>
                    </select>
                </label>
            </div>
            <div className="mt-3 grid gap-2 sm:grid-cols-2">
                {booleanFields.map((field) => (
                    <label
                        key={field.key}
                        className="flex items-center gap-2 text-xs"
                    >
                        <input
                            type="checkbox"
                            checked={Boolean(rule[field.key])}
                            onChange={(e) =>
                                setRule({
                                    ...rule,
                                    [field.key]: e.target.checked,
                                })
                            }
                        />
                        {field.label}
                    </label>
                ))}
            </div>
            <button
                onClick={save}
                className="mt-3 rounded bg-primary px-3 py-1.5 text-xs font-medium text-primary-foreground"
            >
                Save
            </button>
        </div>
    );
}

export default function CategoryAccessRulesIndex({
    categories,
}: {
    categories: CategoryRow[];
    levels: string[];
}) {
    return (
        <>
            <Head title="Category Marketplace Tier Rules" />
            <div className="p-4">
                <h1 className="text-2xl font-semibold">
                    Category Marketplace Tier Rules
                </h1>
                <p className="mt-1 text-sm text-muted-foreground">
                    Category provides the default tier only — individual
                    listings can still be manually assigned a different
                    tier as an exception.
                </p>
                <div className="mt-6 grid gap-3">
                    {categories.map((category) => (
                        <RuleRow key={category.id} category={category} />
                    ))}
                </div>
            </div>
        </>
    );
}

CategoryAccessRulesIndex.layout = {
    breadcrumbs: [
        { title: 'Category Tier Rules', href: '/adm/category-access-rules' },
    ],
};
