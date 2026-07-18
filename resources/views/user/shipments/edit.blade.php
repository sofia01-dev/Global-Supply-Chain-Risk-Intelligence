@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-8">
        
        <!-- Header -->
        <div class="d-flex align-items-center mb-4">
            <a href="{{ route('user.shipments.index') }}" class="btn btn-light rounded-circle shadow-sm me-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h3 class="fw-bold mb-0 text-dark">{{ __('Edit Shipment') }} <span class="text-primary">#{{ $shipment->shipment_code }}</span></h3>
                <p class="text-muted mb-0 small">{{ __('Update logistics route or current tracking status') }}</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-0 py-4 px-4 px-md-5">
                <h5 class="fw-bold text-warning mb-0"><i class="bi bi-pencil-square me-2"></i>{{ __('Shipment Configuration') }}</h5>
            </div>
            
            <div class="card-body px-4 px-md-5 pb-5">
                <form action="{{ route('user.shipments.update', $shipment->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <!-- Route Section -->
                    <div class="p-4 bg-light rounded-4 mb-4 position-relative border border-warning border-opacity-25">
                        <span class="badge bg-warning text-dark position-absolute top-0 start-0 translate-middle-y ms-4 px-3">{{ __('Logistics Route') }}</span>
                        
                        <div class="row g-4 position-relative pt-2">
                            <!-- Removed connector line based on user request -->
                            
                            <div class="col-md-6 z-index-2 position-relative" style="z-index: 2;">
                                <label class="form-label text-muted small fw-bold"><i class="bi bi-geo-alt-fill text-warning me-1"></i>{{ __('Origin Port') }}</label>
                                <select name="origin_port_id" class="form-select border-0 shadow-sm py-2" required>
                                    <option value="">{{ __('Select Origin...') }}</option>
                                    @foreach($ports as $port)
                                        <option value="{{ $port->id }}" {{ $shipment->origin_port_id == $port->id ? 'selected' : '' }}>
                                            {{ $port->name }} ({{ __($port->country->name ?? 'Unknown') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-6 z-index-2 position-relative" style="z-index: 2;">
                                <label class="form-label text-muted small fw-bold"><i class="bi bi-flag-fill text-success me-1"></i>{{ __('Destination Port') }}</label>
                                <select name="destination_port_id" class="form-select border-0 shadow-sm py-2" required>
                                    <option value="">{{ __('Select Destination...') }}</option>
                                    @foreach($ports as $port)
                                        <option value="{{ $port->id }}" {{ $shipment->destination_port_id == $port->id ? 'selected' : '' }}>
                                            {{ $port->name }} ({{ __($port->country->name ?? 'Unknown') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Schedule & Status Section -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold">{{ __('Departure Date') }}</label>
                            <div class="input-group input-group-lg shadow-sm rounded-3">
                                <span class="input-group-text bg-white border-0 text-muted"><i class="bi bi-calendar-check"></i></span>
                                <input type="date" name="departure_date" class="form-control border-0 bg-white" value="{{ $shipment->departure_date ? $shipment->departure_date->format('Y-m-d') : '' }}" required>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold">{{ __('Est. Arrival Date') }}</label>
                            <div class="input-group input-group-lg shadow-sm rounded-3">
                                <span class="input-group-text bg-white border-0 text-muted"><i class="bi bi-calendar-event"></i></span>
                                <input type="date" name="estimated_arrival" class="form-control border-0 bg-white" value="{{ $shipment->estimated_arrival ? $shipment->estimated_arrival->format('Y-m-d') : '' }}" required>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold">{{ __('Current Status') }}</label>
                            <select name="current_status" class="form-select form-select-lg border-0 shadow-sm bg-white" required>
                                <option value="pending" {{ $shipment->current_status == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                                <option value="transit" {{ $shipment->current_status == 'transit' ? 'selected' : '' }}>{{ __('Transit') }}</option>
                                <option value="delayed" {{ $shipment->current_status == 'delayed' ? 'selected' : '' }}>{{ __('Delayed') }}</option>
                                <option value="arrived" {{ $shipment->current_status == 'arrived' ? 'selected' : '' }}>{{ __('Arrived') }}</option>
                                <option value="delivered" {{ $shipment->current_status == 'delivered' ? 'selected' : '' }}>{{ __('Delivered') }}</option>
                            </select>
                        </div>
                    </div>

                    <hr class="text-muted opacity-25 my-4">

                    <div class="d-flex justify-content-end gap-3 mt-4">
                        <a href="{{ route('user.shipments.index') }}" class="btn btn-light px-4 rounded-pill fw-medium border">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-warning px-5 rounded-pill fw-bold shadow-sm text-dark">
                            {{ __('Update Shipment') }} <i class="bi bi-save ms-1"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
    </div>
</div>
@endsection
