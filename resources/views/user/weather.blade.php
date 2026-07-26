@extends('layouts.app')

@push('styles')
<!-- Flag Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.0.0/css/flag-icons.min.css"/>
<!-- Leaflet CSS is already in app.blade.php, but just in case -->
<style>
    .country-nav-list {
        flex-grow: 1;
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

@section('page_header')
<div class="d-none d-sm-block">
    <h5 class="m-0 fw-semibold text-dark">{{ __('Global Weather Monitoring') }}</h5>
    <div class="text-muted" style="font-size: 0.75rem;">{{ __('Monitor global weather conditions in real-time based on the selected country.') }}</div>
</div>
@endsection

@section('content')

<div class="row g-4">
    <!-- LEFT PANEL: Country List -->
    <div class="col-lg-3 position-relative">
        <div class="position-absolute" style="top: 0; bottom: 0; left: 12px; right: 12px;">
            <div class="modern-card p-3 h-100 d-flex flex-column">
                <h6 class="fw-bold mb-3 px-2 flex-shrink-0">{{ __('Select Country') }}</h6>
                <div class="mb-3 px-2 flex-shrink-0">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="searchInput" class="form-control border-start-0 bg-light" placeholder="{{ __('Search country...') }}">
                    </div>
                </div>
                
                <div class="country-nav-list px-2 flex-grow-1" style="overflow-y: auto;">
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
                
                <div class="mt-auto text-center px-2 flex-shrink-0 pt-3">
                    <button class="btn btn-light btn-sm w-100 text-primary fw-bold">{{ __('View All Countries') }}</button>
                </div>
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
                        <div class="text-muted fw-bold small mb-3">{{ __('Weather Condition') }}</div>
                        <div class="d-flex justify-content-center align-items-center gap-2 mb-2">
                            @php
                                $condText = $wea ? $wea->condition : 'Unknown';
                                $lowerCond = strtolower($condText);
                                $condIcon = str_contains($lowerCond, 'rain') ? 'bi-cloud-rain-fill text-primary' : (str_contains($lowerCond, 'storm') || str_contains($lowerCond, 'thunder') ? 'bi-cloud-lightning-fill text-danger' : (str_contains($lowerCond, 'cloud') ? 'bi-cloud-fill text-secondary' : 'bi-sun-fill text-warning'));
                            @endphp
                            <i class="bi {{ $condIcon }} fs-3"></i>
                            <h3 class="fw-bold mb-0" style="font-size: 1.25rem;">{{ __($condText) }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Row: Details, Alerts, Trend -->
            <div class="row g-3">
                <!-- Details -->
                <div class="col">
                    <div class="modern-card p-4 h-100">
                        <h6 class="fw-bold mb-4">{{ __('Weather Details') }}</h6>
                        <ul class="list-unstyled mb-0">
                            <li class="d-flex justify-content-between align-items-center py-2 px-2 mb-2 rounded hover-bg-light" style="transition: all 0.2s;">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="d-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary" style="width: 32px; height: 32px;">
                                        <i class="bi bi-cloud-rain"></i>
                                    </div>
                                    <span class="text-muted small fw-semibold">{{ __('Condition') }}</span>
                                </div>
                                <span class="fw-bold text-dark">{{ $wea ? $wea->condition : '-' }}</span>
                            </li>
                            <li class="d-flex justify-content-between align-items-center py-2 px-2 mb-2 rounded hover-bg-light" style="transition: all 0.2s;">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="d-flex align-items-center justify-content-center rounded-circle bg-info bg-opacity-10 text-info" style="width: 32px; height: 32px;">
                                        <i class="bi bi-speedometer2"></i>
                                    </div>
                                    <span class="text-muted small fw-semibold">{{ __('Pressure') }}</span>
                                </div>
                                <span class="fw-bold text-dark">{{ $current['surface_pressure'] ?? '-' }} <span class="small text-muted fw-normal">hPa</span></span>
                            </li>
                            <li class="d-flex justify-content-between align-items-center py-2 px-2 mb-2 rounded hover-bg-light" style="transition: all 0.2s;">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="d-flex align-items-center justify-content-center rounded-circle bg-secondary bg-opacity-10 text-secondary" style="width: 32px; height: 32px;">
                                        <i class="bi bi-clouds"></i>
                                    </div>
                                    <span class="text-muted small fw-semibold">{{ __('Cloud') }}</span>
                                </div>
                                <span class="fw-bold text-dark">{{ $current['cloud_cover'] ?? '-' }}<span class="small text-muted fw-normal">%</span></span>
                            </li>
                            <li class="d-flex justify-content-between align-items-center py-2 px-2 mb-2 rounded hover-bg-light" style="transition: all 0.2s;">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="d-flex align-items-center justify-content-center rounded-circle bg-warning bg-opacity-10 text-warning" style="width: 32px; height: 32px;">
                                        <i class="bi bi-sunrise"></i>
                                    </div>
                                    <span class="text-muted small fw-semibold">{{ __('Sunrise') }}</span>
                                </div>
                                <span class="fw-bold text-dark">
                                    {{ isset($daily['sunrise'][0]) ? \Carbon\Carbon::parse($daily['sunrise'][0])->format('H:i A') : '-' }}
                                </span>
                            </li>
                            <li class="d-flex justify-content-between align-items-center py-2 px-2 mb-2 rounded hover-bg-light" style="transition: all 0.2s;">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="d-flex align-items-center justify-content-center rounded-circle bg-danger bg-opacity-10 text-danger" style="width: 32px; height: 32px;">
                                        <i class="bi bi-sunset"></i>
                                    </div>
                                    <span class="text-muted small fw-semibold">{{ __('Sunset') }}</span>
                                </div>
                                <span class="fw-bold text-dark">
                                    {{ isset($daily['sunset'][0]) ? \Carbon\Carbon::parse($daily['sunset'][0])->format('H:i A') : '-' }}
                                </span>
                            </li>
                        </ul>
                        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top px-2">
                            <span class="text-muted" style="font-size: 0.7rem;"><i class="bi bi-clock me-1"></i>{{ __('Last Updated') }}</span>
                            <span class="fw-bold text-dark" style="font-size: 0.75rem;">{{ $wea ? $wea->updated_at->format('M d, Y H:i') : '-' }}</span>
                        </div>
                        <style>
                            .hover-bg-light:hover { background-color: #f8f9fa; transform: translateX(5px); }
                        </style>
                    </div>
                </div>

                <!-- Alerts -->
                <div class="col">
                    <div class="modern-card p-4 h-100 d-flex flex-column">
                        <h6 class="fw-bold mb-4 flex-shrink-0">{{ __('Weather Alerts') }}</h6>
                        
                        <div class="d-flex flex-column flex-grow-1 gap-2 mt-2">
                            @foreach($weatherAlerts as $alert)
                            <div class="d-flex justify-content-between align-items-center bg-{{ $alert['type'] }} bg-opacity-10 rounded-3 px-3 py-2 border-0">
                                <div class="d-flex gap-3 align-items-center">
                                    <i class="bi {{ $alert['icon'] }} text-{{ $alert['type'] }} fs-4"></i>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-0" style="font-size: 0.95rem;">{{ $alert['title'] }}</h6>
                                        <p class="mb-0 text-muted" style="font-size: 0.75rem;">{{ $alert['message'] }}</p>
                                    </div>
                                </div>
                                <span class="badge bg-{{ $alert['type'] }} rounded-pill px-3">{{ $alert['badge'] }}</span>
                            </div>
                            @endforeach
                        </div>

                    </div>
                </div>

                <!-- Trend -->
                <div class="col-md-5">
                    <div class="modern-card p-4 h-100 d-flex flex-column">
                        <h6 class="fw-bold mb-4 flex-shrink-0">{{ __('24 Hours Weather Trend') }}</h6>
                        <div class="flex-grow-1 position-relative" style="min-height: 180px;">
                            @if(isset($rawData['hourly']['temperature_2m']) && count($rawData['hourly']['temperature_2m']) > 0)
                                <canvas id="trendChart"></canvas>
                            @else
                                <div class="d-flex align-items-center justify-content-center h-100 text-muted small border rounded bg-light">
                                    {{ __('Data histori belum tersedia') }}
                                </div>
                            @endif
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
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>
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
            const map = L.map('weatherMap').setView([20, 0], 2);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            setTimeout(() => {
                map.flyTo([lat, lng], 5, {
                    duration: 2.0,
                    easeLinearity: 0.25
                });
            }, 400);

            // Custom Marker based on condition
            @php
                $cond = strtolower($wea->condition ?? '');
                $markerColor = str_contains($cond, 'rain') ? '#1C55FF' : (str_contains($cond, 'storm') || str_contains($cond, 'thunder') ? '#dc3545' : (str_contains($cond, 'cloud') ? '#6c757d' : '#ffc107'));
                $iconClass = str_contains($cond, 'rain') ? 'bi-cloud-rain-fill' : (str_contains($cond, 'storm') || str_contains($cond, 'thunder') ? 'bi-cloud-lightning-fill' : (str_contains($cond, 'cloud') ? 'bi-cloud-fill' : 'bi-sun-fill'));
            @endphp

            const markerHtml = `
                <div style="background-color: ${'{{ $markerColor }}'}; color: white; border-radius: 8px; padding: 5px 10px; display: flex; align-items: center; gap: 5px; box-shadow: 0 4px 6px rgba(0,0,0,0.2); font-weight: bold; font-family: sans-serif;">
                    <i class="bi ${'{{ $iconClass }}'}"></i> ${'{{ round($wea->temperature ?? 0) }}'}°C
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
                        $oIconClass = str_contains($ocond, 'rain') ? 'bi-cloud-rain-fill' : (str_contains($ocond, 'storm') || str_contains($ocond, 'thunder') ? 'bi-cloud-lightning-fill' : (str_contains($ocond, 'cloud') ? 'bi-cloud-fill' : 'bi-sun-fill'));
                    @endphp
                    L.marker([{{ $other->latitude }}, {{ $other->longitude }}], {
                        icon: L.divIcon({
                            html: `<div title="${'{{ round($ow->temperature) }}'}°C" style="background-color: ${'{{ $omColor }}'}; color: white; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.2); font-size: 14px;"><i class="bi ${'{{ $oIconClass }}'}"></i></div>`,
                            className: '',
                            iconSize: [32, 32],
                            iconAnchor: [16, 16]
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

            if (typeof ChartDataLabels !== 'undefined') {
                Chart.register(ChartDataLabels);
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
                        pointBackgroundColor: '#1C55FF',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            top: 25,
                            right: 20,
                            left: 10,
                            bottom: 10
                        }
                    },
                    plugins: { 
                        legend: { 
                            display: true,
                            position: 'top',
                            align: 'start',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 8,
                                boxHeight: 8,
                                font: {
                                    family: "'Inter', sans-serif",
                                    size: 13,
                                    weight: '600'
                                },
                                color: '#4b5563'
                            }
                        },
                        datalabels: {
                            color: '#1f2937',
                            anchor: 'end',
                            align: 'top',
                            offset: 4,
                            font: {
                                weight: 'bold',
                                size: 12
                            },
                            formatter: function(value) {
                                return value + '°C';
                            }
                        }
                    },
                    scales: {
                        x: { 
                            grid: { display: false },
                            ticks: {
                                color: '#6b7280',
                                font: { size: 11 }
                            }
                        },
                        y: { 
                            display: true, 
                            position: 'left',
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                drawBorder: false,
                            },
                            ticks: {
                                color: '#6b7280',
                                font: { size: 11 },
                                callback: function(value) {
                                    return value + '°C';
                                },
                                stepSize: 4
                            },
                            min: Math.min(...displayTemps) - 4, 
                            max: Math.max(...displayTemps) + 4 
                        }
                    }
                }
            });
        }
        @endif
    });
</script>
@endpush