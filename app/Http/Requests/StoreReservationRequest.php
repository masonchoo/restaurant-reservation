<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\ContactDetails; // Import your ContactDetails model
use App\Models\TimeSlot;     // Import your TimeSlot model

class StoreReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Set this to true to allow the request.
        // You might add authorization logic here later (e.g., Auth::check())
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'contact_details_id' => [
                'required',
                'integer',
                'exists:contact_details,id', // Ensures the contact_details_id exists in the 'contact_details' table
            ],
            'time_slot_id' => [
                'required',
                'integer',
                'exists:time_slots,id',      // Ensures the time_slot_id exists in the 'time_slots' table
            ],
            'number_of_guests' => [
                'required',
                'integer',
                'min:1',                     // At least 1 guest
                'max:20',                    // Example max, adjust as needed
            ],
            'status' => [
                'nullable',                  // Allow not providing status, default 'pending' will be used
                'string',
                'in:pending,confirmed,cancelled,completed', // Only these allowed values
            ],
            'special_requests' => [
                'nullable',
                'string',
                'max:500',                   // Max 500 characters for special requests
            ],
            'booked_date' => [
                'required',
                'date',                      // Must be a valid date format
                'after_or_equal:today',      // Reservations should be for today or in the future
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'contact_details_id.exists' => 'The selected contact person does not exist.',
            'time_slot_id.exists' => 'The selected time slot does not exist or is invalid.',
            'number_of_guests.min' => 'There must be at least 1 guest.',
            'booked_date.after_or_equal' => 'Reservations cannot be made for past dates.',
        ];
    }
}