<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'user_id',
    'payable_type',
    'payable_id',
    'amount',
    'method',
    'reference_number',
    'status',
    'proof_file',
    'paid_at',
])]
class Payment extends Model
{
    public const MethodCash = 'cash';

    public const MethodGcash = 'gcash';

    public const MethodBankTransfer = 'bank_transfer';

    public const StatusPending = 'pending';

    public const StatusConfirmed = 'confirmed';

    public const StatusRejected = 'rejected';

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }
}
