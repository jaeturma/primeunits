<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'logo', 'category_group', 'sort_order', 'is_active'])]
class Brand extends Model
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<VehicleModel, $this>
     */
    public function models(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(VehicleModel::class)->orderBy('sort_order')->orderBy('name');
    }

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
