@extends('layouts.app')
@section('content')
<div class="row mb-4"><div class="col"><h2>Currency Dashboard</h2></div></div>
<div class="row mb-4">
    <div class="col-md-6"><div class="card"><div class="card-header">Currency Trend Chart</div><div class="card-body"><div class="chart-container"><canvas id="currChart"></canvas></div></div></div></div>
    <div class="col-md-6"><div class="card"><div class="card-header">Currency Table</div><div class="card-body">
        <table class="table"><thead><tr><th>Code</th><th>Rate (USD)</th></tr></thead>
        <tbody>
            @if($currencies->isEmpty())
                <tr><td colspan="2" class="text-center">No data available</td></tr>
            @else
                @foreach($currencies as $c)<tr><td>{{ $c->currency_code }}</td><td>{{ $c->exchange_rate_usd }}</td></tr>@endforeach
            @endif
        </tbody></table>
    </div></div></div>
</div>
@endsection
@stack('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    var ctx = document.getElementById('currChart').getContext('2d');
    new Chart(ctx, { type: 'line', data: { labels: [], datasets: [] }, options: { responsive: true, maintainAspectRatio: false } });
});
</script>