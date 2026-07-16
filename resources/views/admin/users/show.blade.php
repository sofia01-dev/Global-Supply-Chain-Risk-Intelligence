@extends('layouts.app')
@section('content')
<div class="container">
    <h2>User Details</h2>
    @if(!$user)
        <div class="alert alert-warning">User not found.</div>
    @else
        <div class="card mt-3">
            <div class="card-body">
                <p><strong>ID:</strong> {{ $user->id }}</p>
                <p><strong>Name:</strong> {{ $user->name }}</p>
                <p><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>Role:</strong> {{ $user->role }}</p>
                <a href="{{ url('/admin/users') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>
    @endif
</div>
@endsection