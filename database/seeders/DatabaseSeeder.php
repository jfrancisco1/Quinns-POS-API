<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // -------------------------------------------------------
        // 1. TENANT
        // -------------------------------------------------------
        $tenantId = DB::table('tenants')->insertGetId([
            'name' => "Quinn's Laundry House",
            'slug' => 'quinns-laundry',
            'email' => 'quinnslaundry@gmail.com',
            'phone' => '+639171234567',
            'address' => 'Naga City, Camarines Sur',
            'plan' => 'pro',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // -------------------------------------------------------
        // 2. BRANCH
        // -------------------------------------------------------
        $branchId = DB::table('branches')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => 'Main Branch',
            'address' => 'Bagong Sirang, San Felipe, Naga City, Camarines Sur',
            'phone' => '+639171234567',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // -------------------------------------------------------
        // 3. USERS
        // -------------------------------------------------------
        $users = [
            // Admin — null branch = access all
            [
                'name' => 'Julius Francisco',
                'username' => 'quinns.admin',
                'password' => Hash::make('admin1234'),
                'role' => 'admin',
                'is_active' => true,
                'tenant_id' => $tenantId,
                'branch_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Staff
            [
                'name' => 'Quinns Staff Main',
                'username' => 'quinns.main.staff',
                'password' => Hash::make('staff1234'),
                'role' => 'staff',
                'is_active' => true,
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Delivery
            [
                'name' => 'Quinns Delivery Main',
                'username' => 'quinns.delivery.main',
                'password' => Hash::make('delivery1234'),
                'role' => 'delivery',
                'is_active' => true,
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('users')->insert($users);

        $this->command->info("Tenant seeded: Quinn's Laundry House");
        $this->command->info('Branch seeded: Main Branch');
        $this->command->info('Users seeded: 1 admin, 1 staff, 1 delivery');
        $this->command->newLine();
        $this->command->table(
            ['Role', 'Username', 'Password'],
            [
                ['admin', 'quinn.admin.main', 'admin1234'],
                ['staff', 'quinns.staff.main', 'staff1234'],
                ['delivery', 'quinns.delivery.main', 'delivery1234'],
            ]
        );
    }
}
