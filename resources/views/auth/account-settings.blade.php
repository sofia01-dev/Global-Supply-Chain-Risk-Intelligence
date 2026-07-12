@extends('layouts.app')

@section('content')
<div class="container d-flex justify-content-center">
    <div class="auth-container w-100">
        <div class="card shadow-sm">
            <div class="card-header bg-info text-white">Account Settings</div>
            <div class="card-body">
                <form method="POST" action="{{ route('account.settings') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $user->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <hr>
                    <p class="text-muted small">Leave password fields blank if you do not want to change it.</p>

                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm New Password</label>
                        <input id="password_confirmation" type="password" class="form-control" name="password_confirmation">
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-info text-white">Update Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection