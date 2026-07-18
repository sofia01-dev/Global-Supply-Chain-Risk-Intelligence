@extends('layouts.app')
@section('content')
<div class="row mb-4">
    <div class="col d-flex justify-content-between align-items-center">
        <h2 class="mb-0 fw-bold">{{ __('My Shipments') }}</h2>
        <a href="{{ route('user.shipments.create') }}" class="btn fw-bold px-4 rounded-pill shadow-sm text-white" style="background-color: var(--primary-navy);"><i class="bi bi-plus-lg me-1"></i> {{ __('Create Shipment') }}</a>
    </div>
</div>

<!-- Search & Filter Card -->
<div class="card border-0 shadow-sm mb-4 bg-white rounded-3">
    <div class="card-body p-4">
        <form action="{{ route('user.shipments.index') }}" method="GET" id="filterForm">
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label text-muted small fw-bold">{{ __('Search') }}</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 bg-light" placeholder="{{ __('Code, Country, Port...') }}" value="{{ $filters['search'] ?? '' }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small fw-bold">{{ __('Status') }}</label>
                    <select name="status" class="form-select bg-light">
                        <option value="All" {{ ($filters['status'] ?? '') == 'All' ? 'selected' : '' }}>{{ __('All Status') }}</option>
                        <option value="Pending" {{ ($filters['status'] ?? '') == 'Pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                        <option value="In Transit" {{ ($filters['status'] ?? '') == 'In Transit' ? 'selected' : '' }}>{{ __('In Transit') }}</option>
                        <option value="Delayed" {{ ($filters['status'] ?? '') == 'Delayed' ? 'selected' : '' }}>{{ __('Delayed') }}</option>
                        <option value="Delivered" {{ ($filters['status'] ?? '') == 'Delivered' ? 'selected' : '' }}>{{ __('Delivered') }}</option>
                        <option value="Cancelled" {{ ($filters['status'] ?? '') == 'Cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small fw-bold">{{ __('Risk Level') }}</label>
                    <select name="risk_level" class="form-select bg-light">
                        <option value="All" {{ ($filters['risk_level'] ?? '') == 'All' ? 'selected' : '' }}>{{ __('All Risks') }}</option>
                        <option value="Low" {{ ($filters['risk_level'] ?? '') == 'Low' ? 'selected' : '' }}>{{ __('Low') }}</option>
                        <option value="Medium" {{ ($filters['risk_level'] ?? '') == 'Medium' ? 'selected' : '' }}>{{ __('Medium') }}</option>
                        <option value="High" {{ ($filters['risk_level'] ?? '') == 'High' ? 'selected' : '' }}>{{ __('High') }}</option>
                        <option value="Critical" {{ ($filters['risk_level'] ?? '') == 'Critical' ? 'selected' : '' }}>{{ __('Critical') }}</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn w-100 fw-bold" id="btnFilter" style="background-color: #CCD4DE; color: var(--primary-navy); border: none;">
                        <span class="spinner-border spinner-border-sm d-none" id="filterSpinner" role="status" aria-hidden="true"></span>
                        <span id="filterText">{{ __('Filter') }}</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3 overflow-hidden">
    <div class="card-body p-0" id="tableContainer">
        <div class="table-responsive">
            <table class="table table-hover align-middle text-sm mb-0">
                <style>
                    .custom-thead th {
                        background-color: var(--primary-navy) !important;
                        color: white !important;
                        border-bottom: none !important;
                    }
                </style>
                <thead class="custom-thead" style="font-size: 0.85rem;">
                    <tr>
                        <th class="ps-4 py-3 border-bottom-0">{{ __('Shipment Code') }}</th>
                        <th class="py-3 border-bottom-0">{{ __('Goods') }}</th>
                        <th class="py-3 border-bottom-0">{{ __('Route') }}</th>
                        <th class="py-3 border-bottom-0">{{ __('Current Stage') }}</th>
                        <th class="py-3 border-bottom-0">{{ __('Risk Level') }}</th>
                        <th class="py-3 border-bottom-0">{{ __('ETA') }}</th>
                        <th class="py-3 border-bottom-0">{{ __('Status') }}</th>
                        <th class="py-3 border-bottom-0">{{ __('Last Updated') }}</th>
                        <th class="pe-4 py-3 border-bottom-0 text-end">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @if($shipments->isEmpty())
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="empty-state d-flex flex-column align-items-center justify-content-center text-muted">
                                    <div class="rounded-circle bg-light p-4 mb-3 d-inline-flex">
                                        <i class="bi bi-box-seam fs-1 text-secondary opacity-50"></i>
                                    </div>
                                    <h5 class="fw-bold text-dark mb-1">{{ __('No Shipment Available') }}</h5>
                                    <p class="small mb-0">{{ __('We couldn\'t find any shipments matching your criteria.') }}</p>
                                </div>
                            </td>
                        </tr>
                    @else
                        @foreach($shipments as $shipment)
                            @php
                                $mon = $shipment->monitoring;
                                $statusColor = $mon['current_status'] === 'Delayed' ? 'warning' : ($mon['current_status'] === 'At Risk' ? 'danger' : 'success');
                                $levelColor = $mon['risk_level'] === 'Critical' ? 'danger' : ($mon['risk_level'] === 'High' ? 'warning' : ($mon['risk_level'] === 'Medium' ? 'info' : 'success'));
                                $eta = $shipment->estimated_arrival ? \Carbon\Carbon::parse($shipment->estimated_arrival)->diffForHumans() : 'N/A';
                            @endphp
                            <tr>
                                <td class="ps-4 py-3 fw-bold text-primary">{{ $shipment->shipment_code }}</td>
                                <td class="py-3 text-dark fw-medium">{{ $shipment->goods ?? '-' }}</td>
                                <td class="py-3">
                                    <div class="d-flex flex-row align-items-center gap-2" style="font-size:0.85rem">
                                        <span class="text-dark">{{ $mon['origin']['country'] }}</span>
                                        <i class="bi bi-arrow-right text-muted" style="font-size: 0.7rem;"></i>
                                        <span class="text-dark">{{ $mon['destination']['country'] }}</span>
                                    </div>
                                </td>
                                <td class="py-3 text-dark">{{ $mon['destination']['port'] }}</td>
                                <td class="py-3"><span class="badge bg-{{ $levelColor }} px-2 py-1">{{ __($mon['risk_level']) }}</span></td>
                                <td class="py-3 text-dark">{{ $eta }}</td>
                                <td class="py-3"><span class="badge bg-light-{{ $statusColor }} text-{{ $statusColor }} px-2 py-1">{{ __($mon['current_status']) }}</span></td>
                                <td class="py-3 text-muted" style="font-size:0.8rem">{{ \Carbon\Carbon::parse($mon['last_updated'])->format('d M Y H:i') }}</td>
                                <td class="pe-4 py-3 text-end">
                                    <div class="d-flex gap-1 justify-content-end">
                                        <a href="{{ route('user.shipments.show', $shipment->id) }}" class="btn btn-sm btn-light text-primary border" title="Monitor">
                                            <i class="bi bi-eye"></i> {{ __('Monitor') }}
                                        </a>
                                        <a href="{{ route('user.shipments.edit', $shipment->id) }}" class="btn btn-sm btn-light text-warning border" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('user.shipments.destroy', $shipment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this shipment?') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light text-danger border" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Pagination -->
    @if($shipments->hasPages())
        <div class="card-footer bg-white border-top py-3 d-flex justify-content-end">
            {{ $shipments->appends(request()->query())->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
    document.getElementById('filterForm').addEventListener('submit', function() {
        document.getElementById('filterText').classList.add('d-none');
        document.getElementById('filterSpinner').classList.remove('d-none');
        document.getElementById('tableContainer').style.opacity = '0.6';
    });
</script>
@endpush
@endsection