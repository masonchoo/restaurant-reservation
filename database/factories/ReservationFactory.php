<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\ContactDetails;
use App\Models\TimeSlot;
use App\Models\Table;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition()
    {
        // Ensure parent records exist or create them
        $contactDetailsId = ContactDetails::inRandomOrder()->first()->id ?? ContactDetails::factory()->create()->id;
        $timeSlotId = TimeSlot::inRandomOrder()->first()->id ?? TimeSlot::factory()->create(['time' => '18:00:00'])->id;
        $tableId = Table::inRandomOrder()->first()->id ?? Table::factory()->create()->id;

        // Ensure the selected table is available for the given guests and time slot on the date
        // This logic is complex for a factory; for simple seeding, we'll create a valid entry.
        // In real app, availability checked in Livewire/Controller before creation.
        $bookedDate = $this->faker->dateTimeBetween('today', '+1 month')->format('Y-m-d');
        $numberOfGuests = $this->faker->numberBetween(1, 10);

        return [
            'contact_details_id' => $contactDetailsId,
            'time_slot_id' => $timeSlotId,
            'table_id' => $tableId,
            'number_of_guests' => $numberOfGuests,
            'status' => $this->faker->randomElement(['pending', 'confirmed']),
            'special_requests' => $this->faker->boolean(30) ? $this->faker->sentence : null,
            'booked_date' => $bookedDate,
        ];
    }
}