<?php

use Illuminate\Support\Facades\Route;
use App\Models\ContactDetails;

Route::get('/book', function () {
    return view('booking-page');
})->name('book.table');


Route::get('/contacts/create', function () {
    return view('contact-details-page');
})->name('contacts.create');

Route::get('/contacts/{contact}/edit', function (ContactDetails $contact) {
    return view('contact-details-page', ['contact' => $contact]);
})->name('contacts.edit');


Route::get('/', function () {
    return redirect()->route('book.table');
});

