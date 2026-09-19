<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'title',
    'category',
    'body',
    'cta_label',
    'cta_url',
    'image_url',
    'accent_color',
    'sort_order',
    'is_active',
])]
class LandingAd extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
