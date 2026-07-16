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
    <!-- LEFT PANEL: Currency List -->
    <div class="col-lg-3">
        <div class="modern-card p-3 h-100">
            <h6 class="fw-bold mb-3 px-2">{{ __('Search Currency Pair') }}</h6>
            
            <form action="{{ route('user.currency') }}" method="GET" class="mb-3 px-2">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" value="{{ $search }}" class="form-control border-start-0 bg-light" placeholder="{{ __('Search currency pair...') }}">
                </div>
            </form>
            
            <div class="currency-nav-list px-2">
                @forelse($currencies as $c)
                    @php
                        $isActive = $selectedCurrency && $selectedCurrency->id === $c->id;
                        $iso = strtolower(substr($c->currency_code, 0, 2));
                    @endphp
                    <a href="{{ route('user.currency', ['currency' => $c->currency_code]) }}" class="currency-item {{ $isActive ? 'active' : '' }}">
                        <div class="d-flex align-items-center gap-2">
                            <!-- Show USD vs Target -->
                            <div class="d-flex">
                                <span class="fi fi-us rounded-circle border" style="width: 20px; height: 20px; z-index: 2;"></span>
                                <span class="fi fi-{{ $iso }} rounded-circle border" style="width: 20px; height: 20px; margin-left: -8px; z-index: 1;"></span>
                            </div>
                            <div class="ms-1">
                                <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.85rem;">USD / {{ $c->currency_code }}</h6>
                                <span class="text-muted" style="font-size: 0.65rem;">US Dollar / {{ $c->currency_code }}</span>
                            </div>
                        </div>
                        <div class="text-end">
                            <h6 class="mb-0 fw-bold" style="font-size: 0.85rem;">{{ number_format($c->exchange_rate_usd, 4) }}</h6>
                            <span class="text-muted" style="font-size: 0.65rem;">-</span>
                        </div>
                    </a>
                @empty
                    <div class="text-center text-muted py-4 small">{{ __('No currency pairs found.') }}</div>
                @endforelse
            </div>
            
            <div class="mt-3 text-center px-2">
                <a href="{{ route('user.currency') }}" class="btn btn-light btn-sm w-100 text-primary fw-bold"><i class="bi bi-arrow-up-right"></i> {{ __('View All Currency Pairs') }}</a>
            </div>
        </div>
    </div>

    <!-- RIGHT PANEL: Dashboard Content -->
    <div class="col-lg-9">
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
                    <h6 class="fw-bold mb-0">{{ __('Exchange Rate Trend') }} (USD / {{ $selectedCurrency->currency_code }})</h6>
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
                    <div class="empty-chart-overlay text-center">
                        <div>
                            <i class="bi bi-bar-chart text-muted opacity-50 mb-2" style="font-size: 2rem;"></i>
                            <h6 class="fw-bold text-muted mb-0">{{ __('Data histori kurs belum tersedia') }}</h6>
                            <p class="small text-muted mb-0">{{ __('Sistem sedang menunggu siklus sinkronisasi histori selanjutnya.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4 Summary Cards -->
            <div class="row g-3 mb-4">
                @foreach($topCurrencies as $top)
                    @php
                        $topIso = strtolower(substr($top->currency_code, 0, 2));
                    @endphp
                    <div class="col-md-3">
                        <div class="modern-card p-3 h-100">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <div class="d-flex">
                                    <span class="fi fi-us rounded-circle border" style="width: 16px; height: 16px; z-index: 2;"></span>
                                    <span class="fi fi-{{ $topIso }} rounded-circle border" style="width: 16px; height: 16px; margin-left: -6px; z-index: 1;"></span>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold" style="font-size: 0.8rem;">USD / {{ $top->currency_code }}</h6>
                                </div>
                            </div>
                            <h3 class="fw-bold mb-1">{{ number_format($top->exchange_rate_usd, 4) }}</h3>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge rounded-pill badge-stable px-2" style="font-size: 0.65rem;">-</span>
                                <span class="text-muted" style="font-size: 0.65rem;">{{ __('Today') }}</span>
                            </div>
                            <!-- Mini sparkline placeholder (Empty State) -->
                            <div class="mt-2 text-muted text-center" style="font-size: 0.6rem; border-top: 1px solid #f0f0f0; padding-top: 5px;">{{ __('No history') }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Bottom Row: Detail, Insight, Table -->
            <div class="row g-4">
                <!-- Currency Detail Card -->
                <div class="col-md-4">
                    <div class="modern-card p-4 h-100">
                        <h6 class="fw-bold mb-4">{{ __('Currency Detail') }}</h6>
                        <ul class="list-unstyled mb-0">
                            <li class="d-flex justify-content-between border-bottom py-2">
                                <span class="text-muted small"><i class="bi bi-coin me-2"></i>{{ __('Base Currency') }}</span>
                                <span class="fw-bold small">USD - US Dollar</span>
                            </li>
                            <li class="d-flex justify-content-between border-bottom py-2">
                                <span class="text-muted small"><i class="bi bi-arrow-right-circle me-2"></i>{{ __('Target Currency') }}</span>
                                <span class="fw-bold small">{{ $selectedCurrency->currency_code }}</span>
                            </li>
                            <li class="d-flex justify-content-between border-bottom py-2">
                                <span class="text-muted small"><i class="bi bi-graph-up me-2"></i>{{ __('Current Rate') }}</span>
                                <span class="fw-bold small">{{ number_format($selectedCurrency->exchange_rate_usd, 4) }}</span>
                            </li>
                            <li class="d-flex justify-content-between border-bottom py-2">
                                <span class="text-muted small"><i class="bi bi-calendar-day me-2"></i>{{ __('Daily Change') }}</span>
                                <span class="fw-bold small text-muted">-</span>
                            </li>
                            <li class="d-flex justify-content-between border-bottom py-2">
                                <span class="text-muted small"><i class="bi bi-calendar-week me-2"></i>{{ __('Weekly Change') }}</span>
                                <span class="fw-bold small text-muted">-</span>
                            </li>
                            <li class="d-flex justify-content-between border-bottom py-2">
                                <span class="text-muted small"><i class="bi bi-calendar-month me-2"></i>{{ __('Monthly Change') }}</span>
                                <span class="fw-bold small text-muted">-</span>
                            </li>
                            <li class="d-flex justify-content-between border-bottom py-2">
                                <span class="text-muted small"><i class="bi bi-clock me-2"></i>{{ __('Last Update') }}</span>
                                <span class="fw-bold small">{{ $selectedCurrency->updated_at->format('M d, Y H:i A') }}</span>
                            </li>
                            <li class="d-flex justify-content-between pt-3">
                                <span class="text-muted small"><i class="bi bi-hdd-network me-2"></i>{{ __('Data Source') }}</span>
                                <span class="badge bg-light text-primary border px-2">ExchangeRate API</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- AI Currency Insight -->
                <div class="col-md-8">
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
                        
                        <!-- Recent Currency Updates -->
                        <div class="col-12">
                            <div class="modern-card p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="fw-bold mb-0">{{ __('Recent Currency Updates') }}</h6>
                                    <a href="#" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1" style="font-size: 0.75rem;">{{ __('View All') }}</a>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover table-borderless align-middle mb-0">
                                        <thead class="text-muted" style="font-size: 0.75rem; border-bottom: 1px solid #f0f0f0;">
                                            <tr>
                                                <th class="fw-medium pb-2">{{ __('Time') }}</th>
                                                <th class="fw-medium pb-2">{{ __('Currency Pair') }}</th>
                                                <th class="fw-medium pb-2 text-end">{{ __('Rate (USD)') }}</th>
                                                <th class="fw-medium pb-2 text-center">{{ __('Change') }}</th>
                                                <th class="fw-medium pb-2 text-center">{{ __('Status') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody style="font-size: 0.85rem;">
                                            @foreach($currencies->take(5) as $rcur)
                                            <tr>
                                                <td class="text-muted">{{ $rcur->updated_at->format('H:i A') }}</td>
                                                <td class="fw-bold">USD / {{ $rcur->currency_code }}</td>
                                                <td class="text-end fw-bold">{{ number_format($rcur->exchange_rate_usd, 4) }}</td>
                                                <td class="text-center text-muted">-</td>
                                                <td class="text-center">
                                                    <span class="badge rounded-pill badge-stable px-3">{{ __('Stable') }}</span>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-3 text-muted" style="font-size: 0.65rem;">
                                    {{ __('All times in WIB (UTC+7)') }}
                                </div>
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
        // Initialize Chart.js with empty structure (ready for future data)
        const chartCtx = document.getElementById('trendChart');
        if (chartCtx) {
            new Chart(chartCtx, {
                type: 'line',
                data: {
                    labels: [], // No history yet
                    datasets: [{
                        label: 'Exchange Rate',
                        data: [],
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
                        x: { display: false },
                        y: { display: false }
                    }
                }
            });
        }
    });
</script>
@endpush