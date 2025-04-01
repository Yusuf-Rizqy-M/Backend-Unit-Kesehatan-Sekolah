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
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => fake()->randomElement(['admin', 'user']),
            'phone_number' => fake()->phoneNumber(),
            'gender' => fake()->randomElement(['male', 'female']),
            'name_grades' => fake()->randomElement(['X PPLG', 'XI PPLG', 'XII PPLG']),
            'no_hp_parent' => fake()->phoneNumber(),
            'name_parent' => fake()->name(),
            'name_walikelas' => fake()->name(),
            'address_walikelas' => fake()->address(),
            'absent' => fake()->numberBetween(1, 40),
            'remember_token' => Str::random(10),
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
}
