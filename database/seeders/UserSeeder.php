<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'labapawel@gmail.com'],
            [
                'name' => 'Paweł Laba',
                'password' => bcrypt('12345678'),
                'role' => 'admin',
                'permissions' => [], // Admin ma dostęp przez Gate::before
            ]
        );
    }
}
