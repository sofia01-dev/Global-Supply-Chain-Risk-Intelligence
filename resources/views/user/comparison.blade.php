@extends('layouts.app')
@section('content')
<div class="row mb-4"><div class="col"><h2>Country Comparison Dashboard</h2></div></div>
<div class="card"><div class="card-body">
    <table class="table table-bordered">
        <thead><tr><th>Country</th><th>GDP</th><th>Inflation</th><th>Currency</th><th>Weather</th><th>Risk Score</th></tr></thead>
        <tbody>
            @if($countries->isEmpty())
                <tr><td colspan="6" class="text-center">No data available</td></tr>
            @else
                @foreach($countries as $c)
                    <tr><td>{{ $c->name }}</td><td>{{ $c->gdp ?? '-' }}</td><td>{{ $c->inflation_rate ?? '-' }}</td><td>{{ $c->currency_code ?? '-' }}</td><td>-</td><td>-</td></tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div></div>
@endsection