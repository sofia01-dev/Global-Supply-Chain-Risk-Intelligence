@extends('layouts.app')
@section('content')
<div class="row mb-4"><div class="col"><h2>News Intelligence Dashboard</h2></div></div>
<div class="row">
    <div class="col-md-8">
        <div class="card"><div class="card-header">News List</div><div class="card-body">
            @if($news->isEmpty()) <p class="text-center text-muted">No data available</p>
            @else
                @foreach($news as $n) <div class="mb-2 border-bottom pb-2"><strong>{{ $n->title }}</strong></div> @endforeach
            @endif
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card"><div class="card-header">Sentiment Summary</div><div class="card-body text-center text-muted">No data available</div></div>
    </div>
</div>
@endsection