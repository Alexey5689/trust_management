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
            'first_name' => 'Сергей',
            'last_name' => 'Демидов',
            'middle_name' => 'Александрович',
            'email' => 'demidov323232@mail.ru',
            'phone_number' => '+79999999999',
            'password' => bcrypt('password'), // Устанавливаем пароль для администратора
            'token' => Str::random(60),
            'active'=>true,
            'refresh_token' => Str::random(60),
            'role_id' => 1, // ID роли администратора
        ]);
    }


}
