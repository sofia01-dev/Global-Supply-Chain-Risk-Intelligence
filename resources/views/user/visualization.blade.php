@extends('layouts.app')
@section('content')
<div class="row mb-4"><div class="col"><h2>Data Visualization Dashboard</h2></div></div>
<div class="row g-4">
    <div class="col-md-6"><div class="card"><div class="card-header">GDP Trend</div><div class="card-body"><div class="chart-container"><canvas id="gdpChart"></canvas></div></div></div></div>
    <div class="col-md-6"><div class="card"><div class="card-header">Inflation Trend</div><div class="card-body"><div class="chart-container"><canvas id="infChart"></canvas></div></div></div></div>
</div>
@endsection
@stack('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    ['gdpChart', 'infChart'].forEach(id => {
        new Chart(document.getElementById(id).getContext('2d'), { type: 'line', data: { labels: [], datasets: [] }, options: { responsive: true, maintainAspectRatio: false } });
    });
});
</script>