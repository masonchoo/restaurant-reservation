<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ContactDetails;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class ContactDetailsForm extends Component
{
    // Public properties for the form fields
    public $contactId; // To store the ID if we're editing an existing contact
    public $firstName = '';
    public $lastName = '';
    public $email = '';
    public $phoneNumber = '';

    /**
     * Define validation rules for the form properties.
     */
    protected function rules()
    {
        return [
            'firstName' => ['required', 'string', 'max:255'],
            'lastName' => ['nullable', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                // Ensure email is unique, ignoring the current contact if editing
                Rule::unique('contact_details', 'email')->ignore($this->contactId),
            ],
            'phoneNumber' => ['required', 'string', 'max:20', 'min:8'],
        ];
    }

    /**
     * Optional: Real-time validation as user types.
     */
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    /**
     * Mount method to initialize the component.
     * This allows us to pass an existing ContactDetails model for editing.
     *
     * @param ContactDetails|null $contact
     */
    public function mount(?ContactDetails $contact = null)
    {
        if ($contact->exists) { // Check if a contact model was passed and it exists in DB
            $this->contactId = $contact->id;
            $this->firstName = $contact->first_name;
            $this->lastName = $contact->last_name;
            $this->email = $contact->email;
            $this->phoneNumber = $contact->phone_number;
        }
    }

    /**
     * The method that handles saving (creating or updating) the contact details.
     */
    public function saveContact()
    {
        // Validate all properties
        $validatedData = $this->validate();

        try {
            if ($this->contactId) {
                // Update existing contact
                $contact = ContactDetails::findOrFail($this->contactId);
                $contact->update([
                    'first_name' => $validatedData['firstName'],
                    'last_name' => $validatedData['lastName'],
                    'email' => $validatedData['email'],
                    'phone_number' => $validatedData['phoneNumber'],
                ]);
                $message = 'Contact updated successfully!';
                Log::info('Livewire: Contact updated:', ['contact_id' => $contact->id, 'data' => $contact->toArray()]);
            } else {
                // Create new contact
                $contact = ContactDetails::create([
                    'first_name' => $validatedData['firstName'],
                    'last_name' => $validatedData['lastName'],
                    'email' => $validatedData['email'],
                    'phone_number' => $validatedData['phoneNumber'],
                ]);
                $message = 'Contact created successfully!';
                Log::info('Livewire: New contact created:', ['contact_id' => $contact->id, 'data' => $contact->toArray()]);
            }

            // Reset the form fields if creating a new one
            if (!$this->contactId) {
                $this->reset(['firstName', 'lastName', 'email', 'phoneNumber']);
            }

            // Flash a success message
            session()->flash('success', $message);

            // Optional: Redirect to a list of contacts or the edited contact's page
            // For example: return redirect()->route('contacts.index');

        } catch (\Exception $e) {
            Log::error('Livewire: Error saving contact: ' . $e->getMessage(), [
                'input_data' => $this->all(),
                'exception' => $e
            ]);
            session()->flash('error', 'Failed to save contact: ' . $e->getMessage());
        }
    }

    /**
     * Render method to display the component's view.
     */
    public function render()
    {
        return view('livewire.contact-details-form');
    }
}