<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * A single recorded "card was actually rendered" event for a Featured,
 * Sponsored, or Advertisement placement in the landing feed. Guarded by a
 * deterministic `dedupe_key` (promotion + subject + session/user + hour
 * bucket) so a page refresh or repeated Load More cannot inflate counts —
 * see PromotionImpressionService::record().
 */
#[Fillable([
    'promotion_type', 'promotable_type', 'promotable_id',
    'user_id', 'session_id', 'marketplace_mode', 'viewed_at', 'dedupe_key',
])]
class PromotionImpression extends Model
{
    public const TypeFeatured = 'featured';

    public const TypeSponsored = 'sponsored';

    public const TypeAdvertisement = 'advertisement';

    public $timestamps = false;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function promotable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'viewed_at' => 'datetime',
        ];
    }
}
