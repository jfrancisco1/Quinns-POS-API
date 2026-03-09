<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Order extends Model
{
    protected $fillable = [
        'customer_id',
        'fulfillment_type',
        'subtotal',
        'delivery_fee',
        'total',
        'created_at_client',
        'payment_status',
        'order_status',
        'tenant_id',
        'branch_id',
    ];

    protected $casts = [
        'created_at_client' => 'datetime',
        'subtotal'          => 'float',
        'delivery_fee'      => 'float',
        'total'             => 'float',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function paymentHistory(): HasMany
    {
        return $this->hasMany(PaymentHistory::class);
    }

    public function orderStatusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
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
