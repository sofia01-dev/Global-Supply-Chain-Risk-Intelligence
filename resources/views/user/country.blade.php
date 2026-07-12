@extends('layouts.app')
@section('content')
<div class="row mb-4">
    <div class="col"><h2>Country Dashboard {{ $country ? ' - '.$country->name : '' }}</h2></div>
</div>
@if(!$country)
    <div class="alert alert-info">No data available</div>
@else
    <div class="row g-4">
        <div class="col-md-4"><div class="card"><div class="card-body"><h5>GDP</h5><p>{{ $country->gdp ?? '-' }}</p></div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-body"><h5>Inflation</h5><p>{{ $country->inflation_rate ?? '-' }}</p></div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-body"><h5>Population</h5><p>{{ $country->population ?? '-' }}</p></div></div></div>
    </div>
@endif
@endsection