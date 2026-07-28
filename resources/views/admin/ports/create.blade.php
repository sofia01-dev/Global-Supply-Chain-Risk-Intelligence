@extends('layouts.app')

@push('styles')
<style>
    .admin-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
        background-color: #fff;
    }
    .form-label {
        font-weight: 600;
        font-size: 0.85rem;
        color: #555;
    }
    .form-control, .form-select {
        border-radius: 8px;
        padding: 10px 15px;
        border: 1px solid #e0e0e0;
        font-size: 0.9rem;
    }
    .form-control:focus, .form-select:focus {
        border-color: #3E53A0;
        box-shadow: 0 0 0 0.25rem rgba(62, 83, 160, 0.1);
    }
    .btn-navy {
        background-color: #3E53A0;
        color: white;
        border: none;
    }
    .btn-navy:hover {
        background-color: #2c3e80;
        color: white;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4" style="max-width: 800px;">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-dark">{{ __('Add New Port') }}</h4>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">Register a new port to the global dataset</p>
        </div>
        <a href="{{ route('admin.ports.index') }}" class="btn btn-light border" style="border-radius: 8px; font-size: 0.85rem;">
            <i class="bi bi-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <!-- Form Card -->
    <div class="admin-card card p-4">
        @if($errors->any())
            <div class="alert alert-danger" style="border-radius: 8px;">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.ports.store') }}">
            @csrf
            
            <h6 class="fw-bold mb-4 border-bottom pb-2">{{ __('Port Details') }}</h6>
            
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label">{{ __('Port Name') }}</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. Tanjung Priok Port" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Port Code (UN/LOCODE)</label>
                    <input type="text" name="unlocode" class="form-control" value="{{ old('unlocode') }}" placeholder="e.g. IDTJK" style="text-transform: uppercase;" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label">{{ __('COUNTRY') }}</label>
                    <select name="country_id" class="form-select" required>
                        <option value="">-- Select Country --</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->id }}" {{ old('country_id') == $country->id ? 'selected' : '' }}>
                                {{ $country->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <h6 class="fw-bold mb-4 border-bottom pb-2">Geographical Coordinates</h6>

            <div class="alert alert-info" style="border-radius: 8px; font-size: 0.85rem; padding: 10px 15px;">
                <i class="bi bi-info-circle me-2"></i> Klik titik manapun pada peta di bawah ini untuk mengisi angka Latitude & Longitude secara otomatis.
            </div>
            <div id="map" style="height: 350px; width: 100%; border-radius: 8px; margin-bottom: 20px; z-index: 1; border: 1px solid #ddd;"></div>

            <div class="row g-4 mb-5">
                <div class="col-md-6">
                    <label class="form-label">Latitude</label>
                    <input type="number" step="any" id="latitudeInput" name="latitude" class="form-control" value="{{ old('latitude') }}" placeholder="e.g. -6.1431" required>
                    <div class="form-text" style="font-size: 0.75rem;">Range: -90 to 90</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Longitude</label>
                    <input type="number" step="any" id="longitudeInput" name="longitude" class="form-control" value="{{ old('longitude') }}" placeholder="e.g. 106.8706" required>
                    <div class="form-text" style="font-size: 0.75rem;">Range: -180 to 180</div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.ports.index') }}" class="btn btn-light border px-4" style="border-radius: 8px;">{{ __('Cancel') }}</a>
                <button type="submit" class="btn btn-navy px-4" style="border-radius: 8px;">
                    <i class="bi bi-save me-1"></i> Save Port
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inisialisasi peta dengan koordinat awal (Fokus ke Indonesia, zoom 5)
        var map = L.map('map').setView([-0.7893, 113.9213], 5);
        
        // Memuat tile layer dari Google Maps (Standard Streets)
        L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
            maxZoom: 20,
            subdomains: ['mt0','mt1','mt2','mt3'],
            attribution: '&copy; Google Maps'
        }).addTo(map);

        var marker = null;
        var latInput = document.getElementById('latitudeInput');
        var lngInput = document.getElementById('longitudeInput');

        // Jika ada nilai lama (misal error validasi form), tampilkan pin
        if(latInput.value && lngInput.value) {
            var lat = parseFloat(latInput.value);
            var lng = parseFloat(lngInput.value);
            marker = L.marker([lat, lng]).addTo(map);
            map.setView([lat, lng], 8);
        }

        // Event listener saat pengguna mengklik titik pada peta
        map.on('click', function(e) {
            var lat = e.latlng.lat;
            var lng = e.latlng.lng;
            
            // Format ke 6 angka desimal agar rapi
            latInput.value = lat.toFixed(6);
            lngInput.value = lng.toFixed(6);

            // Geser pin ke titik yang diklik
            if (marker) {
                marker.setLatLng(e.latlng);
            } else {
                marker = L.marker(e.latlng).addTo(map);
            }
        });

        // Event listener jika pengguna mengetik koordinat manual, peta ikut bergeser
        function updateMapFromInput() {
            var lat = parseFloat(latInput.value);
            var lng = parseFloat(lngInput.value);
            
            if(!isNaN(lat) && !isNaN(lng)) {
                if(marker) {
                    marker.setLatLng([lat, lng]);
                } else {
                    marker = L.marker([lat, lng]).addTo(map);
                }
                map.setView([lat, lng], map.getZoom() > 5 ? map.getZoom() : 8);
            }
        }

        latInput.addEventListener('input', updateMapFromInput);
        lngInput.addEventListener('input', updateMapFromInput);
    });
</script>
@endpush
