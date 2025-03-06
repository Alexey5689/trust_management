<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        
        return [
            'first_name' => 'John',
            'last_name'  => 'Doe',
            'middle_name'=> 'Middle',
            'phone_number' => '89999999999',
            'email'      => 'john.doe@example.com',
            'password'   => static::$password ??= Hash::make('password'),
            'token'      => Str::random(60), 
            'refresh_token' => Str::random(60),
            'role_id'    => null,
        ];
    }
    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => 1, // Предположим, что роль администратора имеет ID = 1
        ]);
    }
}
