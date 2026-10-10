<?php

namespace App\Models;

use App\Models\Order;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Customer extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'nickname',
        'mobile',
        'address',
        'latitude',
        'longitude',
        'notes',
        'delivery_fee',
        'tenant_id',
        'branch_id',
    ];

    protected $hidden = [
        'deleted_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $user = Auth::user();

        // Customers are shared across all branches of a tenant.
        return $this->where($field ?? $this->getRouteKeyName(), $value)
            ->where('tenant_id', $user?->tenant_id ?? 1)
            ->firstOrFail();
    }
}
