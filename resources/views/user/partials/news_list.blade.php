@if($news->isEmpty())
    <div class="empty-state text-center py-5 bg-white rounded-4 shadow-sm border border-secondary border-opacity-10">
        <i class="bi bi-journal-x text-muted opacity-50 mb-3 d-block" style="font-size: 4rem;"></i>
        <h5 class="fw-bold text-muted">{{ __('No News Found') }}</h5>
        <p class="text-muted small">{{ __('Try adjusting your filters or click Sync News to fetch latest data.') }}</p>
    </div>
@else
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center rounded-top-4">
            <h5 class="fw-bold text-dark mb-0">{{ __('Latest Articles') }}</h5>
            <span class="text-muted small" id="loadingIndicator" style="display: none;"><i class="bi bi-arrow-repeat bi-spin me-1"></i> Filtering...</span>
        </div>
        <div class="card-body p-0">
            <div class="d-flex flex-column">
                @foreach($news as $item)
                <div class="news-list-item p-4 {{ !$loop->last ? 'border-bottom' : '' }}" style="transition: background-color 0.2s ease;">
                    <div class="row g-0 align-items-center">
                        <!-- Image Section -->
                        <div class="col-md-3">
                            @php
                                $imgUrl = 'https://picsum.photos/seed/'.$item->id.'/400/200';
                                if($item->category === 'Shipping') $imgUrl = 'https://images.unsplash.com/photo-1494412685616-a5d310fbb07d?w=400&h=200&fit=crop';
                                if($item->category === 'Logistics') $imgUrl = 'https://images.unsplash.com/photo-1586528116311-ad8ed7c663c0?w=400&h=200&fit=crop';
                                if($item->category === 'Trade') $imgUrl = 'https://images.unsplash.com/photo-1578575437130-527eed3abbec?w=400&h=200&fit=crop';
                                if($item->category === 'Economy') $imgUrl = 'https://images.unsplash.com/photo-1611974789855-9c2a0a7236a3?w=400&h=200&fit=crop';
                            @endphp
                            <div class="position-relative h-100 me-md-4">
                                <img src="{{ $imgUrl }}" class="img-fluid rounded-3 object-fit-cover w-100" alt="News Image" style="height: 120px;">
                            </div>
                        </div>
                        
                        <!-- Content Section -->
                        <div class="col-md-9">
                            <div class="d-flex flex-column justify-content-center h-100">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <h6 class="fw-bold text-dark lh-sm mb-1" style="font-size: 1.05rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; padding-right: 15px;">
                                        <a href="{{ $item->url }}" target="_blank" class="text-decoration-none text-dark">{{ $item->title }}</a>
                                    </h6>
                                    @php
                                        $badgeClass = 'bg-secondary';
                                        if($item->sentiment_label === 'Positive') $badgeClass = 'bg-success';
                                        if($item->sentiment_label === 'Negative') $badgeClass = 'bg-danger';
                                        if($item->sentiment_label === 'Neutral') $badgeClass = 'bg-warning';
                                    @endphp
                                    <span class="badge {{ $badgeClass }} bg-opacity-10 text-{{ str_replace('bg-', '', $badgeClass) }} rounded-pill px-3 py-1 flex-shrink-0" style="font-size: 0.75rem;">
                                        {{ __($item->sentiment_label) }}
                                    </span>
                                </div>
                                
                                <p class="text-muted mb-2 pe-md-4" style="font-size: 0.85rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                    {{ Str::limit($item->summary ?? $item->title, 150) }}
                                </p>
                                
                                <div class="d-flex justify-content-between align-items-end mt-2">
                                    <div class="text-muted fw-medium d-flex align-items-center gap-3" style="font-size: 0.75rem;">
                                        <span><i class="bi bi-globe2 me-1"></i>{{ $item->country ? $item->country->name : 'Global' }}</span>
                                        <span style="opacity: 0.4;">•</span>
                                        <span>{{ $item->published_at ? \Carbon\Carbon::parse($item->published_at)->format('M d, Y') : 'Recently' }}</span>
                                        <span style="opacity: 0.4;">•</span>
                                        <span>{{ __($item->category) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    
    <div class="d-flex justify-content-center pb-3">
        {{ $news->links('pagination::bootstrap-5') }}
    </div>

    <style>
        .news-list-item:hover { background-color: rgba(0,0,0,0.01); }
    </style>
@endif
