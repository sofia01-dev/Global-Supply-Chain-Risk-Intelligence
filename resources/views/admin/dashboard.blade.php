@extends('layouts.app')
@section('content')
<div class="container">
    <h2 class="mb-4">Admin Dashboard</h2>
    @if(empty($summary))
        <div class="alert alert-info">Dashboard data is currently unavailable.</div>
    @else
        <div class="row g-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white text-center p-3">
                    <h3>{{ $summary['total_users'] ?? 0 }}</h3>
                    <p>Users</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white text-center p-3">
                    <h3>{{ $summary['total_countries'] ?? 0 }}</h3>
                    <p>Countries</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white text-center p-3">
                    <h3>{{ $summary['total_ports'] ?? 0 }}</h3>
                    <p>Ports</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark text-center p-3">
                    <h3>{{ $summary['total_shipments'] ?? 0 }}</h3>
                    <p>Shipments</p>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection