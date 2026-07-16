@extends('layouts.app')
@section('content')
<div class="container">
    <h2>Article Details</h2>
    @if(!$article)
        <div class="alert alert-warning">Article not found.</div>
    @else
        <div class="card mt-3">
            <div class="card-body">
                <h3>{{ $article->title }}</h3>
                <p class="text-muted">By {{ $article->author ?? 'Unknown' }}</p>
                <p>{{ $article->content }}</p>
                <a href="{{ url('/admin/articles') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>
    @endif
</div>
@endsection