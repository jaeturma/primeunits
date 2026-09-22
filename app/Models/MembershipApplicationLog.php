<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['membership_application_id', 'actor_id', 'action', 'from_status', 'to_status', 'notes'])]
class MembershipApplicationLog extends Model
{
    public const UPDATED_AT = null;

    public const ActionInvited = 'invited';

    public const ActionAccepted = 'accepted';

    public const ActionDeclined = 'declined';

    public const ActionSubmitted = 'submitted';

    public const ActionInfoRequested = 'info_requested';

    public const ActionApproved = 'approved';

    public const ActionRejected = 'rejected';

    public const ActionWithdrawn = 'withdrawn';

    public const ActionSuspended = 'suspended';

    public const ActionExpired = 'expired';

    /**
     * @return BelongsTo<MembershipApplication, $this>
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(MembershipApplication::class, 'membership_application_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
