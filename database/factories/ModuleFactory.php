<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Module>
 */
class ModuleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->unique()->sentence,
            'description' => $this->faker->text,
            'price' => $this->faker->randomFloat(2, 0, 1000),
            'created_by' => User::where('name', 'Admin')->first()?->id ?? User::factory()->create()->id,
        ];
    }
}
