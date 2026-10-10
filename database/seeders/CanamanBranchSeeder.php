<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * CanamanBranchSeeder — add the Canaman branch and its users to Quinn's Laundry.
 *
 * Usage:
 *   php artisan db:seed --class=CanamanBranchSeeder
 *
 * Safe to re-run: the branch is matched by tenant + name and users by username.
 */
class CanamanBranchSeeder extends Seeder
{
    public function run(): void
    {
        // -------------------------------------------------------
        // CONFIG
        // -------------------------------------------------------
        $tenantSlug = 'quinns-laundry';

        $branch = [
            'name'    => 'Canaman Branch',
            'address' => '122 de Castro St., Pangpang, Canaman, Camarines Sur',
            'phone'   => '09761082555',
        ];

        $users = [
            ['name' => 'Quinns Staff Canaman',    'username' => 'canaman_staff',  'password' => 'staff0701',    'role' => 'staff'],
            ['name' => 'Quinns Driver Canaman',   'username' => 'canaman_driver', 'password' => 'delivery0701', 'role' => 'delivery'],
        ];
        // -------------------------------------------------------
        // END CONFIG
        // -------------------------------------------------------

        $tenant = DB::table('tenants')->where('slug', $tenantSlug)->first();
        abort_if(!$tenant, 1, "Tenant '{$tenantSlug}' not found. Run DatabaseSeeder first.");

        DB::transaction(function () use ($tenant, $branch, $users) {
            $attributes = [
                'address'    => $branch['address'],
                'phone'      => $branch['phone'],
                'is_active'  => true,
                'updated_at' => now(),
            ];

            $branchId = DB::table('branches')
                ->where('tenant_id', $tenant->id)
                ->where('name', $branch['name'])
                ->value('id');

            if ($branchId) {
                DB::table('branches')->where('id', $branchId)->update($attributes);
            } else {
                $branchId = DB::table('branches')->insertGetId([
                    'tenant_id'  => $tenant->id,
                    'name'       => $branch['name'],
                    'created_at' => now(),
                    ...$attributes,
                ]);
            }

            $rows = array_map(fn (array $u) => [
                'name'       => $u['name'],
                'username'   => $u['username'],
                'password'   => Hash::make($u['password']),
                'role'       => $u['role'],
                'is_active'  => true,
                'tenant_id'  => $tenant->id,
                'branch_id'  => $branchId,
                'created_at' => now(),
                'updated_at' => now(),
            ], $users);

            DB::table('users')->upsert($rows, ['username'], [
                'name', 'password', 'role', 'is_active', 'tenant_id', 'branch_id', 'updated_at',
            ]);
        });

        $this->command->info("Branch seeded: {$branch['name']} (tenant: {$tenant->name})");
        $this->command->newLine();
        $this->command->table(
            ['Username', 'Password', 'Role', 'Branch'],
            array_map(fn (array $u) => [$u['username'], $u['password'], $u['role'], $branch['name']], $users),
        );
    }
}
