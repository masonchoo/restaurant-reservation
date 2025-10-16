<?php

namespace Database\Seeders;

use App\Models\TimeSlot;
use Illuminate\Database\Seeder;

class TimeSlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Define specific, generic time slots manually
        $timeSlots = [
            '17:00:00', '17:30:00', '18:00:00', '18:30:00', '19:00:00',
            '19:30:00', '20:00:00', '20:30:00', '21:00:00', '21:30:00', '22:00:00'
        ];

        foreach ($timeSlots as $time) {
            TimeSlot::firstOrCreate(['time' => $time]);
        }
    }
}