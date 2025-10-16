<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Reservation;
use App\Models\ContactDetails;
use App\Models\TimeSlot;
use App\Models\Table;
use App\Models\Restaurant;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class ReservationForm extends Component
{
    // === Multi-step Control ===
    public $currentStep = 1;

    // === Step 1 (Reservation Details) ===
    public $selectedRestaurantId = null;
    public $reservationDate = '';
    public $numberOfGuests = '';
    public $selectedTimeSlotId = null;
    public $selectedTableId = null;

    // === Step 2 (Contact Details) ===
    public $contactFirstName = '';
    public $contactLastName = '';
    public $contactEmail = '';
    public $contactPhoneNumber = '';

    // Data for dropdowns/buttons
    /**
     * @var \Illuminate\Database\Eloquent\Collection<\App\Models\Restaurant>
     */
    public $restaurants = []; 
    /**
     * @var \Illuminate\Database\Eloquent\Collection<\App\Models\TimeSlot>
     */
    public $allTimeSlots = []; 
    public $availableTimeSlots = [];

    // Store the mapping of TimeSlot ID to its assigned Table ID
    public $timeSlotToTableMap = [];

    /**
     * Define validation rules based on the current step.
     */
    protected function rules()
    {
        if ($this->currentStep == 1) {
            return [
                'selectedRestaurantId' => ['required', 'integer', 'exists:restaurants,id'],
                'reservationDate' => ['required', 'date', 'after_or_equal:today'],
                'numberOfGuests' => ['required', 'integer', 'min:1', 'max:10'],
                'selectedTimeSlotId' => [
                    'required',
                    'integer',
                    'exists:time_slots,id',
                    function ($attribute, $value, $fail) {
                        if (!collect($this->availableTimeSlots)->contains('id', $value) || empty($this->timeSlotToTableMap[$value])) {
                            $fail('The selected time slot is not available for this date or guest count.');
                        }
                    },
                ],
                'selectedTableId' => ['nullable', 'integer', 'exists:tables,id'],
            ];
        } elseif ($this->currentStep == 2) {
            return [
                'contactFirstName' => ['required', 'string', 'max:255'],
                'contactLastName' => ['nullable', 'string', 'max:255'],
                'contactEmail' => ['required', 'string', 'email', 'max:255', Rule::unique('contact_details', 'email')->ignore($this->contactEmail, 'email')],
                'contactPhoneNumber' => ['required', 'string', 'max:20', 'min:8'],
            ];
        }
        return [];
    }

    /**
     * Define custom validation messages.
     */
    protected function messages()
    {
        return [
            'selectedTimeSlotId.required' => 'Please select a time slot.',
            'selectedRestaurantId.required' => 'Please select a restaurant.',
            'reservationDate.required' => 'Please select a reservation date.',
            'numberOfGuests.required' => 'Please select the number of guests.',
            'contactFirstName.required' => 'Your first name is required.',
            'contactEmail.required' => 'Your email is required.',
            'contactEmail.email' => 'Please enter a valid email address.',
            'contactPhoneNumber.required' => 'Your phone number is required.',
        ];
    }

    /**
     * Real-time validation and reactive updates.
     */
    public function updated($propertyName)
    {
        Log::info('Livewire: `updated` method called', ['propertyName' => $propertyName, 'value' => $this->$propertyName]);

        $currentStepRules = $this->rules();
        $rulesForProperty = $currentStepRules[$propertyName] ?? null;

        if ($rulesForProperty) {
            $this->validateOnly($propertyName, [$propertyName => $rulesForProperty]);
        }

        if (in_array($propertyName, ['selectedRestaurantId', 'reservationDate', 'numberOfGuests'])) {
            Log::info('Livewire: Restaurant, Date, or Guests changed. Re-evaluating available time slots.');
            $this->selectedTimeSlotId = null;
            $this->selectedTableId = null;
            
            $this->updateAvailableTimeSlots();
        }
        
        if ($propertyName === 'selectedTimeSlotId' && $this->selectedTimeSlotId) {
            Log::info('Livewire: selectedTimeSlotId changed directly.', ['new_id' => $this->selectedTimeSlotId]);
            $assignedTableId = $this->timeSlotToTableMap[$this->selectedTimeSlotId] ?? null;

            if ($assignedTableId) {
                $this->selectedTableId = $assignedTableId;
                Log::info('Livewire: selectedTableId assigned via `updated` method.', ['assigned_table_id' => $this->selectedTableId]);
            } else {
                Log::warning('Livewire: Selected time slot ID exists, but no table assigned in map.', ['time_slot_id' => $this->selectedTimeSlotId]);
                session()->flash('error', 'The selected time slot is no longer available or no table could be assigned.');
            }
        }
    }

    /**
     * Initialize properties when the component is mounted.
     */
    public function mount()
    {
        Log::info('Livewire: `mount` method called.');
        // Ensure that restaurants and time slots are initialized as Collections
        $this->restaurants = Restaurant::all();
        $this->allTimeSlots = TimeSlot::orderBy('time')->get();
        $this->reservationDate = now()->toDateString();
        
        $this->updateAvailableTimeSlots();
    }

    /**
     * Selects a time slot (used by buttons).
     */
    public function selectTimeSlot($timeSlotId)
    {
        Log::info('Livewire: `selectTimeSlot` method called (from button click).', ['timeSlotId' => $timeSlotId]);

        $this->selectedTimeSlotId = $timeSlotId;

        $assignedTableId = $this->timeSlotToTableMap[$this->selectedTimeSlotId] ?? null;

        if ($assignedTableId) {
            $this->selectedTableId = $assignedTableId;
            Log::info('Livewire: Table assigned in `selectTimeSlot`.', ['assigned_table_id' => $this->selectedTableId]);
        } else {
            Log::warning('Livewire: Time slot was clicked, but no table found in map for it.', ['timeSlotId' => $timeSlotId]);
            session()->flash('error', 'The selected time slot does not have an assigned table. Please try another.');
            $this->selectedTableId = null;
        }
    }

    /**
     * Updates the list of available time slots based on the selected restaurant, date, and guest count.
     */
    private function updateAvailableTimeSlots()
    {
        $this->availableTimeSlots = collect();
        $this->timeSlotToTableMap = [];

        Log::info('Livewire: `updateAvailableTimeSlots` started.', [
            'selectedRestaurantId' => $this->selectedRestaurantId,
            'reservationDate' => $this->reservationDate,
            'numberOfGuests' => $this->numberOfGuests
        ]);

        if (!$this->selectedRestaurantId || !$this->reservationDate || !$this->numberOfGuests) {
            Log::info('Livewire: `updateAvailableTimeSlots`: Missing restaurant, date or number of guests. Returning early.');
            return;
        }

        $suitableTables = Table::where('restaurant_id', $this->selectedRestaurantId)
                               ->where('is_available', true)
                               ->where('capacity', '>=', $this->numberOfGuests)
                               ->get();
        Log::info('Livewire: `updateAvailableTimeSlots`: Suitable Tables Query Result', [
            'count' => $suitableTables->count(),
            'table_ids' => $suitableTables->pluck('id')->toArray(),
            'table_capacities' => $suitableTables->pluck('capacity')->toArray(),
        ]);


        $reservationsForDate = Reservation::where('booked_date', $this->reservationDate)
                                        ->get();
        Log::info('Livewire: `updateAvailableTimeSlots`: Reservations For Date Query Result', [
            'count' => $reservationsForDate->count(),
            'reservation_data_sample' => $reservationsForDate->take(5)->map(fn($r) => ['ts_id' => $r->time_slot_id, 't_id' => $r->table_id])->toArray(),
        ]);


        foreach ($this->allTimeSlots as $timeSlot) {
            $tablesBookedForThisSlot = $reservationsForDate->where('time_slot_id', $timeSlot->id)
                                                            ->pluck('table_id')
                                                            ->toArray();
            Log::info('Livewire: `updateAvailableTimeSlots`: Checking TimeSlot', [
                'time_slot_id' => $timeSlot->id,
                'time_slot_time' => $timeSlot->time,
                'tables_booked_for_this_slot_on_date' => $tablesBookedForThisSlot
            ]);

            $availableTable = $suitableTables->whereNotIn('id', $tablesBookedForThisSlot)->first();

            if ($availableTable) {
                Log::info('Livewire: `updateAvailableTimeSlots`: Found AVAILABLE Table for TimeSlot', [
                    'time_slot_id' => $timeSlot->id,
                    'available_table_id' => $availableTable->id,
                    'available_table_capacity' => $availableTable->capacity,
                ]);
                $this->timeSlotToTableMap[$timeSlot->id] = $availableTable->id;
                $this->availableTimeSlots->push($timeSlot);
            } else {
                Log::info('Livewire: `updateAvailableTimeSlots`: NO available Table found for TimeSlot', [
                    'time_slot_id' => $timeSlot->id,
                ]);
            }
        }
        Log::info('Livewire: `updateAvailableTimeSlots`: Finished. Final availableTimeSlots count', ['count' => count($this->availableTimeSlots), 'timeSlotToTableMap' => $this->timeSlotToTableMap]);
    }

    /**
     * Navigate to the next step.
     */
    public function nextStep()
    {
        Log::info('Livewire: `nextStep` called: Before validation');
        $this->validate();

        Log::info('Livewire: `nextStep` called: After validation', [
            'selectedRestaurantId' => $this->selectedRestaurantId,
            'reservationDate' => $this->reservationDate,
            'numberOfGuests' => $this->numberOfGuests,
            'selectedTimeSlotId' => $this->selectedTimeSlotId,
            'selectedTableId' => $this->selectedTableId
        ]);

        if (empty($this->selectedTableId)) {
             Log::error('Livewire: `nextStep`: Validation passed, but selectedTableId is still empty after time slot selection.');
             session()->flash('error', 'Please select a time slot and ensure a table is assigned.');
             return;
        }

        Log::info('Livewire: `nextStep`: Moving to next step.', [
            'selectedTimeSlotId' => $this->selectedTimeSlotId,
            'selectedTableId' => $this->selectedTableId
        ]);

        $this->currentStep++;
    }

    /**
     * Navigate to the previous step.
     */
    public function previousStep()
    {
        Log::info('Livewire: `previousStep` called.');
        $this->currentStep--;
    }

    /**
     * Final submission: Create ContactDetails and Reservation.
     */
    public function confirmReservation()
    {
        Log::info('Livewire: `confirmReservation` called: Before validation.');
        $this->validate();

        try {
            DB::transaction(function () {
                $contact = ContactDetails::firstOrCreate(
                    ['email' => $this->contactEmail],
                    [
                        'first_name' => $this->contactFirstName,
                        'last_name' => $this->contactLastName,
                        'phone_number' => $this->contactPhoneNumber,
                    ]
                );

                Reservation::create([
                    'contact_details_id' => $contact->id,
                    'time_slot_id' => $this->selectedTimeSlotId,
                    'table_id' => $this->selectedTableId,
                    'number_of_guests' => $this->numberOfGuests,
                    'status' => 'pending',
                    'booked_date' => $this->reservationDate,
                    'special_requests' => null,
                ]);

                Log::info('Livewire: Reservation successfully created.', [
                    'contact_email' => $contact->email,
                    'date' => $this->reservationDate,
                    'time_slot_id' => $this->selectedTimeSlotId,
                    'table_id' => $this->selectedTableId,
                ]);

                $this->reset([
                    'selectedRestaurantId', 'reservationDate', 'numberOfGuests', 'selectedTimeSlotId', 'selectedTableId',
                    'contactFirstName', 'contactLastName', 'contactEmail', 'contactPhoneNumber'
                ]);
                $this->reservationDate = now()->toDateString();
                $this->currentStep = 1;

                $this->updateAvailableTimeSlots();

                session()->flash('success', 'Reservation created successfully! A confirmation has been sent to your email.');
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Livewire: Validation Exception during confirmReservation.', ['errors' => $e->errors()]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Livewire: General Exception during confirmReservation: ' . $e->getMessage(), [
                'current_step_data' => $this->all(),
                'exception' => $e
            ]);
            session()->flash('error', 'Failed to create reservation. Please try again. ' . $e->getMessage());
        }
    }

    /**
     * Render the Livewire component view.
     */
    public function render()
    {
        return view('livewire.reservation-form');
    }
}