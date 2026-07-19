@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Area is handled by layouts.app top-navbar, but we add our title here -->
    <div class="d-flex justify-content-end align-items-end mb-3">
        <div class="d-none d-md-flex align-items-center gap-2">
            <!-- Sync Time Indicator -->
            <div class="bg-white rounded px-3 py-2 shadow-sm d-flex align-items-center gap-2 border" style="font-size: 0.75rem;">
                <i class="bi bi-calendar3 text-muted"></i>
                <span id="dashboard-time-indicator" class="fw-semibold text-dark">{{ now()->format('d M Y, H:i') }} WIB</span>
                <i class="bi bi-arrow-repeat text-muted ms-2" style="cursor:pointer;" onclick="fetchDashboardData()" title="Force Sync"></i>
            </div>
        </div>
    </div>

    <!-- ROW 1: Summary Cards -->
    <div class="row row-cols-1 row-cols-md-3 row-cols-xl-5 g-3 mb-4" id="summary-container">
        <!-- 1. Countries Monitored -->
        <div class="col">
            <div class="card h-100 border-0 shadow-sm rounded-4 summary-card">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-3 bg-primary bg-opacity-10 d-flex align-items-center justify-content-center text-primary" style="width: 45px; height: 45px;">
                            <i class="bi bi-globe fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1" style="font-size: 0.7rem;">{{ __('Countries Monitored') }}</h6>
                            <h3 class="fw-bold text-dark mb-0" id="val-countries">{{ $summary['countries_monitored']['value'] ?? 0 }}</h3>
                        </div>
                    </div>
                    <div class="mt-3 d-flex justify-content-between align-items-end">
                        <small class="fw-semibold text-{{ ($summary['countries_monitored']['trend'] ?? 'up') == 'up' ? 'success' : 'danger' }}" style="font-size: 0.7rem;" id="growth-countries">
                            <i class="bi bi-arrow-{{ $summary['countries_monitored']['trend'] ?? 'up' }}-short"></i> {{ $summary['countries_monitored']['growth'] ?? '0' }}
                        </small>
                        <div style="width: 60px; height: 25px;"><canvas id="spark-countries"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. High Risk Countries -->
        <div class="col">
            <div class="card h-100 border-0 shadow-sm rounded-4 summary-card">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-3 bg-danger bg-opacity-10 d-flex align-items-center justify-content-center text-danger" style="width: 45px; height: 45px;">
                            <i class="bi bi-exclamation-triangle fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1" style="font-size: 0.7rem;">{{ __('High Risk Countries') }}</h6>
                            <h3 class="fw-bold text-dark mb-0" id="val-high-risk">{{ $summary['high_risk']['value'] ?? 0 }}</h3>
                        </div>
                    </div>
                    <div class="mt-3 d-flex justify-content-between align-items-end">
                        <small class="fw-semibold text-{{ ($summary['high_risk']['trend'] ?? 'up') == 'up' ? 'danger' : 'success' }}" style="font-size: 0.7rem;" id="growth-high-risk">
                            <i class="bi bi-arrow-{{ $summary['high_risk']['trend'] ?? 'up' }}-short"></i> {{ $summary['high_risk']['growth'] ?? '0' }}
                        </small>
                        <div style="width: 60px; height: 25px;"><canvas id="spark-high-risk"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Global News Today -->
        <div class="col">
            <div class="card h-100 border-0 shadow-sm rounded-4 summary-card">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-3 bg-info bg-opacity-10 d-flex align-items-center justify-content-center text-info" style="width: 45px; height: 45px;">
                            <i class="bi bi-newspaper fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1" style="font-size: 0.7rem;">{{ __('Global News Today') }}</h6>
                            <h3 class="fw-bold text-dark mb-0" id="val-global-news">{{ $summary['global_news']['value'] ?? 0 }}</h3>
                        </div>
                    </div>
                    <div class="mt-3 d-flex justify-content-between align-items-end">
                        <small class="fw-semibold text-{{ ($summary['global_news']['trend'] ?? 'up') == 'up' ? 'success' : 'danger' }}" style="font-size: 0.7rem;" id="growth-global-news">
                            <i class="bi bi-arrow-{{ $summary['global_news']['trend'] ?? 'up' }}-short"></i> {{ $summary['global_news']['growth'] ?? '0' }}
                        </small>
                        <div style="width: 60px; height: 25px;"><canvas id="spark-global-news"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Weather Alerts -->
        <div class="col">
            <div class="card h-100 border-0 shadow-sm rounded-4 summary-card">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-3 bg-success bg-opacity-10 d-flex align-items-center justify-content-center text-success" style="width: 45px; height: 45px;">
                            <i class="bi bi-cloud-lightning-rain fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1" style="font-size: 0.7rem;">{{ __('Weather Alerts') }}</h6>
                            <h3 class="fw-bold text-dark mb-0" id="val-weather">{{ $summary['weather_alerts']['value'] ?? 0 }}</h3>
                        </div>
                    </div>
                    <div class="mt-3 d-flex justify-content-between align-items-end">
                        <small class="fw-semibold text-{{ ($summary['weather_alerts']['trend'] ?? 'down') == 'down' ? 'success' : 'danger' }}" style="font-size: 0.7rem;" id="growth-weather">
                            <i class="bi bi-arrow-{{ $summary['weather_alerts']['trend'] ?? 'down' }}-short"></i> {{ $summary['weather_alerts']['growth'] ?? '0' }}
                        </small>
                        <div style="width: 60px; height: 25px;"><canvas id="spark-weather"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. Currency Volatility -->
        <div class="col">
            <div class="card h-100 border-0 shadow-sm rounded-4 summary-card">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-3 bg-warning bg-opacity-10 d-flex align-items-center justify-content-center text-warning" style="width: 45px; height: 45px;">
                            <i class="bi bi-currency-exchange fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1" style="font-size: 0.7rem;">{{ __('Currency Volatility') }}</h6>
                            <h3 class="fw-bold text-dark mb-0" id="val-currency">{{ $summary['currency_volatility']['value'] ?? 'Medium' }}</h3>
                        </div>
                    </div>
                    <div class="mt-3 d-flex justify-content-between align-items-end">
                        <small class="fw-semibold text-warning" style="font-size: 0.7rem;" id="growth-currency">
                            <i class="bi bi-arrow-{{ $summary['currency_volatility']['trend'] ?? 'up' }}-short"></i> {{ $summary['currency_volatility']['growth'] ?? '0%' }}
                        </small>
                        <div style="width: 60px; height: 25px;"><canvas id="spark-currency"></canvas></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ROW 2: Map & Top Risk Countries -->
    <div class="row g-4 mb-4">
        <!-- Global Risk Map -->
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">{{ __('Global Supply Chain Risk Map') }} <i class="bi bi-info-circle text-muted ms-1" style="font-size: 0.8rem;"></i></h6>
                    <a href="{{ route('user.countries.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">{{ __('View Full Map') }}</a>
                </div>
                <div class="card-body p-0 position-relative">
                    <div id="worldMap" style="height: 400px; width: 100%;" class="rounded-bottom-4"></div>
                    
                    <!-- Legend -->
                    <div class="position-absolute bottom-0 start-0 p-3" style="z-index: 500;">
                        <div class="bg-white p-2 rounded-3 shadow-sm" style="font-size: 0.7rem;">
                            <div class="d-flex align-items-center gap-2 mb-1"><span class="badge rounded-pill bg-success p-1" style="width:8px;height:8px;">&nbsp;</span> {{ __('Low Risk') }}</div>
                            <div class="d-flex align-items-center gap-2 mb-1"><span class="badge rounded-pill bg-warning p-1" style="width:8px;height:8px;">&nbsp;</span> {{ __('Medium Risk') }}</div>
                            <div class="d-flex align-items-center gap-2 mb-1"><span class="badge rounded-pill bg-orange p-1" style="width:8px;height:8px;background-color:#fd7e14;">&nbsp;</span> {{ __('High Risk') }}</div>
                            <div class="d-flex align-items-center gap-2"><span class="badge rounded-pill bg-danger p-1" style="width:8px;height:8px;">&nbsp;</span> {{ __('Critical Risk') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Top Risk Countries -->
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">{{ __('Top Risk Countries') }} <i class="bi bi-info-circle text-muted ms-1" style="font-size: 0.8rem;"></i></h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-borderless align-middle mb-0" id="top-risk-table">
                            <thead class="border-bottom text-muted" style="font-size: 0.75rem;">
                                <tr>
                                    <th class="ps-4">No.</th>
                                    <th>{{ __('Country') }}</th>
                                    <th>{{ __('Risk Level') }}</th>
                                    <th>{{ __('Score') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topRiskCountries as $index => $rc)
                                <tr>
                                    <td class="ps-4 fw-medium py-4 fs-6">{{ $index + 1 }}</td>
                                    <td class="py-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <span style="font-size:1.5rem;">
                                                @php
                                                    $code = strtolower($rc->country->iso2_code ?? 'id');
                                                @endphp
                                                <img src="https://flagcdn.com/32x24/{{ $code }}.png" alt="{{ $code }}" class="rounded shadow-sm">
                                            </span>
                                            <span class="fw-bold text-dark" style="font-size:1rem;">{{ $rc->country->name ?? 'Unknown' }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4">
                                        @php
                                            $level = $rc->risk_level ?? 'Low';
                                            $bColor = $level == 'Critical' ? 'danger' : ($level == 'High' ? 'orange' : ($level == 'Medium' ? 'warning' : 'success'));
                                            if($bColor == 'orange') $bColorStyle = "background-color: #fd7e14; color: white;";
                                            else $bColorStyle = "";
                                        @endphp
                                        <span class="badge bg-{{ $bColor }} px-3 py-2" style="{{ $bColorStyle }} font-size: 0.85rem;">{{ __($level) }}</span>
                                    </td>
                                    <td class="fw-bold py-4 fs-5 text-dark">{{ $rc->final_score ?? 0 }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ROW 3: Risk Trend & Currency Trend -->
    <div class="row g-4 mb-4">
        <!-- Risk Trend -->
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">{{ __('Global Supply Chain Risk Trend') }} <i class="bi bi-info-circle text-muted ms-1" style="font-size: 0.8rem;"></i></h6>
                    <select class="form-select form-select-sm border-0 bg-light rounded-pill px-3" style="width: 90px;">
                        <option>7 Days</option>
                    </select>
                </div>
                <div class="card-body p-3">
                    @if(empty($riskTrendData))
                        <div class="empty-state text-center py-5">
                            <i class="bi bi-graph-up text-muted fs-2"></i>
                            <p class="mt-2 text-muted">{{ __('Historical risk data is not available yet.') }}</p>
                        </div>
                    @else
                        <div style="height: 250px;"><canvas id="riskTrendChart"></canvas></div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Currency Trend -->
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">{{ __('Currency Trend (vs IDR)') }} <i class="bi bi-info-circle text-muted ms-1" style="font-size: 0.8rem;"></i></h6>
                    <select class="form-select form-select-sm border-0 bg-light rounded-pill px-3" style="width: 90px;">
                        <option>7 Days</option>
                    </select>
                </div>
                <div class="card-body p-3">
                    @if(empty($currencyTrendData))
                        <div class="empty-state text-center py-5">
                            <i class="bi bi-currency-exchange text-muted fs-2"></i>
                            <p class="mt-2 text-muted">{{ __('Historical exchange rate data is not available yet.') }}</p>
                        </div>
                    @else
                        <div style="height: 250px;"><canvas id="currencyTrendChart"></canvas></div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- ROW 3.5: Economic Trends (GDP & Inflation) -->
    <div class="row g-4 mb-4">
        <!-- Global GDP Trend -->
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">{{ __('Global GDP Trend') }} <i class="bi bi-info-circle text-muted ms-1" style="font-size: 0.8rem;"></i></h6>
                    <span class="badge bg-light text-muted border">7 Years</span>
                </div>
                <div class="card-body p-3">
                    @if(empty($gdpTrendData))
                        <div class="text-center py-5 m-auto">
                            <i class="bi bi-bar-chart text-muted fs-2 mb-3 d-block"></i>
                            <p class="text-muted">{{ __('Data is not available.') }}</p>
                        </div>
                    @else
                        <div style="height: 250px;"><canvas id="gdpTrendChart"></canvas></div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Global Inflation Trend -->
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">{{ __('Global Inflation Trend') }} <i class="bi bi-info-circle text-muted ms-1" style="font-size: 0.8rem;"></i></h6>
                    <span class="badge bg-light text-muted border">7 Years</span>
                </div>
                <div class="card-body p-3">
                    @if(empty($inflationTrendData))
                        <div class="text-center py-5 m-auto">
                            <i class="bi bi-bar-chart text-muted fs-2 mb-3 d-block"></i>
                            <p class="text-muted">{{ __('Data is not available.') }}</p>
                        </div>
                    @else
                        <div style="height: 250px;"><canvas id="inflationTrendChart"></canvas></div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- ROW 4: Weather & News Category (Left), AI Recommendation (Right) -->
    <div class="row g-4 mb-4">
        
        <!-- Left Column -->
        <div class="col-xl-6 d-flex flex-column gap-4">
            
            <!-- Weather Trend -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">{{ __('Weather Trend') }} <i class="bi bi-info-circle text-muted ms-1" style="font-size: 0.8rem;"></i></h6>
                </div>
                <div class="card-body p-3">
                    <div style="height: 250px;"><canvas id="weatherTrendChart"></canvas></div>
                </div>
            </div>

            <!-- News Category Chart -->
            <div class="card border-0 shadow-sm rounded-4 flex-grow-1">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">{{ __('Global News Category') }} <i class="bi bi-info-circle text-muted ms-1" style="font-size: 0.8rem;"></i></h6>
                    <select class="form-select form-select-sm border-0 bg-light rounded-pill px-3" style="width: 90px;">
                        <option>7 Days</option>
                    </select>
                </div>
                <div class="card-body p-3 d-flex flex-column justify-content-center position-relative">
                    <div style="position: relative; height: 200px; width: 100%;">
                        <canvas id="newsCategoryChart"></canvas>
                        <!-- Center text for Doughnut -->
                        <div class="position-absolute top-50 start-50 translate-middle text-center" style="pointer-events: none;">
                            <span class="text-muted" style="font-size:0.7rem;">Total News</span><br>
                            <span class="fw-bold text-dark fs-5" id="news-total">{{ $newsCategoryData['total'] ?? 0 }}</span>
                        </div>
                    </div>
                    <div class="mt-3" id="news-legend">
                        <!-- Legend generated by JS -->
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column: AI Recommendation Redesign -->
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 position-relative">
                <div class="card-body p-4 d-flex flex-column">
                    
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-robot fs-4 text-success"></i>
                            <h6 class="fw-bold mb-0 text-dark">{{ __('AI Risk Recommendation') }}</h6>
                        </div>
                        <span class="badge bg-success bg-opacity-25 text-success rounded-pill px-3">{{ __('New') }}</span>
                    </div>

                    @if(!empty($aiRecommendation))
                    
                    <!-- Sentiment Bars -->
                    <div class="mb-4">
                        <p class="fw-semibold text-dark mb-3" style="font-size: 0.85rem;">{{ __('News Sentiment Analysis (Lexicon Based)') }} <i class="bi bi-info-circle text-muted ms-1"></i></p>
                        
                        <!-- Positive -->
                        <div class="d-flex align-items-center mb-2 gap-3">
                            <i class="bi bi-emoji-smile fs-5 text-success"></i>
                            <span class="fw-semibold" style="width: 70px; font-size:0.85rem;">{{ __('Positive') }}</span>
                            <div class="progress flex-grow-1" style="height: 6px;">
                                <div class="progress-bar bg-success rounded-pill" role="progressbar" style="width: {{ $aiRecommendation['sentiment']['positive'] ?? 0 }}%"></div>
                            </div>
                            <span class="fw-bold text-dark" style="width: 35px; text-align: right; font-size:0.85rem;">{{ $aiRecommendation['sentiment']['positive'] ?? 0 }}%</span>
                        </div>
                        
                        <!-- Neutral -->
                        <div class="d-flex align-items-center mb-2 gap-3">
                            <i class="bi bi-emoji-neutral fs-5 text-warning"></i>
                            <span class="fw-semibold" style="width: 70px; font-size:0.85rem;">{{ __('Neutral') }}</span>
                            <div class="progress flex-grow-1" style="height: 6px;">
                                <div class="progress-bar bg-warning rounded-pill" role="progressbar" style="width: {{ $aiRecommendation['sentiment']['neutral'] ?? 0 }}%"></div>
                            </div>
                            <span class="fw-bold text-dark" style="width: 35px; text-align: right; font-size:0.85rem;">{{ $aiRecommendation['sentiment']['neutral'] ?? 0 }}%</span>
                        </div>
                        
                        <!-- Negative -->
                        <div class="d-flex align-items-center mb-3 gap-3">
                            <i class="bi bi-emoji-frown fs-5 text-danger"></i>
                            <span class="fw-semibold" style="width: 70px; font-size:0.85rem;">{{ __('Negative') }}</span>
                            <div class="progress flex-grow-1" style="height: 6px;">
                                <div class="progress-bar bg-danger rounded-pill" role="progressbar" style="width: {{ $aiRecommendation['sentiment']['negative'] ?? 0 }}%"></div>
                            </div>
                            <span class="fw-bold text-dark" style="width: 35px; text-align: right; font-size:0.85rem;">{{ $aiRecommendation['sentiment']['negative'] ?? 0 }}%</span>
                        </div>
                    </div>

                    <!-- Scores -->
                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-4 text-center">
                                <p class="text-muted fw-semibold mb-1" style="font-size:0.8rem;">{{ __('Overall Sentiment') }}</p>
                                @php
                                    $oSent = $aiRecommendation['sentiment']['overall'] ?? 'Neutral';
                                    $oColor = $oSent == 'Negative' ? 'danger' : ($oSent == 'Positive' ? 'success' : 'warning');
                                @endphp
                                <span class="badge bg-{{ $oColor }} bg-opacity-25 text-{{ $oColor }} rounded-pill px-3 py-2 fw-bold" style="font-size:0.85rem;">{{ __($oSent) }}</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-4 text-center">
                                <p class="text-muted fw-semibold mb-1" style="font-size:0.8rem;">{{ __('Confidence Score') }}</p>
                                <span class="fw-bold text-success fs-5">{{ $aiRecommendation['confidence_score'] ?? 0 }}%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Potential Impact -->
                    <div class="mb-4">
                        <p class="fw-bold text-dark mb-3" style="font-size: 0.85rem;">{{ __('Potential Impact') }}</p>
                        <div class="d-flex flex-column gap-2">
                            @foreach($aiRecommendation['impacts'] ?? [] as $impact)
                            <div class="d-flex align-items-start gap-3">
                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 28px; height: 28px;">
                                    <i class="bi {{ $impact['icon'] ?? 'bi-info-circle' }}" style="font-size: 0.85rem;"></i>
                                </div>
                                <span class="text-muted" style="font-size: 0.85rem; line-height: 1.5;">{{ __($impact['text']) }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Recommendation -->
                    <div class="mb-4">
                        <p class="fw-bold text-dark mb-3" style="font-size: 0.85rem;">{{ __('Recommendation') }}</p>
                        <div class="d-flex flex-column gap-2">
                            @foreach($aiRecommendation['recommendations'] ?? [] as $rec)
                            <div class="d-flex align-items-start gap-3">
                                <i class="bi bi-check-circle-fill text-success flex-shrink-0" style="font-size: 1.1rem; margin-top: -2px;"></i>
                                <span class="text-muted fw-medium" style="font-size: 0.85rem; line-height: 1.5;">{{ __($rec) }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold text-dark" style="font-size: 0.85rem;">{{ __('Last Analysis') }}</span>
                            <span class="text-muted d-flex align-items-center gap-1" style="font-size: 0.8rem;">
                                <i class="bi bi-clock"></i> {{ $aiRecommendation['last_analysis'] ?? 'N/A' }} <i class="bi bi-info-circle ms-1"></i>
                            </span>
                        </div>
                        <button class="btn btn-sm btn-light rounded-circle text-muted" onclick="location.reload()" style="width: 32px; height: 32px; padding: 0;">
                            <i class="bi bi-arrow-repeat"></i>
                        </button>
                    </div>

                    @else
                    <div class="text-center py-5 m-auto">
                        <i class="bi bi-robot text-muted fs-2 mb-3 d-block"></i>
                        <p class="text-muted">{{ __('No AI Recommendation available yet.') }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- ROW 5: Latest Global News -->
    <div class="row g-4 mb-4">
        <!-- Latest News -->
        <div class="col-xl-12">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">{{ __('Latest Global News') }} <i class="bi bi-info-circle text-muted ms-1" style="font-size: 0.8rem;"></i></h6>
                    <a href="{{ route('user.news') }}" class="btn btn-sm text-primary fw-semibold">{{ __('View All') }}</a>
                </div>
                <div class="card-body p-0">
                    <div class="row g-0">
                        @forelse($latestNews as $news)
                            @php
                                $sColor = $news->sentiment_label == 'Negative' ? 'danger' : ($news->sentiment_label == 'Positive' ? 'success' : 'warning');
                                if($sColor == 'warning') $sColorStyle = "background-color: #FFF3E0; color: #fd7e14;";
                                else if($sColor == 'danger') $sColorStyle = "background-color: #FFEBEE; color: #dc3545;";
                                else $sColorStyle = "background-color: #E8F5E9; color: #198754;";
                            @endphp
                            <div class="col-md-6 border-bottom {{ $loop->iteration % 2 != 0 ? 'border-end' : '' }}">
                                <a href="{{ $news->url }}" target="_blank" class="d-block p-4 text-decoration-none h-100 list-group-item-action">
                                    <div class="d-flex gap-3">
                                        <!-- Thumbnail -->
                                        <div class="rounded-3 overflow-hidden bg-light" style="width: 80px; height: 80px; flex-shrink: 0;">
                                            <img src="{{ $news->image_url ?? 'https://picsum.photos/150/150?random='.$loop->index }}" alt="News" style="width: 100%; height: 100%; object-fit: cover;">
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-2 text-dark fw-bold" style="font-size: 0.95rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.4;">{{ $news->title }}</h6>
                                            <div class="d-flex align-items-center justify-content-between mt-auto">
                                                <span class="badge rounded-pill" style="{{ $sColorStyle }} font-weight: 600; font-size: 0.7rem; padding: 0.35em 0.65em;">{{ __($news->sentiment_label ?? 'Neutral') }}</span>
                                                <span class="text-muted" style="font-size: 0.75rem;"><i class="bi bi-calendar3 me-1"></i> {{ \Carbon\Carbon::parse($news->published_at)->format('M d, Y') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @empty
                            <div class="col-12 p-5 text-center text-muted">
                                <i class="bi bi-newspaper fs-1 mb-3 d-block"></i>
                                {{ __('No recent news available.') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(isset($adminArticles) && $adminArticles->count() > 0)
    <!-- ROW 6: Admin Articles / Expert Analysis -->
    <div class="row g-4 mb-4">
        <div class="col-xl-12">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(145deg, #ffffff, #f8f9fa);">
                <div class="card-header bg-transparent border-0 py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-journal-bookmark-fill text-primary me-2"></i>{{ __('Expert Analysis & Reports') }}</h6>
                </div>
                <div class="card-body p-4 pt-0">
                    <div class="row g-4">
                        @foreach($adminArticles as $article)
                        <div class="col-md-3 col-sm-6">
                            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden" style="transition: transform 0.3s; cursor: pointer;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                                @if($article->image)
                                <img src="{{ $article->image }}" class="card-img-top" alt="{{ $article->title }}" style="height: 140px; object-fit: cover;">
                                @else
                                <div class="bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="height: 140px;">
                                    <i class="bi bi-file-earmark-text text-primary fs-1"></i>
                                </div>
                                @endif
                                <div class="card-body p-3 d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-light text-primary" style="font-size: 0.7rem;">{{ $article->category ?? 'Analysis' }}</span>
                                        <span class="text-muted" style="font-size: 0.7rem;">{{ $article->created_at->format('M d, Y') }}</span>
                                    </div>
                                    <h6 class="card-title fw-bold text-dark mb-2" style="font-size: 0.9rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">{{ $article->title }}</h6>
                                    <p class="card-text text-muted small mt-auto" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; font-size: 0.75rem;">
                                        {{ strip_tags($article->content) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Dashboard State
let map;
let sparklines = {};
let charts = {};

// Initial Data safely fallback if not defined
const initialData = {
    summary: @json($summary ?? []),
    mapData: @json($mapData ?? []),
    newsCategoryData: @json($newsCategoryData ?? ['labels'=>[], 'data'=>[], 'total'=>0]),
    riskTrendData: @json($riskTrendData ?? null),
    currencyTrendData: @json($currencyTrendData ?? null),
    weatherTrendData: @json($weatherTrendData ?? null),
    gdpTrendData: @json($gdpTrendData ?? null),
    inflationTrendData: @json($inflationTrendData ?? null)
};

document.addEventListener("DOMContentLoaded", function() {
    initMap();
    if(initialData.summary && initialData.summary.countries_monitored) {
        initSparklines();
    }
    initNewsChart();
    
    @if(!empty($riskTrendData)) initRiskTrend(); @endif
    @if(!empty($currencyTrendData)) initCurrencyTrend(); @endif
    @if(!empty($weatherTrendData)) initWeatherTrend(); @endif
    @if(!empty($gdpTrendData)) initGdpTrend(); @endif
    @if(!empty($inflationTrendData)) initInflationTrend(); @endif

    // Setup Auto Refresh every 45s
    setInterval(fetchDashboardData, 45000);
});

// Helper for mini sparklines
function createSparkline(ctx, data, color) {
    if(!ctx) return null;
    return new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['1','2','3','4','5','6','7'],
            datasets: [{
                data: data,
                borderColor: color,
                borderWidth: 2,
                tension: 0.4,
                pointRadius: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { enabled: false } },
            scales: { x: { display: false }, y: { display: false } }
        }
    });
}

function initSparklines() {
    const s = initialData.summary;
    sparklines['countries'] = createSparkline(document.getElementById('spark-countries'), s.countries_monitored.sparkline, '#1c74ff');
    sparklines['high_risk'] = createSparkline(document.getElementById('spark-high-risk'), s.high_risk.sparkline, '#dc3545');
    sparklines['global_news'] = createSparkline(document.getElementById('spark-global-news'), s.global_news.sparkline, '#0dcaf0');
    sparklines['weather'] = createSparkline(document.getElementById('spark-weather'), s.weather_alerts.sparkline, '#198754');
    sparklines['currency'] = createSparkline(document.getElementById('spark-currency'), s.currency_volatility.sparkline, '#ffc107');
}

function initMap() {
    const mapEl = document.getElementById('worldMap');
    if(!mapEl) return;
    map = L.map('worldMap', { zoomControl: false }).setView([20, 0], 2);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);
    L.control.zoom({ position: 'topleft' }).addTo(map);

    updateMapMarkers(initialData.mapData);
}

function updateMapMarkers(data) {
    if(!map || !data) return;
    // Clear existing
    map.eachLayer((layer) => {
        if(layer instanceof L.CircleMarker) map.removeLayer(layer);
    });
    
    data.forEach(function(country) {
        let color = '#198754'; // Low
        if(country.risk_level === 'Medium') color = '#ffc107';
        if(country.risk_level === 'High') color = '#fd7e14';
        if(country.risk_level === 'Critical') color = '#dc3545';

        L.circleMarker([country.lat, country.lng], {
            radius: 5, fillColor: color, color: '#fff', weight: 1, opacity: 1, fillOpacity: 0.9
        }).addTo(map).bindPopup("<b>" + country.name + "</b><br>Risk: " + country.risk_level);
    });
}

function initNewsChart() {
    const ctx = document.getElementById('newsCategoryChart');
    if(!ctx) return;
    const data = initialData.newsCategoryData;
    const colors = ['#1C55FF', '#198754', '#ffc107', '#dc3545'];
    
    charts['news'] = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.labels,
            datasets: [{
                data: data.data,
                backgroundColor: colors,
                borderWidth: 2,
                borderColor: '#fff',
                cutout: '65%'
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

    let legendHtml = '<div class="d-flex justify-content-between flex-wrap gap-2">';
    if(data.labels && data.labels.length > 0) {
        data.labels.forEach((label, i) => {
            let pct = data.total > 0 ? Math.round((data.data[i] / data.total) * 100) : 0;
            legendHtml += `
            <div class="d-flex align-items-center gap-2" style="font-size:0.75rem;">
                <span style="width:8px;height:8px;border-radius:50%;background-color:${colors[i]}"></span>
                <span class="text-muted">${label}</span>
                <span class="fw-bold">${data.data[i]} (${pct}%)</span>
            </div>`;
        });
    }
    legendHtml += '</div>';
    document.getElementById('news-legend').innerHTML = legendHtml;
}

function initRiskTrend() {
    const ctx = document.getElementById('riskTrendChart');
    if(!ctx) return;
    const data = initialData.riskTrendData;
    charts['risk'] = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Risk Score',
                data: data.data,
                borderColor: '#1C55FF',
                backgroundColor: 'rgba(28, 85, 255, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3,
                pointBackgroundColor: '#1C55FF',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, max: 100, grid: { borderDash: [5,5] } }, x: { grid: { display: false } } }
        }
    });
}

function initCurrencyTrend() {
    const ctx = document.getElementById('currencyTrendChart');
    if(!ctx) return;
    const data = initialData.currencyTrendData;
    charts['currency'] = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [
                { label: 'USD/IDR', data: data.datasets['USD/IDR'], borderColor: '#1C55FF', tension: 0.3, pointRadius: 2 },
                { label: 'EUR/IDR', data: data.datasets['EUR/IDR'], borderColor: '#198754', tension: 0.3, pointRadius: 2 },
                { label: 'CNY/IDR', data: data.datasets['CNY/IDR'], borderColor: '#ffc107', tension: 0.3, pointRadius: 2 }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'right', labels: { boxWidth: 10, font: { size: 10 } } } },
            scales: { y: { grid: { borderDash: [5,5] } }, x: { grid: { display: false } } }
        }
    });
}

function initWeatherTrend() {
    const ctx = document.getElementById('weatherTrendChart');
    if(!ctx) return;
    const data = initialData.weatherTrendData;
    charts['weather'] = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [
                { label: 'Temp (°C)', data: data.temp, borderColor: '#dc3545', tension: 0.3, pointRadius: 2 },
                { label: 'Humidity (%)', data: data.humidity, borderColor: '#1C55FF', tension: 0.3, pointRadius: 2 },
                { label: 'Wind (km/h)', data: data.wind, borderColor: '#198754', tension: 0.3, pointRadius: 2 }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'right', labels: { boxWidth: 10, font: { size: 10 } } } },
            scales: { y: { grid: { borderDash: [5,5] } }, x: { grid: { display: false } } }
        }
    });
}

function initGdpTrend() {
    const canvas = document.getElementById('gdpTrendChart');
    if(!canvas) return;
    const ctx = canvas.getContext('2d');
    const data = initialData.gdpTrendData;
    
    let gradientFill = ctx.createLinearGradient(0, 0, 0, 250);
    gradientFill.addColorStop(0, 'rgba(13, 202, 240, 0.6)');
    gradientFill.addColorStop(1, 'rgba(13, 202, 240, 0.0)');

    charts['gdp'] = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [
                { 
                    label: 'Global GDP', 
                    data: data.datasets['Global GDP (Trillion USD)'], 
                    borderColor: '#0dcaf0', 
                    backgroundColor: gradientFill, 
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4, 
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#0dcaf0',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { grid: { borderDash: [5,5] } }, x: { grid: { display: false } } },
            interaction: { mode: 'index', intersect: false }
        }
    });
}

