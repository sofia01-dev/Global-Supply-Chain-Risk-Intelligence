@extends('layouts.app')
@section('content')
<div class="container">
    <h2>Manage Users</h2>
    @if($users->isEmpty())
        <div class="alert alert-info mt-3">No users found.</div>
    @else
        <table class="table table-striped mt-3">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->role }}</td>
                    <td>
                        <a href="{{ url('/admin/users/'.$user->id) }}" class="btn btn-sm btn-info text-white">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection