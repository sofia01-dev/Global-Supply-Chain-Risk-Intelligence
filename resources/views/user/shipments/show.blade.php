@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .map-container {
        height: 420px;
        border-radius: 12px;
        z-index: 1;
    }
    .risk-gauge-container {
        position: relative;
        width: 180px;
        height: 180px;
        margin: 0 auto;
    }
    .risk-score-text {
        position: absolute;
        top: 55%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 2.5rem;
        font-weight: 800;
        line-height: 1;
    }
    .risk-score-label {
        position: absolute;
        top: 75%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    /* Timeline styles */
    .timeline-horizontal {
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        padding: 2rem 0;
    }
    .timeline-horizontal::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 2px;
        background-color: #e9ecef;
        z-index: 1;
        transform: translateY(-50%);
    }
    .timeline-step {
        position: relative;
        z-index: 2;
        text-align: center;
        background: #fff;
        padding: 0 10px;
    }
    .timeline-marker {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background-color: #e9ecef;
        margin: 0 auto 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 4px solid #fff;
    }
    .timeline-step.completed .timeline-marker {
        background-color: #198754;
    }
    .timeline-step.current .timeline-marker {
        background-color: #0d6efd;
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.2);
    }
    /* Map Marker Blinking */
    .blinking-marker div {
        animation: blinker 1.5s ease-in-out infinite;
    }
    @keyframes blinker {
        0% { box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(25, 135, 84, 0); }
        100% { box-shadow: 0 0 0 0 rgba(25, 135, 84, 0); }
    }
</style>
@endpush

@section('content')
<div class="d-flex align-items-center mb-4 gap-3">
    <a href="{{ route('user.shipments.index') }}" class="btn btn-light rounded-circle shadow-sm" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
        <i class="bi bi-arrow-left"></i>
    </a>
    <span class="text-primary fw-medium">{{ __('Shipment:') }} {{ $shipment->shipment_code }}</span>
</div>

