<?php

namespace Database\Seeders;

namespace Database\Seeders;

use App\Models\Table;
use Illuminate\Database\Seeder;
use App\Models\Restaurant; // Don't forget to import the Restaurant model

class RestaurantsTablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure some restaurants exist to link tables to
        // If you already have RestaurantSeeder running before this,
        // you might not need to create them here, but it's good for robustness.
        if (Restaurant::count() === 0) {
            Restaurant::factory()->count(3)->create(); // Create 10 restaurants if none exist
        }

        // Create 200 tables, randomly assigning them to existing restaurants
        Table::factory()->count(3)->create();

        // Example: Create specific tables for a specific restaurant
        // You would need to retrieve a restaurant first
        // $restaurant = Restaurant::first();
        // if ($restaurant) {
        //     Table::factory()->count(5)->create([
        //         'restaurant_id' => $restaurant->id,
        //         'capacity' => 4, // All these 5 tables have a capacity of 4
        //     ]);
        // }

        // Another way to create tables with specific capacities for a random restaurant:
        // Table::factory()->count(5)->capacity(2)->create(); // 5 tables with capacity 2
        // Table::factory()->count(10)->capacity(4)->create(); // 10 tables with capacity 4
    }
}
