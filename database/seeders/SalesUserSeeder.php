<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SalesUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'sales@djavva.net'],
            [
                'name'     => 'Sales Support',
                'password' => Hash::make('password'),
                'role'     => 'sales',
            ]
        );
    }
}
