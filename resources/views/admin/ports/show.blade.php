@extends('layouts.app')
@section('content')
<div class="container">
    <h2>Port Details</h2>
    @if(!$port)
        <div class="alert alert-warning">Port not found.</div>
    @else
        <div class="card mt-3">
            <div class="card-body">
                <p><strong>Name:</strong> {{ $port->name }}</p>
                <p><strong>Country:</strong> {{ $port->country->name ?? '-' }}</p>
                <p><strong>Latitude:</strong> {{ $port->latitude ?? '-' }}</p>
                <p><strong>Longitude:</strong> {{ $port->longitude ?? '-' }}</p>
                <a href="{{ url('/admin/ports') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>
    @endif
</div>
@endsection