<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'owner'],
            [
                'name'      => 'System Superadmin',
                'username'  => 'quinns_owner',
                'password'  => Hash::make('tef$a5wA'),
                'role'      => 'superadmin',
                'is_active' => true,
                'tenant_id' => null,
                'branch_id' => null,
            ]
        );

        $this->command->info('Superadmin user created. Username: owner | Password: owner1234');
        $this->command->warn('Change the password immediately after first login!');
    }
}
