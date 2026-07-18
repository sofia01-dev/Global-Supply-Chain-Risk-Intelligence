@extends('layouts.app')

@push('styles')
<style>
    /* Admin Dashboard Specific Styles */
    .admin-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
        transition: transform 0.2s;
        height: 100%;
        background-color: #fff;
    }
    
    .admin-card-title {
        font-size: 0.85rem;
        font-weight: 600;
        color: #666;
        margin-bottom: 5px;
    }
    
    .admin-card-value {
        font-size: 1.8rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 0;
    }
    
    .admin-card-sub {
        font-size: 0.75rem;
        color: #28a745;
        font-weight: 500;
    }
    
    .admin-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    
    .table-responsive {
        border-radius: 10px;
        overflow: hidden;
    }
    
    .admin-table th {
        font-size: 0.75rem;
        text-transform: uppercase;
        color: #888;
        font-weight: 600;
        background-color: #fcfcfc;
        border-bottom: 1px solid #eee;
        padding: 12px 15px;
    }
    
    .admin-table td {
        font-size: 0.85rem;
        vertical-align: middle;
        border-bottom: 1px solid #f9f9f9;
        padding: 12px 15px;
    }
    
    .badge-soft-success { background-color: #e8f5e9; color: #2e7d32; }
    .badge-soft-danger { background-color: #ffebee; color: #c62828; }
    .badge-soft-warning { background-color: #fff8e1; color: #f9a825; }
    .badge-soft-primary { background-color: #e3f2fd; color: #1565c0; }
    
    .btn-quick-action {
        text-align: left;
        padding: 15px;
        border-radius: 10px;
        border: 1px solid #eee;
        background-color: #fff;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 15px;
        color: #333;
        text-decoration: none;
    }
    
    .btn-quick-action:hover {
        background-color: #f4f6f9;
        border-color: #ddd;
        color: var(--primary-navy);
    }
    
    .btn-quick-action i {
        font-size: 1.5rem;
        color: var(--primary-navy);
        background-color: #E6EDFF;
        width: 40px; height: 40px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 8px;
    }
    
    .btn-quick-action h6 { margin: 0; font-weight: 600; font-size: 0.9rem; }
    .btn-quick-action p { margin: 0; font-size: 0.75rem; color: #888; }
</style>
@endpush

@section('content')
<div class="container-fluid py-2">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-dark">Admin Dashboard</h4>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">Manage users, datasets, and analytical articles</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="bg-white border rounded px-3 py-2 text-muted" style="font-size: 0.85rem;">
                <i class="bi bi-calendar3 me-2"></i> {{ $summary['serverTime'] ?? now()->format('d M Y, H:i') . ' WIB' }}
            </div>
        </div>
    </div>

    <!-- KPIs Row -->
    <div class="row g-3 mb-4">
        <div class="col-md">
            <div class="admin-card card p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="admin-card-title">Total Users</div>
                        <div class="admin-card-value">{{ number_format($summary['totalUsers'] ?? 0) }}</div>
                        <div class="admin-card-sub"><i class="bi bi-arrow-up-short"></i> {{ $summary['newUsers'] ?? 0 }} this month</div>
                    </div>
                    <div class="admin-icon-box bg-light-primary text-primary" style="background-color: #e3f2fd; color: #1565c0;">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md">
            <div class="admin-card card p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="admin-card-title">Total Ports</div>
                        <div class="admin-card-value">{{ number_format($summary['totalPorts'] ?? 0) }}</div>
                        <div class="admin-card-sub"><i class="bi bi-arrow-up-short"></i> {{ $summary['newPorts'] ?? 0 }} this month</div>
                    </div>
                    <div class="admin-icon-box" style="background-color: #e8f5e9; color: #2e7d32;">
                        <i class="bi bi-buildings"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md">
            <div class="admin-card card p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="admin-card-title">Published Articles</div>
                        <div class="admin-card-value">{{ number_format($summary['publishedArticles'] ?? 0) }}</div>
                        <div class="admin-card-sub"><i class="bi bi-arrow-up-short"></i> {{ $summary['newArticles'] ?? 0 }} this month</div>
                    </div>
                    <div class="admin-icon-box" style="background-color: #f3e5f5; color: #6a1b9a;">
                        <i class="bi bi-journal-text"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md">
            <div class="admin-card card p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="admin-card-title">API Status</div>
                        <div class="admin-card-value text-success">{{ $summary['apiStatus'] ?? 'Online' }}</div>
                        <div class="text-muted" style="font-size: 0.75rem;">All services operational</div>
                    </div>
                    <div class="admin-icon-box" style="background-color: #fff8e1; color: #f9a825;">
                        <i class="bi bi-hdd-network"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md">
            <div class="admin-card card p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="admin-card-title">Data Sync Status</div>
                        <div class="admin-card-value text-info" style="font-size: 1.5rem;">Synced</div>
                        <div class="text-muted" style="font-size: 0.75rem;">Last sync: {{ $summary['lastSync'] ?? 'N/A' }}</div>
                    </div>
                    <div class="admin-icon-box" style="background-color: #e0f7fa; color: #00838f;">
                        <i class="bi bi-arrow-repeat"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Middle Row: Quick Actions -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="admin-card card p-4">
                <h6 class="fw-bold mb-3">Quick Actions</h6>
                <div class="row g-3">
                    <div class="col-md-3">
                        <a href="{{ route('admin.users.index') }}" class="btn-quick-action">
                            <i class="bi bi-person-plus"></i>
                            <div>
                                <h6>Add New User</h6>
                                <p>Create a new user account</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.articles.index') }}" class="btn-quick-action">
                            <i class="bi bi-pencil-square"></i>
                            <div>
                                <h6>Create Article</h6>
                                <p>Write new analysis article</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.ports.index') }}" class="btn-quick-action">
                            <i class="bi bi-cloud-arrow-up"></i>
                            <div>
                                <h6>Import Ports</h6>
                                <p>Manage port datasets</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="#" class="btn-quick-action">
                            <i class="bi bi-arrow-repeat"></i>
                            <div>
                                <h6>Sync All Data</h6>
                                <p>Sync all external datasets</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Row: Tables -->
    <div class="row g-3">
        <!-- Recent Users -->
        <div class="col-lg-4">
            <div class="admin-card card p-0">
                <div class="card-header border-0 bg-transparent py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold m-0">Recent Users</h6>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-light border" style="font-size: 0.75rem;">View All Users</a>
                </div>
                <div class="table-responsive">
                    <table class="table admin-table mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Joined At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($summary['recentUsers'] ?? [] as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-light-primary text-primary d-flex align-items-center justify-content-center fw-bold" style="width:28px; height:28px; font-size: 0.7rem;">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </div>
                                        <span class="fw-medium">{{ $user->name }}</span>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td><span class="badge {{ $user->role == 'admin' ? 'badge-soft-primary' : 'badge-soft-success' }} rounded-pill text-capitalize px-2 py-1">{{ $user->role }}</span></td>
                                <td class="text-muted">{{ $user->created_at->format('d M Y') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-3 text-muted">No users found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Articles -->
        <div class="col-lg-4">
            <div class="admin-card card p-0">
                <div class="card-header border-0 bg-transparent py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold m-0">Recent Articles</h6>
                    <a href="{{ route('admin.articles.index') }}" class="btn btn-sm btn-light border" style="font-size: 0.75rem;">View All Articles</a>
                </div>
                <div class="table-responsive">
                    <table class="table admin-table mb-0">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Published At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($summary['recentArticles'] ?? [] as $article)
                            <tr>
                                <td><span class="text-truncate d-inline-block fw-medium" style="max-width: 150px;">{{ $article->title }}</span></td>
                                <td><span class="badge {{ $article->status == 'Published' ? 'badge-soft-success' : 'badge-soft-warning' }} rounded-pill px-2 py-1">{{ $article->status }}</span></td>
                                <td class="text-muted">{{ $article->created_at->format('d M Y') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center py-3 text-muted">No articles found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Dataset Status -->
        <div class="col-lg-4">
            <div class="admin-card card p-0">
                <div class="card-header border-0 bg-transparent py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold m-0">Dataset Status</h6>
                    <a href="#" class="btn btn-sm btn-light border" style="font-size: 0.75rem;">View All Datasets</a>
                </div>
                <div class="table-responsive">
                    <table class="table admin-table mb-0">
                        <thead>
                            <tr>
                                <th>Dataset</th>
                                <th>Records</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($summary['datasets'] ?? [] as $ds)
                            <tr>
                                <td class="fw-medium">{{ $ds->name }}</td>
                                <td>{{ number_format($ds->records) }}</td>
                                <td><span class="badge badge-soft-success rounded-pill px-2 py-1">{{ $ds->status }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- System Info Footer -->
    <div class="row mt-4 mb-2">
        <div class="col-12 text-center text-muted" style="font-size: 0.75rem;">
            <span class="me-3"><strong>Laravel:</strong> {{ app()->version() }}</span>
            <span class="me-3"><strong>PHP:</strong> {{ phpversion() }}</span>
            <span class="me-3"><strong>Environment:</strong> {{ app()->environment() }}</span>
            <span><strong>Timezone:</strong> {{ date_default_timezone_get() }}</span>
        </div>
    </div>
</div>
@endsection