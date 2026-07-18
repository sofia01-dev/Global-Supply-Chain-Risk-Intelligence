@extends('layouts.app')

@push('styles')
<!-- Flag Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.0.0/css/flag-icons.min.css"/>
<!-- Leaflet CSS is already in app.blade.php, but just in case -->
<style>
    .country-nav-list {
        max-height: calc(100vh - 220px);
        overflow-y: auto;
        padding-right: 5px;
    }
    .country-nav-list::-webkit-scrollbar { width: 5px; }
    .country-nav-list::-webkit-scrollbar-thumb { background: #e0e0e0; border-radius: 10px; }
    
    .country-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 15px;
        margin-bottom: 8px;
        border-radius: 12px;
        border: 1px solid transparent;
        text-decoration: none;
        color: inherit;
        transition: all 0.2s ease;
        background-color: #fff;
    }
    .country-item:hover {
        background-color: #f8f9fa;
        border-color: #e9ecef;
    }
    .country-item.active {
        background-color: #f0f4ff;
        border-color: #c7d2fe;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
    }
    
    .modern-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        background-color: #fff;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    #weatherMap {
        height: 480px;
        border-radius: 16px;
        z-index: 1; /* Keep map below navbar */
    }
    
    .weather-icon-lg {
        font-size: 2.5rem;
    }
    
    .alert-card-item {
        padding: 12px 16px;
        border-radius: 12px;
        margin-bottom: 12px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    .alert-card-item:last-child {
        margin-bottom: 0;
    }
</style>
@endpush

@section('content')
<div class="row mb-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-1">{{ __('Global Weather Monitoring') }}</h4>
            <p class="text-muted small mb-0">{{ __('Monitor kondisi cuaca global secara realtime berdasarkan negara yang dipilih.') }}</p>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- LEFT PANEL: Country List -->
    <div class="col-lg-3">
        <div class="modern-card p-3 h-100">
            <h6 class="fw-bold mb-3 px-2">{{ __('Select Country') }}</h6>
            <div class="mb-3 px-2">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="searchInput" class="form-control border-start-0 bg-light" placeholder="{{ __('Search country...') }}">
                </div>
            </div>
            
            <div class="country-nav-list px-2">
                @forelse($countries as $c)
                    @php
                        $isActive = $country && $country->id === $c->id;
                        $w = $c->weatherCaches->first();
                    @endphp
                    <a href="{{ route('user.weather', ['country_id' => $c->id]) }}" class="country-item {{ $isActive ? 'active' : '' }}">
                        <div class="d-flex align-items-center gap-3">
                            <span class="fi fi-{{ strtolower($c->iso2_code ?? 'un') }} fs-4 rounded shadow-sm"></span>
                            <div>
                                <h6 class="mb-0 fw-bold {{ $isActive ? 'text-primary' : 'text-dark' }}" style="font-size: 0.85rem;">{{ $c->name }}</h6>
                                <span class="text-muted" style="font-size: 0.7rem;">{{ $w ? $w->condition : 'Unknown' }}</span>
                            </div>
                        </div>
                        @if($w)
                            <div class="text-end">
                                <h6 class="mb-0 fw-bold" style="font-size: 0.9rem;">{{ round($w->temperature) }}°C</h6>
                                @if(str_contains(strtolower($w->condition), 'rain'))
                                    <i class="bi bi-cloud-rain text-primary" style="font-size: 0.7rem;"></i>
                                @elseif(str_contains(strtolower($w->condition), 'cloud'))
                                    <i class="bi bi-cloud text-secondary" style="font-size: 0.7rem;"></i>
                                @elseif(str_contains(strtolower($w->condition), 'thunder') || str_contains(strtolower($w->condition), 'storm'))
                                    <i class="bi bi-cloud-lightning text-danger" style="font-size: 0.7rem;"></i>
                                @else
                                    <i class="bi bi-sun text-warning" style="font-size: 0.7rem;"></i>
                                @endif
                            </div>
                        @endif
                    </a>
                @empty
                    <div class="text-center text-muted py-4 small">{{ __('No countries with weather data.') }}</div>
                @endforelse
            </div>
            
            <div class="mt-3 text-center px-2">
                <button class="btn btn-light btn-sm w-100 text-primary fw-bold">{{ __('View All Countries') }}</button>
            </div>
        </div>
    </div>

    <!-- RIGHT PANEL: Dashboard Content -->
    <div class="col-lg-9">
        @if(!$country)
            <div class="modern-card p-5 text-center d-flex flex-column align-items-center justify-content-center" style="height: 600px;">
                <i class="bi bi-cloud-sun text-muted opacity-25" style="font-size: 5rem;"></i>
                <h4 class="mt-3 fw-bold text-dark">{{ __('No Country Selected') }}</h4>
                <p class="text-muted">{{ __('Please select a country to view weather details.') }}</p>
            </div>
        @else
            @php
                $wea = $country->weatherCaches->first();
                $rawData = $wea ? $wea->raw_data : null;
                $current = $rawData['current'] ?? null;
                $daily = $rawData['daily'] ?? null;
            @endphp
            
            <!-- Map Area -->
            <div class="modern-card p-2 mb-4">
                <div class="px-3 pt-3 pb-2 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">{{ __('World Weather Map') }}</h6>
                    <div class="d-flex gap-3 small fw-bold">
                        <span><i class="bi bi-circle-fill text-warning me-1"></i> {{ __('Clear') }}</span>
                        <span><i class="bi bi-circle-fill text-secondary me-1"></i> {{ __('Cloudy') }}</span>
                        <span><i class="bi bi-circle-fill text-primary me-1"></i> {{ __('Rain') }}</span>
                        <span><i class="bi bi-circle-fill text-danger me-1"></i> {{ __('Storm') }}</span>
                    </div>
                </div>
                <div id="weatherMap"></div>
            </div>

            <!-- Summary Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="modern-card p-4 text-center h-100">
                        <div class="text-muted fw-bold small mb-3">{{ __('Temperature') }}</div>
                        <div class="d-flex justify-content-center align-items-center gap-2 mb-2">
                            <i class="bi bi-thermometer-half text-danger fs-3"></i>
                            <h2 class="fw-bold mb-0">{{ $wea ? round($wea->temperature) : '-' }}°C</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="modern-card p-4 text-center h-100">
                        <div class="text-muted fw-bold small mb-3">{{ __('Humidity') }}</div>
                        <div class="d-flex justify-content-center align-items-center gap-2 mb-2">
                            <i class="bi bi-droplet text-primary fs-3"></i>
                            <h2 class="fw-bold mb-0">{{ $current['relative_humidity_2m'] ?? '-' }} %</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="modern-card p-4 text-center h-100">
                        <div class="text-muted fw-bold small mb-3">{{ __('Wind Speed') }}</div>
                        <div class="d-flex justify-content-center align-items-center gap-2 mb-2">
                            <i class="bi bi-wind text-success fs-3"></i>
                            <h2 class="fw-bold mb-0">{{ $wea ? round($wea->wind_speed) : '-' }} <span class="fs-6">km/h</span></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="modern-card p-4 text-center h-100">
                        <div class="text-muted fw-bold small mb-3">{{ __('Visibility') }}</div>
                        <div class="d-flex justify-content-center align-items-center gap-2 mb-2">
                            <i class="bi bi-eye text-info fs-3"></i>
                            <h2 class="fw-bold mb-0">
                                @if(isset($current['visibility']))
                                    {{ round($current['visibility'] / 1000) }} <span class="fs-6">km</span>
                                @else
                                    -
                                @endif
                            </h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Row: Details, Alerts, Trend -->
            <div class="row g-3">
                <!-- Details -->
                <div class="col-md-4">
                    <div class="modern-card p-4 h-100">
                        <h6 class="fw-bold mb-4">{{ __('Weather Details') }}</h6>
                        <ul class="list-unstyled mb-0">
                            <li class="d-flex justify-content-between border-bottom py-2">
                                <span class="text-muted small"><i class="bi bi-cloud-rain me-2"></i>{{ __('Condition') }}</span>
                                <span class="fw-bold small">{{ $wea ? $wea->condition : '-' }}</span>
                            </li>
                            <li class="d-flex justify-content-between border-bottom py-2">
                                <span class="text-muted small"><i class="bi bi-speedometer2 me-2"></i>{{ __('Pressure') }}</span>
                                <span class="fw-bold small">{{ $current['surface_pressure'] ?? '-' }} hPa</span>
                            </li>
                            <li class="d-flex justify-content-between border-bottom py-2">
                                <span class="text-muted small"><i class="bi bi-clouds me-2"></i>{{ __('Cloud') }}</span>
                                <span class="fw-bold small">{{ $current['cloud_cover'] ?? '-' }}%</span>
                            </li>
                            <li class="d-flex justify-content-between border-bottom py-2">
                                <span class="text-muted small"><i class="bi bi-sunrise me-2"></i>{{ __('Sunrise') }}</span>
                                <span class="fw-bold small">
                                    {{ isset($daily['sunrise'][0]) ? \Carbon\Carbon::parse($daily['sunrise'][0])->format('H:i A') : '-' }}
                                </span>
                            </li>
                            <li class="d-flex justify-content-between border-bottom py-2">
                                <span class="text-muted small"><i class="bi bi-sunset me-2"></i>{{ __('Sunset') }}</span>
                                <span class="fw-bold small">
                                    {{ isset($daily['sunset'][0]) ? \Carbon\Carbon::parse($daily['sunset'][0])->format('H:i A') : '-' }}
                                </span>
                            </li>
                            <li class="d-flex justify-content-between pt-3">
                                <span class="text-muted small"><i class="bi bi-clock me-2"></i>{{ __('Last Updated') }}</span>
                                <span class="fw-bold small">{{ $wea ? $wea->updated_at->format('M d, Y H:i') : '-' }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Alerts -->
                <div class="col-md-4">
                    <div class="modern-card p-4 h-100">
                        <h6 class="fw-bold mb-4">{{ __('Weather Alerts') }}</h6>
                        
                        <div class="alert-card-item bg-light-{{ $weatherAlert['type'] }} border border-{{ $weatherAlert['type'] }} border-opacity-25">
                            <i class="bi bi-exclamation-triangle-fill text-{{ $weatherAlert['type'] }} fs-4 mt-1"></i>
                            <div>
                                <h6 class="fw-bold text-{{ $weatherAlert['type'] }} mb-1" style="font-size: 0.9rem;">{{ __($weatherAlert['title']) }}</h6>
                                <p class="mb-0 text-muted" style="font-size: 0.75rem;">{{ __($weatherAlert['message']) }}</p>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Trend -->
                <div class="col-md-4">
                    <div class="modern-card p-4 h-100">
                        <h6 class="fw-bold mb-4">{{ __('24 Hours Weather Trend') }}</h6>
                        <div style="height: 180px; position: relative;">
                            @if(isset($rawData['hourly']['temperature_2m']) && count($rawData['hourly']['temperature_2m']) > 0)
                                <canvas id="trendChart"></canvas>
                            @else
                                <div class="d-flex align-items-center justify-content-center h-100 text-muted small border rounded bg-light">
                                    {{ __('Data histori belum tersedia') }}
                                </div>
                            @endif
                        </div>
                        <div class="text-center mt-3">
                            <a href="#" class="small text-decoration-none fw-bold">{{ __('View Full Forecast') }} <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-end text-muted small">
                {{ __('Data Source:') }} Open-Meteo API
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Live Search functionality
        const searchInput = document.getElementById('searchInput');
        const countryItems = document.querySelectorAll('.country-item');
        
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                const term = e.target.value.toLowerCase();
                countryItems.forEach(item => {
                    const countryName = item.querySelector('h6').textContent.toLowerCase();
                    if (countryName.includes(term)) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        }

        @if($country && isset($country->latitude) && isset($country->longitude))
        // 2. Leaflet Map
        const mapElement = document.getElementById('weatherMap');
        if (mapElement && typeof L !== 'undefined') {
            const lat = {{ $country->latitude }};
            const lng = {{ $country->longitude }};
            const map = L.map('weatherMap').setView([lat, lng], 5);

            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            // Custom Marker based on condition
            @php
                $cond = strtolower($wea->condition ?? '');
                $markerColor = str_contains($cond, 'rain') ? '#1C55FF' : (str_contains($cond, 'storm') || str_contains($cond, 'thunder') ? '#dc3545' : (str_contains($cond, 'cloud') ? '#6c757d' : '#ffc107'));
            @endphp

            const markerHtml = `
                <div style="background-color: white; border: 2px solid ${'{{ $markerColor }}'}; border-radius: 8px; padding: 5px 10px; display: inline-block; box-shadow: 0 4px 6px rgba(0,0,0,0.1); font-weight: bold; font-family: sans-serif;">
                    ${'{{ round($wea->temperature ?? 0) }}'}°C
                </div>
            `;
            
            const customIcon = L.divIcon({
                html: markerHtml,
                className: '',
                iconSize: [40, 30],
                iconAnchor: [20, 30]
            });

            L.marker([lat, lng], {icon: customIcon}).addTo(map)
             .bindPopup(`<b>${'{{ $country->name }}'}</b><br>${'{{ $wea->condition ?? "Unknown" }}'}`);
             
            // Add a few dummy markers for visual context (just for demo of global map if desired, but user said no dummy data, so let's stick to the true ones)
            @foreach($countries as $other)
                @if($other->id !== $country->id && $other->weatherCaches->isNotEmpty())
                    @php 
                        $ow = $other->weatherCaches->first(); 
                        $ocond = strtolower($ow->condition ?? '');
                        $omColor = str_contains($ocond, 'rain') ? '#1C55FF' : (str_contains($ocond, 'storm') || str_contains($ocond, 'thunder') ? '#dc3545' : (str_contains($ocond, 'cloud') ? '#6c757d' : '#ffc107'));
                    @endphp
                    L.marker([{{ $other->latitude }}, {{ $other->longitude }}], {
                        icon: L.divIcon({
                            html: `<div style="background-color: white; border: 2px solid ${'{{ $omColor }}'}; border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); font-weight: bold; font-size: 10px;">${'{{ round($ow->temperature) }}'}</div>`,
                            className: '',
                            iconSize: [30, 30],
                            iconAnchor: [15, 15]
                        })
                    }).addTo(map);
                @endif
            @endforeach
        }
        @endif

        @if($country && isset($rawData['hourly']['temperature_2m']))
        // 3. Trend Chart
        const trendCtx = document.getElementById('trendChart');
        if (trendCtx) {
            // Get next 24 hours of data
            const times = {!! json_encode(array_slice($rawData['hourly']['time'] ?? [], 0, 24, true)) !!};
            const temps = {!! json_encode(array_slice($rawData['hourly']['temperature_2m'] ?? [], 0, 24, true)) !!};
            
            // Format labels
            const labels = times.map(t => {
                const d = new Date(t);
                return d.getHours().toString().padStart(2, '0') + ':00';
            });

            // Downsample to 6 points for cleaner UI like mockup
            const step = Math.floor(temps.length / 6);
            const displayLabels = [];
            const displayTemps = [];
            for(let i=0; i<temps.length; i+=step) {
                if(displayLabels.length < 6) {
                    displayLabels.push(labels[i]);
                    displayTemps.push(temps[i]);
                }
            }

            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: displayLabels,
                    datasets: [{
                        label: 'Temperature (°C)',
                        data: displayTemps,
                        borderColor: '#1C55FF',
                        backgroundColor: 'rgba(28, 85, 255, 0.1)',
                        borderWidth: 2,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#1C55FF',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false } },
                        y: { display: false, min: Math.min(...displayTemps) - 2, max: Math.max(...displayTemps) + 2 }
                    }
                }
            });
        }
        @endif
    });
</script>
@endpush