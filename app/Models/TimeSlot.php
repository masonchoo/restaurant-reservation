<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'time', // Only 'time' should be fillable
    ];

    public function getDisplayTimeAttribute()
    {
        return date('g:i A', strtotime($this->time));
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}