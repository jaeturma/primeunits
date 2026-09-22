export type PromotionKind = 'featured' | 'sponsored' | 'advertisement';

const labels: Record<PromotionKind, string> = {
    featured: 'Featured',
    sponsored: 'Sponsored',
    advertisement: 'Ad',
};

/**
 * Featured/Sponsored/Advertisement placement labels. Featured uses the
 * viewer's active tier-theme accent (a premium emphasis of "your current
 * marketplace experience"); Sponsored uses a neutral campaign treatment;
 * Advertisement is deliberately the most visually distinct of the three so
 * it never reads as an ordinary listing. Text labels carry the meaning —
 * color alone is never the only signal.
 */
export function PromotionBadge({ kind }: { kind: PromotionKind }) {
    const base =
        'absolute top-2 left-2 rounded px-2 py-1 text-xs font-semibold shadow-sm';

    if (kind === 'featured') {
        return (
            <span
                className={`${base} bg-brand-accent-soft text-brand-accent-strong`}
            >
                {labels.featured}
            </span>
        );
    }

    if (kind === 'sponsored') {
        return (
            <span
                className={`${base} bg-white/95 text-zinc-700 ring-1 ring-zinc-300 ring-inset`}
            >
                {labels.sponsored}
            </span>
        );
    }

    return (
        <span className={`${base} bg-zinc-900 text-white`}>
            {labels.advertisement}
        </span>
    );
}
