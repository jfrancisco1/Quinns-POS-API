<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * DemoShopSeeder — a self-contained dummy laundry shop for testers.
 *
 * Creates one tenant with 2 branches, an admin, a staff and a driver per branch,
 * categories, items, customers, and 20 orders per branch covering every
 * order / payment / delivery status.
 *
 * Usage:
 *   php artisan db:seed --class=DemoShopSeeder
 *
 * Safe to re-run: the existing demo tenant and everything under it is wiped first.
 */
class DemoShopSeeder extends Seeder
{
    private const ORDERS_PER_BRANCH = 20;

    public function run(): void
    {
        // -------------------------------------------------------
        // CONFIG
        // -------------------------------------------------------
        $tenant = [
            'name'    => 'Suds & Bubbles Laundry (Demo)',
            'slug'    => 'demo-laundry',
            'email'   => 'demo-laundry@example.com',
            'phone'   => null,
            'address' => 'Naga City, Camarines Sur',
            'plan'    => 'pro',
        ];

        $adminUser = ['name' => 'Demo Admin', 'username' => 'demo.admin', 'password' => 'demo1234'];

        $branches = [
            [
                'name'    => 'Demo Branch — Centro',
                'address' => '12 Elias Angeles St., Centro, Naga City',
                'phone'   => '09170000001',
                'users'   => [
                    ['name' => 'Demo Staff Centro',  'username' => 'demo.staff.centro',  'password' => 'demo1234', 'role' => 'staff'],
                    ['name' => 'Demo Driver Centro', 'username' => 'demo.driver.centro', 'password' => 'demo1234', 'role' => 'delivery'],
                ],
                'customers' => [
                    ['nickname' => 'Liza Manalo',     'mobile' => '09170001001', 'address' => 'Brgy. Dinaga, Naga City',       'delivery_fee' => 50],
                    ['nickname' => 'Ramon Aquino',    'mobile' => '09170001002', 'address' => 'Brgy. Sta. Cruz, Naga City',    'delivery_fee' => 0],
                    ['nickname' => 'Grace Dizon',     'mobile' => '09170001003', 'address' => 'Brgy. Lerma, Naga City',        'delivery_fee' => 60],
                    ['nickname' => 'Noel Salazar',    'mobile' => '09170001004', 'address' => 'Brgy. Tabuco, Naga City',       'delivery_fee' => 50],
                    ['nickname' => 'Joy Ramirez',     'mobile' => '09170001005', 'address' => 'Brgy. San Francisco, Naga City', 'delivery_fee' => 40],
                    ['nickname' => 'Ben Castillo',    'mobile' => '09170001006', 'address' => 'Brgy. Calauag, Naga City',      'delivery_fee' => 80],
                    ['nickname' => 'Tess Navarro',    'mobile' => '09170001007', 'address' => 'Brgy. Liboton, Naga City',      'delivery_fee' => 0],
                    ['nickname' => 'Arnel Pascual',   'mobile' => '09170001008', 'address' => 'Brgy. Balatas, Naga City',      'delivery_fee' => 70],
                ],
            ],
            [
                'name'    => 'Demo Branch — Pili',
                'address' => '45 Maharlika Hwy., San Roque, Pili, Camarines Sur',
                'phone'   => '09170000002',
                'users'   => [
                    ['name' => 'Demo Staff Pili',  'username' => 'demo.staff.pili',  'password' => 'demo1234', 'role' => 'staff'],
                    ['name' => 'Demo Driver Pili', 'username' => 'demo.driver.pili', 'password' => 'demo1234', 'role' => 'delivery'],
                ],
                'customers' => [
                    ['nickname' => 'Cora Bernardo',   'mobile' => '09170002001', 'address' => 'Brgy. Cadlan, Pili',         'delivery_fee' => 60],
                    ['nickname' => 'Dante Ocampo',    'mobile' => '09170002002', 'address' => 'Brgy. San Jose, Pili',       'delivery_fee' => 0],
                    ['nickname' => 'Weng Soriano',    'mobile' => '09170002003', 'address' => 'Brgy. Palestina, Pili',      'delivery_fee' => 80],
                    ['nickname' => 'Jojo Valdez',     'mobile' => '09170002004', 'address' => 'Brgy. Sagurong, Pili',       'delivery_fee' => 50],
                    ['nickname' => 'Mylene Robles',   'mobile' => '09170002005', 'address' => 'Brgy. Tagbong, Pili',        'delivery_fee' => 100],
                    ['nickname' => 'Ronnie Flores',   'mobile' => '09170002006', 'address' => 'Brgy. Old San Roque, Pili',  'delivery_fee' => 0],
                    ['nickname' => 'Bing Espiritu',   'mobile' => '09170002007', 'address' => 'Brgy. Anayan, Pili',         'delivery_fee' => 70],
                    ['nickname' => 'Toto Galang',     'mobile' => '09170002008', 'address' => 'Brgy. Santiago, Pili',       'delivery_fee' => 50],
                ],
            ],
        ];

        $categoryItems = [
            'Wash & Dry'   => [['Regular Load', 80, 30], ['Large Load', 120, 45]],
            'Wash & Fold'  => [['Wash & Fold Basic', 100, 40], ['Wash & Fold Plus', 150, 55]],
            'Dry Clean'    => [['Barong / Blazer', 200, 80], ['Dress', 180, 70]],
            'Iron / Press' => [['Shirt Press', 30, 10], ['Pants Press', 40, 12]],
            'Comforter'    => [['Single Comforter', 250, 100], ['Double Comforter', 350, 140]],
        ];
        // -------------------------------------------------------
        // END CONFIG
        // -------------------------------------------------------

        // Same data on every run, so testers can share bug reports against it.
        mt_srand(20261017);

        $summary = DB::transaction(function () use ($tenant, $adminUser, $branches, $categoryItems) {
            $this->wipe($tenant['slug'], $adminUser, $branches);

            $tenantId = DB::table('tenants')->insertGetId([
                ...$tenant,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $adminId = DB::table('users')->insertGetId($this->userRow($adminUser + ['role' => 'admin'], $tenantId, null));

            $expenseCategoryIds = [];
            foreach (ExpenseCategory::DEFAULT_NAMES as $i => $name) {
                $expenseCategoryIds[$name] = DB::table('expense_categories')->insertGetId([
                    'tenant_id'  => $tenantId,
                    'name'       => $name,
                    'is_active'  => true,
                    'sort_order' => $i,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $categoryIds = [];
            foreach (array_keys($categoryItems) as $i => $name) {
                $categoryIds[$name] = DB::table('categories')->insertGetId([
                    'name'       => $name,
                    'is_active'  => true,
                    'sort_order' => $i,
                    'tenant_id'  => $tenantId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $summary = [];

            foreach ($branches as $branch) {
                $branchId = DB::table('branches')->insertGetId([
                    'tenant_id'  => $tenantId,
                    'name'       => $branch['name'],
                    'address'    => $branch['address'],
                    'phone'      => $branch['phone'],
                    'is_active'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $userIds = [];
                foreach ($branch['users'] as $u) {
                    $userIds[$u['role']] = DB::table('users')->insertGetId($this->userRow($u, $tenantId, $branchId));
                }

                // Items are branch-scoped, so each branch gets its own copy of the menu.
                $items = [];
                $sort  = 0;
                foreach ($categoryItems as $category => $rows) {
                    foreach ($rows as [$name, $price, $cost]) {
                        $items[] = [
                            'id'    => DB::table('items')->insertGetId([
                                'name'        => $name,
                                'price'       => $price,
                                'cost'        => $cost,
                                'is_active'   => true,
                                'sort_order'  => $sort++,
                                'category_id' => $categoryIds[$category],
                                'tenant_id'   => $tenantId,
                                'branch_id'   => $branchId,
                                'created_at'  => now(),
                                'updated_at'  => now(),
                            ]),
                            'name'  => $name,
                            'price' => $price,
                        ];
                    }
                }

                $customers = [];
                foreach ($branch['customers'] as $c) {
                    $id = Str::uuid()->toString();
                    DB::table('customers')->insert([
                        'id'           => $id,
                        ...$c,
                        'notes'        => null,
                        'tenant_id'    => $tenantId,
                        'branch_id'    => $branchId,
                        'created_at'   => now()->subDays(45),
                        'updated_at'   => now()->subDays(45),
                    ]);
                    $customers[] = ['id' => $id, 'delivery_fee' => $c['delivery_fee']];
                }

                $this->seedOrders($tenantId, $branchId, $items, $customers, $userIds['staff'], $userIds['delivery']);
                $this->seedExpenses($tenantId, $branchId, $expenseCategoryIds, $userIds['staff'], $adminId);

                $summary[] = $branch;
            }

            return ['adminId' => $adminId, 'branches' => $summary];
        });

        $this->command->info("Tenant seeded: {$tenant['name']} (slug: {$tenant['slug']})");
        $this->command->info('Branches: ' . \count($branches) . ', orders per branch: ' . self::ORDERS_PER_BRANCH . ', expenses per branch: 10');
        $this->command->newLine();

        $rows = [[$adminUser['username'], $adminUser['password'], 'admin', 'all branches']];
        foreach ($summary['branches'] as $branch) {
            foreach ($branch['users'] as $u) {
                $rows[] = [$u['username'], $u['password'], $u['role'], $branch['name']];
            }
        }
        $this->command->table(['Username', 'Password', 'Role', 'Branch'], $rows);
    }

    /**
     * Orders follow a fixed status mix so every screen (queue, dispatch, reports) has data:
     * pending, in-progress, ready, completed and cancelled; unpaid and every paid method;
     * pickup-deliver orders in the pool, claimed by the driver, and delivered.
     */
    private function seedOrders(int $tenantId, int $branchId, array $items, array $customers, int $staffId, int $driverId): void
    {
        // [order_status, payment_status, fulfillment_type, delivery_status, days_ago]
        $plan = [
            ['pending',     'unpaid',      'walk-in',        'unassigned', 0],
            ['pending',     'unpaid',      'pickup-deliver', 'unassigned', 0],
            ['in_progress', 'unpaid',      'walk-in',        'unassigned', 0],
            ['in_progress', 'paid_cash',   'walk-in',        'unassigned', 0],
            ['in_progress', 'unpaid',      'pickup-deliver', 'unassigned', 1],
            ['in_progress', 'paid_gcash',  'walk-in',        'unassigned', 1],
            ['ready',       'unpaid',      'walk-in',        'unassigned', 1],
            ['ready',       'paid_cash',   'walk-in',        'unassigned', 2],
            ['ready',       'unpaid',      'pickup-deliver', 'unassigned', 2],
            ['ready',       'paid_gcash',  'pickup-deliver', 'assigned',   2],
            ['ready',       'unpaid',      'pickup-deliver', 'assigned',   3],
            ['completed',   'paid_cash',   'walk-in',        'unassigned', 3],
            ['completed',   'paid_gcash',  'walk-in',        'unassigned', 5],
            ['completed',   'paid_bank',   'walk-in',        'unassigned', 7],
            ['completed',   'paid_others', 'walk-in',        'unassigned', 9],
            ['completed',   'paid_cash',   'pickup-deliver', 'delivered',  10],
            ['completed',   'paid_gcash',  'pickup-deliver', 'delivered',  14],
            ['completed',   'paid_cash',   'walk-in',        'unassigned', 20],
            ['cancelled',   'unpaid',      'walk-in',        'unassigned', 4],
            ['cancelled',   'unpaid',      'pickup-deliver', 'unassigned', 12],
        ];

        $statusFlow = ['pending', 'in_progress', 'ready', 'completed'];

        foreach (\array_slice($plan, 0, self::ORDERS_PER_BRANCH) as $i => [$orderStatus, $paymentStatus, $fulfillment, $deliveryStatus, $daysAgo]) {
            $createdAt   = Carbon::now()->subDays($daysAgo)->setTime(mt_rand(8, 17), mt_rand(0, 59));
            $customer    = $customers[$i % \count($customers)];
            $orderNumber = Str::uuid()->toString();

            $subtotal  = 0;
            $orderItems = [];
            $picked    = (array) array_rand($items, mt_rand(1, 3));
            foreach ($picked as $key) {
                $qty        = mt_rand(1, 3);
                $subtotal  += $items[$key]['price'] * $qty;
                $orderItems[] = [
                    'id'           => Str::uuid()->toString(),
                    'order_number' => $orderNumber,
                    'item_id'      => $items[$key]['id'],
                    'label'        => $items[$key]['name'],
                    'qty'          => $qty,
                    'price'        => $items[$key]['price'],
                ];
            }

            $deliveryFee = $fulfillment === 'pickup-deliver' ? ($customer['delivery_fee'] ?: 50) : 0;
            $discount    = $i % 7 === 3 ? 20 : 0;

            DB::table('orders')->insert([
                'order_number'       => $orderNumber,
                'customer_id'        => $customer['id'],
                'fulfillment_type'   => $fulfillment,
                'subtotal'           => $subtotal,
                'delivery_fee'       => $deliveryFee,
                'discount_amount'    => $discount,
                'total'              => $subtotal + $deliveryFee - $discount,
                'created_at_client'  => $createdAt,
                'payment_status'     => $paymentStatus,
                'order_status'       => $orderStatus,
                'tenant_id'          => $tenantId,
                'branch_id'          => $branchId,
                'assigned_driver_id' => $deliveryStatus === 'unassigned' ? null : $driverId,
                'delivery_status'    => $deliveryStatus,
                'assigned_at'        => $deliveryStatus === 'unassigned' ? null : $createdAt->copy()->addHours(3),
                'created_at'         => $createdAt,
                'updated_at'         => $createdAt,
            ]);

            DB::table('order_items')->insert($orderItems);

            // Status history: walk the normal flow up to the current status (cancelled jumps from pending).
            $path = $orderStatus === 'cancelled'
                ? ['pending', 'cancelled']
                : \array_slice($statusFlow, 0, array_search($orderStatus, $statusFlow, true) + 1);

            $history = [];
            for ($step = 1; $step < \count($path); $step++) {
                $history[] = [
                    'order_number' => $orderNumber,
                    'from_status'  => $path[$step - 1],
                    'to_status'    => $path[$step],
                    'changed_at'   => $createdAt->copy()->addHours($step),
                    'updated_by'   => $staffId,
                ];
            }
            if ($history) {
                DB::table('order_status_history')->insert($history);
            }

            if ($paymentStatus !== 'unpaid') {
                DB::table('payment_history')->insert([
                    'order_number' => $orderNumber,
                    'from_status'  => 'unpaid',
                    'to_status'    => $paymentStatus,
                    'changed_at'   => $createdAt->copy()->addMinutes(10),
                    'updated_by'   => $staffId,
                ]);
            }
        }
    }

    /**
     * Expenses cover every default category over the last ~30 days, recorded by
     * both the branch staff and the admin, so expense lists and profit reports have data.
     */
    private function seedExpenses(int $tenantId, int $branchId, array $categoryIds, int $staffId, int $adminId): void
    {
        // [description, amount, category, days_ago, recorded_by_admin, note]
        $plan = [
            ['Detergent (5 gal)',          1250, 'Supplies',    0,  false, null],
            ['Fabric softener',             780, 'Supplies',    1,  false, null],
            ['Plastic bags & labels',       320, 'Supplies',    3,  false, null],
            ['Delivery gas',                500, 'Others',      2,  false, 'Motorcycle refill'],
            ['Dryer belt replacement',     1800, 'Maintenance', 6,  true,  'Dryer #2'],
            ['Electricity bill',           4200, 'Utilities',   8,  true,  'September billing'],
            ['Water bill',                 1350, 'Utilities',   9,  true,  null],
            ['Staff salary (half month)',  6500, 'Salaries',    15, true,  null],
            ['Bleach & stain remover',      560, 'Supplies',    18, false, null],
            ['Snacks for staff',            250, 'Others',      22, false, null],
        ];

        DB::table('expenses')->insert(array_map(function (array $row) use ($tenantId, $branchId, $categoryIds, $staffId, $adminId) {
            [$description, $amount, $category, $daysAgo, $byAdmin, $note] = $row;
            $date = Carbon::now()->subDays($daysAgo)->setTime(mt_rand(8, 17), mt_rand(0, 59));

            return [
                'id'                  => Str::uuid()->toString(),
                'description'         => $description,
                'amount'              => $amount,
                'expense_date'        => $date->toDateString(),
                'note'                => $note,
                'expense_category_id' => $categoryIds[$category],
                'user_id'             => $byAdmin ? $adminId : $staffId,
                'tenant_id'           => $tenantId,
                'branch_id'           => $branchId,
                'created_at'          => $date,
                'updated_at'          => $date,
            ];
        }, $plan));
    }

    /** Removes a previous demo run so the seeder can be re-run to reset tester data. */
    private function wipe(string $slug, array $adminUser, array $branches): void
    {
        $usernames = [$adminUser['username']];
        foreach ($branches as $branch) {
            array_push($usernames, ...array_column($branch['users'], 'username'));
        }

        $tenantId = DB::table('tenants')->where('slug', $slug)->value('id');

        if ($tenantId) {
            $orderNumbers = DB::table('orders')->where('tenant_id', $tenantId)->pluck('order_number');

            DB::table('payment_history')->whereIn('order_number', $orderNumbers)->delete();
            DB::table('order_status_history')->whereIn('order_number', $orderNumbers)->delete();
            DB::table('order_items')->whereIn('order_number', $orderNumbers)->delete();
            DB::table('orders')->where('tenant_id', $tenantId)->delete();

            foreach (['pickup_requests', 'expenses', 'customers', 'items', 'categories', 'expense_categories', 'discounts'] as $table) {
                DB::table($table)->where('tenant_id', $tenantId)->delete();
            }

            DB::table('personal_access_tokens')
                ->where('tokenable_type', \App\Models\User::class)
                ->whereIn('tokenable_id', DB::table('users')->where('tenant_id', $tenantId)->select('id'))
                ->delete();
            DB::table('users')->where('tenant_id', $tenantId)->delete();
            DB::table('branches')->where('tenant_id', $tenantId)->delete();
            DB::table('tenants')->where('id', $tenantId)->delete();
        }

        // Guard against the usernames having been taken outside the demo tenant.
        $taken = DB::table('users')->whereIn('username', $usernames)->pluck('username');
        abort_if($taken->isNotEmpty(), 1, 'Usernames already used by another tenant: ' . $taken->implode(', '));
    }

    private function userRow(array $u, int $tenantId, ?int $branchId): array
    {
        return [
            'name'       => $u['name'],
            'username'   => $u['username'],
            'password'   => Hash::make($u['password']),
            'role'       => $u['role'],
            'is_active'  => true,
            'tenant_id'  => $tenantId,
            'branch_id'  => $branchId,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
