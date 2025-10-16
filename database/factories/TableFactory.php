<?php

namespace Database\Factories;

use App\Models\Table;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

class TableFactory extends Factory
{
    protected $model = Table::class;

    public function definition()
    {
        $restaurantId = Restaurant::inRandomOrder()->first()->id ?? Restaurant::factory()->create()->id;

        return [
            'restaurant_id' => $restaurantId,
            'table_number' => $this->faker->numberBetween(1, 10),
            'capacity' => $this->faker->randomElement([2, 4, 6]),
            'is_available' => $this->faker->boolean(90), // 90% chance of being available
        ];
    }
}