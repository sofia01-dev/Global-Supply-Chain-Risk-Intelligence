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

@section('content')
<div class="row mb-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-1">{{ __('Currency Impact Dashboard') }}</h4>
            <p class="text-muted small mb-0">{{ __('Monitor exchange rate impact on global supply chain') }}</p>
        </div>
        <div class="d-flex gap-3 align-items-center text-muted small">
            <div><i class="bi bi-calendar3"></i> {{ __('Last Update:') }} {{ \Carbon\Carbon::now()->format('M d, Y H:i A') }}</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- FULL WIDTH PANEL -->
    <div class="col-lg-12">
        <!-- Dual-Card Currency Selector -->
        <div class="modern-card p-4 mb-4">
            <div class="row align-items-center justify-content-center text-center">
                <!-- Target Currency (Left) -->
                <div class="col-md-5">
                    @php 
                        $sIso = $selectedCurrency ? strtolower(substr($selectedCurrency->currency_code, 0, 2)) : 'us'; 
                        $sCode = $selectedCurrency ? $selectedCurrency->currency_code : 'USD';
                    @endphp
                    <div class="border rounded-4 p-4 d-inline-block w-100 position-relative bg-light" style="cursor: pointer; transition: all 0.2s;" data-bs-toggle="modal" data-bs-target="#currencyModal" onmouseover="this.classList.add('shadow-sm')" onmouseout="this.classList.remove('shadow-sm')">
                        <span class="position-absolute top-0 end-0 mt-3 me-3 text-primary"><i class="bi bi-pencil-square"></i></span>
                        <div class="mb-2">
                            <span class="fi fi-{{ $sIso }} rounded-circle border shadow-sm" style="width: 48px; height: 48px; font-size: 48px;"></span>
                        </div>
                        <h4 class="fw-bold mb-0 text-dark">1 {{ $sCode }}</h4>
                        <small class="text-muted">{{ __('Click to change target currency') }}</small>
                    </div>
                </div>

                <!-- Separator (Middle) -->
                <div class="col-md-2 my-3 my-md-0">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mx-auto" style="width: 50px; height: 50px;">
                        <i class="bi bi-arrow-left-right fs-4"></i>
                    </div>
                </div>

                <!-- IDR Base (Right) -->
                <div class="col-md-5">
                    @php
                        $cRate = 0;
                        if ($selectedCurrency) {
                            $tRate = (float)$selectedCurrency->exchange_rate_usd;
                            $cRate = $tRate > 0 ? ($idrRate / $tRate) : 0;
                        }
                    @endphp
                    <div class="border rounded-4 p-4 d-inline-block w-100 bg-white shadow-sm border-primary">
                        <div class="mb-2">
                            <span class="fi fi-id rounded-circle border shadow-sm" style="width: 48px; height: 48px; font-size: 48px;"></span>
                        </div>
                        <h2 class="fw-bold mb-0 text-primary" style="font-size: 2.5rem; letter-spacing: -1px;">Rp {{ number_format($cRate, 2, ',', '.') }}</h2>
                        <small class="text-muted fw-bold">IDR (Indonesian Rupiah)</small>
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
            <!-- Exchange Rate Trend Chart -->
            <div class="modern-card p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold mb-0">{{ __('Exchange Rate Trend') }} (IDR / {{ $selectedCurrency->currency_code }})</h6>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-primary">7D</button>
                        <button type="button" class="btn btn-outline-primary">30D</button>
                        <button type="button" class="btn btn-outline-primary">90D</button>
                        <button type="button" class="btn btn-outline-primary">1Y</button>
                        <button type="button" class="btn btn-outline-primary"><i class="bi bi-calendar3"></i></button>
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


            <!-- Bottom Row: Insight -->
            <div class="row g-4">
                <!-- AI Currency Insight -->
                <div class="col-12">
                    <div class="row g-4">
                        <div class="col-12">
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

                                <div class="bg-white border rounded p-3 d-flex gap-3 align-items-start">
                                    <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-2">
                                        <i class="bi bi-lightbulb fs-5"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold small mb-1">{{ __('Recommendation') }} :</h6>
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