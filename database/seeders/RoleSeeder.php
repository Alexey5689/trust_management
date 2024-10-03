<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create([
            'id' => 1, // Убедись, что роль администратора имеет id = 1
            'title' => 'admin',
            'description' => 'Administrator role',
        ]);
        Role::create([
            'id' => 2, // Убедись, что роль администратора имеет id = 1
            'title' => 'manager',
            'description' => 'Manager role',
        ]);
        Role::create([
            'id' => 3, // Убедись, что роль администратора имеет id = 1
            'title' => 'client',
            'description' => 'Client role',
        ]);
    }
}
