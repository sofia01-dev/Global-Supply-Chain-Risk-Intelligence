@extends('layouts.app')
@section('content')
<div class="row mb-4"><div class="col"><h2>Global Weather Dashboard</h2></div></div>
<div class="row mb-4">
    <div class="col-md-8"><div class="card"><div class="card-body p-0"><div id="weatherMap" class="dashboard-map"></div></div></div></div>
    <div class="col-md-4"><div class="card h-100"><div class="card-header">Weather Summary</div><div class="card-body text-center text-muted">No data available</div></div></div>
</div>
@endsection
@stack('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    var map = L.map('weatherMap').setView([20, 0], 2);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
});
</script>