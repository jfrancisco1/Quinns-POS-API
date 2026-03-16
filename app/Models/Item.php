<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
    protected $fillable = ['name', 'description', 'color', 'shape', 'price', 'cost', 'is_active', 'category_id', 'tenant_id', 'branch_id'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