<!-- Header Card -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">{{ __('Shipment Detail') }}</h4>
            <div class="d-flex gap-2">
                <a href="{{ route('user.shipments.edit', $shipment->id) }}" class="btn btn-sm rounded-pill px-3 fw-bold" style="background-color: #CCD4DE; color: var(--primary-navy); border: none;"><i class="bi bi-pencil me-1"></i> Edit Shipment</a>
            </div>
        </div>
        
        <div class="row text-center g-3">
            <div class="col border-end">
                <span class="text-muted small d-block">{{ __('Shipment Code') }}</span>
                <span class="fw-bold text-dark">{{ $shipment->shipment_code }}</span>
            </div>
            <div class="col border-end">
                <span class="text-muted small d-block">{{ __('Shipment Name') }}</span>
                <span class="fw-bold text-dark">{{ $shipment->shipment_name ?? '-' }}</span>
            </div>
            <div class="col border-end">
                <span class="text-muted small d-block">{{ __('Goods') }}</span>
                <span class="fw-bold text-dark">{{ $shipment->goods ?? '-' }}</span>
            </div>
            <div class="col border-end">
                <span class="text-muted small d-block">{{ __('Status') }}</span>
                @php
                    $statusColor = $monitorObj['current_status'] === 'Delayed' ? 'warning' : ($monitorObj['current_status'] === 'At Risk' ? 'danger' : 'success');
                @endphp
                <span class="fw-bold text-{{ $statusColor }}">{{ __($monitorObj['current_status']) }}</span>
            </div>
            <div class="col border-end">
                <span class="text-muted small d-block">{{ __('Current Stage') }}</span>
                <span class="fw-bold text-dark">{{ $monitorObj['current_location'] }}</span>
            </div>
            <div class="col border-end">
                <span class="text-muted small d-block">{{ __('Progress') }}</span>
                <div class="d-flex align-items-center justify-content-center gap-2">
                    <div class="progress flex-grow-1" style="height: 6px; max-width: 60px;">
                        <div class="progress-bar bg-primary" style="width: {{ $monitorObj['progress_percentage'] }}%"></div>
                    </div>
                    <span class="fw-bold text-dark">{{ $monitorObj['progress_percentage'] }}%</span>
                </div>
            </div>
            <div class="col border-end">
                <span class="text-muted small d-block">{{ __('ETA') }}</span>
                <span class="fw-bold text-dark">{{ $monitorObj['estimated_delay'] }} ({{ $shipment->estimated_arrival ? $shipment->estimated_arrival->format('d M Y') : 'N/A' }})</span>
            </div>
            <div class="col">
                <span class="text-muted small d-block">{{ __('Overall Risk') }}</span>
                @php
                    $levelColor = $monitorObj['risk_level'] === 'Critical' ? 'danger' : ($monitorObj['risk_level'] === 'High' ? 'warning' : ($monitorObj['risk_level'] === 'Medium' ? 'info' : 'success'));
                @endphp
                <span class="fw-bold text-{{ $levelColor }}">{{ __($monitorObj['risk_level']) }} ({{ $monitorObj['risk_score'] }}/100)</span>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Map Section -->
    <div class="col-xl-6 col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3">{{ __('Route & Map') }}</h6>
                <div id="shipmentMap" class="map-container w-100" style="height: 340px; border-radius: 8px;"></div>
            </div>
        </div>
    </div>
    
    <!-- Risk Analysis Section -->
    <div class="col-xl-3 col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3">{{ __('Risk Analysis') }}</h6>
                <div class="row g-2">
                    <!-- 4 Risk Cards -->
                    <div class="col-6">
                        <div class="card border border-light bg-white shadow-sm rounded-3 h-100 p-3" id="card-weather">
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center text-primary me-2" style="width: 32px; height: 32px;"><i class="bi bi-cloud-rain"></i></div>
                                <span class="fw-bold text-dark" style="font-size: 0.85rem;">{{ __('Weather Risk') }}</span>
                            </div>
                            <div class="small text-dark mb-1" id="val-weather-rain" style="font-size: 0.8rem;">{{ $monitorObj['weather']['rain'] }}</div>
                            <div class="small text-muted" id="val-weather-temp" style="font-size: 0.75rem;">{{ $monitorObj['weather']['temp'] }} • {{ $monitorObj['weather']['wind'] }}</div>
                            <div class="mt-2"><span class="badge bg-light-{{ $monitorObj['weather']['level'] === 'High' ? 'danger' : 'warning' }} text-{{ $monitorObj['weather']['level'] === 'High' ? 'danger' : 'warning' }} border rounded-1 px-2 py-1" id="badge-weather">{{ $monitorObj['weather']['level'] }}</span></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card border border-light bg-white shadow-sm rounded-3 h-100 p-3" id="card-currency">
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center text-primary me-2" style="width: 32px; height: 32px;"><i class="bi bi-currency-dollar"></i></div>
                                <span class="fw-bold text-dark" style="font-size: 0.85rem;">{{ __('Currency Risk') }}</span>
                            </div>
                            <div class="small text-dark mb-1" style="font-size: 0.8rem;">USD/IDR</div>
                            <div class="small text-muted" id="val-currency-rate" style="font-size: 0.75rem;">{{ $monitorObj['currency']['rate'] }}</div>
                            <div class="mt-2"><span class="badge bg-light-warning text-warning border rounded-1 px-2 py-1" id="badge-currency">{{ $monitorObj['currency']['level'] }}</span></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card border border-light bg-white shadow-sm rounded-3 h-100 p-3" id="card-news">
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center text-primary me-2" style="width: 32px; height: 32px;"><i class="bi bi-newspaper"></i></div>
                                <span class="fw-bold text-dark" style="font-size: 0.85rem;">{{ __('News Risk') }}</span>
                            </div>
                            <div class="small text-dark mb-1" id="val-news-neg" style="font-size: 0.8rem;">{{ $monitorObj['news']['negative'] }} Negative News</div>
                            <div class="small text-muted" style="font-size: 0.75rem;">{{ __('Logistics Sector') }}</div>
                            <div class="mt-2"><span class="badge bg-light-danger text-danger border rounded-1 px-2 py-1" id="badge-news">{{ $monitorObj['news']['level'] }}</span></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card border border-light bg-white shadow-sm rounded-3 h-100 p-3" id="card-economic">
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center text-primary me-2" style="width: 32px; height: 32px;"><i class="bi bi-graph-up"></i></div>
                                <span class="fw-bold text-dark" style="font-size: 0.85rem;">{{ __('Economic Risk') }}</span>
                            </div>
                            <div class="small text-dark mb-1" id="val-economic-inf" style="font-size: 0.8rem;">{{ __('Inflation (ID)') }}</div>
                            <div class="small text-muted" id="val-economic-val" style="font-size: 0.75rem;">{{ $monitorObj['economic']['inflation'] }}</div>
                            <div class="mt-2"><span class="badge bg-light-warning text-warning border rounded-1 px-2 py-1" id="badge-economic">{{ $monitorObj['economic']['level'] }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gauge Engine Section -->
    <div class="col-xl-3 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4 text-center d-flex flex-column justify-content-between">
                <div>
                    <h6 class="fw-bold mb-3 text-start">{{ __('Risk Intelligence Engine') }}</h6>
                    <div class="risk-gauge-container mx-auto" style="height: 140px; width: 140px;">
                        <canvas id="riskGauge"></canvas>
                        <div class="risk-score-text text-dark" id="gaugeScoreText" style="font-size: 2rem;">{{ $monitorObj['risk_score'] }}</div>
                        <div class="risk-score-label text-{{ $levelColor }} fw-bold" id="gaugeLevelText" style="font-size: 0.65rem;">{{ __(strtoupper($monitorObj['risk_level']) . ' RISK') }}</div>
                    </div>
                </div>
                
                <div class="text-start mt-2">
                    <div class="d-flex justify-content-between small mb-1" style="font-size: 0.75rem;"><span class="text-muted">{{ __('Weather Impact') }}</span><span class="fw-bold">30%</span></div>
                    <div class="progress mb-2 rounded-0" style="height: 3px;"><div class="progress-bar bg-danger" style="width: 30%"></div></div>
                    
                    <div class="d-flex justify-content-between small mb-1" style="font-size: 0.75rem;"><span class="text-muted">{{ __('News Impact') }}</span><span class="fw-bold">40%</span></div>
                    <div class="progress mb-2 rounded-0" style="height: 3px;"><div class="progress-bar bg-danger" style="width: 40%"></div></div>
                    
                    <div class="d-flex justify-content-between small mb-1" style="font-size: 0.75rem;"><span class="text-muted">{{ __('Inflation Impact') }}</span><span class="fw-bold">20%</span></div>
                    <div class="progress mb-2 rounded-0" style="height: 3px;"><div class="progress-bar bg-warning" style="width: 20%"></div></div>
                    
                    <div class="d-flex justify-content-between small mb-0" style="font-size: 0.75rem;"><span class="text-muted">{{ __('Currency Impact') }}</span><span class="fw-bold">10%</span></div>
                    <div class="progress mb-0 rounded-0" style="height: 3px;"><div class="progress-bar bg-warning" style="width: 10%"></div></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Timeline Section -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-3">
                <h6 class="fw-bold mb-3 ms-2">{{ __('Shipment Timeline') }}</h6>
                <div class="timeline-horizontal px-3 mt-2">
                    @php
                        $histories = $shipment->histories->sortBy('timestamp')->values();
                        $steps = [
                            ['label' => 'Shipment Created', 'date' => $shipment->created_at->format('d M Y'), 'status' => 'completed'],
                        ];
                        
                        foreach($histories as $h) {
                            $steps[] = [
                                'label' => $h->status . ' ' . $h->location_desc,
                                'date' => \Carbon\Carbon::parse($h->timestamp)->format('d M Y'),
                                'status' => 'completed'
                            ];
                        }
                        
                        if($monitorObj['current_status'] !== 'Delivered' && $monitorObj['current_status'] !== 'Completed') {
                            $steps[count($steps)-1]['status'] = 'current';
                            $steps[count($steps)-1]['is_current'] = true;
                            
                            $steps[] = [
                                'label' => 'Estimated Arrival',
                                'date' => $shipment->estimated_arrival ? $shipment->estimated_arrival->format('d M Y') : 'N/A',
                                'status' => 'pending'
                            ];
                        }
                    @endphp
                    
                    @foreach($steps as $step)
                        <div class="timeline-step {{ $step['status'] }}">
                            <div class="timeline-marker shadow-sm">
                                @if($step['status'] == 'completed') <i class="bi bi-check text-white small"></i> @endif
                                @if($step['status'] == 'current') <i class="bi bi-flag-fill text-white small" style="font-size:0.6rem;"></i> @endif
                            </div>
                            <div class="small fw-bold text-dark mt-2" style="font-size: 0.8rem">{{ $step['label'] }}</div>
                            <div class="small text-muted" style="font-size: 0.7rem">{{ $step['date'] }}</div>
                            @if(isset($step['is_current'])) <div class="badge bg-primary bg-opacity-10 text-primary mt-1 border border-primary" style="font-size:0.65rem">{{ __('Current Stage') }}</div> @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- AI Recommendation Section -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-3">
                <div class="d-flex align-items-center mb-2 ms-2">
                    <i class="bi bi-lightbulb text-primary fs-5 me-2"></i>
                    <h6 class="fw-bold mb-0">{{ __('AI Recommendation') }}</h6>
                </div>
                
                <div class="row align-items-center ms-2">
                    <div class="col-md-5">
                        <p class="text-dark lh-sm mb-0" style="font-size: 0.75rem" id="ai-rec-text">{{ $monitorObj['recommendation'] }}</p>
                    </div>
                    <div class="col-md-3 border-start text-center">
                        <div class="text-muted fw-bold" style="font-size: 0.65rem;">{{ __('Estimated Delay') }}</div>
                        <div class="fw-bold text-danger fs-6 mt-1" id="ai-delay">{{ $monitorObj['estimated_delay'] }}</div>
                    </div>
                    <div class="col-md-4 border-start ps-3">
                        <div class="text-muted fw-bold mb-1" style="font-size: 0.65rem;">{{ __('Recommendation') }}</div>
                        <ul class="list-unstyled mb-0" id="ai-bullets" style="font-size: 0.7rem;">
                            @foreach($monitorObj['recommendation_bullets'] as $bullet)
                                <li class="mb-1 d-flex align-items-start"><i class="bi bi-check text-success me-1"></i> <span class="lh-sm">{{ $bullet }}</span></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const mapData = @json($mapData);
    const currentLocation = "{{ $monitorObj['current_location'] }}";
    
    // Init Map
    const map = L.map('shipmentMap');
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap contributors &copy; CARTO'
    }).addTo(map);

    const latlngs = [];
    const bounds = L.latLngBounds();
    
    // Custom Icons
    const createIcon = (color, isCurrent) => {
        let className = 'custom-div-icon';
        if(isCurrent) className += ' blinking-marker';
        
        return L.divIcon({
            className: className,
            html: `<div style="background-color: ${color}; width: 14px; height: 14px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 5px rgba(0,0,0,0.3);"></div>`,
            iconSize: [14, 14],
            iconAnchor: [7, 7]
        });
    };

    mapData.forEach(point => {
        const pLatlng = [parseFloat(point.lat), parseFloat(point.lng)];
        latlngs.push(pLatlng);
        bounds.extend(pLatlng);
        
        let color = '#198754'; // Green Origin
        if(point.type === 'Destination') color = '#dc3545'; // Red Dest
        if(point.type === 'Transit') color = '#0dcaf0'; // Cyan Transit
        
        const isCurrent = (point.name === currentLocation);
        
        L.marker(pLatlng, {icon: createIcon(color, isCurrent)})
         .bindTooltip(`<b>${point.name}</b><br>${point.type}`, {direction: 'top', offset: [0,-10]})
         .addTo(map);
    });

    // Function to calculate a quadratic bezier curve for maritime routes
    function getBezierCurve(start, end, tension = 0.2) {
        const points = [];
        const midLat = (start[0] + end[0]) / 2;
        const midLng = (start[1] + end[1]) / 2;
        
        // Offset the midpoint to create a curve
        const dLng = end[1] - start[1];
        const dLat = end[0] - start[0];
        
        // Control point
        const ctrlLat = midLat - (dLng * tension);
        const ctrlLng = midLng + (dLat * tension);
        
        for (let i = 0; i <= 100; i++) {
            const t = i / 100;
            const lat = Math.pow(1 - t, 2) * start[0] + 2 * (1 - t) * t * ctrlLat + Math.pow(t, 2) * end[0];
            const lng = Math.pow(1 - t, 2) * start[1] + 2 * (1 - t) * t * ctrlLng + Math.pow(t, 2) * end[1];
            points.push([lat, lng]);
        }
        return points;
    }

    if (latlngs.length > 1) {
        let allCurvePoints = [];
        for (let i = 0; i < latlngs.length - 1; i++) {
            // Use negative tension (-0.25) to invert the curve towards the ocean (East)
            const curve = getBezierCurve(latlngs[i], latlngs[i+1], -0.25);
            allCurvePoints = allCurvePoints.concat(curve);
        }
        
        // Add animated CSS to page
        const style = document.createElement('style');
        style.innerHTML = `
            .ship-route-path {
                animation: dash 5s linear infinite;
                filter: drop-shadow(0px 2px 3px rgba(13, 110, 253, 0.4));
            }
            @keyframes dash {
                to { stroke-dashoffset: -100; }
            }
        `;
        document.head.appendChild(style);

        L.polyline(allCurvePoints, {
            color: '#0d6efd', // Deep blue for bright maps
            weight: 4,
            dashArray: '10, 15',
            className: 'ship-route-path',
            opacity: 0.8
        }).addTo(map);
    }
    
    if (latlngs.length > 0) {
        map.fitBounds(bounds, {padding: [50, 50]});
    }

    // Init Gauge Chart
    const ctx = document.getElementById('riskGauge').getContext('2d');
    let riskGauge = new Chart(ctx, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [{{ $monitorObj['risk_score'] }}, {{ 100 - $monitorObj['risk_score'] }}],
                backgroundColor: [
                    '{{ $monitorObj['risk_level'] === 'Critical' ? '#dc3545' : ($monitorObj['risk_level'] === 'High' ? '#fd7e14' : ($monitorObj['risk_level'] === 'Medium' ? '#ffc107' : '#198754')) }}',
                    '#e9ecef'
                ],
                borderWidth: 0,
                cutout: '80%',
                circumference: 270,
                rotation: 225,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { tooltip: { enabled: false }, legend: { display: false } }
        }
    });

    // Auto Refresh Logic (Every 60 Seconds)
    setInterval(() => {
        fetch(window.location.href, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            const mon = data.monitorObj;
            
            // Update Gauge
            riskGauge.data.datasets[0].data = [mon.risk_score, 100 - mon.risk_score];
            let color = '#198754';
            if(mon.risk_level === 'Critical') color = '#dc3545';
            else if(mon.risk_level === 'High') color = '#fd7e14';
            else if(mon.risk_level === 'Medium') color = '#ffc107';
            riskGauge.data.datasets[0].backgroundColor[0] = color;
            riskGauge.update();
            
            // Update Texts
            document.getElementById('gaugeScoreText').innerText = mon.risk_score;
            document.getElementById('gaugeScoreText').className = `risk-score-text text-${mon.risk_level === 'Critical' ? 'danger' : 'dark'}`;
            const transMap = {
                'Critical': '{{ __('Critical') }}',
                'High': '{{ __('High') }}',
                'Medium': '{{ __('Medium') }}',
                'Low': '{{ __('Low') }}',
                'RISK': '{{ __('RISK') }}'
            };
            document.getElementById('gaugeLevelText').innerText = `${transMap[mon.risk_level].toUpperCase()} ${transMap['RISK']}`;
            
            // Update Risk Cards (Weather)
            document.getElementById('val-weather-rain').innerText = mon.weather.rain;
            document.getElementById('val-weather-temp').innerText = `${mon.weather.temp} • ${mon.weather.wind}`;
            document.getElementById('badge-weather').innerText = mon.weather.level;
            
            // Update AI Recommendation
            document.getElementById('ai-rec-text').innerText = mon.recommendation;
            document.getElementById('ai-delay').innerText = mon.estimated_delay;
            
            const ul = document.getElementById('ai-bullets');
            ul.innerHTML = '';
            mon.recommendation_bullets.forEach(b => {
                ul.innerHTML += `<li class="mb-1"><i class="bi bi-check text-success me-1"></i> ${b}</li>`;
            });
            
        });
    }, 60000);
</script>
@endpush
@endsection