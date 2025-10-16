{{-- resources/views/booking-page.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container">
        @livewire('reservation-form') {{-- This line embeds your Livewire component --}}
    </div>
@endsection