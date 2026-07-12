@extends('layouts.app')
@section('content')
<div class="row mb-4">
    <div class="col"><h2>Admin Dashboard</h2></div>
</div>
<div class="row g-4">
    <div class="col-md-3">
        <div class="card text-center bg-primary text-white">
            <div class="card-body">
                <h5>Total Users</h5>
                <h2>{{ $summary['total_users'] ?? 0 }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center bg-success text-white">
            <div class="card-body">
                <h5>Total Countries</h5>
                <h2>{{ $summary['total_countries'] ?? 0 }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center bg-info text-white">
            <div class="card-body">
                <h5>Total Ports</h5>
                <h2>{{ $summary['total_ports'] ?? 0 }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center bg-warning">
            <div class="card-body">
                <h5>Total Shipments</h5>
                <h2>{{ $summary['total_shipments'] ?? 0 }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center bg-secondary text-white">
            <div class="card-body">
                <h5>Total Articles</h5>
                <h2>{{ $summary['total_articles'] ?? 0 }}</h2>
            </div>
        </div>
    </div>
</div>
@endsection