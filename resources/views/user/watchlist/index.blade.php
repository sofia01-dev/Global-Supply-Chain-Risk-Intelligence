@extends('layouts.app')

@push('styles')
<!-- Flag Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.0.0/css/flag-icons.min.css"/>
@endpush

@section('title', __('Favorites Monitoring'))

@section('page_header')
<div class="d-none d-sm-block">
    <h5 class="m-0 fw-semibold text-dark">{{ __('Favorites Monitoring') }}</h5>
    <div class="text-muted" style="font-size: 0.75rem;">{{ __('Track and monitor your favorited countries in real-time.') }}</div>
</div>
@endsection

@section('content')
<div class="container-fluid px-4 py-4">

    @if($countries->isEmpty())
        <div class="card border-0 shadow-sm rounded-4 text-center py-5">
            <div class="card-body">
                <div class="display-1 text-muted mb-3"><i class="bi bi-star"></i></div>
                <h5 class="fw-bold text-dark">{{ __('No Favorites Yet') }}</h5>
                <p class="text-muted mb-4">{{ __('You haven\'t added any countries to your favorites list yet.') }}</p>
                <a href="{{ route('user.countries.index') }}" class="btn btn-primary rounded-pill px-4 fw-bold">
                    {{ __('Explore Countries') }}
                </a>
            </div>
        </div>
    @else
        <div class="row g-4">
            @foreach($countries as $country)
                <div class="col-lg-4 col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100 position-relative overflow-hidden">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle overflow-hidden d-flex align-items-center justify-content-center bg-light" style="width: 48px; height: 48px;">
                                        <span class="fi fi-{{ strtolower($country->iso2_code ?? 'un') }}" style="font-size: 3rem; border-radius: 50%;"></span>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-0 text-dark">{{ $country->name }}</h5>
                                        <span class="text-muted small">{{ __($country->region ?? 'Unknown Region') }}</span>
                                    </div>
                                </div>
                                <button class="btn btn-sm btn-light rounded-circle text-warning btn-remove-favorite shadow-sm" data-id="{{ $country->id }}" title="{{ __('Remove from favorites') }}">
                                    <i class="bi bi-star-fill"></i>
                                </button>
                            </div>

                            @php
                                $riskLevel = $country->riskData['level'] ?? 'N/A';
                                $riskColor = str_contains($riskLevel, 'High') ? 'danger' : (str_contains($riskLevel, 'Medium') ? 'warning' : 'success');
                                $weather = $country->weatherCaches->first();
                            @endphp

                            <div class="d-flex flex-wrap gap-2 mb-4">
                                <span class="badge bg-{{ $riskColor }} bg-opacity-10 text-{{ $riskColor }} px-3 py-2 rounded-pill">
                                    <i class="bi bi-shield-exclamation me-1"></i> {{ __($riskLevel) }}
                                </span>
                                @if($weather)
                                <span class="badge bg-info bg-opacity-10 text-info px-3 py-2 rounded-pill">
                                    <i class="bi bi-cloud-sun me-1"></i> {{ round($weather->temperature) }}°C
                                </span>
                                @endif
                                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                                    <i class="bi bi-currency-dollar me-1"></i> {{ $country->currency_code ?? 'N/A' }}
                                </span>
                            </div>

                            <a href="{{ route('user.country', $country->id) }}" class="btn btn-outline-primary w-100 rounded-pill fw-bold">
                                {{ __('View Full Details') }}
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const removeBtns = document.querySelectorAll('.btn-remove-favorite');
        removeBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const countryId = this.getAttribute('data-id');
                const cardCol = this.closest('.col-lg-4');
                
                if (confirm('{{ __("Are you sure you want to remove this country from favorites?") }}')) {
                    this.disabled = true;
                    fetch('{{ route("user.watchlist.toggle") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ country_id: countryId })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'removed') {
                            cardCol.remove();
                            // Optional: If no cards left, reload to show empty state
                            if (document.querySelectorAll('.btn-remove-favorite').length === 0) {
                                window.location.reload();
                            }
                        }
                    })
                    .catch(error => console.error('Error:', error))
                    .finally(() => {
                        if(this) this.disabled = false;
                    });
                }
            });
        });
    });
</script>
@endpush
@endsection
