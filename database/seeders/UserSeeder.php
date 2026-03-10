<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * UserSeeder — add users to an existing tenant.
 *
 * Usage:
 *   php artisan db:seed --class=UserSeeder
 *
 * Edit the CONFIG section below before running.
 * Tenant and branch are looked up by name — make sure they already exist.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        // -------------------------------------------------------
        // CONFIG — edit this before running
        // -------------------------------------------------------
        $tenantSlug = 'quinns-laundry';   // from tenants.slug

        $users = [
            [
                'name'        => 'Julius Francisco',
                'username'    => 'quinns.admin',
                'password'    => 'admin0701',
                'role'        => 'admin',
                'branch_name' => null,       // null = access all branches
            ],
            [
                'name'        => 'Quinns Staff Main',
                'username'    => 'quinns.main.staff',
                'password'    => 'staff0701',
                'role'        => 'staff',
                'branch_name' => 'Main Branch',
            ],
            [
                'name'        => 'Quinns Delivery Main',
                'username'    => 'quinns.delivery.main',
                'password'    => 'delivery0701',
                'role'        => 'delivery',
                'branch_name' => 'Main Branch',
            ],
        ];
        // -------------------------------------------------------
        // END CONFIG
        // -------------------------------------------------------

        $tenant = DB::table('tenants')->where('slug', $tenantSlug)->first();
        abort_if(!$tenant, 1, "Tenant '{$tenantSlug}' not found. Run DatabaseSeeder first.");

        // Cache branch lookups to avoid repeated queries
        $branchCache = [];
        $getBranchId = function (?string $branchName) use ($tenant, &$branchCache): ?int {
            if ($branchName === null) return null;
            if (!isset($branchCache[$branchName])) {
                $branch = DB::table('branches')
                    ->where('tenant_id', $tenant->id)
                    ->where('name', $branchName)
                    ->first();
                abort_if(!$branch, 1, "Branch '{$branchName}' not found for tenant '{$tenant->name}'.");
                $branchCache[$branchName] = $branch->id;
            }
            return $branchCache[$branchName];
        };

        $rows = [];
        $summary = [];

        foreach ($users as $u) {
            $branchId = $getBranchId($u['branch_name']);

            $rows[] = [
                'name'       => $u['name'],
                'username'   => $u['username'],
                'password'   => Hash::make($u['password']),
                'role'       => $u['role'],
                'is_active'  => true,
                'tenant_id'  => $tenant->id,
                'branch_id'  => $branchId,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $summary[] = [$u['username'], $u['password'], $u['role'], $u['branch_name'] ?? 'all branches'];
        }

        DB::table('users')->insert($rows);

        $this->command->info("Users seeded for tenant: {$tenant->name}");
        $this->command->newLine();
        $this->command->table(['Username', 'Password', 'Role', 'Branch'], $summary);
    }
}
