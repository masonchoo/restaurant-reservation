<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Http\Requests\StoreReservationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\ContactDetails; // Assuming you might need these for form dropdowns
use App\Models\TimeSlot;     // Assuming you might need these for form dropdowns

class ReservationController extends Controller
{
    /**
     * Display the reservation form.
     *
     * @return \Illuminate\View\View
     */
    public function showForm()
    {

        return view('reservation' );
    }

    /**
     * Store a newly created reservation in storage.
     *
     * @param  \App\Http\Requests\StoreReservationRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreReservationRequest $request)
    {
        // ... (Your existing store method code) ...
        try {
            $reservation = DB::transaction(function () use ($request) {
                $reservation = Reservation::create($request->validated());
                Log::info('New reservation created:', ['reservation_id' => $reservation->id, 'data' => $reservation->toArray()]);
                return $reservation;
            });

            // For a web application, you might redirect after success,
            // instead of returning JSON.
            return redirect()->route('reservations.success') // Example: redirect to a success page
                             ->with('success', 'Reservation created successfully!');

            // Or, if staying on the same page and using JavaScript to handle success:
            // return response()->json([
            //     'message' => 'Reservation created successfully!',
            //     'reservation' => $reservation
            // ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating reservation: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'exception' => $e
            ]);

            // For a web application, you might redirect back with errors
            return back()->withInput()->withErrors(['reservation_error' => 'Failed to create reservation: ' . $e->getMessage()]);

            // Or, for API:
            // return response()->json([
            //     'message' => 'Failed to create reservation.',
            //     'error' => $e->getMessage()
            // ], 500);
        }
    }

    // ... (other controller methods) ...
}