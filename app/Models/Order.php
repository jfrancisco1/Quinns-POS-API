<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;

class Order extends Model
{
    use HasUuids;

    protected $primaryKey = 'order_number';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'order_number',
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
        'discount_amount',
        'assigned_driver_id',
        'delivery_status',
        'assigned_at',
        'delivery_failed_reason',
    ];

    protected $casts = [
        'created_at_client' => 'datetime',
        'subtotal'          => 'float',
        'delivery_fee'      => 'float',
        'discount_amount'   => 'float',
        'total'             => 'float',
        'assigned_at'       => 'datetime',
        'assigned_driver_id' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignedDriver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_driver_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function getRouteKeyName(): string
    {
        return 'order_number';
    }

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

        // Drivers serve every branch of their tenant; staff stay scoped to their own branch.
        if (($user?->role ?? 'admin') === 'staff') {
            $query->where('branch_id', $user?->branch_id ?? 1);
        }

        return $query->firstOrFail();
    }
}
