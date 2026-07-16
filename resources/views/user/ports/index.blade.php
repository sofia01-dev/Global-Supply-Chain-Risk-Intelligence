@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-end mb-3">
        <div>
            <h3 class="fw-bold mb-0 text-dark">{{ __('Port Location Dashboard') }}</h3>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">{{ __('Global Interactive Map for Port Tracking') }}</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden position-relative" style="height: 75vh;">
        <!-- Leaflet Map Container -->
        <div id="portsMap" style="width: 100%; height: 100%; z-index: 1;"></div>

        <!-- Floating Glassmorphism Panel (Search & Filter) -->
        <div class="position-absolute top-0 start-0 m-4" style="z-index: 1000; width: 350px;">
            <div class="card border-0 shadow rounded-4" style="background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-search me-2 text-primary"></i>{{ __('Find Port') }}</h5>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted mb-1">{{ __('Search by Name') }}</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" id="searchInput" class="form-control border-start-0 ps-0" placeholder="e.g. Tanjung Priok...">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted mb-1">{{ __('Filter by Country') }}</label>
                        <select id="countrySelect" class="form-select">
                            <option value="all">-- All Countries --</option>
                            @php
                                // Get unique countries from the ports collection
                                $countries = $ports->pluck('country')->unique('id')->sortBy('name');
                            @endphp
                            @foreach($countries as $c)
                                @if($c)
                                    <option value="{{ strtolower($c->iso2_code ?? '') }}" data-lat="{{ $c->latitude }}" data-lng="{{ $c->longitude }}">{{ $c->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <!-- Search Results -->
                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-bold text-muted">{{ __('Results') }}</span>
                            <span class="badge bg-primary rounded-pill" id="resultCount">{{ $ports->count() }}</span>
                        </div>
                        <div id="searchResults" class="overflow-auto pe-2" style="max-height: 250px;">
                            <!-- List dynamically populated via JS -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<style>
    /* Custom Scrollbar for search results */
    #searchResults::-webkit-scrollbar { width: 5px; }
    #searchResults::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
    #searchResults::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 10px; }
    #searchResults::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
    
    .port-item { transition: all 0.2s ease; cursor: pointer; border-left: 3px solid transparent; }
    .port-item:hover { background-color: #f8f9fa; border-left: 3px solid #1C55FF; }
    
    /* Custom Anchor Icon Style */
    .custom-div-icon .icon-container {
        display: flex; justify-content: center; align-items: center;
        width: 32px; height: 32px;
        background-color: #1C55FF; color: white;
        border-radius: 50%; box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        border: 2px solid white;
        transition: transform 0.2s;
    }
    .custom-div-icon .icon-container:hover { transform: scale(1.1); background-color: #0d3bce; }
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const rawPorts = @json($ports);
    let map, markerCluster;
    let markers = [];
    
    // Default center (World view)
    map = L.map('portsMap', { zoomControl: false }).setView([20, 0], 3);
    
    // Add modern map tiles (CartoDB Positron for clean look)
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap contributors &copy; CARTO'
    }).addTo(map);
    
    // Move zoom control to bottom right
    L.control.zoom({ position: 'bottomright' }).addTo(map);

    // Custom Icon (Anchor)
    const portIcon = L.divIcon({
        className: 'custom-div-icon',
        html: `<div class="icon-container"><i class="bi bi-geo-alt-fill"></i></div>`,
        iconSize: [32, 32],
        iconAnchor: [16, 32],
        popupAnchor: [0, -32]
    });

    // Initialize Markers and List
    renderMapAndList(rawPorts);

    function renderMapAndList(dataToRender) {
        // Clear existing markers
        markers.forEach(m => map.removeLayer(m));
        markers = [];
        
        const listContainer = document.getElementById('searchResults');
        listContainer.innerHTML = '';
        document.getElementById('resultCount').innerText = dataToRender.length;

        if(dataToRender.length === 0) {
            listContainer.innerHTML = `<div class="text-center text-muted small py-3"><i class="bi bi-info-circle mb-1 fs-5 d-block"></i> No ports found.</div>`;
            return;
        }

        dataToRender.forEach((port, index) => {
            // Validate Lat/Lng
            const lat = parseFloat(port.latitude);
            const lng = parseFloat(port.longitude);
            const countryCode = port.country ? port.country.iso2_code.toLowerCase() : 'id';
            const countryName = port.country ? port.country.name : 'Unknown';

            // Add List Item
            const item = document.createElement('div');
            item.className = 'port-item p-2 mb-2 rounded bg-white shadow-sm border';
            item.innerHTML = `
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 text-dark fw-bold" style="font-size:0.8rem;">${port.name}</h6>
                        <small class="text-muted" style="font-size:0.7rem;"><img src="https://flagcdn.com/16x12/${countryCode}.png" class="me-1 border" onerror="this.style.display='none'">${countryName}</small>
                    </div>
                    <i class="bi bi-chevron-right text-muted" style="font-size:0.7rem;"></i>
                </div>
            `;
            
            // Marker
            let marker = null;
            if(!isNaN(lat) && !isNaN(lng)) {
                marker = L.marker([lat, lng], {icon: portIcon}).addTo(map);
                const popupContent = `
                    <div class="text-center p-1">
                        <img src="https://flagcdn.com/32x24/${countryCode}.png" class="mb-2 shadow-sm rounded border">
                        <h6 class="fw-bold mb-1">${port.name}</h6>
                        <p class="small text-muted mb-2">${countryName}</p>
                        <a href="/user/ports/${port.id}" class="btn btn-sm btn-primary rounded-pill w-100" style="font-size:0.75rem;">View Details</a>
                    </div>
                `;
                marker.bindPopup(popupContent);
                markers.push(marker);
            }

            // Click behavior
            item.addEventListener('click', () => {
                if(marker) {
                    map.flyTo([lat, lng], 10, { duration: 1.5 });
                    marker.openPopup();
                }
            });
            
            listContainer.appendChild(item);
        });
    }

    // Filter Logic
    const searchInput = document.getElementById('searchInput');
    const countrySelect = document.getElementById('countrySelect');

    function filterData() {
        const query = searchInput.value.toLowerCase();
        const selectedCountry = countrySelect.value.toLowerCase();
        
        const filtered = rawPorts.filter(port => {
            const matchesName = port.name.toLowerCase().includes(query);
            const portCountry = port.country ? port.country.iso2_code.toLowerCase() : '';
            const matchesCountry = selectedCountry === 'all' || portCountry === selectedCountry;
            return matchesName && matchesCountry;
        });

        renderMapAndList(filtered);
    }

    searchInput.addEventListener('input', filterData);
    
    countrySelect.addEventListener('change', function() {
        filterData();
        // Fly to country
        const selectedOption = this.options[this.selectedIndex];
        if(this.value !== 'all') {
            const lat = parseFloat(selectedOption.getAttribute('data-lat'));
            const lng = parseFloat(selectedOption.getAttribute('data-lng'));
            if(!isNaN(lat) && !isNaN(lng)) {
                map.flyTo([lat, lng], 5, { duration: 1.5 });
            }
        } else {
            map.flyTo([20, 0], 3, { duration: 1.5 });
        }
    });
});
</script>
@endpush