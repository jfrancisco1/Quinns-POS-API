<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Expense extends Model
{
    protected $fillable = [
        'user_id',
        'description',
        'amount',
        'expense_date',
        'note',
        'tenant_id',
        'branch_id',
    ];

    protected $casts = [
        'amount'       => 'float',
        'expense_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $user = Auth::user();

        $query = $this->where($field ?? $this->getRouteKeyName(), $value)
            ->where('tenant_id', $user?->tenant_id ?? 1);

        if (in_array($user?->role ?? 'admin', ['staff', 'delivery'])) {
            $query->where('branch_id', $user?->branch_id ?? 1);
        }

        return $query->firstOrFail();
    }
}
