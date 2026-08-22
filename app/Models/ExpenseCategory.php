<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseCategory extends Model
{
    /** Seeded for every tenant; users may rename, deactivate, or add to these. */
    public const DEFAULT_NAMES = ['Utilities', 'Supplies', 'Maintenance', 'Salaries', 'Others'];

    /** Assigned to pre-existing expenses when this feature was introduced. */
    public const UNCATEGORIZED_NAME = 'Uncategorized';

    protected $fillable = ['name', 'is_active', 'sort_order', 'tenant_id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