function initInflationTrend() {
    const canvas = document.getElementById('inflationTrendChart');
    if(!canvas) return;
    const ctx = canvas.getContext('2d');
    const data = initialData.inflationTrendData;

    let gradientFill = ctx.createLinearGradient(0, 0, 0, 250);
    gradientFill.addColorStop(0, 'rgba(253, 126, 20, 0.6)');
    gradientFill.addColorStop(1, 'rgba(253, 126, 20, 0.0)');

    charts['inflation'] = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [
                { 
                    label: 'Global Inflation (%)', 
                    data: data.datasets['Global Inflation (%)'], 
                    borderColor: '#fd7e14', 
                    backgroundColor: gradientFill,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4, 
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#fd7e14',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { grid: { borderDash: [5,5] } }, x: { grid: { display: false } } },
            interaction: { mode: 'index', intersect: false }
        }
    });
}

// AJAX Sync function
function fetchDashboardData() {
    const syncInd = document.getElementById('sync-indicator');
    const syncText = document.getElementById('sync-text');
    if(syncInd) {
        syncInd.className = 'spinner-grow spinner-grow-sm text-warning';
        syncText.innerText = 'Syncing...';
    }
    const spinnerIcon = document.querySelector('.bi-arrow-repeat');
    if(spinnerIcon) spinnerIcon.classList.add('bi-spin');

    fetch('{{ route("user.dashboard.sync") }}')
        .then(response => response.json())
        .then(data => {
            // Update Summary Cards
            if(document.getElementById('val-countries')) {
                document.getElementById('val-countries').innerText = data.summary.countries_monitored.value;
                document.getElementById('val-high-risk').innerText = data.summary.high_risk.value;
                document.getElementById('val-global-news').innerText = data.summary.global_news.value;
                document.getElementById('val-weather').innerText = data.summary.weather_alerts.value;
            }
            
            // Update Sparklines data silently
            if(sparklines['countries']) {
                sparklines['countries'].data.datasets[0].data = data.summary.countries_monitored.sparkline; sparklines['countries'].update();
                sparklines['high_risk'].data.datasets[0].data = data.summary.high_risk.sparkline; sparklines['high_risk'].update();
                sparklines['global_news'].data.datasets[0].data = data.summary.global_news.sparkline; sparklines['global_news'].update();
                sparklines['weather'].data.datasets[0].data = data.summary.weather_alerts.sparkline; sparklines['weather'].update();
            }
            
            // Update Map
            if(map) updateMapMarkers(data.mapData);
            
            // Update Charts
            if(charts['news'] && data.newsCategoryData) {
                charts['news'].data.datasets[0].data = data.newsCategoryData.data;
                charts['news'].update();
                document.getElementById('news-total').innerText = data.newsCategoryData.total;
            }
            if(charts['risk'] && data.riskTrendData) {
                charts['risk'].data.labels = data.riskTrendData.labels;
                charts['risk'].data.datasets[0].data = data.riskTrendData.data;
                charts['risk'].update();
            }
            if(charts['currency'] && data.currencyTrendData) {
                charts['currency'].data.labels = data.currencyTrendData.labels;
                charts['currency'].data.datasets[0].data = data.currencyTrendData.datasets['USD/IDR'];
                charts['currency'].data.datasets[1].data = data.currencyTrendData.datasets['EUR/IDR'];
                charts['currency'].data.datasets[2].data = data.currencyTrendData.datasets['CNY/IDR'];
                charts['currency'].update();
            }
            if(charts['weather'] && data.weatherTrendData) {
                charts['weather'].data.labels = data.weatherTrendData.labels;
                charts['weather'].data.datasets[0].data = data.weatherTrendData.temp;
                charts['weather'].data.datasets[1].data = data.weatherTrendData.humidity;
                charts['weather'].data.datasets[2].data = data.weatherTrendData.wind;
                charts['weather'].update();
            }
            if(charts['gdp'] && data.gdpTrendData) {
                charts['gdp'].data.labels = data.gdpTrendData.labels;
                charts['gdp'].data.datasets[0].data = data.gdpTrendData.datasets['Global GDP (Trillion USD)'];
                charts['gdp'].update();
            }
            if(charts['inflation'] && data.inflationTrendData) {
                charts['inflation'].data.labels = data.inflationTrendData.labels;
                charts['inflation'].data.datasets[0].data = data.inflationTrendData.datasets['Global Inflation (%)'];
                charts['inflation'].update();
            }

            // Update AI Recommendation
            if(data.aiRecommendation && document.getElementById('ai-sentiment')) {
                document.getElementById('ai-sentiment').innerText = data.aiRecommendation.overall_sentiment;
                document.getElementById('ai-confidence').innerText = data.aiRecommendation.confidence_score + '%';
                document.getElementById('ai-message').innerText = data.aiRecommendation.recommendation;
            }

            // Update Sync Status successful
            setTimeout(() => {
                if(syncInd) {
                    syncInd.className = 'rounded-circle bg-success';
                    syncInd.style = 'width: 8px; height: 8px; box-shadow: 0 0 5px #198754;';
                    syncText.innerText = 'Synced';
                }
                const lastSync = document.getElementById('last-sync-time');
                if(lastSync) lastSync.innerText = data.timestamp;
                const dashTime = document.getElementById('dashboard-time-indicator');
                if(dashTime) dashTime.innerText = data.timestamp;
                if(spinnerIcon) spinnerIcon.classList.remove('bi-spin');
            }, 1000); 
        })
        .catch(err => {
            console.error('Sync failed', err);
            if(syncInd) {
                syncInd.className = 'rounded-circle bg-danger';
                syncInd.style = 'width: 8px; height: 8px; box-shadow: 0 0 5px #dc3545;';
                syncText.innerText = 'Sync Failed';
            }
            if(spinnerIcon) spinnerIcon.classList.remove('bi-spin');
        });
}
</script>
<style>
.bi-spin { animation: spin 1s linear infinite; }
@keyframes spin { 100% { transform: rotate(360deg); } }
.summary-card:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important; transition: all 0.3s; }
</style>
@endpush