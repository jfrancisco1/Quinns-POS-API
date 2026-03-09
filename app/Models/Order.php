<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Order extends Model
{
    protected $primaryKey = 'order_number';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'order_number',
        'customer_nickname',
        'customer_mobile',
        'customer_address',
        'customer_notes',
        'customer_delivery_fee',
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
        'customer_delivery_fee' => 'float',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_number', 'order_number');
    }

    public function paymentHistory(): HasMany
    {
        return $this->hasMany(PaymentHistory::class, 'order_number', 'order_number');
    }

    public function orderStatusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class, 'order_number', 'order_number');
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
