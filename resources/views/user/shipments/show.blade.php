@extends('layouts.app')

@push('styles')
<style>
    .timeline-vertical {
        position: relative;
        padding-left: 2rem;
        margin-bottom: 0;
    }
    .timeline-vertical::before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0.75rem;
        width: 2px;
        background-color: #e9ecef;
    }
    .timeline-item {
        position: relative;
        padding-bottom: 1.5rem;
    }
    .timeline-item:last-child {
        padding-bottom: 0;
    }
    .timeline-marker {
        position: absolute;
        left: -2.05rem;
        top: 0.25rem;
        width: 1rem;
        height: 1rem;
        border-radius: 50%;
        background-color: #1C55FF;
        border: 2px solid #fff;
        box-shadow: 0 0 0 2px rgba(28,85,255,0.2);
    }
    .map-container {
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid rgba(0,0,0,0.05);
        box-shadow: inset 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .risk-gauge-container {
        position: relative;
        width: 150px;
        height: 150px;
        margin: 0 auto;
    }
    
    .risk-score-text {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 2rem;
        font-weight: 800;
        line-height: 1;
    }
    .risk-score-label {
        position: absolute;
        top: 70%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
</style>
@endpush

@section('content')
<!-- Header Section -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 gap-3">
    <div>
        <div class="d-flex align-items-center gap-3 mb-2">
            <a href="{{ route('user.shipments.index') }}" class="btn btn-light rounded-circle shadow-sm" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                <i class="bi bi-arrow-left"></i>
            </a>
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-medium">
                {{ __('Shipment') }}: {{ $shipment->shipment_code }}
            </span>
        </div>
        <h3 class="fw-bold mb-0 text-dark">{{ __('Live Monitoring Dashboard') }}</h3>
    </div>
    
    <div class="d-flex align-items-center gap-3 bg-white p-3 rounded-4 shadow-sm">
        <div class="text-end">
            <p class="text-muted small mb-0">{{ __('Current Status') }}</p>
            @php
                $statusColor = $monitoringData['current_status'] === 'Delayed' ? 'warning' : ($monitoringData['current_status'] === 'At Risk' ? 'danger' : 'success');
            @endphp
            <h5 class="mb-0 fw-bold text-{{ $statusColor }}"><i class="bi bi-circle-fill me-2 small"></i>{{ __($monitoringData['current_status']) }}</h5>
        </div>
        <div class="vr mx-2"></div>
        <div class="text-start">
            <p class="text-muted small mb-0">{{ __('Progress') }}</p>
            <h5 class="mb-0 fw-bold">{{ $monitoringData['progress_percentage'] }}%</h5>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="row g-4 mb-4">
    <!-- Left Column: Map & Route Info -->
    <div class="col-lg-8">
        <!-- Map Card -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden h-100">
            <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="bi bi-map text-primary me-2"></i>{{ __('Interactive Route Map') }}</h6>
                <span class="badge bg-light text-muted border"><i class="bi bi-geo-alt-fill me-1"></i>{{ __($monitoringData['last_known_location']) }}</span>
            </div>
            <div class="card-body p-0">
                @if(empty($mapData))
                    <div class="d-flex justify-content-center align-items-center bg-light" style="height: 450px;">
                        <div class="text-center text-muted">
                            <i class="bi bi-compass fs-1 opacity-50 mb-2 d-block"></i>
                            <span>{{ __('No route data available') }}</span>
                        </div>
                    </div>
                @else
                    <div class="map-container rounded-0" id="routeMap" style="height: 450px;"></div>
                @endif
            </div>
            
            <!-- Route Details Bottom Bar -->
            <div class="bg-light p-4 border-top">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-4">
                    <!-- Origin -->
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-geo-alt fs-5"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-0 text-uppercase fw-semibold">{{ __('Origin') }}</p>
                            <h6 class="fw-bold mb-0">{{ $shipment->originPort->name ?? '-' }}</h6>
                            <span class="text-muted small">{{ __($shipment->originPort->country->name ?? '-') }}</span>
                        </div>
                    </div>
                    
                    <!-- Arrow/Line -->
                    <div class="flex-grow-1 px-4 d-none d-md-block text-center position-relative">
                        <div style="height: 2px; background: repeating-linear-gradient(90deg, #ccc, #ccc 6px, transparent 6px, transparent 12px); width: 100%;"></div>
                        <i class="bi bi-box-seam-fill text-muted position-absolute top-50 start-50 translate-middle bg-light px-2"></i>
                    </div>

                    <!-- Destination -->
                    <div class="d-flex align-items-center gap-3 text-md-end flex-row-reverse flex-md-row">
                        <div class="text-start text-md-end">
                            <p class="text-muted small mb-0 text-uppercase fw-semibold">{{ __('Destination') }}</p>
                            <h6 class="fw-bold mb-0">{{ $shipment->destinationPort->name ?? '-' }}</h6>
                            <span class="text-muted small">{{ __($shipment->destinationPort->country->name ?? '-') }}</span>
                        </div>
                        <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-flag fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Risk & Timeline -->
    <div class="col-lg-4 d-flex flex-column gap-4">
        
        <!-- Risk Intelligence Card -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                <h6 class="fw-bold mb-0"><i class="bi bi-shield-exclamation text-danger me-2"></i>{{ __('Risk Intelligence Engine') }}</h6>
            </div>
            <div class="card-body p-4 text-center">
                @php
                    $riskLevel = $monitorObj['risk_level'];
                    $riskColor = $riskLevel === 'Critical' ? '#dc3545' : ($riskLevel === 'High' ? '#fd7e14' : ($riskLevel === 'Medium' ? '#ffc107' : '#198754'));
                    $riskTextClass = $riskLevel === 'Critical' ? 'text-danger' : ($riskLevel === 'High' ? 'text-orange' : ($riskLevel === 'Medium' ? 'text-warning' : 'text-success'));
                @endphp
                
                <!-- Circular CSS Gauge -->
                <div class="risk-gauge-container mb-4">
                    <svg viewBox="0 0 36 36" class="circular-chart" style="width:100%; height:100%;">
                        <!-- Background Circle -->
                        <path class="circle-bg"
                            d="M18 2.0845
                            a 15.9155 15.9155 0 0 1 0 31.831
                            a 15.9155 15.9155 0 0 1 0 -31.831"
                            fill="none" stroke="#f0f0f0" stroke-width="3" stroke-dasharray="75, 100" stroke-linecap="round" transform="rotate(-135 18 18)" />
                        
                        <!-- Value Circle -->
                        <path class="circle"
                            d="M18 2.0845
                            a 15.9155 15.9155 0 0 1 0 31.831
                            a 15.9155 15.9155 0 0 1 0 -31.831"
                            fill="none" stroke="{{ $riskColor }}" stroke-width="3" stroke-dasharray="{{ ($monitorObj['risk_score'] / 100) * 75 }}, 100" stroke-linecap="round" transform="rotate(-135 18 18)" 
                            style="animation: fillup 1.5s ease-out forwards;" />
                    </svg>
                    <div class="risk-score-text {{ $riskTextClass }}">{{ number_format($monitorObj['risk_score'], 0) }}</div>
                    <div class="risk-score-label text-muted">{{ __($riskLevel) }}</div>
                </div>

                <!-- Delay Indicator -->
                <div class="bg-light rounded-3 p-3 mb-3 d-flex justify-content-between align-items-center border">
                    <span class="text-muted fw-semibold small"><i class="bi bi-clock-history me-2"></i>{{ __('Estimated Delay') }}</span>
                    <span class="badge {{ $monitorObj['estimated_delay'] == '0 Days' ? 'bg-success' : 'bg-danger' }} rounded-pill px-3">{{ __($monitorObj['estimated_delay']) }}</span>
                </div>

                <!-- Risk Breakdown -->
                <div class="text-start">
                    <p class="text-muted small fw-bold text-uppercase mb-2">{{ __('AI Impact Factors') }}</p>
                    <div class="d-flex align-items-start gap-2 mb-2">
                        <i class="bi bi-cloud-lightning-rain text-primary mt-1"></i>
                        <span class="small text-muted">{{ __($monitorObj['weather_summary']) }}</span>
                    </div>
                    <div class="d-flex align-items-start gap-2 mb-2">
                        <i class="bi bi-newspaper text-info mt-1"></i>
                        <span class="small text-muted">{{ __($monitorObj['latest_news_summary']) }}</span>
                    </div>
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-currency-exchange text-success mt-1"></i>
                        <span class="small text-muted">{{ __($monitorObj['currency_summary']) }}</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Timeline & Details -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="fw-bold mb-0"><i class="bi bi-clock-history text-primary me-2"></i>{{ __('Shipment History & Timeline') }}</h6>
            </div>
            <div class="card-body p-4">
                @if($shipment->histories->isEmpty())
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-inbox fs-1 d-block mb-3 opacity-25"></i>
                        <p>{{ __('No timeline records available yet.') }}</p>
                    </div>
                @else
                    <div class="timeline-vertical mt-2">
                        @foreach($shipment->histories as $history)
                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-1">
                                    <h6 class="fw-bold mb-0 text-dark">{{ __($history->status) }}</h6>
                                    <span class="badge bg-light text-muted border px-2 py-1"><i class="bi bi-calendar-event me-1"></i>{{ $history->timestamp ? $history->timestamp->format('d M Y, H:i') : '-' }}</span>
                                </div>
                                <p class="text-muted small mb-1"><i class="bi bi-geo-alt me-1"></i>{{ __($history->location_desc) }}</p>
                                @if($history->latitude && $history->longitude)
                                    <span class="text-muted" style="font-size: 0.7rem;">Lat: {{ $history->latitude }}, Lng: {{ $history->longitude }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@if(!empty($mapData))
@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    var mapData = @json($mapData);
    if(mapData.length > 0) {
        var map = L.map('routeMap', { zoomControl: false });
        L.control.zoom({ position: 'bottomright' }).addTo(map);
        
        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap contributors &copy; CARTO'
        }).addTo(map);

        var latlngs = [];
        var iconHtml = function(color) {
            return `<div style="background-color:${color}; width:16px; height:16px; border-radius:50%; border:3px solid white; box-shadow:0 0 5px rgba(0,0,0,0.3);"></div>`;
        };

        mapData.forEach(function(point) {
            if(point.lat && point.lng) {
                var latlng = [point.lat, point.lng];
                latlngs.push(latlng);
                
                var color = point.type === 'Origin' ? '#1C55FF' : '#198754';
                var customIcon = L.divIcon({
                    html: iconHtml(color),
                    className: '',
                    iconSize: [16, 16],
                    iconAnchor: [8, 8]
                });

                var marker = L.marker(latlng, {icon: customIcon}).addTo(map);
                marker.bindPopup(`<div class="text-center"><span class="badge bg-primary mb-1">${point.type}</span><br><b style="font-size:14px;">${point.name}</b></div>`);
            }
        });

        if(latlngs.length > 1) {
            // Draw a curved line or just a simple dashed line
            var polyline = L.polyline(latlngs, {
                color: '#1C55FF', 
                weight: 3, 
                dashArray: '8, 8',
                opacity: 0.7
            }).addTo(map);
            map.fitBounds(polyline.getBounds(), { padding: [50, 50] });
        } else if(latlngs.length === 1) {
            map.setView(latlngs[0], 5);
        } else {
            map.setView([20, 0], 2);
        }
    }
});
</script>
@endpush
@endif