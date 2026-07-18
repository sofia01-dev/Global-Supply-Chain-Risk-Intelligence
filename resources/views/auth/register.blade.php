@extends('layouts.app')

@section('content')
<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="w-100" style="max-width: 450px;">
        <div class="card shadow border-0 rounded-4 overflow-hidden">
            <div class="card-header text-white py-3 fs-5 fw-bold d-flex justify-content-center align-items-center" style="background-color: var(--primary-navy); border-bottom: none;">Register</div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autofocus>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password (Min. 8 characters)</label>
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <input id="password_confirmation" type="password" class="form-control" name="password_confirmation" required>
                    </div>

                    <div class="d-grid gap-2 mt-4 mb-2">
                        <button type="submit" class="btn text-white py-2 fw-bold" style="background-color: var(--primary-navy); border-radius: 8px;">Register</button>
                    </div>

                    <div class="text-center mt-3">
                        <span class="text-muted" style="font-size: 0.9rem;">Sudah punya akun?</span> 
                        <a href="{{ route('login') }}" class="text-decoration-none fw-bold" style="color: var(--primary-navy); font-size: 0.9rem;">Login di sini</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection