/**
 * Shows a listing's actual marketplace tier (Silver/Gold), independent of
 * the viewer's currently active theme. A Gold-mode viewer must not see
 * every card appear "Gold certified" — the badge always reflects the
 * listing's own classification, not `data-marketplace-mode`, so these
 * colors are intentionally fixed rather than driven by the brand-* tokens.
 */
export function TierBadge({
    tier,
    label,
    className = '',
}: {
    tier: string;
    label: string;
    className?: string;
}) {
    if (tier === 'regular') {
        return null;
    }

    const styles =
        tier === 'gold' || tier === 'gold_enterprise'
            ? 'bg-amber-100 text-amber-800 ring-1 ring-inset ring-amber-300'
            : 'bg-slate-200 text-slate-700 ring-1 ring-inset ring-slate-300';

    return (
        <span
            className={`inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold ${styles} ${className}`}
        >
            {label}
        </span>
    );
}
