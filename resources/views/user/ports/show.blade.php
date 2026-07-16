@extends('layouts.app')
@section('content')
<div class="container">
    @if(!$port)
        <div class="alert alert-warning">Port information could not be found.</div>
    @else
        <h2>Port: {{ $port->name }}</h2>
        <div class="card mt-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>General Information</h5>
                        <p><strong>Country:</strong> {{ $port->country->name ?? '-' }}</p>
                        <p><strong>Region:</strong> {{ $port->country->region ?? '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <h5>Coordinates</h5>
                        <p><strong>Latitude:</strong> {{ $port->latitude ?? '-' }}</p>
                        <p><strong>Longitude:</strong> {{ $port->longitude ?? '-' }}</p>
                    </div>
                </div>
                <hr>
                <h5>Related Shipments</h5>
                @if($port->shipments && $port->shipments->isNotEmpty())
                    <ul>
                        @foreach($port->shipments as $shipment)
                            <li>{{ $shipment->shipment_code }} (Status: {{ $shipment->current_status }})</li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted">No related shipments found.</p>
                @endif
                <a href="{{ url('/user/ports') }}" class="btn btn-secondary mt-3">Back to Ports</a>
            </div>
        </div>
    @endif
</div>
@endsection