<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Immutable audit trail of who viewed or requested confidential listing
 * content, per the "audit logs for confidential-content access"
 * security requirement. Never expose this log's contents publicly.
 */
#[Fillable(['user_id', 'listing_id', 'action', 'ip_address'])]
class ConfidentialAccessLog extends Model
{
    public const UPDATED_AT = null;

    public const ActionViewedPreview = 'viewed_preview';

    public const ActionViewedFull = 'viewed_full';

    public const ActionRequestedAccess = 'requested_access';

    public const ActionAccessGranted = 'access_granted';

    public const ActionAccessRevoked = 'access_revoked';

    public const ActionDeniedAccess = 'denied_access';

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Listing, $this>
     */
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }
}
