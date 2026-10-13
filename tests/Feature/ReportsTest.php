<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private Branch $main;

    private Branch $canaman;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = $this->makeTenant('quinns-laundry');
        $this->main = $this->tenant->branches()->create(['name' => 'Main Branch', 'address' => 'Naga', 'phone' => '0917']);
        $this->canaman = $this->tenant->branches()->create(['name' => 'Canaman Branch']);
        $this->admin = $this->makeUser('admin');
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

    /**
     * @param  string  $createdAt  local (Asia/Manila) time
     * @param  array<int, array{0: string, 1: string, 2: int}>  $items  [itemId, label, qty]
     */
    private function makeOrder(
        Branch $branch,
        string $createdAt,
        array $items = [['5', 'Wash & Fold', 1]],
        string $paymentStatus = 'paid_cash',
        string $fulfillmentType = 'walk-in',
        string $orderStatus = 'completed',
        float $subtotal = 100,
        float $deliveryFee = 0,
        ?float $total = null,
    ): Order {
        $order = new Order([
            'fulfillment_type' => $fulfillmentType,
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'total' => $total ?? $subtotal + $deliveryFee,
            'payment_status' => $paymentStatus,
            'order_status' => $orderStatus,
            'tenant_id' => $branch->tenant_id,
            'branch_id' => $branch->id,
        ]);
        $order->created_at = Carbon::parse($createdAt, 'Asia/Manila')->setTimezone('UTC');
        $order->save();

        foreach ($items as [$itemId, $label, $qty]) {
            $order->items()->create(['item_id' => $itemId, 'label' => $label, 'qty' => $qty, 'price' => 50]);
        }

        return $order;
    }

    public function test_loads_counts_paid_unpaid_and_cancelled_orders_in_range(): void
    {
        $this->makeOrder($this->main, '2026-01-01 00:05:00', [['5', 'Wash & Fold', 2], ['7', 'Fabric Conditioner', 1]]);
        $this->makeOrder($this->main, '2026-03-15 12:00:00', [['5', 'Wash & Fold', 3]], paymentStatus: 'unpaid', fulfillmentType: 'pickup-deliver');
        $this->makeOrder($this->canaman, '2026-12-31 23:55:00', [['9', 'Dry Clean', 1]], paymentStatus: 'paid_gcash');
        $this->makeOrder($this->main, '2026-06-01 09:00:00', [['7', 'Fabric Conditioner', 4]], paymentStatus: 'unpaid', orderStatus: 'cancelled');

        // Outside the local-day range, and another tenant's order.
        $this->makeOrder($this->main, '2025-12-31 23:59:00', [['5', 'Wash & Fold', 50]]);
        $this->makeOrder($this->main, '2027-01-01 00:00:00', [['5', 'Wash & Fold', 50]]);
        $other = $this->makeTenant('other-laundry');
        $this->makeOrder($other->branches()->create(['name' => 'Other']), '2026-05-01 10:00:00', [['5', 'Wash & Fold', 50]]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/reports/loads?from=2026-01-01&to=2026-12-31');

        $response->assertOk()->assertExactJson([
            'from' => '2026-01-01 00:00:00',
            'to' => '2026-12-31 23:59:59',
            'branch' => null,
            'totalLoads' => 11,
            'orderCount' => 4,
            'breakdown' => [
                ['itemId' => '5', 'label' => 'Wash & Fold', 'qty' => 5],
                ['itemId' => '7', 'label' => 'Fabric Conditioner', 'qty' => 5],
                ['itemId' => '9', 'label' => 'Dry Clean', 'qty' => 1],
            ],
            'ordersByFulfillment' => ['pickup_deliver' => 1, 'walk_in' => 3],
        ]);

        // Same order set as the orders list for the same range.
        $orders = $this->actingAs($this->admin)->getJson('/api/v1/orders?from=2026-01-01&to=2026-12-31');
        $this->assertCount(4, $orders->json('data'));
        $this->assertSame(11, collect($orders->json('data'))->flatMap(fn ($o) => $o['items'])->sum('qty'));
    }

    public function test_loads_filters_by_branch_for_admin(): void
    {
        $this->makeOrder($this->main, '2026-02-01 10:00:00', [['5', 'Wash & Fold', 2]]);
        $this->makeOrder($this->canaman, '2026-02-01 10:00:00', [['5', 'Wash & Fold', 7]], fulfillmentType: 'pickup-deliver');

        $response = $this->actingAs($this->admin)
            ->getJson("/api/v1/reports/loads?from=2026-02-01&to=2026-02-01&branch_id={$this->canaman->id}");

        $response->assertOk()
            ->assertJsonPath('branch.id', $this->canaman->id)
            ->assertJsonPath('branch.name', 'Canaman Branch')
            ->assertJsonPath('totalLoads', 7)
            ->assertJsonPath('orderCount', 1)
            ->assertJsonPath('breakdown', [['itemId' => '5', 'label' => 'Wash & Fold', 'qty' => 7]])
            ->assertJsonPath('ordersByFulfillment', ['pickup_deliver' => 1]);
    }

    public function test_loads_returns_zeros_and_empty_collections_for_empty_range(): void
    {
        $this->makeOrder($this->main, '2026-02-01 10:00:00');

        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/reports/loads?from=2026-03-01&to=2026-03-31');

        $response->assertOk()
            ->assertJsonPath('totalLoads', 0)
            ->assertJsonPath('orderCount', 0)
            ->assertJsonPath('breakdown', []);
        $this->assertStringContainsString('"breakdown":[]', $response->getContent());
        $this->assertStringContainsString('"ordersByFulfillment":{}', $response->getContent());
    }

    public function test_loads_normalizes_fulfillment_keys(): void
    {
        $this->makeOrder($this->main, '2026-02-01 10:00:00', fulfillmentType: 'walk-in');
        $this->makeOrder($this->main, '2026-02-01 11:00:00', fulfillmentType: 'Walk-In');
        $this->makeOrder($this->main, '2026-02-01 12:00:00', fulfillmentType: 'walk_in');
        $this->makeOrder($this->main, '2026-02-01 13:00:00', fulfillmentType: 'pickup-deliver');

        $this->actingAs($this->admin)
            ->getJson('/api/v1/reports/loads?from=2026-02-01&to=2026-02-01')
            ->assertOk()
            ->assertJsonPath('ordersByFulfillment', ['pickup_deliver' => 1, 'walk_in' => 3]);
    }

    public function test_loads_uses_latest_label_for_renamed_item(): void
    {
        $this->makeOrder($this->main, '2026-02-01 10:00:00', [['5', 'Wash Fold', 1]]);
        $this->makeOrder($this->main, '2026-02-02 10:00:00', [['5', 'Wash & Fold', 2]]);

        $this->actingAs($this->admin)
            ->getJson('/api/v1/reports/loads?from=2026-02-01&to=2026-02-02')
            ->assertOk()
            ->assertJsonPath('breakdown', [['itemId' => '5', 'label' => 'Wash & Fold', 'qty' => 3]]);
    }

    public function test_loads_is_admin_only(): void
    {
        $this->actingAs($this->makeUser('staff', $this->main))
            ->getJson('/api/v1/reports/loads?from=2026-02-01&to=2026-02-01')
            ->assertForbidden();
    }

    public function test_unpaid_figures_equal_sum_of_order_total_from_orders_list(): void
    {
        // total deliberately differs from subtotal + delivery_fee - discount_amount, which the API never enforces.
        $this->makeOrder($this->main, '2026-02-01 10:00:00', paymentStatus: 'unpaid', subtotal: 200, deliveryFee: 75, total: 250);
        $this->makeOrder($this->main, '2026-02-02 10:00:00', paymentStatus: 'unpaid', orderStatus: 'cancelled', subtotal: 100, deliveryFee: 0, total: 100);
        $this->makeOrder($this->canaman, '2026-02-02 10:00:00', paymentStatus: 'unpaid', subtotal: 500, total: 500);
        $this->makeOrder($this->main, '2026-02-02 11:00:00', paymentStatus: 'paid_cash', subtotal: 300, deliveryFee: 75, total: 370);
        $this->makeOrder($this->main, '2026-02-03 10:00:00', paymentStatus: 'unpaid', subtotal: 999, total: 999);

        $query = "from=2026-02-01&to=2026-02-02&branch_id={$this->main->id}";

        $orders = $this->actingAs($this->admin)->getJson("/api/v1/orders?{$query}&payment_status=unpaid")->json('data');
        $expectedAmount = collect($orders)->sum('total');
        $this->assertCount(2, $orders);
        $this->assertEquals(350.0, $expectedAmount);

        $this->actingAs($this->admin)->getJson("/api/v1/reports/sales?{$query}")
            ->assertOk()
            ->assertJsonPath('outstanding', $expectedAmount)
            ->assertJsonPath('unpaidOrders', 2)
            ->assertJsonPath('collected', 370);

        $this->actingAs($this->admin)->getJson("/api/v1/reports/sales-by-payment-type?{$query}")
            ->assertOk()
            ->assertJsonPath('unpaid.amount', $expectedAmount)
            ->assertJsonPath('unpaid.transactions', 2);
    }
}
