<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_details_id',
        'time_slot_id',
        'table_id', // Now explicitly part of the schema
        'number_of_guests',
        'status',
        'special_requests',
        'booked_date',
    ];

    public function contactDetails()
    {
        return $this->belongsTo(ContactDetails::class, 'contact_details_id');
    }

    public function timeSlot()
    {
        return $this->belongsTo(TimeSlot::class);
    }

    public function table()
    {
        return $this->belongsTo(Table::class);
    }
}