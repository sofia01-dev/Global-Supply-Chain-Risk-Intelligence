@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-10">
        
        <!-- Header -->
        <div class="d-flex align-items-center mb-4">
            <div class="me-3 p-3 bg-white shadow-sm rounded-3">
                <i class="bi bi-box-seam fs-3 text-primary"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-0 text-dark">{{ __('Create New Shipment') }}</h3>
                <p class="text-muted mb-0">{{ __('Fill in the information to create a new shipment') }}</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-md-5">
                <form action="{{ route('user.shipments.store') }}" method="POST">
                    @csrf
                    
                    <div class="row g-4">
                        <!-- Shipment Name -->
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">{{ __('Shipment Name') }}</label>
                            <input type="text" name="shipment_name" class="form-control form-control-lg border-light bg-light" placeholder="e.g. Import Laptop Juli" required>
                        </div>

                        <!-- Goods -->
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">{{ __('Goods') }}</label>
                            <input type="text" name="goods" class="form-control form-control-lg border-light bg-light" placeholder="e.g. Laptop" required>
                        </div>

                        <!-- Origin Section -->
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">{{ __('Origin Country') }}</label>
                            <select id="origin_country" class="form-select form-select-lg border-light bg-light" required>
                                <option value="">{{ __('Select Country') }}</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}">{{ $country->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">{{ __('Origin Port') }}</label>
                            <select id="origin_port" name="origin_port_id" class="form-select form-select-lg border-light bg-light" required disabled>
                                <option value="">{{ __('Select Port') }}</option>
                            </select>
                        </div>

                        <!-- Destination Section -->
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">{{ __('Destination Country') }}</label>
                            <select id="dest_country" class="form-select form-select-lg border-light bg-light" required>
                                <option value="">{{ __('Select Country') }}</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}">{{ $country->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">{{ __('Destination Port') }}</label>
                            <select id="dest_port" name="destination_port_id" class="form-select form-select-lg border-light bg-light" required disabled>
                                <option value="">{{ __('Select Port') }}</option>
                            </select>
                        </div>

                        <!-- ETA & Status -->
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">{{ __('Estimated Arrival (ETA)') }}</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text border-light bg-light"><i class="bi bi-calendar"></i></span>
                                <input type="date" name="estimated_arrival" class="form-control border-light bg-light" required>
                            </div>
                        </div>

                        <!-- Hidden Required Fields with Defaults -->
                        <input type="hidden" name="departure_date" value="{{ date('Y-m-d') }}">
                        <input type="hidden" name="current_status" value="pending">
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-5">
                        <div class="text-primary bg-primary bg-opacity-10 px-4 py-3 rounded-3 d-flex align-items-center">
                            <i class="bi bi-info-circle-fill me-3 fs-4"></i>
                            <div>
                                <small class="d-block">{{ __('Shipment code will be generated automatically after saving.') }}</small>
                                <small class="fw-bold">{{ __('Example: SHP-20260711-0001') }}</small>
                            </div>
                        </div>
                        <div class="d-flex gap-3">
                            <a href="{{ route('user.shipments.index') }}" class="btn btn-light px-4 py-2 rounded-3 fw-medium">{{ __('Cancel') }}</a>
                            <button type="submit" class="btn text-white px-5 py-2 rounded-3 fw-bold shadow-sm" style="background-color: var(--primary-navy);">
                                {{ __('Create Shipment') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    function loadPorts(countrySelectId, portSelectId) {
        const countryId = document.getElementById(countrySelectId).value;
        const portSelect = document.getElementById(portSelectId);
        
        portSelect.innerHTML = '<option value="">{{ __("Loading...") }}</option>';
        portSelect.disabled = true;

        if(!countryId) {
            portSelect.innerHTML = '<option value="">{{ __("Select Port") }}</option>';
            return;
        }

        fetch(`/user/shipments/api/ports/${countryId}`)
            .then(res => res.json())
            .then(data => {
                portSelect.innerHTML = '<option value="">{{ __("Select Port") }}</option>';
                data.forEach(port => {
                    portSelect.innerHTML += `<option value="${port.id}">${port.name}</option>`;
                });
                portSelect.disabled = false;
            })
            .catch(err => {
                portSelect.innerHTML = '<option value="">{{ __("Error loading ports") }}</option>';
                console.error(err);
            });
    }

    document.getElementById('origin_country').addEventListener('change', function() {
        loadPorts('origin_country', 'origin_port');
    });

    document.getElementById('dest_country').addEventListener('change', function() {
        loadPorts('dest_country', 'dest_port');
    });
});
</script>
@endpush
@endsection
