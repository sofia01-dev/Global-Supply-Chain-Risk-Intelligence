@extends('layouts.app')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1 text-dark">{{ __('Country Comparison Engine') }}</h3>
        <p class="text-muted small mb-0">{{ __('Compare economic, risk, weather and currency indicators between countries.') }}</p>
    </div>
</div>

<!-- Selectors -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form id="compareForm" class="d-flex align-items-center justify-content-between w-100 m-0">
            <!-- Country A -->
            <div class="flex-grow-1 mx-2">
                <label class="form-label text-muted small fw-bold mb-1">{{ __('Select Country A') }}</label>
                <div class="input-group">
                    <select name="country_a" id="country_a" class="form-select border rounded-3 bg-white shadow-sm" required style="height: 45px;">
                        <option value="">{{ __('Select Country...') }}</option>
                        @foreach($countries as $c)
                            <option value="{{ $c->id }}" data-iso2="{{ $c->iso2_code }}">{{ $c->name }} &nbsp; ({{ $c->iso3_code }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <!-- VS Separator -->
            <div class="px-4 text-center d-flex align-items-center mt-4">
                <i class="bi bi-arrow-left text-muted me-3"></i>
                <div class="d-inline-flex align-items-center justify-content-center bg-white rounded-circle text-dark fw-bold shadow-sm border" style="width: 45px; height: 45px;">
                    VS
                </div>
                <i class="bi bi-arrow-right text-muted ms-3"></i>
            </div>

            <!-- Country B -->
            <div class="flex-grow-1 mx-2">
                <label class="form-label text-muted small fw-bold mb-1">{{ __('Select Country B') }}</label>
                <div class="input-group">
                    <select name="country_b" id="country_b" class="form-select border rounded-3 bg-white shadow-sm" required style="height: 45px;">
                        <option value="">{{ __('Select Country...') }}</option>
                        @foreach($countries as $c)
                            <option value="{{ $c->id }}" data-iso2="{{ $c->iso2_code }}">{{ $c->name }} &nbsp; ({{ $c->iso3_code }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Compare Button -->
            <div class="mx-2" style="margin-top: 25px;">
                <button type="submit" id="compareBtn" class="btn px-4 fw-bold shadow-sm rounded-3 d-flex align-items-center justify-content-center text-white" style="height: 45px; min-width: 150px; background-color: var(--primary-navy); border: none;">
                    <i class="bi bi-graph-up-arrow me-2"></i>{{ __('Compare Now') }}
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Loading Skeleton -->
<div id="loadingSkeleton" class="d-none text-center py-5 my-5">
    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
        <span class="visually-hidden">Loading...</span>
    </div>
    <h5 class="text-muted mt-3 fw-bold">{{ __('Analyzing and fetching data...') }}</h5>
</div>

<!-- Comparison Content (Hidden initially) -->
<div id="comparisonContent" class="d-none">
    
    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <!-- GDP -->
        <div class="col-md">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary bg-opacity-10 rounded text-primary p-2 me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <span class="fw-bold text-dark small">GDP (Nominal)</span>
                        <i class="bi bi-info-circle text-muted ms-auto" style="font-size: 0.8rem;"></i>
                    </div>
                    <div class="row g-0">
                        <div class="col-6 border-end pe-2">
                            <div class="fs-5 fw-bold text-primary" id="gdpA">-</div>
                            <div class="small text-muted" id="nameA_gdp">-</div>
                        </div>
                        <div class="col-6 ps-3">
                            <div class="fs-5 fw-bold text-warning" id="gdpB">-</div>
                            <div class="small text-muted" id="nameB_gdp">-</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Inflation -->
        <div class="col-md">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-success bg-opacity-10 rounded text-success p-2 me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <span class="fw-bold text-dark small">Inflation Rate (CPI)</span>
                        <i class="bi bi-info-circle text-muted ms-auto" style="font-size: 0.8rem;"></i>
                    </div>
                    <div class="row g-0">
                        <div class="col-6 border-end pe-2">
                            <div class="fs-5 fw-bold text-success" id="infA">-</div>
                            <div class="small text-muted" id="nameA_inf">-</div>
                        </div>
                        <div class="col-6 ps-3">
                            <div class="fs-5 fw-bold text-warning" id="infB">-</div>
                            <div class="small text-muted" id="nameB_inf">-</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Risk Score -->
        <div class="col-md">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-danger bg-opacity-10 rounded text-danger p-2 me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-shield-exclamation"></i>
                        </div>
                        <span class="fw-bold text-dark small">Risk Score (Overall)</span>
                        <i class="bi bi-info-circle text-muted ms-auto" style="font-size: 0.8rem;"></i>
                    </div>
                    <div class="row g-0">
                        <div class="col-6 border-end pe-2">
                            <div class="fs-5 fw-bold text-success" id="riskA">-</div>
                            <div class="small fw-bold text-warning" id="riskLabelA">-</div>
                        </div>
                        <div class="col-6 ps-3">
                            <div class="fs-5 fw-bold text-danger" id="riskB">-</div>
                            <div class="small fw-bold text-danger" id="riskLabelB">-</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Weather -->
        <div class="col-md">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-info bg-opacity-10 rounded text-info p-2 me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-cloud-sun"></i>
                        </div>
                        <span class="fw-bold text-dark small">Weather (Today)</span>
                        <i class="bi bi-info-circle text-muted ms-auto" style="font-size: 0.8rem;"></i>
                    </div>
                    <div class="row g-0">
                        <div class="col-6 border-end pe-2">
                            <div class="fs-5 fw-bold text-primary" id="weatherA">-</div>
                            <div class="small text-muted" id="weatherDescA">-</div>
                        </div>
                        <div class="col-6 ps-3">
                            <div class="fs-5 fw-bold text-warning" id="weatherB">-</div>
                            <div class="small text-muted" id="weatherDescB">-</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Currency -->
        <div class="col-md">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-purple bg-opacity-10 rounded text-purple p-2 me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; color: #6f42c1; background-color: rgba(111, 66, 193, 0.1);">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                        <span class="fw-bold text-dark small" style="white-space: nowrap;">Currency (vs USD)</span>
                        <i class="bi bi-info-circle text-muted ms-auto" style="font-size: 0.8rem;"></i>
                    </div>
                    <div class="row g-0">
                        <div class="col-6 border-end pe-2">
                            <div class="fs-6 fw-bold text-primary" id="currA">-</div>
                            <div class="small fw-bold text-success" id="currChangeA">-</div>
                        </div>
                        <div class="col-6 ps-3">
                            <div class="fs-6 fw-bold text-warning" id="currB">-</div>
                            <div class="small fw-bold text-danger" id="currChangeB">-</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 1: Radar & Currency -->
    <div class="row g-4 mb-4">
        <!-- Radar & Insight -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-4 text-center">{{ __('Radar Comparison') }}</h6>
                    <div class="row align-items-center">
                        <div class="col-md-6 border-end-md">
                            <div style="height: 300px; display: flex; justify-content: center;">
                                <canvas id="radarChart"></canvas>
                            </div>
                        </div>
                        <div class="col-md-6 ps-md-4 mt-4 mt-md-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-borderless small align-middle mb-0">
                                    <thead>
                                        <tr class="text-muted border-bottom">
                                            <th>Indicator</th>
                                            <th class="text-end" id="radarNameA">A</th>
                                            <th class="text-end" id="radarNameB">B</th>
                                        </tr>
                                    </thead>
                                    <tbody id="radarSummaryTable">
                                        <!-- Populated via JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Currency Trend -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-4">{{ __('Currency Trend (7 Days)') }}</h6>
                    <div class="mb-5">
                        <div class="d-flex justify-content-between align-items-end mb-2">
                            <div class="small fw-bold" id="cNameA">-</div>
                            <div class="small fw-bold" style="font-size: 0.75rem;" id="cTrendA"></div>
                        </div>
                        <div style="height: 110px;"><canvas id="lineCurrA"></canvas></div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between align-items-end mb-2">
                            <div class="small fw-bold" id="cNameB">-</div>
                            <div class="small fw-bold" style="font-size: 0.75rem;" id="cTrendB"></div>
                        </div>
                        <div style="height: 110px;"><canvas id="lineCurrB"></canvas></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: Risk Factors & News Sentiment -->
    <div class="row g-4 mb-4">
        <!-- Risk Factors -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-4">{{ __('Risk Factor Comparison') }}</h6>
                    <div class="table-responsive">
                        <table class="table table-borderless table-sm mb-0 align-middle">
                            <thead>
                                <tr class="text-dark small border-bottom">
                                    <th class="fw-bold py-3">Risk Factor</th>
                                    <th class="text-center fw-bold py-3" id="rNameA">Country A</th>
                                    <th class="text-center fw-bold py-3" id="rNameB">Country B</th>
                                </tr>
                            </thead>
                            <tbody id="riskTableBody">
                                <!-- Populated via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- News Sentiment -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-4">{{ __('Recent News Sentiment (Lexicon Based)') }}</h6>
                    <div class="row align-items-center h-100">
                        <div class="col-5 text-center">
                            <div class="fw-bold mb-3" id="nNameA">-</div>
                            <div style="height: 200px; display: flex; justify-content: center;"><canvas id="doughnutNewsA"></canvas></div>
                            <div class="d-flex justify-content-center gap-3 mt-3 fw-bold" style="font-size: 0.9rem;" id="nStatsA"></div>
                        </div>
                        <div class="col-2 text-center text-muted fw-bold">VS</div>
                        <div class="col-5 text-center">
                            <div class="fw-bold mb-3" id="nNameB">-</div>
                            <div style="height: 200px; display: flex; justify-content: center;"><canvas id="doughnutNewsB"></canvas></div>
                            <div class="d-flex justify-content-center gap-3 mt-3 fw-bold" style="font-size: 0.9rem;" id="nStatsB"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 3: AI Recommendation -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-primary bg-opacity-10 border border-primary border-opacity-25">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-robot text-primary fs-4 me-2"></i>
                        <h6 class="fw-bold text-primary mb-0">{{ __('AI Recommendation Engine') }}</h6>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="fw-bold text-dark mb-2 small">{{ __('Overall Comparison') }}</div>
                            <div class="bg-white rounded p-3 mb-3 shadow-sm small text-dark h-100" id="aiOverall">-</div>
                        </div>
                        <div class="col-md-4">
                            <div class="fw-bold text-dark mb-2 small">{{ __('Key Insights') }}</div>
                            <ul class="list-unstyled mb-3 small text-dark lh-lg bg-white rounded p-3 shadow-sm h-100" id="aiInsights">
                                <!-- Populated via JS -->
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <div class="fw-bold text-dark mb-2 small">{{ __('Recommendation') }}</div>
                            <div class="text-muted small fst-italic bg-white rounded p-3 shadow-sm h-100" id="aiRec">-</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Select2 Bootstrap 5 Theme overrides for this specific form */
    .select2-container .select2-selection--single {
        height: 45px;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        display: flex;
        align-items: center;
        background-color: #fff;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 45px;
        padding-left: 12px;
        color: #212529;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 43px;
        right: 10px;
    }
    .select2-results__option {
        padding: 8px 12px;
    }
</style>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function formatCountry(country) {
        if (!country.id) { return country.text; }
        var iso2 = $(country.element).data('iso2');
        if(!iso2) return country.text;
        
        var $country = $(
            '<span><img src="https://flagcdn.com/w20/' + iso2.toLowerCase() + '.png" class="img-flag me-2" style="width:20px;" /> ' + country.text + '</span>'
        );
        return $country;
    }

    $(document).ready(function() {
        $('#country_a, #country_b').select2({
            templateResult: formatCountry,
            templateSelection: formatCountry,
            width: '100%'
        });
    });

    let charts = {};

    function getBadge(level, extra = '') {
        let extText = extra ? ` <span class="ms-1">${extra}</span>` : '';
        const baseStyle = 'badge px-2 rounded-3';
        const customStyle = 'style="padding-top: 4px; padding-bottom: 4px; font-size: 0.75rem;"';
        
        if(level === 'Low') return `<span class="${baseStyle} bg-success bg-opacity-10 text-success" ${customStyle}>Low${extText}</span>`;
        if(level === 'Medium') return `<span class="${baseStyle} bg-warning bg-opacity-10 text-warning" ${customStyle}>Medium${extText}</span>`;
        if(level === 'High') return `<span class="${baseStyle} bg-danger bg-opacity-10 text-danger" ${customStyle}>High${extText}</span>`;
        return `<span class="${baseStyle} bg-secondary bg-opacity-10 text-secondary" ${customStyle}>-</span>`;
    }

    function getIcon(iconClass, colorClass = 'primary') {
        return `<div class="d-inline-flex align-items-center justify-content-center bg-${colorClass} bg-opacity-10 text-${colorClass} rounded-2 me-2" style="width: 24px; height: 24px; font-size: 0.8rem;"><i class="bi ${iconClass}"></i></div>`;
    }

    function initBarChart(id, valA, valB, nameA, nameB, colorA, colorB) {
        if(charts[id]) charts[id].destroy();
        const ctx = document.getElementById(id).getContext('2d');
        charts[id] = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [nameA, nameB],
                datasets: [{
                    data: [valA, valB],
                    backgroundColor: [colorA, colorB],
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { 
                    y: { beginAtZero: true, display: false },
                    x: { grid: { display: false }, ticks: { font: {size: 10} } }
                }
            }
        });
    }

    function initLineChart(id, data, color) {
        if(charts[id]) charts[id].destroy();
        const ctx = document.getElementById(id).getContext('2d');
        
        let gradient = ctx.createLinearGradient(0, 0, 0, 80);
        gradient.addColorStop(0, color + '50'); // ~30% opacity
        gradient.addColorStop(1, color + '00'); // 0% opacity
        
        const minVal = Math.min(...data);
        const maxVal = Math.max(...data);
        const buffer = (maxVal - minVal) * 0.1 || minVal * 0.01;

        charts[id] = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map((_, i) => i === data.length - 1 ? 'Today' : (data.length - 1 - i) + ' Days Ago'),
                datasets: [{
                    data: data,
                    borderColor: color,
                    backgroundColor: gradient,
                    borderWidth: 2,
                    pointBackgroundColor: color,
                    pointBorderColor: '#fff',
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    pointHoverBorderWidth: 2,
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
                        backgroundColor: 'rgba(255,255,255,0.9)',
                        titleColor: '#333',
                        bodyColor: color,
                        borderColor: '#e9ecef',
                        borderWidth: 1,
                        padding: 10,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y.toLocaleString(undefined, {minimumFractionDigits: 2});
                            }
                        }
                    }
                },
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: { 
                    x: { 
                        display: true, 
                        grid: { display: false, drawBorder: false },
                        ticks: { 
                            font: { size: 9 }, 
                            color: '#adb5bd',
                            maxTicksLimit: 3
                        }
                    }, 
                    y: { 
                        display: false,
                        min: minVal - buffer,
                        max: maxVal + buffer
                    } 
                }
            }
        });
    }

    function initDoughnutChart(id, pos, neu, neg) {
        if(charts[id]) charts[id].destroy();
        const ctx = document.getElementById(id).getContext('2d');
        charts[id] = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Positive', 'Neutral', 'Negative'],
                datasets: [{
                    data: [pos, neu, neg],
                    backgroundColor: ['#198754', '#ffc107', '#dc3545'],
                    borderWidth: 0,
                    cutout: '70%'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });
    }

    function initRadarChart(id, dataA, dataB, nameA, nameB) {
        if(charts[id]) charts[id].destroy();
        const ctx = document.getElementById(id).getContext('2d');
        
        // Normalize logic (simple relative scaling out of 100)
        const maxGdp = Math.max(dataA.gdp, dataB.gdp, 1);
        const maxInf = Math.max(dataA.inflation, dataB.inflation, 1);
        const maxTemp = Math.max(dataA.weather.temp, dataB.weather.temp, 1);
        
        const normA = [
            (dataA.gdp / maxGdp) * 100,
            (dataA.inflation / maxInf) * 100,
            dataA.risk.overall,
            (dataA.weather.temp / maxTemp) * 100,
            50 // Currency is hard to normalize directly on radar
        ];
        
        const normB = [
            (dataB.gdp / maxGdp) * 100,
            (dataB.inflation / maxInf) * 100,
            dataB.risk.overall,
            (dataB.weather.temp / maxTemp) * 100,
            50
        ];

        charts[id] = new Chart(ctx, {
            type: 'radar',
            data: {
                labels: ['GDP', 'Inflation', 'Risk Score', 'Weather Temp', 'Currency Volatility'],
                datasets: [
                    {
                        label: nameA,
                        data: normA,
                        backgroundColor: 'rgba(13, 110, 253, 0.2)',
                        borderColor: 'rgba(13, 110, 253, 1)',
                        pointBackgroundColor: 'rgba(13, 110, 253, 1)',
                        borderWidth: 2
                    },
                    {
                        label: nameB,
                        data: normB,
                        backgroundColor: 'rgba(220, 53, 69, 0.2)',
                        borderColor: 'rgba(220, 53, 69, 1)',
                        pointBackgroundColor: 'rgba(220, 53, 69, 1)',
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        beginAtZero: true,
                        max: 100,
                        ticks: { display: false }
                    }
                },
                plugins: {
                    legend: { position: 'top', labels: { boxWidth: 10, font: {size: 10} } }
                }
            }
        });
    }

    document.getElementById('compareForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const ca = document.getElementById('country_a').value;
        const cb = document.getElementById('country_b').value;
        const btn = document.getElementById('compareBtn');
        
        if(ca === cb) {
            alert('Please select two different countries to compare.');
            return;
        }

        btn.disabled = true;
        document.getElementById('comparisonContent').classList.add('d-none');
        document.getElementById('loadingSkeleton').classList.remove('d-none');

        fetch(`{{ route('user.comparison.ajax') }}?country_a=${ca}&country_b=${cb}`)
            .then(res => res.json())
            .then(data => {
                if(data.error) {
                    alert(data.error);
                    return;
                }
                
                const A = data.countryA;
                const B = data.countryB;

                // Populate Summary Cards
                document.getElementById('gdpA').innerText = A.gdp + 'T';
                document.getElementById('gdpB').innerText = B.gdp + 'T';
                document.getElementById('nameA_gdp').innerText = A.name;
                document.getElementById('nameB_gdp').innerText = B.name;

                document.getElementById('infA').innerText = A.inflation + '%';
                document.getElementById('infB').innerText = B.inflation + '%';
                document.getElementById('nameA_inf').innerText = A.name;
                document.getElementById('nameB_inf').innerText = B.name;

                document.getElementById('riskA').innerText = A.risk.overall;
                document.getElementById('riskB').innerText = B.risk.overall;
                document.getElementById('riskLabelA').innerHTML = getBadge(A.risk.overall_label);
                document.getElementById('riskLabelB').innerHTML = getBadge(B.risk.overall_label);

                document.getElementById('weatherA').innerText = A.weather.temp + '°C';
                document.getElementById('weatherB').innerText = B.weather.temp + '°C';
                document.getElementById('weatherDescA').innerText = A.weather.desc;
                document.getElementById('weatherDescB').innerText = B.weather.desc;

                document.getElementById('currA').innerText = A.currency.rate;
                document.getElementById('currB').innerText = B.currency.rate;
                
                const formatChange = c => {
                    if(c > 0) return `<span class="text-success"><i class="bi bi-caret-up-fill"></i> ${c}%</span>`;
                    if(c < 0) return `<span class="text-danger"><i class="bi bi-caret-down-fill"></i> ${Math.abs(c)}%</span>`;
                    return `<span class="text-muted">- 0%</span>`;
                };
                document.getElementById('currChangeA').innerHTML = formatChange(A.currency.change) + ` <span class="text-muted fw-normal">${A.currency_code}/USD</span>`;
                document.getElementById('currChangeB').innerHTML = formatChange(B.currency.change) + ` <span class="text-muted fw-normal">${B.currency_code}/USD</span>`;

                // Draw Charts
                initRadarChart('radarChart', A, B, A.name, B.name);

                // Populate Radar Simple Table
                document.getElementById('radarNameA').innerText = A.name;
                document.getElementById('radarNameB').innerText = B.name;

                const radarInsightsHtml = `
                    <tr><td class="text-muted"><i class="bi bi-graph-up text-primary me-2"></i>GDP</td><td class="text-end fw-bold">${A.gdp}T</td><td class="text-end fw-bold">${B.gdp}T</td></tr>
                    <tr><td class="text-muted"><i class="bi bi-percent text-success me-2"></i>Inflation</td><td class="text-end fw-bold">${A.inflation}%</td><td class="text-end fw-bold">${B.inflation}%</td></tr>
                    <tr><td class="text-muted"><i class="bi bi-shield-exclamation text-danger me-2"></i>Risk Score</td><td class="text-end fw-bold">${A.risk.overall}</td><td class="text-end fw-bold">${B.risk.overall}</td></tr>
                    <tr><td class="text-muted"><i class="bi bi-cloud-sun text-info me-2"></i>Temperature</td><td class="text-end fw-bold">${A.weather.temp}°C</td><td class="text-end fw-bold">${B.weather.temp}°C</td></tr>
                    <tr><td class="text-muted"><i class="bi bi-currency-exchange text-warning me-2"></i>Currency</td><td class="text-end fw-bold">${A.currency.rate}</td><td class="text-end fw-bold">${B.currency.rate}</td></tr>
                `;
                
                document.getElementById('radarSummaryTable').innerHTML = radarInsightsHtml;

                // Currency Trend
                const formatTrendBadge = (change, rate, code) => {
                    let colorClass = change > 0 ? 'text-success' : (change < 0 ? 'text-danger' : 'text-muted');
                    let icon = change > 0 ? 'bi-arrow-up-right' : (change < 0 ? 'bi-arrow-down-right' : 'bi-dash');
                    let displayChange = change !== 0 ? Math.abs(change) + '%' : '0%';
                    return `<span class="text-muted fw-normal me-2">1 USD = ${rate}</span><span class="${colorClass}"><i class="bi ${icon}"></i> ${displayChange}</span>`;
                };

                document.getElementById('cNameA').innerText = A.name + ' (' + A.currency_code + ')';
                document.getElementById('cTrendA').innerHTML = formatTrendBadge(A.currency.change, A.currency.rate, A.currency_code);
                
                document.getElementById('cNameB').innerText = B.name + ' (' + B.currency_code + ')';
                document.getElementById('cTrendB').innerHTML = formatTrendBadge(B.currency.change, B.currency.rate, B.currency_code);
                
                initLineChart('lineCurrA', A.currency.history, '#198754');
                initLineChart('lineCurrB', B.currency.history, '#dc3545');

                // Risk Table
                document.getElementById('rNameA').innerText = A.name;
                document.getElementById('rNameB').innerText = B.name;
                
                const tableHtml = `
                    <tr class="align-middle border-bottom"><td class="text-dark fw-bold small py-1" style="font-size: 0.8rem;">${getIcon('bi-cloud')}Weather Risk</td><td class="text-center">${getBadge(A.risk.factors.weather)}</td><td class="text-center">${getBadge(B.risk.factors.weather)}</td></tr>
                    <tr class="align-middle border-bottom"><td class="text-dark fw-bold small py-1" style="font-size: 0.8rem;">${getIcon('bi-graph-up')}Economic Risk</td><td class="text-center">${getBadge(A.risk.factors.economic)}</td><td class="text-center">${getBadge(B.risk.factors.economic)}</td></tr>
                    <tr class="align-middle border-bottom"><td class="text-dark fw-bold small py-1" style="font-size: 0.8rem;">${getIcon('bi-newspaper')}News Sentiment Risk</td><td class="text-center">${getBadge(A.risk.factors.news)}</td><td class="text-center">${getBadge(B.risk.factors.news)}</td></tr>
                    <tr class="align-middle border-bottom"><td class="text-dark fw-bold small py-1" style="font-size: 0.8rem;">${getIcon('bi-currency-dollar')}Currency Risk</td><td class="text-center">${getBadge(A.risk.factors.currency)}</td><td class="text-center">${getBadge(B.risk.factors.currency)}</td></tr>
                    <tr class="align-middle border-bottom"><td class="text-dark fw-bold small py-1" style="font-size: 0.8rem;">${getIcon('bi-percent')}Inflation Risk</td><td class="text-center">${getBadge(A.risk.factors.economic)}</td><td class="text-center">${getBadge(B.risk.factors.economic)}</td></tr>
                    <tr class="align-middle"><td class="text-dark fw-bold py-2" style="font-size: 0.8rem;">${getIcon('bi-shield-check', 'danger')}Overall Risk</td><td class="text-center">${getBadge(A.risk.overall_label, `(${Math.round(A.risk.overall)}/100)`)}</td><td class="text-center">${getBadge(B.risk.overall_label, `(${Math.round(B.risk.overall)}/100)`)}</td></tr>
                `;
                document.getElementById('riskTableBody').innerHTML = tableHtml;

                // News Sentiment
                document.getElementById('nNameA').innerText = A.name;
                document.getElementById('nNameB').innerText = B.name;
                
                initDoughnutChart('doughnutNewsA', A.sentiment.positive_pct, A.sentiment.neutral_pct, A.sentiment.negative_pct);
                initDoughnutChart('doughnutNewsB', B.sentiment.positive_pct, B.sentiment.neutral_pct, B.sentiment.negative_pct);
                
                document.getElementById('nStatsA').innerHTML = `<div class="text-success fw-bold">${A.sentiment.positive_pct}%</div><div class="text-warning fw-bold">${A.sentiment.neutral_pct}%</div><div class="text-danger fw-bold">${A.sentiment.negative_pct}%</div>`;
                document.getElementById('nStatsB').innerHTML = `<div class="text-success fw-bold">${B.sentiment.positive_pct}%</div><div class="text-warning fw-bold">${B.sentiment.neutral_pct}%</div><div class="text-danger fw-bold">${B.sentiment.negative_pct}%</div>`;

                // AI Recommendation
                const rec = data.recommendation;
                document.getElementById('aiOverall').innerText = rec.overall;
                document.getElementById('aiInsights').innerHTML = rec.insights.map(i => `<li><i class="bi bi-check text-success me-2"></i>${i}</li>`).join('');
                document.getElementById('aiRec').innerText = rec.recommendation;

                // Show Data
                document.getElementById('loadingSkeleton').classList.add('d-none');
                document.getElementById('comparisonContent').classList.remove('d-none');
            })
            .catch(err => {
                alert("Error fetching data.");
                document.getElementById('loadingSkeleton').classList.add('d-none');
            })
            .finally(() => {
                btn.disabled = false;
            });
    });
</script>
@endpush