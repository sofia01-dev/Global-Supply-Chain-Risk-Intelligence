@extends('layouts.app')

@section('title', __('News Intelligence'))

@section('page_header')
<div class="d-none d-sm-block">
    <h5 class="m-0 fw-semibold text-dark">{{ __('News Intelligence') }}</h5>
    <div class="text-muted" style="font-size: 0.75rem;">{{ __('Global news analysis for Logistics, Trade, Shipping, and Economy') }}</div>
</div>
@endsection

@section('header_actions')
<button class="btn btn-sm shadow-sm rounded-pill text-white fw-medium d-flex align-items-center gap-1 px-3" id="syncNewsBtn" style="background-color: var(--primary-navy); border: none;">
    <i class="bi bi-arrow-repeat"></i> <span class="d-none d-sm-inline">{{ __('Sync News') }}</span>
</button>
@endsection

@section('content')
<div class="container-fluid py-4">

    <!-- Summary Metrics -->
    <style>
        .metric-card {
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            border: 1px solid rgba(0,0,0,0.04);
            overflow: hidden;
            position: relative;
        }
        .metric-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.08) !important;
        }
        .metric-icon-bg {
            position: absolute;
            right: -15px;
            bottom: -15px;
            font-size: 6rem;
            opacity: 0.04;
            transform: rotate(-15deg);
            transition: all 0.3s ease;
        }
        .metric-card:hover .metric-icon-bg {
            transform: rotate(0deg) scale(1.1);
            opacity: 0.08;
        }
        .icon-box {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
    </style>
    
    <div class="row row-cols-2 row-cols-md-3 row-cols-xl-5 g-3 mb-4">
        <!-- Total -->
        <div class="col">
            <div class="card metric-card shadow-sm rounded-4 h-100 bg-white">
                <i class="bi bi-newspaper metric-icon-bg text-primary"></i>
                <div class="card-body p-3 d-flex align-items-center gap-3 position-relative z-1">
                    <div class="icon-box bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-newspaper"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-0 fw-semibold">{{ __('Total News') }}</p>
                        <h3 class="fw-bolder mb-0 text-dark lh-1" id="totalNewsCount" style="letter-spacing: -0.5px;">{{ $categoryStats['total'] }}</h3>
                        <small class="text-primary fw-bold d-block mt-1" style="font-size: 0.7rem;"><i class="bi bi-arrow-up-short"></i> 12% from yesterday</small>
                    </div>
                </div>
            </div>
        </div>
        <!-- Shipping -->
        <div class="col">
            <div class="card metric-card shadow-sm rounded-4 h-100 bg-white">
                <i class="bi bi-box-seam metric-icon-bg" style="color: #6f42c1;"></i>
                <div class="card-body p-3 d-flex align-items-center gap-3 position-relative z-1">
                    <div class="icon-box" style="background-color: rgba(111, 66, 193, 0.1); color: #6f42c1;">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-0 fw-semibold">{{ __('Shipping') }}</p>
                        <h3 class="fw-bolder mb-0 text-dark lh-1" id="shippingNewsCount" style="letter-spacing: -0.5px;">{{ $categoryStats['categories']['Shipping']['count'] }}</h3>
                        <small class="fw-bold d-block mt-1" style="font-size: 0.7rem; color: #6f42c1;"><i class="bi bi-arrow-up-short"></i> 8% from yesterday</small>
                    </div>
                </div>
            </div>
        </div>
        <!-- Trade -->
        <div class="col">
            <div class="card metric-card shadow-sm rounded-4 h-100 bg-white">
                <i class="bi bi-globe metric-icon-bg text-success"></i>
                <div class="card-body p-3 d-flex align-items-center gap-3 position-relative z-1">
                    <div class="icon-box bg-success bg-opacity-10 text-success">
                        <i class="bi bi-globe"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-0 fw-semibold">{{ __('Trade') }}</p>
                        <h3 class="fw-bolder mb-0 text-dark lh-1" id="tradeNewsCount" style="letter-spacing: -0.5px;">{{ $categoryStats['categories']['Trade']['count'] }}</h3>
                        <small class="text-success fw-bold d-block mt-1" style="font-size: 0.7rem;"><i class="bi bi-arrow-up-short"></i> 5% from yesterday</small>
                    </div>
                </div>
            </div>
        </div>
        <!-- Economy -->
        <div class="col">
            <div class="card metric-card shadow-sm rounded-4 h-100 bg-white">
                <i class="bi bi-graph-up-arrow metric-icon-bg text-warning"></i>
                <div class="card-body p-3 d-flex align-items-center gap-3 position-relative z-1">
                    <div class="icon-box bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-0 fw-semibold">{{ __('Economy') }}</p>
                        <h3 class="fw-bolder mb-0 text-dark lh-1" id="economyNewsCount" style="letter-spacing: -0.5px;">{{ $categoryStats['categories']['Economy']['count'] }}</h3>
                        <small class="text-warning fw-bold d-block mt-1" style="font-size: 0.7rem;"><i class="bi bi-dash"></i> 0% from yesterday</small>
                    </div>
                </div>
            </div>
        </div>
        <!-- Logistics -->
        <div class="col">
            <div class="card metric-card shadow-sm rounded-4 h-100 bg-white">
                <i class="bi bi-truck metric-icon-bg text-danger"></i>
                <div class="card-body p-3 d-flex align-items-center gap-3 position-relative z-1">
                    <div class="icon-box bg-danger bg-opacity-10 text-danger">
                        <i class="bi bi-truck"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-0 fw-semibold">{{ __('Logistics') }}</p>
                        <h3 class="fw-bolder mb-0 text-dark lh-1" id="logisticsNewsCount" style="letter-spacing: -0.5px;">{{ $categoryStats['categories']['Logistics']['count'] }}</h3>
                        <small class="text-danger fw-bold d-block mt-1" style="font-size: 0.7rem;"><i class="bi bi-arrow-down-short"></i> 3% from yesterday</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Layout -->
    <div class="row g-4">
        
        <!-- Left Sidebar: Filters & AI Insight -->
        <div class="col-xl-4 col-lg-5 order-1">
            
            <!-- Filters -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-funnel me-2 text-primary"></i>{{ __('Filter News') }}</h6>
                </div>
                <div class="card-body pt-0">
                    <form id="newsFilterForm">
                        <div class="mb-3">
                            <label class="form-label small text-muted fw-semibold">{{ __('Search Keyword') }}</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" name="search" id="searchInput" class="form-control bg-light border-start-0" placeholder="Keywords..." value="{{ $search }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted fw-semibold">{{ __('Category') }}</label>
                            <select name="category" id="categorySelect" class="form-select form-select-sm bg-light">
                                <option value="All Categories" {{ $category == 'All Categories' ? 'selected' : '' }}>{{ __('All Categories') }}</option>
                                <option value="Logistics" {{ $category == 'Logistics' ? 'selected' : '' }}>{{ __('Logistics') }}</option>
                                <option value="Trade" {{ $category == 'Trade' ? 'selected' : '' }}>{{ __('Trade') }}</option>
                                <option value="Shipping" {{ $category == 'Shipping' ? 'selected' : '' }}>{{ __('Shipping') }}</option>
                                <option value="Economy" {{ $category == 'Economy' ? 'selected' : '' }}>{{ __('Economy') }}</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted fw-semibold">{{ __('Country') }}</label>
                            <select name="country" id="countrySelect" class="form-select form-select-sm bg-light">
                                <option value="All Countries" {{ $countryId == 'All Countries' ? 'selected' : '' }}>{{ __('All Countries') }}</option>
                                @foreach($countries as $c)
                                    <option value="{{ $c->id }}" {{ $countryId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sentiment & AI Insight -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4 bg-primary bg-opacity-10 rounded-4 border border-primary border-opacity-10">
                    <div class="d-flex align-items-center gap-2 mb-4">
                        <div class="bg-primary bg-opacity-25 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-robot"></i>
                        </div>
                        <h6 class="fw-bold mb-0 text-dark">{{ __('AI Market Insight') }}</h6>
                    </div>
                    
                    @php
                        $aiOverall = $sentimentStats['overall_sentiment'] ?? 'NEUTRAL';
                        $aiColorClass = $aiOverall === 'POSITIVE' ? 'text-success' : ($aiOverall === 'NEGATIVE' ? 'text-danger' : 'text-warning');
                        $aiColorHex = $aiOverall === 'POSITIVE' ? '#198754' : ($aiOverall === 'NEGATIVE' ? '#dc3545' : '#ffc107');
                    @endphp
                    
                    <!-- Overall Sentiment -->
                    <div class="mb-4">
                        <p class="text-muted small fw-semibold mb-1">{{ __('Overall Market Sentiment') }}</p>
                        <h2 class="fw-bolder {{ $aiColorClass }} mb-2 text-uppercase">{{ $aiOverall }}</h2>
                        <div style="height: 60px; width: 100%; opacity: 0.8;">
                            <svg viewBox="0 0 100 30" preserveAspectRatio="none" style="width: 100%; height: 100%;">
                                <defs>
                                    <linearGradient id="waveGradient" x1="0" x2="0" y1="0" y2="1">
                                        <stop offset="0%" stop-color="{{ $aiColorHex }}" stop-opacity="0.3"/>
                                        <stop offset="100%" stop-color="{{ $aiColorHex }}" stop-opacity="0"/>
                                    </linearGradient>
                                </defs>
                                <path d="M0,30 L0,26 L4,25 L8,26 L12,24 L16,25 L20,21 L24,23 L28,20 L32,18 L36,20 L40,16 L44,18 L48,14 L52,15 L56,11 L60,12 L64,8 L68,10 L72,6 L76,8 L80,4 L84,6 L88,3 L92,5 L96,2 L100,4 L100,30 Z" fill="url(#waveGradient)" stroke="{{ $aiColorHex }}" stroke-width="1.2" stroke-linejoin="round"/>
                            </svg>
                        </div>
                    </div>
                    
                    <hr class="text-muted opacity-25">
                    
                    <!-- Market Summary -->
                    <div class="mb-4 mt-3">
                        <h6 class="fw-bold text-dark mb-2" style="font-size: 0.9rem;">{{ __('Market Summary') }}</h6>
                        <p class="text-muted mb-0" style="font-size: 0.85rem; line-height: 1.6;">{{ $marketInsight['summary'] ?? 'Market is currently stable.' }}</p>
                    </div>
                    
                    <!-- Potential Impact -->
                    @if(!empty($marketInsight['potential_impact']))
                    <div class="mb-4">
                        <h6 class="fw-bold text-dark mb-3" style="font-size: 0.9rem;">{{ __('Potential Impact') }}</h6>
                        <ul class="list-unstyled mb-0 text-muted" style="font-size: 0.85rem;">
                            @foreach($marketInsight['potential_impact'] as $impact)
                            <li class="d-flex mb-2"><i class="bi bi-check-circle-fill text-success me-2 mt-1"></i> {{ $impact }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    
                    <!-- Recommendation -->
                    @if(!empty($marketInsight['recommendation']))
                    <div class="mb-4">
                        <h6 class="fw-bold text-dark mb-3" style="font-size: 0.9rem;">{{ __('Recommendation') }}</h6>
                        @php
                            $recIcons = ['bi-compass', 'bi-box-seam', 'bi-shield-check', 'bi-graph-up'];
                        @endphp
                        @foreach($marketInsight['recommendation'] as $idx => $rec)
                        <div class="d-flex gap-3 mb-3">
                            <div class="text-primary bg-white rounded-circle d-flex justify-content-center align-items-center flex-shrink-0 shadow-sm" style="width: 32px; height: 32px;">
                                <i class="bi {{ $recIcons[$idx % count($recIcons)] }}"></i>
                            </div>
                            <div class="text-muted" style="font-size: 0.85rem;">{{ $rec }}</div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                    
                    <div class="text-muted border-top border-primary border-opacity-10 pt-3" style="font-size: 0.7rem;">
                        {{ __('Insight generated from') }} {{ $categoryStats['total'] }} {{ __('news analyzed') }}<br>
                        {{ \Carbon\Carbon::now()->format('M d, Y h:i A') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Content: News Grid -->
        <div class="col-xl-8 col-lg-7 order-2">
            <div id="newsListContainer">
                @include('user.partials.news_list')
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let sentimentChartObj = null;

document.addEventListener("DOMContentLoaded", function() {
    initCharts(@json($sentimentStats));

    // Filter Trigger
    const formElements = document.querySelectorAll('#newsFilterForm input, #newsFilterForm select');
    formElements.forEach(el => {
        el.addEventListener('change', fetchNews);
    });
    
    // Search input needs debounce
    let typingTimer;
    document.getElementById('searchInput').addEventListener('keyup', function() {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(fetchNews, 500);
    });

    // Sync Button
    document.getElementById('syncNewsBtn').addEventListener('click', function() {
        const btn = this;
        const icon = btn.querySelector('i');
        btn.disabled = true;
        icon.classList.add('bi-spin');
        
        fetch('{{ route("user.news.sync") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            updateDashboard(data);
            btn.disabled = false;
            icon.classList.remove('bi-spin');
        })
        .catch(err => {
            console.error(err);
            btn.disabled = false;
            icon.classList.remove('bi-spin');
        });
    });
});

function fetchNews() {
    document.getElementById('loadingIndicator').style.display = 'inline-block';
    const form = document.getElementById('newsFilterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData).toString();

    fetch('{{ route("user.news") }}?' + params, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        updateDashboard(data);
        document.getElementById('loadingIndicator').style.display = 'none';
    });
}

function updateDashboard(data) {
    if(data.news_html) {
        document.getElementById('newsListContainer').innerHTML = data.news_html;
    }
    
    if(data.category_stats) {
        document.getElementById('totalNewsCount').innerText = data.category_stats.total;
        document.getElementById('shippingNewsCount').innerText = data.category_stats.categories.Shipping.count;
        document.getElementById('tradeNewsCount').innerText = data.category_stats.categories.Trade.count;
        document.getElementById('economyNewsCount').innerText = data.category_stats.categories.Economy.count;
        document.getElementById('logisticsNewsCount').innerText = data.category_stats.categories.Logistics.count;
    }

    if(data.sentiment_stats) {
        updateCharts(data.sentiment_stats);
        document.getElementById('ai-overall-sent').innerText = data.sentiment_stats.overall_sentiment;
    }
    
    if(data.market_insight) {
        document.getElementById('ai-insight-msg').innerText = data.market_insight.summary;
    }
}

function initCharts(sentStats) {
    const ctx2 = document.getElementById('sentimentChart');
    if(ctx2) {
        sentimentChartObj = new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['Positive', 'Neutral', 'Negative'],
                datasets: [{
                    data: [sentStats.positive_pct, sentStats.neutral_pct, sentStats.negative_pct],
                    backgroundColor: ['#198754', '#ffc107', '#dc3545'],
                    borderWidth: 0,
                    cutout: '75%'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }
}

function updateCharts(sentStats) {
    if(sentimentChartObj) {
        sentimentChartObj.data.datasets[0].data = [sentStats.positive_pct, sentStats.neutral_pct, sentStats.negative_pct];
        sentimentChartObj.update();
    }
}
</script>
<style>
.bi-spin { animation: spin 1s linear infinite; }
@keyframes spin { 100% { transform: rotate(360deg); } }
</style>
@endpush