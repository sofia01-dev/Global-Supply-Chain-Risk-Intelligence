@extends('layouts.app')

@section('title', __('News Intelligence'))

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h3 class="fw-bold mb-1 text-dark">{{ __('News Intelligence') }}</h3>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">{{ __('Global news analysis for Logistics, Trade, Shipping, and Economy') }}</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn px-4 shadow-sm rounded-3 text-white fw-bold" id="syncNewsBtn" style="background-color: var(--primary-navy); border: none;">
                <i class="bi bi-arrow-repeat me-2"></i>{{ __('Sync News') }}
            </button>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="row row-cols-2 row-cols-md-3 row-cols-xl-5 g-3 mb-4">
        <!-- Total -->
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-primary bg-opacity-10 d-flex align-items-center justify-content-center text-primary" style="width: 48px; height: 48px;">
                        <i class="bi bi-newspaper fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-0 fw-semibold">{{ __('Total News') }}</p>
                        <h4 class="fw-bold mb-0 text-dark" id="totalNewsCount">{{ $categoryStats['total'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <!-- Shipping -->
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: rgba(111, 66, 193, 0.1); color: #6f42c1;">
                        <i class="bi bi-box-seam fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-0 fw-semibold">{{ __('Shipping') }}</p>
                        <h4 class="fw-bold mb-0 text-dark" id="shippingNewsCount">{{ $categoryStats['categories']['Shipping']['count'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <!-- Trade -->
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-success bg-opacity-10 d-flex align-items-center justify-content-center text-success" style="width: 48px; height: 48px;">
                        <i class="bi bi-globe fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-0 fw-semibold">{{ __('Trade') }}</p>
                        <h4 class="fw-bold mb-0 text-dark" id="tradeNewsCount">{{ $categoryStats['categories']['Trade']['count'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <!-- Economy -->
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-warning bg-opacity-10 d-flex align-items-center justify-content-center text-warning" style="width: 48px; height: 48px;">
                        <i class="bi bi-graph-up-arrow fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-0 fw-semibold">{{ __('Economy') }}</p>
                        <h4 class="fw-bold mb-0 text-dark" id="economyNewsCount">{{ $categoryStats['categories']['Economy']['count'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <!-- Logistics -->
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-danger bg-opacity-10 d-flex align-items-center justify-content-center text-danger" style="width: 48px; height: 48px;">
                        <i class="bi bi-truck fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-0 fw-semibold">{{ __('Logistics') }}</p>
                        <h4 class="fw-bold mb-0 text-dark" id="logisticsNewsCount">{{ $categoryStats['categories']['Logistics']['count'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Layout -->
    <div class="row g-4">
        
        <!-- Right Sidebar: Filters & Insights (Moved to left for standard dashboard flow) -->
        <div class="col-xl-3 col-lg-4 order-2 order-lg-1">
            
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
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-4">{{ __('Global Market Sentiment') }}</h6>
                    
                    <div style="height: 180px; position: relative;">
                        <canvas id="sentimentChart"></canvas>
                        <!-- Center text -->
                        <div class="position-absolute top-50 start-50 translate-middle text-center" style="pointer-events: none;">
                            <span class="fw-bolder fs-4 text-dark" id="ai-overall-sent">{{ $sentimentStats['overall_sentiment'] }}</span>
                        </div>
                    </div>
                    
                    <div class="mt-4 p-3 bg-primary bg-opacity-10 rounded-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-lightning-charge-fill text-warning"></i>
                            <span class="fw-bold text-dark" style="font-size: 0.8rem;">{{ __('AI Market Insight') }}</span>
                        </div>
                        <p class="mb-0 text-muted" style="font-size: 0.75rem;" id="ai-insight-msg">{{ $marketInsight['summary'] ?? 'Market is currently stable.' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Left Content: News Grid -->
        <div class="col-xl-9 col-lg-8 order-1 order-lg-2">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-dark mb-0">{{ __('Latest Articles') }}</h5>
                <span class="text-muted small" id="loadingIndicator" style="display: none;"><i class="bi bi-arrow-repeat bi-spin me-1"></i> Filtering...</span>
            </div>
            
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