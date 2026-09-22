<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A privately stored supporting document (ID, business registration,
 * ownership proof, etc.) for a MembershipApplication. Kept as its own
 * table rather than the shared public-disk ResourceAttachment model
 * because these documents must live on the private disk and be served
 * only through a policy-gated route, mirroring the drone-credential
 * document pattern.
 */
#[Fillable(['membership_application_id', 'label', 'path', 'mime_type', 'size'])]
class MembershipApplicationDocument extends Model
{
    /**
     * @return BelongsTo<MembershipApplication, $this>
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(MembershipApplication::class, 'membership_application_id');
    }
}
