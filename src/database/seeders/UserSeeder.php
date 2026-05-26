<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->create([
            'id' => 1,
            'name' => 'ユーザー1',
            'email' => 'user1@example.com',
            'password' => Hash::make('password'),
        ]);

        User::factory()->create([
            'id' => 2,
            'name' => 'ユーザー2',
            'email' => 'user2@example.com',
            'password' => Hash::make('password'),
        ]);

        User::factory()->create([
            'id' => 3,
            'name' => 'ユーザー3',
            'email' => 'user3@example.com',
            'password' => Hash::make('password'),
            'admin_status' => true,
        ]);
    }
}
