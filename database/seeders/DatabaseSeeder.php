<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        $this->call([
            // Order is important due to foreign key dependencies
            RestaurantSeeder::class,
            TableSeeder::class,           // Depends on RestaurantSeeder
            ContactDetailsSeeder::class,
            TimeSlotSeeder::class,
        ]);
    }
}