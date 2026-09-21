<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'drone_pilot_credential_id',
    'actor_id',
    'action',
    'from_status',
    'to_status',
    'notes',
])]
class DroneCredentialAuditLog extends Model
{
    public const UPDATED_AT = null;

    public const ActionSubmitted = 'submitted';

    public const ActionApproved = 'approved';

    public const ActionRejected = 'rejected';

    public const ActionSuspended = 'suspended';

    public const ActionExpired = 'expired';

    public const ActionReverified = 'reverified';

    public const ActionUpdated = 'updated';

    /**
     * @return BelongsTo<DronePilotCredential, $this>
     */
    public function credential(): BelongsTo
    {
        return $this->belongsTo(DronePilotCredential::class, 'drone_pilot_credential_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
