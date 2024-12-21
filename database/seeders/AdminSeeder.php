<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->admin()->create([
            'first_name' => 'Алексей',
            'last_name' => 'Дьяченко',
            'middle_name' => 'Петрович',
            'email' => 'dya.alex56@mail.ru',
            'phone_number' => '+79991052972',
            'password' => bcrypt('password'), // Устанавливаем пароль для администратора
            'token' => Str::random(60),
            'active'=>true,
            'refresh_token' => Str::random(60),
            'role_id' => 1, // ID роли администратора
        ]);
        User::factory()->admin()->create([
            'first_name' => 'Денис',
            'last_name' => 'Ефимов',
            'middle_name' => 'Васильевич',
            'email' => 'denisefremoff@mail.ru',
            'phone_number' => '+79228671761',
            'password' => bcrypt('password'), // Устанавливаем пароль для администратора
            'token' => Str::random(60),
            'active'=>true,
            'refresh_token' => Str::random(60),
            'role_id' => 1, // ID роли администратора
        ]);
    }


}
