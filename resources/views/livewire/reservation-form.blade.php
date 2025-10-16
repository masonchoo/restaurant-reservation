<div class="container reservation-form-wrapper">
    <h2 class="form-header">Make A Reservation</h2>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form wire:submit.prevent="confirmReservation">

        {{-- Step Indicator --}}
        <div class="step-indicator mb-4">
            <span class="step {{ $currentStep == 1 ? 'active' : '' }}">1. FIND A TABLE</span>
            <span class="step {{ $currentStep == 2 ? 'active' : '' }}">2. YOUR DETAILS</span>
        </div>

        {{-- Step 1: Reservation Details --}}
        @if ($currentStep == 1)
            <div>
                <h3 class="form-section-header">Step 1: Choose Your Slot</h3>

                {{-- Select Restaurant Dropdown --}}
                <div class="mb-3 form-field-group">
                    <label for="selectedRestaurantId" class="form-label">Select Restaurant</label>
                    <select class="form-select" id="selectedRestaurantId" required wire:model.live="selectedRestaurantId">
                        <option value="">Choose a restaurant</option>
                        @foreach($restaurants as $restaurant)
                            <option value="{{ $restaurant->id }}">{{ $restaurant->name }}</option>
                        @endforeach
                    </select>
                    @error('selectedRestaurantId') <span class="text-danger error-message">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3 form-field-group" style="position:relative;">
                    <label for="reservationDate" class="form-label">Select Date</label>
                    <input
                        type="text"
                        class="form-control"
                        id="reservationDate"
                        placeholder="Click to select a date"
                        required
                        autocomplete="off"
                        readonly
                        wire:model.live="reservationDate"
                        x-data
                        x-init="flatpickr($el, {
                            dateFormat: 'Y-m-d',
                            minDate: 'today',
                            onChange: function(selectedDates, dateStr, instance) {
                                @this.set('reservationDate', dateStr);
                            }
                        });"
                        wire:ignore
                    >
                    @error('reservationDate') <span class="text-danger error-message">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3 form-field-group">
                    <label for="numberOfGuests" class="form-label">Number of Guests</label>
                    <select class="form-select" id="numberOfGuests" required wire:model.live="numberOfGuests">
                        <option value="">Select number of guests</option>
                        <option value="1">1 Person</option>
                        <option value="2">2 People</option>
                        <option value="3">3 People</option>
                        <option value="4">4 People</option>
                        <option value="5">5 People</option>
                        <option value="6">6 People</option>
                        <option value="7">7 People</option>
                        <option value="8">8 People</option>
                        <option value="9">9 People</option>
                        <option value="10">10+ People</option>
                    </select>
                    @error('numberOfGuests') <span class="text-danger error-message">{{ $message }}</span> @enderror
                </div>

                @if($selectedRestaurantId && $reservationDate && $numberOfGuests)
                    <div class="mb-4 form-field-group">
                        <label class="form-label d-block">Select Time Slot</label>
                        <div class="time-slot-buttons-grid" id="timeSlots">
                            @forelse($allTimeSlots as $slot)
                                @php
                                    $isAvailable = collect($availableTimeSlots)->contains('id', $slot->id);
                                @endphp
                                <button
                                    type="button"
                                    class="btn time-slot-btn {{ $isAvailable ? '' : 'disabled' }} {{ $selectedTimeSlotId == $slot->id ? 'active' : '' }}"
                                    wire:click="{{ $isAvailable ? 'selectTimeSlot(' . $slot->id . ')' : '' }}"
                                    {{ $isAvailable ? '' : 'disabled' }}
                                    title="{{ $isAvailable ? '' : 'Fully booked' }}"
                                >
                                    {{ $slot->display_time }}
                                </button>
                            @empty
                                <p class="text-muted w-100 text-center">No time slots configured.</p>
                            @endforelse

                            @if(count($allTimeSlots) > 0 && count($availableTimeSlots) == 0)
                                <p class="text-info w-100 text-center mt-3">No time slots available for this date, guests, and restaurant. Please try another combination.</p>
                            @endif
                        </div>
                        @error('selectedTimeSlotId') <span class="text-danger error-message">{{ $message }}</span> @enderror
                    </div>
                @else
                    <div class="mb-4 form-field-group text-center text-muted">
                        <p class="mb-2">Time slots will appear here after you select:</p>
                        @if(!$selectedRestaurantId) <p class="mb-1">- A Restaurant</p> @endif
                        @if(!$reservationDate) <p class="mb-1">- A Date</p> @endif
                        @if(!$numberOfGuests) <p class="mb-1">- Number of Guests</p> @endif
                    </div>
                @endif

                <div class="d-grid gap-2">
                    <button type="button" wire:click="nextStep" class="btn btn-book btn-lg">Next</button>
                </div>
            </div>
        @endif

        {{-- Step 2: Contact Details --}}
        @if ($currentStep == 2)
            <div class="contact-details-section">
                <h3 class="form-section-header">Step 2: Your Details</h3>

                <div class="mb-3 form-field-group">
                    <label for="contactFirstName" class="form-label">First Name</label>
                    <input type="text" class="form-control" id="contactFirstName" placeholder="First Name" required wire:model.live="contactFirstName">
                    @error('contactFirstName') <span class="text-danger error-message">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3 form-field-group">
                    <label for="contactLastName" class="form-label">Last Name (Optional)</label>
                    <input type="text" class="form-control" id="contactLastName" placeholder="Last Name" wire:model.live="contactLastName">
                    @error('contactLastName') <span class="text-danger error-message">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3 form-field-group">
                    <label for="contactEmail" class="form-label">Email</label>
                    <input type="email" class="form-control" id="contactEmail" placeholder="email@example.com" required wire:model.live="contactEmail">
                    @error('contactEmail') <span class="text-danger error-message">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3 form-field-group">
                    <label for="contactPhoneNumber" class="form-label">Phone Number</label>
                    <input type="text" class="form-control" id="contactPhoneNumber" placeholder="e.g., 0123456789" required wire:model.live="contactPhoneNumber">
                    @error('contactPhoneNumber') <span class="text-danger error-message">{{ $message }}</span> @enderror
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <button type="button" wire:click="previousStep" class="btn btn-secondary styled-back-btn btn-block">Back</button>
                    <button type="submit" class="btn btn-book btn-block">Confirm Reservation</button>
                </div>
            </div>
        @endif

    </form>

</div>