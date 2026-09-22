<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['version', 'body', 'is_active', 'published_at'])]
class ConfidentialityNotice extends Model
{
    public const DEFAULT_NOTICE_TEXT = 'Information about this listing is confidential. By requesting access you agree not to disclose owner identity, exact location, registration or serial details, or any other confidential information to third parties, and to use it only to evaluate a genuine purchase or charter interest.';

    /**
     * @return HasMany<ConfidentialityAcknowledgement, $this>
     */
    public function acknowledgements(): HasMany
    {
        return $this->hasMany(ConfidentialityAcknowledgement::class);
    }

    public static function active(): ?self
    {
        return static::query()->where('is_active', true)->latest('published_at')->first();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'published_at' => 'datetime',
        ];
    }
}
