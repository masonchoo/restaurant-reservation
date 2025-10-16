<?php

namespace Database\Seeders;

use App\Models\Table;
use App\Models\Restaurant;
use Illuminate\Database\Seeder;
use Faker\Factory as FakerFactory;

class TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Ensure restaurants exist first
        if (Restaurant::count() === 0) {
            $this->call(RestaurantSeeder::class);
        }

        // For each restaurant, create exactly 3 tables for testing
        Restaurant::all()->each(function ($restaurant) {
            $numTablesToCreate = 3; // Fixed to 3 tables per restaurant
            $faker = FakerFactory::create();
            $uniqueTableNumbers = [];

            $attemptCount = 0;
            // Generate unique table numbers for this specific restaurant's batch
            // The range 1-50 is more than enough for 3 unique numbers.
            while (count($uniqueTableNumbers) < $numTablesToCreate && $attemptCount < 200) {
                $number = $faker->numberBetween(1, 50);
                if (!in_array($number, $uniqueTableNumbers)) {
                    $uniqueTableNumbers[] = $number;
                }
                $attemptCount++;
            }

            foreach ($uniqueTableNumbers as $tableNumber) {
                Table::factory()->create([
                    'restaurant_id' => $restaurant->id,
                    'table_number' => $tableNumber,
                    'capacity' => $faker->randomElement([2, 4, 6]), // Common capacities for testing
                    'is_available' => true, // Force true for initial seeding to ensure availability
                ]);
            }
        });
    }
}