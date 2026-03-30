<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * SampleDataSeeder — populates categories, items, customers, orders, and expenses.
 *
 * Usage:
 *   php artisan db:seed --class=SampleDataSeeder
 *
 * Requires: tenant (quinns-laundry) and users already seeded.
 */
class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = DB::table('tenants')->where('slug', 'quinns-laundry')->first();
        abort_if(!$tenant, 1, "Tenant 'quinns-laundry' not found. Run DatabaseSeeder first.");

        $branch = DB::table('branches')->where('tenant_id', $tenant->id)->first();
        abort_if(!$branch, 1, 'No branch found for tenant. Run DatabaseSeeder first.');

        $adminUser = DB::table('users')
            ->where('tenant_id', $tenant->id)
            ->where('role', 'admin')
            ->first();
        abort_if(!$adminUser, 1, 'No admin user found. Run UserSeeder first.');

        $tenantId = $tenant->id;
        $branchId = $branch->id;
        $userId   = $adminUser->id;

        // -------------------------------------------------------
        // 1. CATEGORIES
        // -------------------------------------------------------
        $categoryData = [
            'Wash & Dry',
            'Wash & Fold',
            'Dry Clean',
            'Iron / Press',
            'Comforter',
            'Bedsheets',
        ];

        $categories = [];
        foreach ($categoryData as $name) {
            $id = DB::table('categories')->insertGetId([
                'name'       => $name,
                'is_active'  => true,
                'tenant_id'  => $tenantId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $categories[$name] = $id;
        }

        $this->command->info('✓ Categories seeded: ' . \count($categories));

        // -------------------------------------------------------
        // 2. ITEMS
        // -------------------------------------------------------
        $itemData = [
            ['name' => 'Regular Load',      'price' => 80,  'cost' => 30,  'category' => 'Wash & Dry'],
            ['name' => 'Large Load',        'price' => 120, 'cost' => 45,  'category' => 'Wash & Dry'],
            ['name' => 'Extra Large Load',  'price' => 160, 'cost' => 60,  'category' => 'Wash & Dry'],
            ['name' => 'Wash & Fold Basic', 'price' => 100, 'cost' => 40,  'category' => 'Wash & Fold'],
            ['name' => 'Wash & Fold Plus',  'price' => 150, 'cost' => 55,  'category' => 'Wash & Fold'],
            ['name' => 'Polo Shirt',        'price' => 60,  'cost' => 20,  'category' => 'Dry Clean'],
            ['name' => 'Blazer / Coat',     'price' => 200, 'cost' => 80,  'category' => 'Dry Clean'],
            ['name' => 'Dress',             'price' => 180, 'cost' => 70,  'category' => 'Dry Clean'],
            ['name' => 'Shirt Press',       'price' => 30,  'cost' => 10,  'category' => 'Iron / Press'],
            ['name' => 'Pants Press',       'price' => 40,  'cost' => 12,  'category' => 'Iron / Press'],
            ['name' => 'Single Comforter',  'price' => 250, 'cost' => 100, 'category' => 'Comforter'],
            ['name' => 'Double Comforter',  'price' => 350, 'cost' => 140, 'category' => 'Comforter'],
            ['name' => 'Single Bedsheet',   'price' => 120, 'cost' => 45,  'category' => 'Bedsheets'],
            ['name' => 'Double Bedsheet',   'price' => 160, 'cost' => 60,  'category' => 'Bedsheets'],
        ];

        $items = [];
        foreach ($itemData as $item) {
            $id = DB::table('items')->insertGetId([
                'name'        => $item['name'],
                'description' => null,
                'price'       => $item['price'],
                'cost'        => $item['cost'],
                'is_active'   => true,
                'category_id' => $categories[$item['category']],
                'tenant_id'   => $tenantId,
                'branch_id'   => $branchId,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
            $items[] = ['id' => $id, 'name' => $item['name'], 'price' => $item['price'], 'cost' => $item['cost']];
        }

        $this->command->info('✓ Items seeded: ' . \count($items));

        // -------------------------------------------------------
        // 3. CUSTOMERS
        // -------------------------------------------------------
        $customerData = [
            ['nickname' => 'Maria Santos',    'mobile' => '09171234501', 'address' => 'Brgy. Triangulo, Naga City', 'delivery_fee' => 50],
            ['nickname' => 'Juan dela Cruz',  'mobile' => '09281234502', 'address' => 'Brgy. Bagong Sirang, Naga City', 'delivery_fee' => 0],
            ['nickname' => 'Ana Reyes',       'mobile' => '09391234503', 'address' => 'Brgy. Concepcion Pequeña, Naga City', 'delivery_fee' => 80],
            ['nickname' => 'Pedro Lim',       'mobile' => '09171234504', 'address' => 'Brgy. Dayangdang, Naga City', 'delivery_fee' => 50],
            ['nickname' => 'Rosa Garcia',     'mobile' => '09281234505', 'address' => 'Brgy. Pacol, Naga City', 'delivery_fee' => 100],
            ['nickname' => 'Carlos Mendoza',  'mobile' => '09391234506', 'address' => 'Brgy. Mabolo, Naga City', 'delivery_fee' => 0],
            ['nickname' => 'Luz Torres',      'mobile' => '09171234507', 'address' => 'Brgy. Tinago, Naga City', 'delivery_fee' => 50],
            ['nickname' => 'Rico Villanueva', 'mobile' => '09281234508', 'address' => 'Brgy. Igualdad, Naga City', 'delivery_fee' => 80],
            ['nickname' => 'Elena Cruz',      'mobile' => '09391234509', 'address' => 'Brgy. Abella, Naga City', 'delivery_fee' => 30],
            ['nickname' => 'Mark Bautista',   'mobile' => '09171234510', 'address' => 'Brgy. Peñafrancia, Naga City', 'delivery_fee' => 0],
        ];

        $customerIds = [];
        foreach ($customerData as $cust) {
            $id = Str::uuid()->toString();
            DB::table('customers')->insert([
                'id'           => $id,
                'nickname'     => $cust['nickname'],
                'mobile'       => $cust['mobile'],
                'address'      => $cust['address'],
                'delivery_fee' => $cust['delivery_fee'],
                'notes'        => null,
                'tenant_id'    => $tenantId,
                'branch_id'    => $branchId,
                'created_at'   => now()->subDays(rand(10, 90)),
                'updated_at'   => now(),
            ]);
            $customerIds[] = $id;
        }

        $this->command->info('✓ Customers seeded: ' . \count($customerIds));

        // -------------------------------------------------------
        // 4. ORDERS + ORDER ITEMS (last 3 months)
        // -------------------------------------------------------
        $paymentStatuses = ['unpaid', 'unpaid', 'unpaid', 'paid_cash', 'paid_cash', 'paid_cash', 'paid_gcash', 'paid_gcash', 'paid_bank'];
        $orderStatuses   = ['in_progress', 'ready', 'completed', 'completed', 'completed'];
        $fulfillmentTypes = ['walk-in', 'walk-in', 'walk-in', 'pickup-deliver'];

        $orderCount = 0;

        for ($i = 0; $i < 60; $i++) {
            $createdAt      = now()->subDays(rand(0, 90))->subHours(rand(0, 23));
            $customerId     = $customerIds[array_rand($customerIds)];
            $fulfillment    = $fulfillmentTypes[array_rand($fulfillmentTypes)];
            $paymentStatus  = $paymentStatuses[array_rand($paymentStatuses)];
            $orderStatus    = $paymentStatus === 'unpaid' ? 'in_progress' : $orderStatuses[array_rand($orderStatuses)];

            // Pick 1–3 random items for this order
            $selectedItems = array_values((array) fake()->randomElements($items, rand(1, 3)));
            $subtotal = 0;
            $orderItemRows = [];

            foreach ($selectedItems as $item) {
                $qty       = rand(1, 3);
                $lineTotal = $item['price'] * $qty;
                $subtotal += $lineTotal;

                $orderItemRows[] = [
                    'id'      => Str::uuid()->toString(),
                    'item_id' => $item['id'],
                    'label'   => $item['name'],
                    'qty'     => $qty,
                    'price'   => $item['price'],
                ];
            }

            $deliveryFee = $fulfillment === 'pickup-deliver' ? 50 : 0;
            $total       = $subtotal + $deliveryFee;
            $orderNumber = Str::uuid()->toString();

            DB::table('orders')->insert([
                'order_number'     => $orderNumber,
                'customer_id'      => $customerId,
                'fulfillment_type' => $fulfillment,
                'subtotal'         => $subtotal,
                'delivery_fee'     => $deliveryFee,
                'total'            => $total,
                'payment_status'   => $paymentStatus,
                'order_status'     => $orderStatus,
                'tenant_id'        => $tenantId,
                'branch_id'        => $branchId,
                'created_at'       => $createdAt,
                'updated_at'       => $createdAt,
            ]);

            $orderItemRows = array_map(fn($r) => [...$r, 'order_number' => $orderNumber], $orderItemRows);
            DB::table('order_items')->insert($orderItemRows);

            $orderCount++;
        }

        $this->command->info("✓ Orders seeded: {$orderCount} (with order items)");

        // -------------------------------------------------------
        // 5. EXPENSES (last 3 months)
        // -------------------------------------------------------
        $expenseData = [
            ['description' => 'Detergent supplies',    'amount' => 1200],
            ['description' => 'Fabric softener',       'amount' => 800],
            ['description' => 'Electricity bill',      'amount' => 3500],
            ['description' => 'Water bill',            'amount' => 1500],
            ['description' => 'Equipment maintenance', 'amount' => 2000],
            ['description' => 'Plastic bags',          'amount' => 300],
            ['description' => 'Staff overtime pay',    'amount' => 1500],
            ['description' => 'Delivery gas',          'amount' => 600],
            ['description' => 'Packaging materials',   'amount' => 450],
            ['description' => 'Cleaning supplies',     'amount' => 700],
            ['description' => 'Detergent supplies',    'amount' => 1100],
            ['description' => 'Electricity bill',      'amount' => 3200],
            ['description' => 'Water bill',            'amount' => 1400],
            ['description' => 'Fabric softener',       'amount' => 750],
            ['description' => 'Staff overtime pay',    'amount' => 2000],
        ];

        $expenseRows = [];
        foreach ($expenseData as $exp) {
            $date = now()->subDays(rand(0, 90));
            $expenseRows[] = [
                'id'           => Str::uuid()->toString(),
                'description'  => $exp['description'],
                'amount'       => $exp['amount'],
                'expense_date' => $date->format('Y-m-d'),
                'note'         => null,
                'user_id'      => $userId,
                'tenant_id'    => $tenantId,
                'branch_id'    => $branchId,
                'created_at'   => $date,
                'updated_at'   => $date,
            ];
        }

        DB::table('expenses')->insert($expenseRows);

        $this->command->info('✓ Expenses seeded: ' . \count($expenseRows));
        $this->command->newLine();
        $this->command->info('Sample data seeding complete!');
    }
}
