@extends('layouts.app')

@section('content')
    <div class="container reservation-container">
        {{-- Render the Livewire component here --}}
        @livewire('reservation-form')
    </div>
@endsection