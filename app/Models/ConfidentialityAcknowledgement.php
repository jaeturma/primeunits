<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'listing_access_request_id',
    'confidentiality_notice_id',
    'notice_version',
    'notice_text',
    'acknowledged_at',
    'ip_address',
])]
class ConfidentialityAcknowledgement extends Model
{
    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<ListingAccessRequest, $this>
     */
    public function listingAccessRequest(): BelongsTo
    {
        return $this->belongsTo(ListingAccessRequest::class);
    }

    /**
     * @return BelongsTo<ConfidentialityNotice, $this>
     */
    public function notice(): BelongsTo
    {
        return $this->belongsTo(ConfidentialityNotice::class, 'confidentiality_notice_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'acknowledged_at' => 'datetime',
        ];
    }
}
