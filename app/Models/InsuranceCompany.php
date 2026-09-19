<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'slug',
    'description',
    'coverage_types',
    'ctpl_price',
    'premium_starting_price',
    'contact_number',
    'contact_email',
    'website',
    'region',
    'province',
    'municipality',
    'full_address',
    'is_active',
    'sort_order',
])]
class InsuranceCompany extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'coverage_types' => 'array',
            'ctpl_price' => 'decimal:2',
            'premium_starting_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
