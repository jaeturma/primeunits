<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Administrator-configurable default tier and requirements for a
 * category. Category alone never permanently fixes a listing's
 * marketplace tier — this only supplies the default and the review
 * requirements; a listing's own marketplace_tier can still be manually
 * overridden per acceptance criteria (e.g. a rare collector chopper vs.
 * an ordinary one in the same category).
 */
#[Fillable([
    'category_id',
    'default_marketplace_tier',
    'min_buyer_access',
    'min_seller_access',
    'manual_review_required',
    'public_preview_allowed',
    'verified_buyer_required',
    'ownership_documents_required',
    'category_credentials_required',
    'proof_of_funds_allowed',
    'confidentiality_required',
])]
class CategoryAccessRule extends Model
{
    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'manual_review_required' => 'boolean',
            'public_preview_allowed' => 'boolean',
            'verified_buyer_required' => 'boolean',
            'ownership_documents_required' => 'boolean',
            'category_credentials_required' => 'boolean',
            'proof_of_funds_allowed' => 'boolean',
            'confidentiality_required' => 'boolean',
        ];
    }
}
