@extends('layouts.app')

@section('page_header')
<div class="d-none d-sm-block">
    <h5 class="m-0 fw-semibold text-dark">{{ __('User Management') }}</h5>
    <div class="text-muted" style="font-size: 0.8rem;">{{ __('Manage system users and their access roles') }}</div>
</div>
@endsection

@section('header_actions')
<div class="d-none d-md-flex align-items-center gap-3 me-3">
    <div class="bg-white rounded px-3 py-2 shadow-sm d-flex align-items-center gap-2 text-muted" style="font-size: 0.85rem; border: 1px solid #e0e0e0;">
        <i class="bi bi-calendar3 text-primary"></i> 
        <span class="fw-medium">{{ now()->format('d M Y, H:i') }} WIB</span>
    </div>
    <a href="{{ route('admin.users.create') }}" class="btn btn-navy" style="border-radius: 8px; font-size: 0.85rem;">
        <i class="bi bi-person-plus me-1"></i> Add New User
    </a>
</div>
@endsection
@push('styles')
<style>
    .admin-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
        background-color: #fff;
    }
    .admin-card-title { font-size: 0.85rem; font-weight: 600; color: #666; margin-bottom: 5px; }
    .admin-card-value { font-size: 1.8rem; font-weight: 700; color: #333; margin-bottom: 0; }
    .admin-card-sub { font-size: 0.75rem; color: #28a745; font-weight: 500; }
    .admin-card-sub.text-danger { color: #dc3545 !important; }
    .admin-icon-box {
        width: 48px; height: 48px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem;
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

    .toolbar-container {
        display: flex; gap: 15px; flex-wrap: wrap; align-items: center; justify-content: space-between;
    }
    .search-box { position: relative; flex-grow: 1; max-width: 350px; }
    .search-box input { padding-left: 35px; border-radius: 8px; border: 1px solid #ddd; }
    .search-box i { position: absolute; left: 12px; top: 10px; color: #888; }
    
    .admin-table { margin-bottom: 0; }
    .admin-table th { font-size: 0.7rem; text-transform: uppercase; color: #888; font-weight: 600; background-color: #fcfcfc; border-bottom: 1px solid #eee; padding: 10px 8px; }
    .admin-table td { font-size: 0.75rem; vertical-align: middle; border-bottom: 1px solid #f9f9f9; padding: 10px 8px; cursor: pointer; }
    .admin-table tbody tr:hover { background-color: #f9f9ff; }
    .admin-table th:first-child, .admin-table td:first-child { padding-left: 20px; }
    
    .badge-soft-success { background-color: #e8f5e9; color: #2e7d32; }
    .badge-soft-primary { background-color: #e3f2fd; color: #1565c0; }
    .badge-soft-danger { background-color: #ffebee; color: #c62828; }
    
    .action-btn {
        width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center;
        border-radius: 6px; border: 1px solid #eee; color: #666; background: #fff; transition: 0.2s;
    }
    .action-btn:hover { background: #f4f4f4; color: #333; }
    .action-btn-danger:hover { background: #ffebee; color: #c62828; border-color: #ffcdd2; }
    
    /* Side Panel Styles */
    #userDetailPanel {
        transition: opacity 0.3s ease;
    }
    .detail-avatar {
        width: 80px; height: 80px; font-size: 2rem;
        margin: 0 auto; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
    }
    .permission-item { font-size: 0.8rem; color: #555; margin-bottom: 8px; display: flex; align-items: center; gap: 8px; }
    .permission-item i { color: #28a745; }
</style>
@endpush

@section('content')
<div class="container-fluid py-2">


    <!-- KPIs -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="admin-card card p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="admin-card-title">Total Users</div>
                        <div class="admin-card-value">{{ number_format($totalUsers) }}</div>
                        <div class="admin-card-sub"><i class="bi bi-arrow-up-short"></i> {{ $newUsers }} this month</div>
                    </div>
                    <div class="admin-icon-box bg-light-primary text-primary" style="background-color: #e3f2fd; color: #1565c0;">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="admin-card card p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="admin-card-title">{{ __('Active Users') }}</div>
                        <div class="admin-card-value">{{ number_format($activeUsers) }}</div>
                        <div class="admin-card-sub"><i class="bi bi-arrow-up-short"></i> {{ $newActiveUsers }} this month</div>
                    </div>
                    <div class="admin-icon-box" style="background-color: #e8f5e9; color: #2e7d32;">
                        <i class="bi bi-person-check"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="admin-card card p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="admin-card-title">{{ __('Inactive Users') }}</div>
                        <div class="admin-card-value">{{ number_format($inactiveUsers) }}</div>
                        <div class="admin-card-sub text-danger"><i class="bi bi-arrow-down-short"></i> {{ $newInactiveUsers }} this month</div>
                    </div>
                    <div class="admin-icon-box" style="background-color: #ffebee; color: #c62828;">
                        <i class="bi bi-person-dash"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="admin-card card p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="admin-card-title">{{ __('Administrators') }}</div>
                        <div class="admin-card-value">{{ number_format($adminUsers) }}</div>
                        <div class="admin-card-sub"><i class="bi bi-arrow-up-short"></i> {{ $newAdmins }} this month</div>
                    </div>
                    <div class="admin-icon-box" style="background-color: #f3e5f5; color: #6a1b9a;">
                        <i class="bi bi-person-badge"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="admin-card card p-3 mb-3">
        <form method="GET" action="{{ route('admin.users.index') }}" class="toolbar-container">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" name="search" class="form-control" placeholder="{{ __('Search users by name or email...') }}" value="{{ request('search') }}">
            </div>
            
            <div class="d-flex gap-3 align-items-center">
                <div class="d-flex flex-column">
                    <label style="font-size:0.65rem; color:#888; font-weight:600; margin-bottom:2px;">{{ __('Role') }}</label>
                    <select name="role" class="form-select form-select-sm" style="min-width: 130px; border-radius: 8px;">
                        <option value="all" {{ request('role') == 'all' ? 'selected' : '' }}>{{ __('All Roles') }}</option>
                        <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>User</option>
                    </select>
                </div>
                <div class="d-flex flex-column">
                    <label style="font-size:0.65rem; color:#888; font-weight:600; margin-bottom:2px;">{{ __('Status') }}</label>
                    <select name="status" class="form-select form-select-sm" style="min-width: 130px; border-radius: 8px;">
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>{{ __('All Status') }}</option>
                        <option value="Active" {{ request('status') == 'Active' ? 'selected' : '' }}>Active</option>
                        <option value="Inactive" {{ request('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-navy btn-sm mt-3" style="border-radius: 8px; display: flex; align-items: center; gap: 5px;">
                    <i class="bi bi-funnel"></i>{{ __('Filter') }}</button>
            </div>
        </form>
    </div>

    <div class="row g-3">
        <!-- Main Table (Left Side) -->
        <div class="col-lg-8">
            <div class="admin-card card p-0">
                <div class="card-header bg-transparent border-0 px-4 pt-4 pb-2 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold m-0">{{ __('User List') }}</h6>
                    <span class="text-muted" style="font-size:0.75rem;">Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} users</span>
                </div>
        
                <div class="table-responsive">
                    <table class="table admin-table" id="usersTable">
                        <thead>
                            <tr>
                                <th style="width:30px;"><input class="form-check-input" type="checkbox"></th>
                                <th>{{ __('NAME') }}</th>
                                <th>{{ __('EMAIL') }}</th>
                                <th>{{ __('Role') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('JOINED AT') }}</th>
                                <th>{{ __('LAST LOGIN') }}</th>
                                <th class="text-center">{{ __('ACTION') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                            <tr onclick="showUserDetail({{ $user->id }})" id="row-{{ $user->id }}">
                                <td><input class="form-check-input" type="checkbox" onclick="event.stopPropagation()"></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-light-primary text-primary d-flex align-items-center justify-content-center fw-bold" style="width:32px; height:32px; font-size: 0.75rem;">
                                            @php
                                                $words = explode(' ', trim($user->name));
                                                $initials = count($words) > 1 
                                                    ? strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1))
                                                    : strtoupper(substr($words[0], 0, 2));
                                            @endphp
                                            {{ $initials }}
                                        </div>
                                        <div>
                                            <div class="fw-medium text-dark" style="line-height:1.2;">{{ $user->name }}</div>
                                            @if($user->country)
                                            <div style="font-size:0.65rem; color:#888;">
                                                <img src="https://flagcdn.com/w20/{{ strtolower($user->country->iso2_code) }}.png" alt="{{ $user->country->name }}" style="width:16px;" class="me-1 border rounded-1"> {{ $user->country->name }}
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-muted">{{ $user->email }}</td>
                                <td>
                                    <span class="badge {{ $user->role == 'admin' ? 'badge-soft-primary' : 'badge-soft-success' }} rounded-pill text-capitalize px-2 py-1">
                                        <i class="bi {{ $user->role == 'admin' ? 'bi-star' : 'bi-person' }} me-1"></i>{{ $user->role }}
                                    </span>
                                </td>
                                <td>
                                    <span style="font-size:0.7rem;" class="fw-bold {{ $user->status == 'Active' ? 'text-success' : 'text-danger' }}">
                                        <i class="bi bi-circle-fill me-1" style="font-size:0.4rem; vertical-align:middle;"></i>{{ $user->status }}
                                    </span>
                                </td>
                                <td class="text-muted" style="white-space:nowrap;">{{ $user->created_at->format('d M Y') }}</td>
                                <td class="text-muted" style="white-space:nowrap; font-size:0.7rem;">
                                    {{ $user->last_login_at ? $user->last_login_at->format('d M Y, H:i') : 'Never' }}
                                </td>
                                <td class="text-center" style="white-space:nowrap;">
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="action-btn me-1" onclick="event.stopPropagation();" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="event.stopPropagation(); return confirm('Are you sure you want to delete this user?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-btn action-btn-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 text-light"></i>
                                    No users found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($users->hasPages())
                <div class="card-footer bg-transparent border-0 p-3 d-flex justify-content-center">
                    {{ $users->links('pagination::bootstrap-5') }}
                </div>
                @endif
            </div>
        </div>
        
        <!-- User Detail Panel (Right Side) -->
        <div class="col-lg-4">
            <div class="admin-card card p-4" id="userDetailPanel" style="opacity: 0.5;">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold m-0">{{ __('User Detail') }}</h6>
                    <button class="btn-close" style="font-size:0.7rem;" onclick="hideUserDetail()"></button>
                </div>
                
                <div class="text-center mb-4">
                    <div class="detail-avatar bg-light-primary text-primary mb-3" id="detailAvatar" style="background-color: #e3f2fd; color: #1565c0;">--</div>
                    <h5 class="fw-bold mb-1" id="detailName">{{ __('Select a User') }}</h5>
                    <div class="mb-2" id="detailRoleBadge"></div>
                    <div style="font-size:0.8rem; color:#666;" id="detailCountry"></div>
                </div>
                
                <div class="border rounded p-3 mb-3 bg-light">
                    <h6 class="fw-bold mb-3" style="font-size:0.8rem;">{{ __('User Information') }}</h6>
                    <div style="font-size:0.8rem; margin-bottom:8px;"><i class="bi bi-envelope text-muted me-2"></i> <span id="detailEmail">--</span></div>
                    <div style="font-size:0.8rem; margin-bottom:8px;"><i class="bi bi-calendar3 text-muted me-2"></i> {{ __('Joined At') }}: <span id="detailJoined">--</span></div>
                    <div style="font-size:0.8rem; margin-bottom:8px;"><i class="bi bi-clock text-muted me-2"></i> {{ __('Last Login') }}: <span id="detailLastLogin">--</span></div>
                    <div style="font-size:0.8rem;"><i class="bi bi-shield-check text-muted me-2"></i> {{ __('Status') }}: <span id="detailStatusBadge">--</span></div>
                </div>
                
                <div class="border rounded p-3 mb-4 bg-light">
                    <h6 class="fw-bold mb-3" style="font-size:0.8rem;">{{ __('Permissions') }}</h6>
                    <div id="detailPermissions">
                        <div class="permission-item"><i class="bi bi-check-circle-fill"></i> {{ __('Access system dashboard') }}</div>
                        <div class="permission-item"><i class="bi bi-check-circle-fill"></i> {{ __('Manage port dataset') }}</div>
                    </div>
                </div>
                
                <div class="d-flex gap-2" id="detailActionButtons">
                    <!-- Buttons will be injected via JS -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Inject users data for JS -->
<script>
    const usersData = @json($users->items());
    
    function showUserDetail(userId) {
        // Find user
        const user = usersData.find(u => u.id === userId);
        if(!user) return;
        
        // Highlight row
        document.querySelectorAll('#usersTable tbody tr').forEach(tr => tr.style.backgroundColor = '');
        document.getElementById('row-' + userId).style.backgroundColor = '#f0f5ff';
        
        // Populate Panel
        document.getElementById('userDetailPanel').style.opacity = '1';
        
        // Initials logic
        const words = user.name.trim().split(/\s+/);
        const initials = words.length > 1 
            ? (words[0][0] + words[1][0]).toUpperCase()
            : user.name.substring(0, 2).toUpperCase();
            
        document.getElementById('detailAvatar').innerText = initials;
        document.getElementById('detailName').innerText = user.name;
        document.getElementById('detailEmail').innerText = user.email;
        
        // Format dates roughly
        const joinDate = new Date(user.created_at).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });
        document.getElementById('detailJoined').innerText = joinDate;
        
        if (user.last_login_at) {
            const loginDate = new Date(user.last_login_at).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' });
            document.getElementById('detailLastLogin').innerText = loginDate + ' WIB';
        } else {
            document.getElementById('detailLastLogin').innerText = 'Never';
        }
        
        // Role & Status Badges
        const roleClass = user.role === 'admin' ? 'badge-soft-primary' : 'badge-soft-success';
        const roleIcon = user.role === 'admin' ? 'bi-star' : 'bi-person';
        const displayRole = user.role === 'admin' ? '{{ __("Admin") }}' : '{{ __("User") }}';
        document.getElementById('detailRoleBadge').innerHTML = `<span class="badge ${roleClass} rounded-pill text-capitalize px-3 py-1"><i class="bi ${roleIcon} me-1"></i> ${displayRole}</span>`;
        
        const statusClass = user.status === 'Active' ? 'badge-soft-success' : 'badge-soft-danger';
        const displayStatus = user.status === 'Active' ? '{{ __("Active") }}' : '{{ __("Inactive") }}';
        document.getElementById('detailStatusBadge').innerHTML = `<span class="badge ${statusClass} rounded-pill px-2 py-1">${displayStatus}</span>`;
        
        // Country
        if(user.country) {
            const flagUrl = 'https://flagcdn.com/w20/' + user.country.iso2_code.toLowerCase() + '.png';
            document.getElementById('detailCountry').innerHTML = `<img src="${flagUrl}" style="width:20px;" class="me-1 border rounded-1"> ${user.country.name}`;
        } else {
            document.getElementById('detailCountry').innerHTML = '';
        }
        
        // Permissions
        let perms = '<div class="permission-item"><i class="bi bi-check-circle-fill"></i> {{ __("Access system dashboard") }}</div>';
        if (user.role === 'admin') {
            perms += '<div class="permission-item"><i class="bi bi-check-circle-fill"></i> {{ __("Manage port dataset") }}</div>';
            perms += '<div class="permission-item"><i class="bi bi-check-circle-fill"></i> {{ __("Manage analysis articles") }}</div>';
            perms += '<div class="permission-item"><i class="bi bi-check-circle-fill"></i> {{ __("Manage system users") }}</div>';
        }
        document.getElementById('detailPermissions').innerHTML = perms;
        
        // Action Buttons
        const editUrl = `{{ url('admin/users') }}/${user.id}/edit`;
        const deleteUrl = `{{ url('admin/users') }}/${user.id}`;
        
        document.getElementById('detailActionButtons').innerHTML = `
            <a href="${editUrl}" class="btn btn-outline-secondary flex-grow-1" style="border-radius:8px; font-size:0.8rem;">
                <i class="bi bi-pencil me-1"></i>{{ __('Edit User') }}</a>
            <form action="${deleteUrl}" method="POST" class="flex-grow-1 d-flex" onsubmit="return confirm('Are you sure you want to delete this user?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger w-100" style="border-radius:8px; font-size:0.8rem;">
                    <i class="bi bi-trash me-1"></i>{{ __('Delete') }}</button>
            </form>
        `;
    }
    
    function hideUserDetail() {
        document.getElementById('userDetailPanel').style.opacity = '0.5';
        document.getElementById('detailName').innerText = '{{ __("Select a User") }}';
        document.getElementById('detailAvatar').innerText = '--';
        document.querySelectorAll('#usersTable tbody tr').forEach(tr => tr.style.backgroundColor = '');
    }

    // Auto-select first user if exists
    if(usersData.length > 0) {
        showUserDetail(usersData[0].id);
    }
</script>
@endsection