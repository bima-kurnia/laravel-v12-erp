<?php

namespace App\Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnitOfMeasure extends Model
{
    protected $fillable = ['name', 'abbreviation'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}