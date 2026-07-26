@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Article Header -->
            <div class="mb-4 text-center">
                <div class="d-inline-block mb-3">
                    <span class="badge bg-primary px-3 py-2 rounded-pill fw-normal fs-6 shadow-sm">
                        {{ $article->category ?? 'General' }}
                    </span>
                </div>
                <h1 class="fw-bold mb-3" style="color: #0f172a; font-size: 2.5rem; letter-spacing: -0.5px; line-height: 1.3;">
                    {{ $article->title }}
                </h1>
                <div class="text-muted d-flex justify-content-center align-items-center gap-3">
                    <span><i class="bi bi-person-circle me-1"></i> {{ $article->author->name ?? 'Admin' }}</span>
                    <span><i class="bi bi-calendar3 me-1"></i> {{ \Carbon\Carbon::parse($article->published_at ?? $article->created_at)->format('d M Y') }}</span>
                </div>
            </div>

            <!-- Featured Image -->
            @if($article->image_path)
            <div class="mb-5 rounded-4 shadow-sm overflow-hidden" style="max-height: 450px;">
                <img src="{{ Storage::url($article->image_path) }}" alt="{{ $article->title }}" class="w-100 h-100" style="object-fit: cover;">
            </div>
            @elseif($article->image)
            <div class="mb-5 rounded-4 shadow-sm overflow-hidden" style="max-height: 450px;">
                <img src="{{ $article->image }}" alt="{{ $article->title }}" class="w-100 h-100" style="object-fit: cover;">
            </div>
            @endif

            <!-- Article Content -->
            <div class="card border-0 shadow-sm rounded-4 mb-5">
                <div class="card-body p-5">
                    <div class="article-content" style="font-size: 1.1rem; line-height: 1.8; color: #334155;">
                        {!! $article->content !!}
                    </div>
                </div>
            </div>
            
            <!-- Back Button -->
            <div class="text-center mb-5">
                <a href="{{ route('user.dashboard') }}" class="btn btn-outline-primary px-4 py-2 rounded-pill fw-semibold">
                    <i class="bi bi-arrow-left me-2"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Related Articles -->
    @if($relatedArticles->count() > 0)
    <div class="row justify-content-center mt-5">
        <div class="col-lg-10">
            <h4 class="fw-bold mb-4" style="color: #0f172a;">Baca Juga</h4>
            <div class="row">
                @foreach($relatedArticles as $related)
                <div class="col-md-4 mb-4">
                    <a href="{{ route('user.articles.show', $related->id) }}" class="text-decoration-none">
                        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden article-card-hover">
                            @if($related->image_path)
                            <img src="{{ Storage::url($related->image_path) }}" class="card-img-top" alt="{{ $related->title }}" style="height: 180px; object-fit: cover;">
                            @elseif($related->image)
                            <img src="{{ $related->image }}" class="card-img-top" alt="{{ $related->title }}" style="height: 180px; object-fit: cover;">
                            @else
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 180px;">
                                <i class="bi bi-image text-muted fs-1"></i>
                            </div>
                            @endif
                            <div class="card-body p-4">
                                <span class="text-primary fw-semibold" style="font-size: 0.8rem;">{{ $related->category ?? 'General' }}</span>
                                <h6 class="card-title fw-bold mt-2 text-dark" style="line-height: 1.4;">{{ Str::limit($related->title, 60) }}</h6>
                                <p class="card-text text-muted small mt-2">{{ \Carbon\Carbon::parse($related->published_at ?? $related->created_at)->format('d M Y') }}</p>
                            </div>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>

<style>
    .article-content h1, .article-content h2, .article-content h3 {
        font-weight: 700;
        color: #0f172a;
        margin-top: 1.5rem;
        margin-bottom: 1rem;
    }
    .article-content p {
        margin-bottom: 1.5rem;
    }
    .article-content img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin: 1.5rem 0;
    }
    .article-card-hover {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .article-card-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
    }
</style>
@endsection
