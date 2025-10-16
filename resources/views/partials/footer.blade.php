{{-- All JavaScript files go here. --}}
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $(function() {
        // Initialize Datepicker
        $("#reservationDate").datepicker({
            dateFormat: "dd/mm/yy", // e.g., 23/05/2025
            minDate: 0, // Cannot select past dates
            prevText: "<", // Set the previous month button text to "<"
            nextText: ">",  // Set the next month button text to ">"
            onSelect: function(dateText, inst) {
                // You can add logic here to fetch available time slots for the selected date
                // For demonstration, let's simulate some disabled slots
                updateTimeSlotsAvailability(dateText);
            },
            beforeShow: function(input, inst) {
                // Get the position and dimensions of the input field
                var inputRect = input.getBoundingClientRect();

                // Use setTimeout to ensure our CSS override happens after jQuery UI's own positioning
                setTimeout(function() {
                    inst.dpDiv.css({
                        top: inputRect.bottom + window.scrollY + 'px', // Position below the input, accounting for page scroll
                        left: inputRect.left + window.scrollX + 'px',   // Align with the left of the input, accounting for page scroll
                        'z-index': 9999 // Ensure it's on top of other elements, adjust as needed
                    });
                }, 0); // A timeout of 0 allows the browser to render the initial positioning
            }
        });

        // Handle time slot button click
        $('#timeSlots').on('click', '.btn', function() {
            if (!$(this).hasClass('disabled-slot')) {
                $('#timeSlots .btn').removeClass('selected'); // Remove selected from all
                $(this).addClass('selected'); // Add selected to clicked button
                $('#timeSlotError').hide(); // Hide error if a time slot is selected
            }
        });

        // Form submission handler
        $('#reservationForm').on('submit', function(e) {
            e.preventDefault(); // Prevent default form submission

            const selectedDate = $('#reservationDate').val();
            const numGuests = $('#numGuests').val();
            const selectedTime = $('#timeSlots .btn.selected').data('time');

            if (!selectedDate) {
                alert('Please select a date.');
                return;
            }
            if (!numGuests) {
                alert('Please select the number of guests.');
                return;
            }
            if (!selectedTime) {
                $('#timeSlotError').show(); // Show error if no time slot is selected
                return;
            } else {
                $('#timeSlotError').hide();
            }

            alert(`Reservation Details:\nDate: ${selectedDate}\nGuests: ${numGuests}\nTime: ${selectedTime}`);

            // Here you would typically send this data to a server using AJAX
            // Example:
            /*
            $.ajax({
                url: '/api/make-reservation', // Your API endpoint
                method: 'POST',
                data: {
                    date: selectedDate,
                    guests: numGuests,
                    time: selectedTime
                },
                success: function(response) {
                    alert('Reservation successful!');
                    // Redirect or show confirmation message
                },
                error: function(xhr, status, error) {
                    alert('Reservation failed: ' + error);
                }
            });
            */
        });

        // Function to simulate updating time slot availability
        function updateTimeSlotsAvailability(selectedDate) {
            // In a real application, you'd make an AJAX call here
            // to your backend to get available slots for `selectedDate`
            // Example of how to enable/disable slots based on a simulated logic:
            const allTimeSlots = $('#timeSlots .btn');
            allTimeSlots.removeClass('disabled-slot'); // Reset all

            // Simulate some slots being unavailable on certain dates
            if (selectedDate === '25/05/2025') { // Example: If a specific date
                $('[data-time="19:00"]').addClass('disabled-slot');
                $('[data-time="19:30"]').addClass('disabled-slot');
            } else if (selectedDate === '26/05/2025') { // Another example
                 $('[data-time="18:00"]').addClass('disabled-slot');
                 $('[data-time="20:30"]').addClass('disabled-slot');
            }
            // No selection by default if slots are updated
            allTimeSlots.removeClass('selected');
        }
    });
</script>

{{-- Any other site-wide footer content can go here --}}
{{-- Example: <p class="text-center mt-5">© 2025 Restaurant Reservations</p> --}}