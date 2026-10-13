<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private Branch $main;

    private Branch $canaman;

    private Tenant $other;

    private Branch $otherBranch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = $this->makeTenant('quinns-laundry');
        $this->main = $this->tenant->branches()->create(['name' => 'Main Branch']);
        $this->canaman = $this->tenant->branches()->create(['name' => 'Canaman Branch']);
        $this->other = $this->makeTenant('other-laundry');
        $this->otherBranch = $this->other->branches()->create(['name' => 'Other']);
    }

    private function makeTenant(string $slug): Tenant
    {
        return Tenant::create(['name' => $slug, 'slug' => $slug, 'email' => "{$slug}@example.com", 'plan' => 'pro']);
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

    private function makeCustomer(Branch $branch, string $nickname, ?string $mobile = null): Customer
    {
        return Customer::create([
            'nickname' => $nickname,
            'mobile' => $mobile,
            'tenant_id' => $branch->tenant_id,
            'branch_id' => $branch->id,
        ]);
    }

    private function makeOrder(Branch $branch, Customer $customer, string $createdAt = '2026-02-01 10:00', string $orderStatus = 'completed', float $total = 100): Order
    {
        $order = new Order([
            'customer_id' => $customer->id,
            'fulfillment_type' => 'walk-in',
            'subtotal' => $total,
            'delivery_fee' => 0,
            'total' => $total,
            'payment_status' => 'paid_cash',
            'order_status' => $orderStatus,
            'tenant_id' => $branch->tenant_id,
            'branch_id' => $branch->id,
        ]);
        $order->created_at = Carbon::parse($createdAt, 'Asia/Manila')->setTimezone('UTC');
        $order->save();
        $order->items()->create(['item_id' => '5', 'label' => 'Wash & Fold', 'qty' => 1, 'price' => $total]);

        return $order;
    }

    private function orderNumbers(User $user, string $query): array
    {
        return array_column($this->actingAs($user)->getJson("/api/v1/orders?{$query}")->assertOk()->json('data'), 'orderNumber');
    }

    private function customerNicknames(User $user, string $query): array
    {
        return array_column($this->actingAs($user)->getJson("/api/v1/customers?{$query}")->assertOk()->json('data'), 'nickname');
    }

    public function test_customer_search_matches_partial_name_or_phone_within_tenant(): void
    {
        $this->makeCustomer($this->main, 'Maria Santos', '09171234567');
        $this->makeCustomer($this->canaman, 'Juan', '09189990000');
        $this->makeCustomer($this->main, 'Mariel', null);
        $this->makeCustomer($this->otherBranch, 'Maria Outsider', '09171234567');

        $staff = $this->makeUser('staff', $this->canaman);

        $this->assertEqualsCanonicalizing(['Maria Santos', 'Mariel'], $this->customerNicknames($staff, 'search=mari'));
        $this->assertEqualsCanonicalizing(['Maria Santos'], $this->customerNicknames($staff, 'search=SANTOS'));
        $this->assertEqualsCanonicalizing(['Maria Santos'], $this->customerNicknames($staff, 'search=1234567'));
        $this->assertEqualsCanonicalizing(['Juan'], $this->customerNicknames($staff, 'search=0918'));
        $this->assertSame([], $this->customerNicknames($staff, 'search=outsider'));
    }

    public function test_customer_list_returns_every_customer_with_visit_and_spend(): void
    {
        $maria = $this->makeCustomer($this->main, 'Maria', '09171234567');
        $this->makeCustomer($this->main, 'Juan', '09189990000');
        $this->makeOrder($this->main, $maria, '2026-02-01 10:00', total: 150);
        $this->makeOrder($this->canaman, $maria, '2026-03-01 10:00', total: 250);

        $response = $this->actingAs($this->makeUser('staff', $this->main))->getJson('/api/v1/customers')->assertOk();

        $this->assertCount(2, $response->json('data'));
        $row = collect($response->json('data'))->firstWhere('nickname', 'Maria');
        $this->assertEquals(400, $row['total_spend']);
        $this->assertStringStartsWith('2026-03-01', $row['last_visit']);
    }

    public function test_order_search_matches_customer_nickname_only_within_tenant(): void
    {
        $maria = $this->makeCustomer($this->main, 'Maria Santos', '09171234567');
        $juan = $this->makeCustomer($this->main, 'Juan', '09189990000');
        $outsider = $this->makeCustomer($this->otherBranch, 'Maria Outsider', '09171234567');

        $mariaOrder = $this->makeOrder($this->main, $maria);
        $juanOrder = $this->makeOrder($this->main, $juan);
        $this->makeOrder($this->otherBranch, $outsider);

        $admin = $this->makeUser('admin');

        $this->assertSame([$mariaOrder->order_number], $this->orderNumbers($admin, 'search=maria'));
        $this->assertSame([$mariaOrder->order_number], $this->orderNumbers($admin, 'search=SANTOS'));
        $this->assertSame([$juanOrder->order_number], $this->orderNumbers($admin, 'search=jua'));
        $this->assertSame([], $this->orderNumbers($admin, 'search=1234567'));
        $this->assertSame([], $this->orderNumbers($admin, 'search=outsider'));
    }

    public function test_order_search_keeps_branch_scope_and_other_filters(): void
    {
        $maria = $this->makeCustomer($this->main, 'Maria', '09171234567');

        $mainFeb = $this->makeOrder($this->main, $maria, '2026-02-01 10:00');
        $this->makeOrder($this->main, $maria, '2026-03-01 10:00');
        $this->makeOrder($this->main, $maria, '2026-02-02 10:00', orderStatus: 'cancelled');
        $this->makeOrder($this->canaman, $maria, '2026-02-01 10:00');

        $staff = $this->makeUser('staff', $this->main);

        $this->assertSame(
            [$mainFeb->order_number],
            $this->orderNumbers($staff, 'search=mar&order_status=completed&from=2026-02-01&to=2026-02-28')
        );
    }

    public function test_search_query_count_does_not_grow_with_results(): void
    {
        $admin = $this->makeUser('admin');

        $countQueries = function (string $uri) use ($admin): int {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->actingAs($admin)->getJson($uri)->assertOk();
            DB::disableQueryLog();

            return count(DB::getQueryLog());
        };

        $customer = $this->makeCustomer($this->main, 'Maria 0', '09170000000');
        $this->makeOrder($this->main, $customer);

        // The first request also loads the user's tenant for the tenant.active check.
        $this->actingAs($admin)->getJson('/api/v1/customers')->assertOk();

        $ordersFew = $countQueries('/api/v1/orders?search=maria');
        $customersFew = $countQueries('/api/v1/customers?search=maria');

        for ($i = 1; $i <= 15; $i++) {
            $customer = $this->makeCustomer($this->main, "Maria {$i}", '0917000000'.$i);
            $this->makeOrder($this->main, $customer);
        }

        $this->assertSame($ordersFew, $countQueries('/api/v1/orders?search=maria'));
        $this->assertSame($customersFew, $countQueries('/api/v1/customers?search=maria'));
    }
}
