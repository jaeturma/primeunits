<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'title',
    'category',
    'body',
    'cta_label',
    'cta_url',
    'image_url',
    'accent_color',
    'sort_order',
    'is_active',
    'starts_at',
    'ends_at',
    'target_marketplace_mode',
    'review_status',
    'show_in_feed',
    'impressions_count',
    'clicks_count',
])]
class LandingAd extends Model
{
    public const ReviewPending = 'pending';

    public const ReviewApproved = 'approved';

    public const ReviewRejected = 'rejected';

    /**
     * Restricts to ads eligible for the landing-page 12-position feed
     * (distinct from the always-eligible standalone advertising carousel):
     * manually enabled for the feed, active, approved, and inside its
     * scheduled window when one is set.
     */
    public function scopeEligibleForFeed(Builder $query): Builder
    {
        return $query
            ->where('show_in_feed', true)
            ->where('is_active', true)
            ->where('review_status', self::ReviewApproved)
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()));
    }

    public function scopeForMode(Builder $query, string $mode): Builder
    {
        return $query->where(fn (Builder $q) => $q->whereNull('target_marketplace_mode')->orWhere('target_marketplace_mode', $mode));
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'show_in_feed' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'impressions_count' => 'integer',
            'clicks_count' => 'integer',
        ];
    }
}
