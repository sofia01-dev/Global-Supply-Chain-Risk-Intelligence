@extends('layouts.app')
@section('content')
<div class="container">
    <h2>Manage Articles</h2>
    @if($articles->isEmpty())
        <div class="alert alert-info mt-3">No articles available.</div>
    @else
        <table class="table table-striped mt-3">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($articles as $article)
                <tr>
                    <td>{{ $article->title }}</td>
                    <td>{{ $article->author ?? 'Unknown' }}</td>
                    <td>
                        <a href="{{ url('/admin/articles/'.$article->id) }}" class="btn btn-sm btn-info text-white">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection