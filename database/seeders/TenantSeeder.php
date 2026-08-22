<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * TenantSeeder — provision a new laundry tenant with branches and users.
 *
 * Usage:
 *   php artisan db:seed --class=TenantSeeder
 *
 * Edit the CONFIG section below before running.
 */
class TenantSeeder extends Seeder
{
    public function run(): void
    {
        // -------------------------------------------------------
        // CONFIG — edit this before running
        // -------------------------------------------------------
        $tenant = [
            'name'    => "Jane's Laundry",
            'slug'    => 'janes-laundry',        // must be unique, url-friendly
            'email'   => 'jane@example.com',      // must be unique
            'phone'   => '+639181234567',          // must be unique, or set to null
            'address' => 'Makati City, Metro Manila',
            'plan'    => 'basic',                 // free | basic | pro
        ];

        $branches = [
            [
                'name'    => 'Branch A — Makati',
                'address' => '123 Ayala Ave, Makati City',
                'phone'   => '+639181234567',
                // users for this branch:
                'users' => [
                    ['name' => 'Jane Staff Makati',    'username' => 'jane.staff.makati',    'password' => 'staff1234',    'role' => 'staff'],
                    ['name' => 'Jane Delivery Makati', 'username' => 'jane.delivery.makati', 'password' => 'delivery1234', 'role' => 'delivery'],
                ],
            ],
            [
                'name'    => 'Branch B — BGC',
                'address' => '456 BGC High St, Taguig City',
                'phone'   => '+639191234567',
                // users for this branch:
                'users' => [
                    ['name' => 'Jane Staff BGC',    'username' => 'jane.staff.bgc',    'password' => 'staff1234',    'role' => 'staff'],
                    ['name' => 'Jane Delivery BGC', 'username' => 'jane.delivery.bgc', 'password' => 'delivery1234', 'role' => 'delivery'],
                ],
            ],
        ];

        $adminUser = [
            'name'     => 'Jane Admin',
            'username' => 'jane.admin',
            'password' => 'admin1234',
        ];
        // -------------------------------------------------------
        // END CONFIG
        // -------------------------------------------------------

        // 1. Tenant
        $tenantId = DB::table('tenants')->insertGetId([
            'name'       => $tenant['name'],
            'slug'       => $tenant['slug'],
            'email'      => $tenant['email'],
            'phone'      => $tenant['phone'],
            'address'    => $tenant['address'],
            'plan'       => $tenant['plan'],
            'is_active'  => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Branches + branch users
        $branchUsers = [];
        $branchSummary = [];

        foreach ($branches as $branch) {
            $branchId = DB::table('branches')->insertGetId([
                'tenant_id'  => $tenantId,
                'name'       => $branch['name'],
                'address'    => $branch['address'] ?? null,
                'phone'      => $branch['phone'] ?? null,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $branchSummary[] = $branch['name'];

            foreach ($branch['users'] as $u) {
                $branchUsers[] = [
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

        DB::table('users')->insert($branchUsers);

        // 3. Default expense categories
        $expenseCategoryRows = [];
        foreach (ExpenseCategory::DEFAULT_NAMES as $i => $name) {
            $expenseCategoryRows[] = [
                'tenant_id'  => $tenantId,
                'name'       => $name,
                'is_active'  => true,
                'sort_order' => $i,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('expense_categories')->insert($expenseCategoryRows);

        // 4. Admin user (null branch = access all branches)
        DB::table('users')->insert([
            'name'       => $adminUser['name'],
            'username'   => $adminUser['username'],
            'password'   => Hash::make($adminUser['password']),
            'role'       => 'admin',
            'is_active'  => true,
            'tenant_id'  => $tenantId,
            'branch_id'  => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Summary
        $this->command->info("Tenant seeded: {$tenant['name']} (id: {$tenantId})");
        $this->command->info('Branches: ' . implode(', ', $branchSummary));

        $rows = [[$adminUser['username'], $adminUser['password'], 'admin', 'all branches']];
        foreach ($branches as $branch) {
            foreach ($branch['users'] as $u) {
                $rows[] = [$u['username'], $u['password'], $u['role'], $branch['name']];
            }
        }

        $this->command->newLine();
        $this->command->table(['Username', 'Password', 'Role', 'Branch'], $rows);
    }
}
