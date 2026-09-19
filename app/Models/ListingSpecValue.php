<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['listing_id', 'spec_field_id', 'value'])]
class ListingSpecValue extends Model
{
    /**
     * @return BelongsTo<Listing, $this>
     */
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    /**
     * @return BelongsTo<CategorySpecField, $this>
     */
    public function specField(): BelongsTo
    {
        return $this->belongsTo(CategorySpecField::class);
    }
}
