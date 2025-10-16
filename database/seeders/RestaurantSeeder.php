<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use Illuminate\Database\Seeder;

class RestaurantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Create exactly 2 restaurants for testing
        Restaurant::factory()->count(2)->create();
    }
}