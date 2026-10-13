<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Order;
use App\Models\PaymentHistory;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * date_basis=paid on GET /orders and GET /reports/sales-by-payment-type: an order is bucketed by
 * the most recent payment_history transition into its current paid status.
 */
class PaidDateBasisTest extends TestCase
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
        $this->main = $this->tenant->branches()->create(['name' => 'Main Branch']);
        $this->canaman = $this->tenant->branches()->create(['name' => 'Canaman Branch']);
        $this->admin = $this->tenant->users()->create([
            'name' => 'Admin',
            'username' => 'admin'.uniqid(),
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
            'must_change_password' => false,
        ]);
    }

    private function makeTenant(string $slug): Tenant
    {
        return Tenant::create(['name' => $slug, 'slug' => $slug, 'email' => "{$slug}@example.com", 'plan' => 'pro']);
    }

    private function local(string $time): Carbon
    {
        return Carbon::parse($time, 'Asia/Manila')->setTimezone('UTC');
    }

    /**
     * @param  array<int, array{0: string, 1: string, 2: string}>  $history  [from, to, local changed_at]
     */
    private function makeOrder(Branch $branch, string $createdAt, string $paymentStatus, array $history, float $total = 100): Order
    {
        $order = new Order([
            'fulfillment_type' => 'walk-in',
            'subtotal' => $total,
            'delivery_fee' => 0,
            'total' => $total,
            'payment_status' => $paymentStatus,
            'order_status' => 'completed',
            'tenant_id' => $branch->tenant_id,
            'branch_id' => $branch->id,
        ]);
        $order->created_at = $this->local($createdAt);
        $order->save();

        foreach ($history as [$from, $to, $changedAt]) {
            PaymentHistory::create([
                'order_number' => $order->order_number,
                'from_status' => $from,
                'to_status' => $to,
                'changed_at' => $this->local($changedAt),
            ]);
        }

        return $order;
    }

    /** @return array<int, string> order numbers across every page */
    private function paidOrderNumbers(string $query): array
    {
        $numbers = [];
        $cursor = null;

        do {
            $response = $this->actingAs($this->admin)
                ->getJson("/api/v1/orders?date_basis=paid&{$query}".($cursor ? "&cursor={$cursor}" : ''))
                ->assertOk();
            $numbers = array_merge($numbers, array_column($response->json('data'), 'orderNumber'));
            $cursor = $response->json('nextCursor');
        } while ($cursor);

        return $numbers;
    }

    private function paymentTypeBreakdown(string $query): array
    {
        return collect(
            $this->actingAs($this->admin)
                ->getJson("/api/v1/reports/sales-by-payment-type?date_basis=paid&{$query}")
                ->assertOk()
                ->json('breakdown')
        )->mapWithKeys(fn ($row) => [$row['payment_method'] => [$row['transactions'], $row['payment_amount']]])->all();
    }

    public function test_orders_are_bucketed_by_latest_transition_into_current_status(): void
    {
        // Created in January, paid in February.
        $paidLater = $this->makeOrder($this->main, '2026-01-20 10:00', 'paid_cash', [['unpaid', 'paid_cash', '2026-02-03 09:00']], 100);

        // Corrected: paid_cash in February, back to unpaid, then paid_gcash in March.
        $corrected = $this->makeOrder($this->main, '2026-02-01 08:00', 'paid_gcash', [
            ['unpaid', 'paid_cash', '2026-02-01 09:00'],
            ['paid_cash', 'unpaid', '2026-02-02 09:00'],
            ['unpaid', 'paid_gcash', '2026-03-05 09:00'],
        ], 200);

        // Reached paid_cash twice; only the later (March) transition counts.
        $repaid = $this->makeOrder($this->main, '2026-02-10 08:00', 'paid_cash', [
            ['unpaid', 'paid_cash', '2026-02-10 09:00'],
            ['paid_cash', 'unpaid', '2026-02-11 09:00'],
            ['unpaid', 'paid_cash', '2026-03-01 00:00'],
        ], 300);

        // Two transitions into the current status at the same instant count once.
        $tied = $this->makeOrder($this->main, '2026-02-14 08:00', 'paid_bank', [
            ['unpaid', 'paid_bank', '2026-02-14 09:00'],
            ['unpaid', 'paid_bank', '2026-02-14 09:00'],
        ], 400);

        // Paid at creation, on the first second of the range.
        $paidAtCreation = $this->makeOrder($this->main, '2026-02-01 00:00', 'paid_cash', [['unpaid', 'paid_cash', '2026-02-01 00:00']], 50);

        // Excluded: currently unpaid, other branch, other tenant, no matching history.
        $this->makeOrder($this->main, '2026-02-05 08:00', 'unpaid', [['unpaid', 'paid_cash', '2026-02-05 09:00'], ['paid_cash', 'unpaid', '2026-02-06 09:00']]);
        $otherBranch = $this->makeOrder($this->canaman, '2026-02-05 08:00', 'paid_cash', [['unpaid', 'paid_cash', '2026-02-05 09:00']], 700);
        $other = $this->makeTenant('other-laundry');
        $this->makeOrder($other->branches()->create(['name' => 'Other']), '2026-02-05 08:00', 'paid_cash', [['unpaid', 'paid_cash', '2026-02-05 09:00']]);
        $this->makeOrder($this->main, '2026-02-07 08:00', 'paid_cash', [['unpaid', 'paid_gcash', '2026-02-07 09:00']]);

        $feb = "from=2026-02-01&to=2026-02-28&branch_id={$this->main->id}";
        $mar = "from=2026-03-01&to=2026-03-31&branch_id={$this->main->id}";

        $this->assertSame(
            [$tied->order_number, $paidAtCreation->order_number, $paidLater->order_number],
            $this->paidOrderNumbers($feb)
        );
        $this->assertSame([$repaid->order_number, $corrected->order_number], $this->paidOrderNumbers($mar));
        $this->assertSame([], $this->paidOrderNumbers("from=2026-01-01&to=2026-01-31&branch_id={$this->main->id}"));
        $this->assertEqualsCanonicalizing(
            [$tied->order_number, $paidAtCreation->order_number, $paidLater->order_number, $otherBranch->order_number],
            $this->paidOrderNumbers('from=2026-02-01&to=2026-02-28')
        );

        $this->assertEquals(['paid_bank' => [1, 400], 'paid_cash' => [2, 150]], $this->paymentTypeBreakdown($feb));
        $this->assertEquals(['paid_cash' => [1, 300], 'paid_gcash' => [1, 200]], $this->paymentTypeBreakdown($mar));
        $this->assertEquals([], $this->paymentTypeBreakdown("from=2026-01-01&to=2026-01-31&branch_id={$this->main->id}"));
        $this->assertEquals(['paid_bank' => [1, 400], 'paid_cash' => [3, 850]], $this->paymentTypeBreakdown('from=2026-02-01&to=2026-02-28'));
    }

    public function test_paid_orders_page_through_every_match_exactly_once_newest_first(): void
    {
        $expected = [];

        for ($day = 1; $day <= 70; $day++) {
            $created = Carbon::create(2026, 1, 1, 10)->addDays($day - 1)->format('Y-m-d H:i');
            $paidAt = Carbon::create(2026, 1, 1, 10)->addDays($day + 4)->format('Y-m-d H:i');
            $order = $this->makeOrder($this->main, $created, 'paid_cash', [['unpaid', 'paid_cash', $paidAt]]);

            if ($paidAt >= '2026-02-01' && $paidAt < '2026-03-01') {
                $expected[] = $order->order_number;
            }
        }

        $this->assertCount(28, $expected);
        $this->assertSame(array_reverse($expected), $this->paidOrderNumbers('from=2026-02-01&to=2026-02-28'));
    }
}
