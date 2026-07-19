@extends('layouts.app')

@push('styles')
<style>
    .admin-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
        background-color: #fff;
    }
    .btn-navy {
        background-color: #3E53A0;
        color: white;
        border: none;
    }
    .btn-navy:hover {
        background-color: #2c3e80;
        color: white;
    }
    .form-label {
        font-weight: 600;
        font-size: 0.85rem;
        color: #555;
    }
    .form-control, .form-select {
        border-radius: 8px;
        padding: 10px 15px;
        border: 1px solid #e0e0e0;
        font-size: 0.9rem;
    }
    .form-control:focus, .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4" style="max-width: 800px;">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-dark">Add New User</h4>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">Create a new admin or user account</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="btn btn-light border" style="border-radius: 8px; font-size: 0.85rem;">
            <i class="bi bi-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <!-- Form Card -->
    <div class="admin-card card p-4">
        @if($errors->any())
            <div class="alert alert-danger" style="border-radius: 8px;">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            
            <h6 class="fw-bold mb-4 border-bottom pb-2">Account Details</h6>
            
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. John Doe" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="e.g. john@example.com" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Minimum 8 characters" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Country (Origin)</label>
                    <select name="country_id" class="form-select" required>
                        <option value="">-- Select Country --</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->id }}" {{ old('country_id') == $country->id ? 'selected' : '' }}>
                                {{ $country->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <h6 class="fw-bold mb-4 border-bottom pb-2">Access & Permissions</h6>

            <div class="row g-4 mb-5">
                <div class="col-md-6">
                    <label class="form-label">System Role</label>
                    <select name="role" class="form-select" required>
                        <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>User (Standard Access)</option>
                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrator (Full Access)</option>
                    </select>
                    <div class="form-text" style="font-size: 0.75rem;">Administrators can manage users and datasets.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Account Status</label>
                    <select name="status" class="form-select" required>
                        <option value="Active" {{ old('status', 'Active') == 'Active' ? 'selected' : '' }}>Active (Can Login)</option>
                        <option value="Inactive" {{ old('status') == 'Inactive' ? 'selected' : '' }}>Inactive (Suspended)</option>
                    </select>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.users.index') }}" class="btn btn-light border px-4" style="border-radius: 8px;">{{ __('Cancel') }}</a>
                <button type="submit" class="btn btn-navy px-4" style="border-radius: 8px;">{{ __('Create User') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
