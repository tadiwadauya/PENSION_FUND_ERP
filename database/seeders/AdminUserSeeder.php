<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'tadiwa@lapf.co.zw'],
            [
                'name' => 'tadiwa',
                'username' => 'tadiwa',
                'first_name' => 'Tadiwanashe',
                'last_name' => 'Dauya',
                'email' => 'tadiwa@lapf.co.zw',
                'password' => Hash::make('makanakamwari'),
                'is_admin' => true,
                'is_hr' => true,
                'must_change_password' => false,
            ]
        );
    }
}