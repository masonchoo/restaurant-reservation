<?php

namespace Database\Factories;

use App\Models\ContactDetails;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactDetailsFactory extends Factory
{
    protected $model = ContactDetails::class;

    public function definition()
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'phone_number' => $this->faker->unique()->phoneNumber,
        ];
    }
}