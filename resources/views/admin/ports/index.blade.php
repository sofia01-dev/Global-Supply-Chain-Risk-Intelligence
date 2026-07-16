@extends('layouts.app')
@section('content')
<div class="container">
    <h2>Manage Ports</h2>
    @if($ports->isEmpty())
        <div class="alert alert-info mt-3">No ports found in the database.</div>
    @else
        <table class="table table-striped mt-3">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Country</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ports as $port)
                <tr>
                    <td>{{ $port->name }}</td>
                    <td>{{ $port->country->name ?? '-' }}</td>
                    <td>
                        <a href="{{ url('/admin/ports/'.$port->id) }}" class="btn btn-sm btn-info text-white">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection