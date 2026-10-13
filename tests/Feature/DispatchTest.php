<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Order;
use App\Models\PickupRequest;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class DispatchTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private Branch $main;

    private Branch $canaman;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Quinns', 'slug' => 'quinns', 'email' => 'quinns@example.com', 'plan' => 'pro',
        ]);
        $this->main = $this->tenant->branches()->create(['name' => 'Main Branch']);
        $this->canaman = $this->tenant->branches()->create(['name' => 'Canaman Branch']);
    }

    private function makeUser(string $role, string $name = null, ?Branch $branch = null): User
    {
        return $this->tenant->users()->create([
            'name' => $name ?? ucfirst($role),
            'username' => $role.uniqid(),
            'password' => Hash::make('password'),
            'role' => $role,
            'branch_id' => ($branch ?? $this->main)->id,
            'is_active' => true,
            'must_change_password' => false,
        ]);
    }

    private function makeOrder(string $orderStatus = 'ready'): Order
    {
        $customer = Customer::create([
            'nickname' => 'Maria', 'tenant_id' => $this->tenant->id, 'branch_id' => $this->main->id,
        ]);

        return Order::create([
            'order_number' => (string) Str::uuid(),
            'customer_id' => $customer->id,
            'fulfillment_type' => 'pickup-deliver',
            'subtotal' => 150, 'delivery_fee' => 75, 'total' => 225,
            'payment_status' => 'unpaid',
            'order_status' => $orderStatus,
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->main->id,
        ]);
    }

    private function newPickup(array $overrides = []): array
    {
        return array_merge([
            'id' => (string) Str::uuid(),
            'branchId' => $this->canaman->id,
            'customerName' => 'Juan',
            'address' => 'Canaman, Camarines Sur',
        ], $overrides);
    }

    // ── Orders ────────────────────────────────────────────────────────────

    public function test_driver_claims_order_and_second_driver_gets_conflict(): void
    {
        $order = $this->makeOrder();
        $juan = $this->makeUser('delivery', 'Juan');
        $pedro = $this->makeUser('delivery', 'Pedro');
        $url = "/api/v1/orders/{$order->order_number}/assignment";

        $this->actingAs($juan)->patchJson($url, ['action' => 'claim'])
            ->assertOk()
            ->assertJsonPath('data.deliveryStatus', 'assigned')
            ->assertJsonPath('data.assignedDriver.name', 'Juan');

        // Repeating a claim (offline retry) is a no-op.
        $this->actingAs($juan)->patchJson($url, ['action' => 'claim'])->assertOk();

        $this->actingAs($pedro)->patchJson($url, ['action' => 'claim'])
            ->assertStatus(409)
            ->assertJsonPath('assignedDriver.name', 'Juan');

        $this->actingAs($pedro)->patchJson($url, ['action' => 'release'])->assertStatus(409);
        $this->assertSame($juan->id, $order->fresh()->assigned_driver_id);
    }

    public function test_assigned_to_filter_splits_pool_and_my_orders(): void
    {
        $mine = $this->makeOrder();
        $pool = $this->makeOrder();
        $theirs = $this->makeOrder();
        $juan = $this->makeUser('delivery', 'Juan');
        $pedro = $this->makeUser('delivery', 'Pedro');

        $this->actingAs($juan)->patchJson("/api/v1/orders/{$mine->order_number}/assignment", ['action' => 'claim']);
        $this->actingAs($pedro)->patchJson("/api/v1/orders/{$theirs->order_number}/assignment", ['action' => 'claim']);

        $this->actingAs($juan)->getJson('/api/v1/orders?assigned_to=me')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.orderNumber', $mine->order_number);

        $this->actingAs($juan)->getJson('/api/v1/orders?assigned_to=none')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.orderNumber', $pool->order_number);
    }

    public function test_release_returns_order_to_pool(): void
    {
        $order = $this->makeOrder();
        $juan = $this->makeUser('delivery');
        $url = "/api/v1/orders/{$order->order_number}/assignment";

        $this->actingAs($juan)->patchJson($url, ['action' => 'claim']);
        $this->actingAs($juan)->patchJson($url, ['action' => 'release'])
            ->assertOk()
            ->assertJsonPath('data.deliveryStatus', 'unassigned')
            ->assertJsonPath('data.assignedDriver', null);
    }

    public function test_delivered_completes_order_with_history_and_is_idempotent(): void
    {
        $order = $this->makeOrder();
        $juan = $this->makeUser('delivery');
        $url = "/api/v1/orders/{$order->order_number}/delivery-status";

        $this->actingAs($juan)->patchJson("/api/v1/orders/{$order->order_number}/assignment", ['action' => 'claim']);
        $this->actingAs($juan)->patchJson($url, ['deliveryStatus' => 'delivered'])
            ->assertOk()
            ->assertJsonPath('data.deliveryStatus', 'delivered')
            ->assertJsonPath('data.orderStatus', 'completed');
        $this->actingAs($juan)->patchJson($url, ['deliveryStatus' => 'delivered'])->assertOk();

        $this->assertSame(1, $order->orderStatusHistory()->where('to_status', 'completed')->count());
    }

    public function test_delivering_an_unclaimed_order_claims_it(): void
    {
        $order = $this->makeOrder();
        $juan = $this->makeUser('delivery');

        $this->actingAs($juan)
            ->patchJson("/api/v1/orders/{$order->order_number}/delivery-status", ['deliveryStatus' => 'delivered'])
            ->assertOk();

        $this->assertSame($juan->id, $order->fresh()->assigned_driver_id);
    }

    public function test_cannot_deliver_another_drivers_order(): void
    {
        $order = $this->makeOrder();
        $juan = $this->makeUser('delivery', 'Juan');

        $this->actingAs($juan)->patchJson("/api/v1/orders/{$order->order_number}/assignment", ['action' => 'claim']);
        $this->actingAs($this->makeUser('delivery', 'Pedro'))
            ->patchJson("/api/v1/orders/{$order->order_number}/delivery-status", ['deliveryStatus' => 'delivered'])
            ->assertStatus(409);

        $this->assertSame('ready', $order->fresh()->order_status);
    }

    public function test_failed_delivery_returns_order_to_pool_with_reason(): void
    {
        $order = $this->makeOrder();
        $juan = $this->makeUser('delivery');

        $this->actingAs($juan)->patchJson("/api/v1/orders/{$order->order_number}/assignment", ['action' => 'claim']);
        $this->actingAs($juan)
            ->patchJson("/api/v1/orders/{$order->order_number}/delivery-status", ['deliveryStatus' => 'failed', 'reason' => 'Nobody home'])
            ->assertOk()
            ->assertJsonPath('data.deliveryStatus', 'unassigned');

        $this->assertSame('Nobody home', $order->fresh()->delivery_failed_reason);
        $this->assertNull($order->fresh()->assigned_driver_id);
    }

    public function test_staff_cannot_claim_orders(): void
    {
        $order = $this->makeOrder();

        $this->actingAs($this->makeUser('staff'))
            ->patchJson("/api/v1/orders/{$order->order_number}/assignment", ['action' => 'claim'])
            ->assertForbidden();
    }

    // ── Pickups ───────────────────────────────────────────────────────────

    public function test_driver_created_pickup_is_assigned_to_them_and_create_is_idempotent(): void
    {
        $juan = $this->makeUser('delivery', 'Juan');
        $payload = $this->newPickup();

        $this->actingAs($juan)->postJson('/api/v1/pickup-requests', $payload)
            ->assertCreated()
            ->assertJsonPath('data.status', 'assigned')
            ->assertJsonPath('data.source', 'driver')
            ->assertJsonPath('data.assignedDriver.name', 'Juan')
            ->assertJsonPath('data.branch.name', 'Canaman Branch');

        $this->actingAs($juan)->postJson('/api/v1/pickup-requests', $payload)->assertSuccessful();
        $this->assertSame(1, PickupRequest::count());
    }

    public function test_admin_pickup_goes_to_pool_and_only_one_driver_can_claim(): void
    {
        $id = $this->actingAs($this->makeUser('admin'))
            ->postJson('/api/v1/pickup-requests', $this->newPickup())
            ->assertJsonPath('data.status', 'requested')
            ->json('data.id');

        $juan = $this->makeUser('delivery', 'Juan');
        $pedro = $this->makeUser('delivery', 'Pedro');

        $this->actingAs($juan)->getJson('/api/v1/pickup-requests')->assertJsonCount(1, 'data');

        $this->actingAs($juan)->patchJson("/api/v1/pickup-requests/{$id}", ['action' => 'claim'])
            ->assertOk()->assertJsonPath('data.status', 'assigned');
        $this->actingAs($pedro)->patchJson("/api/v1/pickup-requests/{$id}", ['action' => 'claim'])
            ->assertStatus(409)->assertJsonPath('assignedDriver.name', 'Juan');

        // Claimed by Juan → no longer in Pedro's pool.
        $this->actingAs($pedro)->getJson('/api/v1/pickup-requests')->assertJsonCount(0, 'data');
    }

    public function test_pickup_lifecycle_pick_up_reopen_receive(): void
    {
        $juan = $this->makeUser('delivery');
        $id = $this->actingAs($juan)->postJson('/api/v1/pickup-requests', $this->newPickup())->json('data.id');
        $url = "/api/v1/pickup-requests/{$id}";

        $this->actingAs($juan)->patchJson($url, ['action' => 'pick_up', 'bagCount' => 2])
            ->assertJsonPath('data.status', 'picked_up')->assertJsonPath('data.bagCount', 2);
        $this->actingAs($juan)->patchJson($url, ['action' => 'pick_up'])->assertOk();
        $this->actingAs($juan)->patchJson($url, ['action' => 'reopen'])->assertJsonPath('data.status', 'assigned');
        $this->actingAs($juan)->patchJson($url, ['action' => 'release'])->assertJsonPath('data.status', 'requested');
        $this->actingAs($juan)->patchJson($url, ['action' => 'pick_up'])->assertJsonPath('data.status', 'picked_up');
        $this->actingAs($juan)->patchJson($url, ['action' => 'receive'])->assertJsonPath('data.status', 'received');

        // Received pickups leave the driver's list.
        $this->actingAs($juan)->getJson('/api/v1/pickup-requests')->assertJsonCount(0, 'data');
    }

    public function test_cannot_release_after_pick_up(): void
    {
        $juan = $this->makeUser('delivery');
        $id = $this->actingAs($juan)->postJson('/api/v1/pickup-requests', $this->newPickup())->json('data.id');

        $this->actingAs($juan)->patchJson("/api/v1/pickup-requests/{$id}", ['action' => 'pick_up']);
        $this->actingAs($juan)->patchJson("/api/v1/pickup-requests/{$id}", ['action' => 'release'])->assertStatus(422);
    }

    public function test_only_creator_or_admin_can_cancel(): void
    {
        $juan = $this->makeUser('delivery');
        $id = $this->actingAs($juan)->postJson('/api/v1/pickup-requests', $this->newPickup())->json('data.id');
        $this->actingAs($juan)->patchJson("/api/v1/pickup-requests/{$id}", ['action' => 'release']);

        $this->actingAs($this->makeUser('delivery', 'Pedro'))
            ->patchJson("/api/v1/pickup-requests/{$id}", ['action' => 'cancel'])->assertForbidden();
        $this->actingAs($juan)
            ->patchJson("/api/v1/pickup-requests/{$id}", ['action' => 'cancel'])->assertJsonPath('data.status', 'cancelled');
    }

    public function test_pickup_branch_must_belong_to_tenant(): void
    {
        $other = Tenant::create(['name' => 'Other', 'slug' => 'other', 'email' => 'o@example.com', 'plan' => 'pro']);
        $branch = $other->branches()->create(['name' => 'Other']);

        $this->actingAs($this->makeUser('delivery'))
            ->postJson('/api/v1/pickup-requests', $this->newPickup(['branchId' => $branch->id]))
            ->assertStatus(422);
    }

    public function test_staff_only_see_pickups_for_their_branch(): void
    {
        $juan = $this->makeUser('delivery');
        $this->actingAs($juan)->postJson('/api/v1/pickup-requests', $this->newPickup(['branchId' => $this->main->id]));
        $this->actingAs($juan)->postJson('/api/v1/pickup-requests', $this->newPickup(['branchId' => $this->canaman->id]));

        $this->actingAs($this->makeUser('staff', 'Staff', $this->canaman))
            ->getJson('/api/v1/pickup-requests')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.branch.name', 'Canaman Branch');
    }
}
