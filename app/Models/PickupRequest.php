<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class PickupRequest extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    /** Statuses a pickup can still move out of. */
    public const OPEN_STATUSES = ['requested', 'assigned', 'picked_up'];

    protected $fillable = [
        'id',
        'tenant_id',
        'branch_id',
        'customer_id',
        'customer_name',
        'mobile',
        'address',
        'latitude',
        'longitude',
        'notes',
        'scheduled_for',
        'status',
        'source',
        'assigned_driver_id',
        'created_by',
        'bag_count',
        'assigned_at',
        'picked_up_at',
        'received_at',
        'cancelled_at',
    ];

    protected $casts = [
        'scheduled_for' => 'date',
        'latitude'      => 'decimal:7',
        'longitude'     => 'decimal:7',
        'bag_count'     => 'integer',
        'assigned_driver_id' => 'integer',
        'created_by'    => 'integer',
        'assigned_at'   => 'datetime',
        'picked_up_at'  => 'datetime',
        'received_at'   => 'datetime',
        'cancelled_at'  => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignedDriver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_driver_id');
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $user = Auth::user();

        $query = $this->where($field ?? $this->getRouteKeyName(), $value)
            ->where('tenant_id', $user?->tenant_id ?? 1);

        // Drivers serve every branch of their tenant; staff only see their own branch.
        if (($user?->role ?? 'admin') === 'staff') {
            $query->where('branch_id', $user?->branch_id ?? 1);
        }

        return $query->firstOrFail();
    }
}
