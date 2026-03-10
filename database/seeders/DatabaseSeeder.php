<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // -------------------------------------------------------
        // 1. TENANT
        // -------------------------------------------------------
        DB::table('tenants')->insertGetId([
            'name'       => "Quinn's Laundry House",
            'slug'       => 'quinns-laundry',
            'email'      => 'quinnslaundry@gmail.com',
            'phone'      => '+639171234567',
            'address'    => 'Naga City, Camarines Sur',
            'plan'       => 'pro',
            'is_active'  => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // -------------------------------------------------------
        // 2. BRANCH
        // -------------------------------------------------------
        $tenantId = DB::table('tenants')->where('slug', 'quinns-laundry')->value('id');

        DB::table('branches')->insert([
            'tenant_id'  => $tenantId,
            'name'       => 'Main Branch',
            'address'    => 'Bagong Sirang, San Felipe, Naga City, Camarines Sur',
            'phone'      => '+639171234567',
            'is_active'  => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info("Tenant seeded: Quinn's Laundry House");
        $this->command->info('Branch seeded: Main Branch');
        $this->command->newLine();
        $this->command->info('Run php artisan db:seed --class=UserSeeder to seed users.');

        // -------------------------------------------------------
        // 3. USERS — managed by UserSeeder
        // -------------------------------------------------------
        $this->call(UserSeeder::class);
    }
}
