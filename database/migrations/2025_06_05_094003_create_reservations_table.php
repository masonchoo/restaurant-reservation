<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();

            // Foreign keys to other tables
            $table->foreignId('contact_details_id')->constrained('contact_details')->onDelete('restrict');
            $table->foreignId('time_slot_id')->constrained('time_slots')->onDelete('restrict');
            $table->foreignId('table_id')->constrained('tables')->onDelete('restrict');

            $table->integer('number_of_guests');
            $table->enum('status', ['pending', 'confirmed', 'cancelled','completed'])->default('pending');
            $table->text('special_requests')->nullable();
            $table->date('booked_date');

            $table->timestamps();

            $table->unique(['table_id', 'time_slot_id', 'booked_date'], 'unique_reservation_slot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};