<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\PickupRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Pickup requests: laundry a driver collects from a customer and brings to a branch.
 *
 *   requested ──claim──► assigned ──pick_up──► picked_up ──receive──► received (dropped at shop)
 *       ▲                   │  ▲                  │
 *       └─────release───────┘  └─────reopen───────┘        any open ──cancel──► cancelled
 *
 * Every action is idempotent (repeating the current state is a no-op) so the driver app can
 * safely retry actions it queued while offline.
 */
class PickupRequestService extends BaseService
{
    private const RELATIONS = ['branch', 'assignedDriver'];

    protected function model(): string
    {
        return PickupRequest::class;
    }

    /** Drivers serve every branch of their tenant; staff stay scoped to their own branch. */
    protected function tenantScope(): Builder
    {
        $user = Auth::user();
        $query = PickupRequest::where('tenant_id', $user?->tenant_id ?? 1);

        if (($user?->role ?? 'admin') === 'staff') {
            $query->where('branch_id', $user->branch_id);
        }

        return $query;
    }

    /**
     * Drivers: their own open pickups plus the unclaimed pool.
     * Admin/staff: every open pickup (or the given statuses), optionally for one date.
     */
    public function getAll(?array $statuses = null, ?string $date = null, ?int $branchId = null): Collection
    {
        $user = Auth::user();
        $query = $this->tenantScope()->with(self::RELATIONS);

        if ($user->role === 'delivery') {
            $query->where(fn ($q) => $q
                ->where(fn ($mine) => $mine
                    ->where('assigned_driver_id', $user->id)
                    ->whereIn('status', ['assigned', 'picked_up']))
                ->orWhere(fn ($pool) => $pool
                    ->whereNull('assigned_driver_id')
                    ->where('status', 'requested')));
        } else {
            $query->whereIn('status', $statuses ?: PickupRequest::OPEN_STATUSES);
        }

        if ($date) {
            $query->whereDate('scheduled_for', $date);
        }

        if ($branchId && in_array($user->role, ['admin', 'delivery'])) {
            $query->where('branch_id', $branchId);
        }

        return $query->orderBy('scheduled_for')->orderBy('created_at')->get();
    }

    public function create(array $data): PickupRequest
    {
        $user = Auth::user();

        // Same id sent twice (offline retry) → return what was created the first time.
        $existing = PickupRequest::find($data['id']);
        if ($existing) {
            abort_unless($existing->tenant_id === $user->tenant_id, 409, 'Pickup id already in use.');
            return $existing->load(self::RELATIONS);
        }

        abort_unless(
            Branch::where('id', $data['branchId'])->where('tenant_id', $user->tenant_id)->exists(),
            422,
            'Branch not found.',
        );

        if (!empty($data['customerId'])) {
            abort_unless(
                Customer::where('id', $data['customerId'])->where('tenant_id', $user->tenant_id)->exists(),
                422,
                'Customer not found.',
            );
        }

        // A driver creating a pickup is doing it themselves; admin may leave it in the pool.
        $driverId = $user->role === 'delivery' ? $user->id : ($data['assignedDriverId'] ?? null);
        if ($driverId && $user->role !== 'delivery') {
            $this->assertDriver($driverId, $user->tenant_id);
        }

        $pickup = PickupRequest::create([
            'id'                 => $data['id'],
            'tenant_id'          => $user->tenant_id,
            'branch_id'          => $data['branchId'],
            'customer_id'        => $data['customerId'] ?? null,
            'customer_name'      => $data['customerName'],
            'mobile'             => $data['mobile'] ?? null,
            'address'            => $data['address'] ?? null,
            'latitude'           => $data['latitude'] ?? null,
            'longitude'          => $data['longitude'] ?? null,
            'notes'              => $data['notes'] ?? null,
            'scheduled_for'      => $data['scheduledFor'] ?? Carbon::now(self::LOCAL_TIMEZONE)->toDateString(),
            'status'             => $driverId ? 'assigned' : 'requested',
            'source'             => $user->role === 'delivery' ? 'driver' : 'admin',
            'assigned_driver_id' => $driverId,
            'assigned_at'        => $driverId ? now() : null,
            'created_by'         => $user->id,
        ]);

        return $pickup->load(self::RELATIONS);
    }

    public function update(PickupRequest $pickup, array $data): PickupRequest
    {
        $user = Auth::user();

        return DB::transaction(function () use ($pickup, $data, $user) {
            // Locked so two drivers claiming at the same moment can't both win.
            $pickup = PickupRequest::whereKey($pickup->getKey())->lockForUpdate()->firstOrFail();

            if (isset($data['action'])) {
                $this->applyAction($pickup, $data['action'], $user);
            } elseif (array_key_exists('driverId', $data)) {
                $this->assignByAdmin($pickup, $data['driverId'], $user);
            }

            $fields = array_filter([
                'customer_name' => $data['customerName'] ?? null,
                'mobile'        => $data['mobile'] ?? null,
                'address'       => $data['address'] ?? null,
                'notes'         => $data['notes'] ?? null,
                'bag_count'     => $data['bagCount'] ?? null,
                'scheduled_for' => $data['scheduledFor'] ?? null,
            ], fn ($v) => $v !== null);

            if ($fields) {
                $this->assertCanEdit($pickup, $user);
                $pickup->update($fields);
            }

            return $pickup->refresh()->load(self::RELATIONS);
        });
    }

