<?php

namespace Database\Factories;

use App\Models\TimeSlot;
use Illuminate\Database\Eloquent\Factories\Factory;

class TimeSlotFactory extends Factory
{
    protected $model = TimeSlot::class;

    public function definition()
    {
        // This factory is less useful for generic time slots that are fixed.
        // Seeding them manually is usually better.
        // This is a placeholder if you wanted to generate random unique times.
        return [
            'time' => $this->faker->unique()->time('H:i:s', '22:00:00'),
        ];
    }
}