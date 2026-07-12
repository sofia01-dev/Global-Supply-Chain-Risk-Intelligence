@extends('layouts.app')
@section('content')
<div class="row mb-4">
    <div class="col"><h2>User Dashboard</h2></div>
</div>
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card text-center bg-primary text-white"><div class="card-body">
            <h5>Countries Monitored</h5><h2>{{ $summary['total_countries_monitored'] ?? 0 }}</h2>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card text-center bg-success text-white"><div class="card-body">
            <h5>Active Shipments</h5><h2>{{ $summary['active_shipments'] ?? 0 }}</h2>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card text-center bg-danger text-white"><div class="card-body">
            <h5>High Risk Countries</h5><h2>{{ $summary['high_risk_countries'] ?? 0 }}</h2>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card text-center bg-info text-white"><div class="card-body">
            <h5>Latest News Count</h5><h2>{{ isset($summary['latest_global_news']) ? $summary['latest_global_news']->count() : 0 }}</h2>
        </div></div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header">Interactive World Map</div>
            <div class="card-body p-0">
                <div id="worldMap" class="dashboard-map"></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header bg-warning">Alert Panel</div>
            <div class="card-body">
                <p class="text-muted text-center">No alerts available</p>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Global Risk Trend</div>
            <div class="card-body"><div class="chart-container"><canvas id="riskChart"></canvas></div></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Top Risk Countries</div>
            <div class="card-body">
                <table class="table">
                    <thead><tr><th>Country</th><th>Risk Score</th><th>Risk Level</th></tr></thead>
                    <tbody><tr><td colspan="3" class="text-center">No data available</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
@stack('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    var map = L.map('worldMap').setView([20, 0], 2);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    var ctx = document.getElementById('riskChart').getContext('2d');
    new Chart(ctx, { type: 'line', data: { labels: [], datasets: [] }, options: { responsive: true, maintainAspectRatio: false } });
});
</script>