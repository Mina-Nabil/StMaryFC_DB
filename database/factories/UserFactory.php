<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'USER_NAME' => fake()->unique()->name(),
            'USER_USTP_ID' => 1,
            'USER_MAIL' => fake()->unique()->safeEmail(),
            'USER_PASS' => Hash::make('password'),
            'USER_FACE_ID' => fake()->unique()->uuid(),
            'USER_BDAY' => fake()->date(),
        ];
    }
}
