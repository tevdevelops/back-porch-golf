<?php

namespace Database\Factories;

use App\Models\Bay;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Bay> */
class BayFactory extends Factory
{
    public function definition(): array
    {
        $position = fake()->unique()->numberBetween(1, 99);

        return [
            'name' => "Bay {$position}",
            'position' => $position,
        ];
    }
}