    private function applyAction(PickupRequest $pickup, string $action, User $user): void
    {
        $holder = $pickup->assigned_driver_id;
        $isDriver = $user->role === 'delivery';
        $heldByOther = $isDriver && $holder !== null && $holder !== $user->id;

        switch ($action) {
            case 'claim':
                abort_unless($isDriver, 403, 'Only drivers can claim pickups.');
                if ($holder === $user->id) return;
                if ($heldByOther) $this->conflict($pickup, 'Another driver already took this pickup.');
                $this->assertOpen($pickup);
                $pickup->update(['assigned_driver_id' => $user->id, 'status' => 'assigned', 'assigned_at' => now()]);
                return;

            case 'release':
                if ($holder === null) return;
                if ($heldByOther) $this->conflict($pickup, 'This pickup belongs to another driver.');
                abort_unless($pickup->status === 'assigned', 422, 'Laundry was already picked up — mark it dropped at the shop instead.');
                $pickup->update(['assigned_driver_id' => null, 'status' => 'requested', 'assigned_at' => null]);
                return;

            case 'pick_up':
                if (in_array($pickup->status, ['picked_up', 'received'])) return;
                if ($heldByOther) $this->conflict($pickup, 'This pickup belongs to another driver.');
                $this->assertOpen($pickup);
                $pickup->update([
                    // Picked up straight from the pool (e.g. the claim was queued offline).
                    'assigned_driver_id' => $holder ?? ($isDriver ? $user->id : null),
                    'assigned_at'        => $pickup->assigned_at ?? now(),
                    'status'             => 'picked_up',
                    'picked_up_at'       => now(),
                ]);
                return;

            case 'reopen':
                if ($pickup->status === 'assigned') return;
                if ($heldByOther) $this->conflict($pickup, 'This pickup belongs to another driver.');
                abort_unless($pickup->status === 'picked_up', 422, 'Only a picked-up pickup can be reopened.');
                $pickup->update(['status' => 'assigned', 'picked_up_at' => null]);
                return;

            case 'receive':
                if ($pickup->status === 'received') return;
                if ($heldByOther) $this->conflict($pickup, 'This pickup belongs to another driver.');
                abort_unless($pickup->status === 'picked_up', 422, 'Laundry has not been picked up yet.');
                $pickup->update(['status' => 'received', 'received_at' => now()]);
                return;

            case 'cancel':
                if ($pickup->status === 'cancelled') return;
                abort_if($pickup->status === 'received', 422, 'This pickup was already received at the shop.');
                abort_unless(
                    $user->role === 'admin' || ($isDriver && $pickup->created_by === $user->id),
                    403,
                    'Only admin or the driver who created this pickup can cancel it.',
                );
                $pickup->update(['status' => 'cancelled', 'cancelled_at' => now()]);
                return;
        }
    }

    private function assignByAdmin(PickupRequest $pickup, ?int $driverId, User $user): void
    {
        abort_unless($user->role === 'admin', 403, 'Only admin can assign pickups.');
        $this->assertOpen($pickup);

        if ($driverId === null) {
            abort_unless($pickup->status !== 'picked_up', 422, 'Laundry was already picked up.');
            $pickup->update(['assigned_driver_id' => null, 'status' => 'requested', 'assigned_at' => null]);
            return;
        }

        $this->assertDriver($driverId, $user->tenant_id);
        $pickup->update([
            'assigned_driver_id' => $driverId,
            'status'             => $pickup->status === 'requested' ? 'assigned' : $pickup->status,
            'assigned_at'        => now(),
        ]);
    }

    private function assertCanEdit(PickupRequest $pickup, User $user): void
    {
        $this->assertOpen($pickup);
        if ($user->role === 'delivery') {
            abort_unless(
                $pickup->assigned_driver_id === null || $pickup->assigned_driver_id === $user->id,
                403,
                'This pickup belongs to another driver.',
            );
        }
    }

    private function assertOpen(PickupRequest $pickup): void
    {
        abort_unless(in_array($pickup->status, PickupRequest::OPEN_STATUSES), 422, 'This pickup is already closed.');
    }

    private function assertDriver(int $driverId, int $tenantId): void
    {
        abort_unless(
            User::where('id', $driverId)->where('tenant_id', $tenantId)->where('role', 'delivery')->exists(),
            422,
            'Driver not found.',
        );
    }

    /** 409 with who holds the pickup, so the app can say "Already taken by Juan". */
    private function conflict(PickupRequest $pickup, string $message): never
    {
        $holder = $pickup->assignedDriver()->first(['id', 'name']);

        throw new HttpResponseException(response()->json([
            'message'        => $message,
            'assignedDriver' => $holder ? ['id' => $holder->id, 'name' => $holder->name] : null,
        ], 409));
    }
}
