<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class DeliveryAllBranchesTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private Branch $main;

    private Branch $canaman;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = $this->makeTenant('quinns-laundry');
        $this->main = $this->tenant->branches()->create(['name' => 'Main Branch']);
        $this->canaman = $this->tenant->branches()->create(['name' => 'Canaman Branch']);
    }

    private function makeTenant(string $slug): Tenant
    {
        return Tenant::create([
            'name' => $slug,
            'slug' => $slug,
            'email' => "{$slug}@example.com",
            'plan' => 'pro',
        ]);
    }

    private function makeUser(string $role, ?Branch $branch = null): User
    {
        return $this->tenant->users()->create([
            'name' => ucfirst($role),
            'username' => $role.uniqid(),
            'password' => Hash::make('password'),
            'role' => $role,
            'branch_id' => $branch?->id,
            'is_active' => true,
            'must_change_password' => false,
        ]);
    }

    private function makeOrder(Branch $branch, ?Tenant $tenant = null): Order
    {
        $tenant ??= $this->tenant;
        $customer = Customer::create([
            'nickname' => 'Maria',
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
        ]);

        return Order::create([
            'order_number' => (string) Str::uuid(),
            'customer_id' => $customer->id,
            'fulfillment_type' => 'pickup-deliver',
            'subtotal' => 150,
            'delivery_fee' => 75,
            'total' => 225,
            'payment_status' => 'unpaid',
            'order_status' => 'ready',
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
        ]);
    }

    public function test_delivery_lists_orders_from_every_branch_of_their_tenant(): void
    {
        $this->makeOrder($this->main);
        $this->makeOrder($this->canaman);

        $other = $this->makeTenant('other-laundry');
        $this->makeOrder($other->branches()->create(['name' => 'Other']), $other);

        $response = $this->actingAs($this->makeUser('delivery', $this->main))
            ->getJson('/api/v1/orders');

        $response->assertOk()->assertJsonCount(2, 'data');
        $this->assertEqualsCanonicalizing(
            [$this->main->id, $this->canaman->id],
            array_column(array_column($response->json('data'), 'branch'), 'id'),
        );
    }

    public function test_delivery_branch_id_filter_applies(): void
    {
        $this->makeOrder($this->main);
        $this->makeOrder($this->canaman);

        $this->actingAs($this->makeUser('delivery', $this->main))
            ->getJson("/api/v1/orders?branch_id={$this->canaman->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.branch.id', $this->canaman->id);
    }

    public function test_delivery_can_view_and_complete_order_from_another_branch(): void
    {
        $order = $this->makeOrder($this->canaman);
        $driver = $this->makeUser('delivery', $this->main);

        $this->actingAs($driver)
            ->getJson("/api/v1/orders/{$order->order_number}")
            ->assertOk();

        $this->actingAs($driver)
            ->patchJson("/api/v1/orders/{$order->order_number}/order-status", ['orderStatus' => 'completed'])
            ->assertOk();

        $this->assertSame('completed', $order->fresh()->order_status);
    }

    public function test_staff_stay_scoped_to_their_own_branch(): void
    {
        $this->makeOrder($this->main);
        $other = $this->makeOrder($this->canaman);
        $staff = $this->makeUser('staff', $this->main);

        $this->actingAs($staff)
            ->getJson("/api/v1/orders?branch_id={$this->canaman->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.branch.id', $this->main->id);

        $this->actingAs($staff)
            ->getJson("/api/v1/orders/{$other->order_number}")
            ->assertNotFound();
    }

    public function test_delivery_cannot_see_another_tenants_order(): void
    {
        $other = $this->makeTenant('other-laundry');
        $order = $this->makeOrder($other->branches()->create(['name' => 'Other']), $other);

        $this->actingAs($this->makeUser('delivery', $this->main))
            ->getJson("/api/v1/orders/{$order->order_number}")
            ->assertNotFound();
    }
}
