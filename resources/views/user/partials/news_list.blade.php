@if($news->isEmpty())
    <div class="empty-state text-center py-5 bg-white rounded-4 shadow-sm">
        <i class="bi bi-journal-x text-muted opacity-50 mb-3 d-block" style="font-size: 4rem;"></i>
        <h5 class="fw-bold text-muted">{{ __('No News Found') }}</h5>
        <p class="text-muted small">{{ __('Try adjusting your filters or click Sync News to fetch latest data.') }}</p>
    </div>
@else
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
        @foreach($news as $item)
        <div class="col">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden news-card position-relative" style="transition: transform 0.2s ease;">
                <!-- Fallback image logic -->
                @php
                    $imgUrl = 'https://picsum.photos/seed/'.$item->id.'/400/200';
                    if($item->category === 'Shipping') $imgUrl = 'https://images.unsplash.com/photo-1494412685616-a5d310fbb07d?w=400&h=200&fit=crop';
                    if($item->category === 'Logistics') $imgUrl = 'https://images.unsplash.com/photo-1586528116311-ad8ed7c663c0?w=400&h=200&fit=crop';
                    if($item->category === 'Trade') $imgUrl = 'https://images.unsplash.com/photo-1578575437130-527eed3abbec?w=400&h=200&fit=crop';
                    if($item->category === 'Economy') $imgUrl = 'https://images.unsplash.com/photo-1611974789855-9c2a0a7236a3?w=400&h=200&fit=crop';
                @endphp
                <img src="{{ $imgUrl }}" class="card-img-top object-fit-cover" alt="News Image" style="height: 160px;">
                
                <!-- Category Badge -->
                <div class="position-absolute top-0 start-0 m-3">
                    <span class="badge bg-dark bg-opacity-75 text-white rounded-pill px-3 py-1 shadow-sm" style="backdrop-filter: blur(4px);">{{ __($item->category) }}</span>
                </div>
                
                <div class="card-body d-flex flex-column p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-muted fw-semibold" style="font-size: 0.75rem;"><i class="bi bi-clock me-1"></i>{{ $item->published_at ? $item->published_at->diffForHumans() : 'Recently' }}</small>
                        @php
                            $badgeClass = 'bg-secondary';
                            if($item->sentiment_label === 'Positive') $badgeClass = 'bg-success';
                            if($item->sentiment_label === 'Negative') $badgeClass = 'bg-danger';
                        @endphp
                        <span class="badge {{ $badgeClass }} bg-opacity-10 text-{{ str_replace('bg-', '', $badgeClass) }} rounded-pill" style="font-size: 0.7rem;">
                            {{ __($item->sentiment_label) }}
                        </span>
                    </div>
                    
                    <h6 class="card-title fw-bold text-dark lh-base mb-2" style="font-size: 0.95rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                        {{ $item->title }}
                    </h6>
                    
                    <p class="card-text text-muted mb-4" style="font-size: 0.8rem; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                        {{ Str::limit($item->summary ?? $item->title, 120) }}
                    </p>
                    
                    <div class="mt-auto d-flex justify-content-between align-items-center">
                        <span class="text-muted small fw-medium" style="font-size: 0.75rem;">
                            <i class="bi bi-geo-alt-fill text-primary me-1"></i>{{ $item->country ? $item->country->name : 'Global' }}
                        </span>
                        <a href="{{ $item->url }}" target="_blank" class="btn btn-sm btn-light text-primary fw-semibold rounded-pill px-3 border" style="font-size: 0.75rem;">
                            {{ __('Read Article') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    
    <div class="mt-4 d-flex justify-content-center">
        {{ $news->links('pagination::bootstrap-5') }}
    </div>

    <style>
        .news-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important; border-color: rgba(28,85,255,0.1) !important; }
    </style>
@endif
