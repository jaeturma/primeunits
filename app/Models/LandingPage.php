<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'key',
    'hero_badge',
    'hero_title',
    'hero_subtitle',
    'search_title',
    'featured_title',
    'featured_subtitle',
    'results_title',
    'results_subtitle',
    'budget_title',
    'seller_cta_title',
    'seller_cta_body',
    'seller_cta_button',
    'is_active',
])]
class LandingPage extends Model
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
