<?php

namespace Database\Factories;

use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

class RestaurantFactory extends Factory
{
    protected $model = Restaurant::class;

    public function definition()
    {
        return [
            'name' => $this->faker->company . ' Eatery',
            'address' => $this->faker->address,
            'phone_number' => $this->faker->unique()->phoneNumber,
            'email' => $this->faker->unique()->companyEmail,
            'description' => $this->faker->paragraph(3),
        ];
    }
}