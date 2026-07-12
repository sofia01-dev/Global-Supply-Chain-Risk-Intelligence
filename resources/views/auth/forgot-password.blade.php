@extends('layouts.app')

@section('content')
<div class="container d-flex justify-content-center">
    <div class="auth-container w-100">
        <div class="card shadow-sm">
            <div class="card-header bg-warning">Reset Password</div>
            <div class="card-body">
                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autofocus>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-warning">Send Password Reset Link</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection