<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OwnerSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'owner'],
            [
                'name'      => 'System Owner',
                'username'  => 'julsanity',
                'password'  => Hash::make('tef$a5wA'),
                'role'      => 'owner',
                'is_active' => true,
                'tenant_id' => null,
                'branch_id' => null,
            ]
        );

        $this->command->info('Owner user created. Username: owner | Password: owner1234');
        $this->command->warn('Change the password immediately after first login!');
    }
}
