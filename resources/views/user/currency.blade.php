@extends('layouts.app')

@push('styles')
<!-- Flag Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.0.0/css/flag-icons.min.css"/>
<style>
    .currency-nav-list {
        max-height: calc(100vh - 220px);
        overflow-y: auto;
        padding-right: 5px;
    }
    .currency-nav-list::-webkit-scrollbar { width: 5px; }
    .currency-nav-list::-webkit-scrollbar-thumb { background: #e0e0e0; border-radius: 10px; }
    
    .currency-item {
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
    .currency-item:hover {
        background-color: #f8f9fa;
        border-color: #e9ecef;
    }
    .currency-item.active {
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
    
    .insight-card {
        background: linear-gradient(180deg, #F9FAFF 0%, #FFFFFF 100%);
        border: 1px solid #E6EDFF;
    }
    
    .badge-bullish { background-color: #E8F5E9; color: #2E7D32; border: 1px solid #C8E6C9; }
    .badge-bearish { background-color: #FFEBEE; color: #C62828; border: 1px solid #FFCDD2; }
    .badge-stable { background-color: #FFF8E1; color: #F57F17; border: 1px solid #FFECB3; }
    
    .chart-container {
        height: 250px;
        width: 100%;
        position: relative;
    }
    .empty-chart-overlay {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.8);
        border: 1px dashed #ced4da;
        border-radius: 12px;
        z-index: 10;
        backdrop-filter: blur(2px);
    }
</style>
@endpush

@section('page_header')
<div class="d-none d-sm-block">
    <h5 class="m-0 fw-semibold text-dark">{{ __('Currency Impact Dashboard') }}</h5>
    <div class="text-muted" style="font-size: 0.75rem;">{{ __('Monitor exchange rate impact on global supply chain') }}</div>
</div>
@endsection

@section('header_actions')
<div class="d-none d-md-flex align-items-center gap-2 text-muted px-3 py-1 rounded-pill bg-light border border-secondary border-opacity-10 shadow-sm" style="font-size: 0.75rem; font-weight: 600;">
    <i class="bi bi-clock-history text-primary"></i> 
    <span>{{ __('Last Update:') }} {{ \Carbon\Carbon::now()->format('M d, Y H:i A') }}</span>
</div>
@endsection

@section('content')

<div class="row g-4">
    <!-- FULL WIDTH PANEL -->
    <div class="col-lg-12">
        <!-- Dual-Card Currency Selector -->
        <div class="modern-card p-4 p-md-5 mb-4 position-relative overflow-hidden" style="background: linear-gradient(135deg, #ffffff 0%, #f4f6fa 100%);">
            <!-- Decorative Background Element -->
            <div class="position-absolute rounded-circle" style="width: 300px; height: 300px; background: radial-gradient(circle, rgba(13,110,253,0.05) 0%, rgba(255,255,255,0) 70%); top: -100px; right: -50px;"></div>
            
            <div class="row align-items-center justify-content-center text-center position-relative z-1">
                <!-- Target Currency (Left) -->
                <div class="col-md-5 mb-4 mb-md-0">
                    @php 
                        $sIso = $selectedCurrency ? strtolower(substr($selectedCurrency->currency_code, 0, 2)) : 'us'; 
                        $sCode = $selectedCurrency ? $selectedCurrency->currency_code : 'USD';
                    @endphp
                    <!-- Interactive Selector Card -->
                    <div class="border-0 rounded-4 p-4 d-inline-block w-100 position-relative bg-white shadow-sm currency-selector-card" 
                         style="cursor: pointer; transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); border: 1px solid rgba(0,0,0,0.05) !important;" 
                         data-bs-toggle="modal" data-bs-target="#currencyModal" 
                         onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 15px 35px rgba(0,0,0,0.08)';" 
                         onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.03)';">
                        
                        <!-- Hover Edit Indicator -->
                        <div class="position-absolute top-0 end-0 m-3 bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-pencil-square text-primary" style="font-size: 0.9rem;"></i>
                        </div>
                        
                        <div class="flag-container mb-3 d-inline-block position-relative">
                            <span class="fi fi-{{ $sIso }} rounded-circle shadow-sm" style="width: 56px; height: 56px; font-size: 56px; display: block; border: 2px solid #fff;"></span>
                            <div class="position-absolute bottom-0 end-0 bg-primary rounded-circle border border-2 border-white d-flex align-items-center justify-content-center" style="width: 20px; height: 20px; right: -5px !important;">
                                <i class="bi bi-chevron-down text-white" style="font-size: 0.6rem;"></i>
                            </div>
                        </div>
                        
                        <h2 class="fw-bolder text-dark mb-1" style="letter-spacing: -0.5px;">1 <span class="text-primary">{{ $sCode }}</span></h2>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill mt-2 fw-medium" style="font-size: 0.75rem;">
                            {{ __('Click to change target currency') }}
                        </span>
                    </div>
                </div>

                <!-- Arrow Middle -->
                <div class="col-md-2 d-flex justify-content-center align-items-center z-2">
                    <div class="exchange-arrow-wrapper d-inline-flex align-items-center justify-content-center bg-white shadow-sm text-primary rounded-circle" 
                         style="width: 56px; height: 56px; border: 1px solid rgba(13,110,253,0.1); z-index: 5; margin: -15px 0;">
                        <i class="bi bi-arrow-left-right fs-4"></i>
                    </div>
                </div>

                <!-- IDR Base (Right) -->
                <div class="col-md-5 mt-4 mt-md-0">
                    @php
                        $cRate = 0;
                        if ($selectedCurrency) {
                            $tRate = (float)$selectedCurrency->exchange_rate_usd;
                            $cRate = $tRate > 0 ? ($idrRate / $tRate) : 0;
                        }
                    @endphp
                    <!-- Premium IDR Card -->
                    <div class="border-0 rounded-4 p-4 d-inline-block w-100 shadow-sm position-relative overflow-hidden text-white" 
                         style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                        
                        <!-- Decorative Shapes -->
                        <div class="position-absolute opacity-25" style="top: -20px; right: -20px;">
                            <i class="bi bi-globe-americas" style="font-size: 8rem;"></i>
                        </div>
                        
                        <div class="position-relative z-1">
                            <span class="fi fi-id rounded-circle shadow-sm mb-3 d-inline-block" style="width: 56px; height: 56px; font-size: 56px; border: 2px solid rgba(255,255,255,0.2);"></span>
                            
                            <h2 class="fw-bolder mb-1" style="letter-spacing: -0.5px; text-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                Rp {{ number_format($cRate, 2, ',', '.') }}
                            </h2>
                            <div class="d-inline-block mt-2">
                                <span class="badge bg-white bg-opacity-25 text-white px-3 py-2 rounded-pill fw-medium" style="backdrop-filter: blur(4px);">
                                    <i class="bi bi-shield-check me-1"></i> IDR (Indonesian Rupiah)
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Currency Selection Modal -->
        <div class="modal fade" id="currencyModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title fw-bold">{{ __('Select Currency') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <input type="text" class="form-control form-control-lg bg-light border-0" id="currencySearch" placeholder="Search currency... (e.g. USD, Yen)">
                        </div>
                        <div class="list-group list-group-flush border-top" id="currencyList">
                            @foreach($currencies as $c)
                                @php
                                    $iso = strtolower(substr($c->currency_code, 0, 2));
                                @endphp
                                <a href="{{ route('user.currency', ['currency' => $c->currency_code]) }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3 border-bottom currency-item-search" data-search="{{ strtolower($c->currency_code) }}">
                                    <span class="fi fi-{{ $iso }} rounded-circle border" style="width: 32px; height: 32px; font-size: 32px;"></span>
                                    <div>
                                        <h6 class="mb-0 fw-bold">{{ $c->currency_code }}</h6>
                                    </div>
                                    <i class="bi bi-chevron-right ms-auto text-muted"></i>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(!$selectedCurrency)
            <div class="modern-card p-5 text-center d-flex flex-column align-items-center justify-content-center" style="height: 600px;">
                <i class="bi bi-currency-exchange text-muted opacity-25" style="font-size: 5rem;"></i>
                <h4 class="mt-3 fw-bold text-dark">{{ __('No Currency Selected') }}</h4>
                <p class="text-muted">{{ __('Please select a currency pair to view impact analysis.') }}</p>
            </div>
        @else
            <!-- Middle Row: Chart & Insight Side by Side -->
            <div class="row g-4 mb-4">
                <!-- Left Column: Trend Chart (8 parts) -->
                <div class="col-lg-8 col-md-12">
                    <div class="modern-card p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="fw-bold mb-0">{{ __('Exchange Rate Trend') }} (IDR / {{ $selectedCurrency->currency_code }})</h6>
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-primary disabled" style="opacity: 1;">7 Days</button>
                            </div>
                        </div>
                        <div class="chart-container">
                            <canvas id="trendChart"></canvas>
                            <!-- Empty State Overlay for Chart -->
                            @if(empty($historicalData) || count($historicalData) <= 1)
                            <div class="empty-chart-overlay text-center">
                                <div>
                                    <i class="bi bi-bar-chart text-muted opacity-50 mb-2" style="font-size: 2rem;"></i>
                                    <h6 class="fw-bold text-muted mb-0">{{ __('Data histori kurs belum tersedia') }}</h6>
                                    <p class="small text-muted mb-0">{{ __('Sistem sedang menunggu siklus sinkronisasi histori selanjutnya.') }}</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>


                <!-- Right Column: AI Insight (4 parts) -->
                <div class="col-lg-4 col-md-12">
                    <div class="modern-card insight-card p-4 h-100">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="bg-primary bg-opacity-10 rounded p-2 text-primary">
                                <i class="bi bi-robot fs-5"></i>
                            </div>
                            <h6 class="fw-bold mb-0">{{ __('AI Currency Insight') }}</h6>
                        </div>
                        <p class="text-dark fw-medium small mb-4">{{ __($insight['summary']) }}</p>
                        
                        <h6 class="fw-bold small mb-2">{{ __('Potential Impact') }} :</h6>
                        <ul class="list-unstyled mb-4">
                            @foreach($insight['impacts'] as $imp)
                            <li class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <span class="small text-muted">{{ __($imp) }}</span>
                            </li>
                            @endforeach
                        </ul>

                        <div class="rounded p-3 d-flex gap-3 align-items-start mt-auto" style="background-color: #f8f7ff; border: 1px solid #edeafc;">
                            <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" style="background-color: #ebe7fe; color: #5c3ce6; width: 40px; height: 40px;">
                                <i class="bi bi-lightbulb-fill fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold small mb-1" style="color: #4328b7;">{{ __('Recommendation') }} :</h6>
                                <p class="small text-muted mb-0">{{ __($insight['recommendation']) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Modal Search Logic
        const searchInput = document.getElementById('currencySearch');
        const items = document.querySelectorAll('.currency-item-search');
        
        if(searchInput) {
            searchInput.addEventListener('keyup', function(e) {
                const term = e.target.value.toLowerCase();
                items.forEach(item => {
                    if(item.getAttribute('data-search').includes(term)) {
                        item.style.setProperty('display', 'flex', 'important');
                    } else {
                        item.style.setProperty('display', 'none', 'important');
                    }
                });
            });
        }

        // Initialize Chart.js with empty structure (ready for future data)
        const chartCtx = document.getElementById('trendChart');
        if (chartCtx) {
            new Chart(chartCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($historicalLabels ?? []) !!},
                    datasets: [{
                        label: 'Exchange Rate',
                        data: {!! json_encode($historicalData ?? []) !!},
                        borderColor: '#1C55FF',
                        backgroundColor: 'rgba(28, 85, 255, 0.1)',
                        borderWidth: 2,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#1C55FF',
                        pointRadius: 4,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: { display: true },
                        y: { display: true }
                    }
                }
            });
        }
    });
</script>
@endpush