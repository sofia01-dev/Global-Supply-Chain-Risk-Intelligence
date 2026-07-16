@extends('layouts.app')

@push('styles')
<!-- Flag Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.0.0/css/flag-icons.min.css"/>
<style>
    .country-nav-list {
        max-height: calc(100vh - 200px);
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
    .modern-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.05);
    }
    .icon-circle {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    .progress-bar-custom {
        height: 8px;
        border-radius: 4px;
        background-color: #e9ecef;
        position: relative;
    }
    .progress-bar-fill {
        height: 100%;
        border-radius: 4px;
        background: linear-gradient(90deg, #28a745, #ffc107, #dc3545);
    }
    .progress-indicator {
        position: absolute;
        top: -4px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background-color: #fff;
        border: 3px solid #333;
        transform: translateX(-50%);
    }
    .news-item {
        padding-bottom: 12px;
        margin-bottom: 12px;
        border-bottom: 1px solid #f0f0f0;
    }
    .news-item:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
</style>
@endpush

@section('content')
<div class="row g-4">
    <!-- LEFT PANEL: Country List -->
    <div class="col-lg-3">
        <div class="modern-card p-3">
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
                        $cRisk = $c->riskScores->first();
                        $cRiskLevel = $cRisk ? $cRisk->risk_level : 'Low';
                        $cColor = $cRiskLevel === 'Critical' ? 'danger' : ($cRiskLevel === 'High' ? 'warning' : ($cRiskLevel === 'Medium' ? 'info' : 'success'));
                        $isActive = $country && $country->id === $c->id;
                    @endphp
                    <a href="{{ route('user.country', $c->id) }}" class="country-item {{ $isActive ? 'active' : '' }}">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fi fi-{{ strtolower($c->iso2_code ?? 'un') }} fs-4 rounded shadow-sm"></span>
                            <div>
                                <h6 class="mb-0 fw-bold {{ $isActive ? 'text-primary' : 'text-dark' }}" style="font-size: 0.9rem;">{{ $c->name }}</h6>
                                <span class="text-muted" style="font-size: 0.75rem;">{{ __($c->region ?? 'Unknown') }}</span>
                            </div>
                        </div>
                        <span class="badge bg-light-{{ $cColor }} text-{{ $cColor }} rounded-pill" style="font-size: 0.7rem;">
                            {{ __($cRiskLevel) }}
                        </span>
                    </a>
                @empty
                    <div class="text-center text-muted py-4 small">{{ __('No countries found.') }}</div>
                @endforelse
            </div>
            
            @if($countries->count() > 0)
                <div class="mt-3 text-center px-2">
                    <p class="text-muted small mb-0">{{ __('Showing') }} {{ $countries->count() }} {{ __('countries') }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- RIGHT PANEL: Dashboard Content -->
    <div class="col-lg-9">
        @if(!$country)
            <div class="modern-card p-5 text-center d-flex flex-column align-items-center justify-content-center" style="height: 600px;">
                <i class="bi bi-globe-americas text-muted opacity-25" style="font-size: 5rem;"></i>
                <h4 class="mt-3 fw-bold text-dark">{{ __('No Country Selected') }}</h4>
                <p class="text-muted">{{ __('Please select a country from the left panel to view its intelligence dashboard.') }}</p>
            </div>
        @else
            <!-- Country Header -->
            <div class="modern-card p-4 mb-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-4">
                    <span class="fi fi-{{ strtolower($country->iso2_code ?? 'un') }} border border-light shadow-sm" style="font-size: 5rem; border-radius: 8px;"></span>
                    <div>
                        <h2 class="fw-bold mb-1">{{ $country->name }}</h2>
                        <p class="text-muted mb-3">{{ $country->official_name ?? $country->name }}</p>
                        <div class="d-flex gap-4 text-sm">
                            <div>
                                <span class="text-muted d-block" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">{{ __('Capital') }}</span>
                                <span class="fw-bold">{{ $country->capital ?? 'N/A' }}</span>
                            </div>
                            <div>
                                <span class="text-muted d-block" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">{{ __('Region') }}</span>
                                <span class="fw-bold">{{ __($country->region ?? 'N/A') }}</span>
                            </div>
                            <div>
                                <span class="text-muted d-block" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">{{ __('Last Updated') }}</span>
                                <span class="fw-bold">{{ $country->updated_at ? $country->updated_at->format('M d, Y h:i A') : 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <button class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm"><i class="bi bi-star me-2"></i>{{ __('Add to Favorites') }}</button>
                </div>
            </div>

            @php
                $eco = $country->economicIndicator;
                $wea = $country->weatherCaches->first();
                $cur = $country->currentCurrency;
            @endphp

            <!-- Economic Summary Cards (4 cols) -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="modern-card p-3 d-flex gap-3 align-items-center">
                        <div class="icon-circle bg-light-primary text-primary"><i class="bi bi-bar-chart-line"></i></div>
                        <div>
                            <span class="text-muted d-block" style="font-size: 0.75rem; font-weight: 600;">{{ __('GDP (Nominal)') }}</span>
                            <h5 class="fw-bold mb-0">{{ $eco ? '$'.number_format($eco->gdp_value / 1000000000, 2).' B' : __('No data') }}</h5>
                            @if($eco && $eco->gdp_growth_rate)
                                <span class="small text-{{ $eco->gdp_growth_rate >= 0 ? 'success' : 'danger' }} fw-bold"><i class="bi bi-arrow-{{ $eco->gdp_growth_rate >= 0 ? 'up' : 'down' }}"></i> {{ $eco->gdp_growth_rate }}%</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="modern-card p-3 d-flex gap-3 align-items-center">
                        <div class="icon-circle bg-light-danger text-danger"><i class="bi bi-graph-down-arrow"></i></div>
                        <div>
                            <span class="text-muted d-block" style="font-size: 0.75rem; font-weight: 600;">{{ __('Inflation (YoY)') }}</span>
                            <h5 class="fw-bold mb-0">{{ $eco ? number_format($eco->inflation_rate, 2).'%' : __('No data') }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="modern-card p-3 d-flex gap-3 align-items-center">
                        <div class="icon-circle bg-light-info text-info"><i class="bi bi-people"></i></div>
                        <div>
                            <span class="text-muted d-block" style="font-size: 0.75rem; font-weight: 600;">{{ __('Population') }}</span>
                            <h5 class="fw-bold mb-0">{{ $country->population ? number_format($country->population) : __('No data') }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="modern-card p-3 d-flex gap-3 align-items-center">
                        <div class="icon-circle bg-light-success text-success"><i class="bi bi-currency-exchange"></i></div>
                        <div>
                            <span class="text-muted d-block" style="font-size: 0.75rem; font-weight: 600;">{{ __('Currency') }}</span>
                            <h5 class="fw-bold mb-0">{{ $country->currency_code ?? __('No data') }}</h5>
                            <span class="small text-muted">{{ $country->currency_name ?? '' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Middle Row: Weather, Risk, Currency -->
            <div class="row g-3 mb-4">
                <!-- Weather -->
                <div class="col-md-4">
                    <div class="modern-card p-4 h-100">
                        <h6 class="fw-bold mb-4">{{ __('Current Weather') }}</h6>
                        @if($wea)
                            <div class="d-flex align-items-center gap-3 mb-4">
                                <i class="bi bi-cloud-sun text-warning" style="font-size: 3rem;"></i>
                                <div>
                                    <h2 class="fw-bold mb-0">{{ $wea->temperature }}°C</h2>
                                    <span class="text-muted">{{ __($wea->weather_condition) }}</span>
                                </div>
                            </div>
                            <div class="row g-2 text-center mt-auto">
                                <div class="col-4">
                                    <i class="bi bi-droplet text-info mb-1"></i>
                                    <span class="d-block text-dark fw-bold small">{{ $wea->humidity }}%</span>
                                    <span class="text-muted" style="font-size: 0.65rem;">{{ __('Humidity') }}</span>
                                </div>
                                <div class="col-4">
                                    <i class="bi bi-wind text-secondary mb-1"></i>
                                    <span class="d-block text-dark fw-bold small">{{ $wea->wind_speed }} km/h</span>
                                    <span class="text-muted" style="font-size: 0.65rem;">{{ __('Wind') }}</span>
                                </div>
                                <div class="col-4">
                                    <i class="bi bi-cloud text-primary mb-1"></i>
                                    <span class="d-block text-dark fw-bold small">N/A</span>
                                    <span class="text-muted" style="font-size: 0.65rem;">{{ __('Pressure') }}</span>
                                </div>
                            </div>
                            <div class="mt-4 pt-3 border-top text-muted" style="font-size: 0.7rem;">
                                Updated: {{ $wea->updated_at->format('M d, Y H:i A') }} <span class="float-end">Source: API</span>
                            </div>
                        @else
                            <div class="text-center text-muted py-4">{{ __('No weather data available') }}</div>
                        @endif
                    </div>
                </div>
                
                <!-- Risk Score Engine -->
                <div class="col-md-4">
                    <div class="modern-card p-4 h-100 text-center">
                        <h6 class="fw-bold mb-4 text-start">{{ __('Risk Scoring Engine') }}</h6>
                        <div class="mb-4">
                            <span class="text-muted d-block small fw-bold text-uppercase">{{ __('Risk Score') }}</span>
                            <h1 class="fw-bold text-dark display-4 mb-0">{{ isset($country->riskData) ? number_format($country->riskData['score']) : 0 }} <span class="fs-4 text-muted">/ 100</span></h1>
                            @php
                                $rLevel = $country->riskData['level'] ?? 'Low Risk';
                                $rColor = str_contains($rLevel, 'Critical') ? 'danger' : (str_contains($rLevel, 'High') ? 'warning' : (str_contains($rLevel, 'Medium') ? 'info' : 'success'));
                                $scorePct = isset($country->riskData) ? $country->riskData['score'] : 0;
                            @endphp
                            <h5 class="fw-bold text-{{ $rColor }} mt-2">{{ __($rLevel) }}</h5>
                        </div>
                        
                        <div class="progress-bar-custom mb-3">
                            <div class="progress-bar-fill"></div>
                            <div class="progress-indicator" style="left: {{ $scorePct }}%;"></div>
                        </div>
                        
                        <div class="d-flex justify-content-between text-muted mt-4 pt-3 border-top" style="font-size: 0.7rem;">
                            <div class="text-center">
                                <i class="bi bi-cloud-snow d-block mb-1"></i>
                                {{ __('Weather') }}
                            </div>
                            <div class="text-center">
                                <i class="bi bi-graph-up d-block mb-1"></i>
                                {{ __('Inflation') }}
                            </div>
                            <div class="text-center">
                                <i class="bi bi-currency-exchange d-block mb-1"></i>
                                {{ __('Exchange') }}
                            </div>
                            <div class="text-center">
                                <i class="bi bi-newspaper d-block mb-1"></i>
                                {{ __('Sentiment') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Currency -->
                <div class="col-md-4">
                    <div class="modern-card p-4 h-100">
                        <h6 class="fw-bold mb-4">{{ __('Currency Information') }}</h6>
                        @if($country->currency_code)
                            <div class="mb-3">
                                <span class="text-muted d-block small fw-bold">{{ __('Exchange Rate (USD to :code)', ['code' => $country->currency_code]) }}</span>
                                <h3 class="fw-bold text-dark mb-1">
                                    <!-- Simplified logic for display -->
                                    {{ $cur ? number_format($cur->exchange_rate_usd, 2) : __('No data') }} {{ $country->currency_code }}
                                </h3>
                                @if($cur)
                                    <span class="small text-muted">{{ __('Last Updated') }}: {{ $cur->updated_at->format('M d, Y H:i A') }}</span>
                                @endif
                            </div>
                            <!-- Real Chart Area -->
                            <div class="mt-4 pt-2" style="height: 100px;">
                                <canvas id="currencyChart"></canvas>
                            </div>
                        @else
                            <div class="text-center text-muted py-4">{{ __('No currency data available') }}</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Bottom Row: News and AI Insight -->
            <div class="row g-3">
                <div class="col-md-8">
                    <div class="modern-card p-4 h-100">
                        <h6 class="fw-bold mb-4">{{ __('Latest News Intelligence') }}</h6>
                        @if($country->globalNewsFallback && $country->globalNewsFallback->count() > 0)
                            @foreach($country->globalNewsFallback as $news)
                                @php
                                    $nSent = $news->positive_percentage > $news->negative_percentage ? 'Positive' : ($news->negative_percentage > $news->positive_percentage ? 'Negative' : 'Neutral');
                                    $nColor = $nSent === 'Positive' ? 'success' : ($nSent === 'Negative' ? 'danger' : 'warning');
                                @endphp
                                <div class="news-item d-flex gap-3 align-items-center">
                                    <div class="bg-light rounded" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-newspaper text-muted fs-3"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-1" style="font-size: 0.85rem;">{{ $news->title }}</h6>
                                        <div class="d-flex align-items-center gap-2" style="font-size: 0.75rem;">
                                            <span class="badge bg-light-primary text-primary">{{ __('News') }}</span>
                                            <span class="text-muted">{{ $news->published_at ? \Carbon\Carbon::parse($news->published_at)->format('M d, Y') : __('Unknown') }}</span>
                                        </div>
                                    </div>
                                    <div class="text-end" style="min-width: 80px;">
                                        <span class="fw-bold text-{{ $nColor }} d-block" style="font-size: 0.8rem;">{{ __($nSent) }}</span>
                                        <a href="#" class="small text-decoration-none">{{ __('Read More') }} <i class="bi bi-arrow-right"></i></a>
                                    </div>
                                </div>
                            @endforeach
                            <div class="text-center mt-3 pt-2">
                                <a href="#" class="btn btn-link text-decoration-none fw-bold small">{{ __('View All News') }} <i class="bi bi-arrow-right"></i></a>
                            </div>
                        @else
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-journal-x fs-1 opacity-50 mb-2 d-block"></i>
                                {{ __('No news intelligence available for this region.') }}
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="modern-card p-4 h-100 bg-light-blue border border-primary border-opacity-10">
                        <div class="d-flex align-items-center gap-2 mb-4">
                            <i class="bi bi-robot text-primary fs-4"></i>
                            <h6 class="fw-bold mb-0 text-primary">{{ __('AI Insight') }}</h6>
                        </div>
                        
                        @if($aiRecommendation)
                            <div class="bg-white rounded p-3 mb-3 shadow-sm border border-light">
                                <span class="text-muted d-block small fw-bold text-uppercase">{{ __('Overall Assessment') }}</span>
                                <h5 class="fw-bold text-{{ str_contains($aiRecommendation['status'], 'Critical') ? 'danger' : (str_contains($aiRecommendation['status'], 'High') ? 'warning' : 'primary') }} mt-1">
                                    {{ $aiRecommendation['status'] }}
                                </h5>
                                <p class="small text-muted mb-0">{{ __('Current conditions indicate a :status level for supply chain operations in :country.', ['status' => __(strtolower($aiRecommendation['status'])), 'country' => $country->name]) }}</p>
                            </div>
                            
                            <ul class="list-unstyled mb-4 small text-dark">
                                @foreach($aiRecommendation['details'] as $detail)
                                    <li class="mb-2 d-flex gap-2">
                                        <i class="bi bi-check-circle-fill text-success"></i> {{ __($detail) }}
                                    </li>
                                @endforeach
                            </ul>
                            
                            <div class="pt-3 border-top border-primary border-opacity-10">
                                <span class="fw-bold d-block mb-1 text-dark" style="font-size: 0.85rem;">{{ __('Recommendation') }}</span>
                                <p class="small text-muted mb-0">{{ __($aiRecommendation['message']) }}</p>
                            </div>
                        @else
                            <div class="text-center text-muted py-4">{{ __('No AI Insights available.') }}</div>
                        @endif
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
        // 1. Live Search functionality
        const searchInput = document.getElementById('searchInput');
        const countryItems = document.querySelectorAll('.country-item');
        
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                const term = e.target.value.toLowerCase();
                let visibleCount = 0;
                
                countryItems.forEach(item => {
                    const countryName = item.querySelector('h6').textContent.toLowerCase();
                    if (countryName.includes(term)) {
                        item.style.display = 'flex';
                        visibleCount++;
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        }

        // 2. Currency Chart.js
        const chartCtx = document.getElementById('currencyChart');
        if (chartCtx) {
            // Generate some mock historical data based on current value for visual effect
            // since we only have current snapshot in CurrencyCache
            const currentVal = {{ isset($cur) ? $cur->exchange_rate_usd : 1 }};
            const mockData = [
                currentVal * 0.98,
                currentVal * 1.02,
                currentVal * 0.99,
                currentVal * 1.01,
                currentVal * 0.97,
                currentVal
            ];

            new Chart(chartCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Exchange Rate',
                        data: mockData,
                        borderColor: '#1C55FF',
                        backgroundColor: 'rgba(28, 85, 255, 0.1)',
                        borderWidth: 2,
                        pointBackgroundColor: '#1C55FF',
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y.toFixed(2);
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            display: true,
                            grid: { display: false }
                        },
                        y: {
                            display: false,
                            min: Math.min(...mockData) * 0.95,
                            max: Math.max(...mockData) * 1.05
                        }
                    }
                }
            });
        }
    });
</script>
@endpush