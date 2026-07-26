<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function show($id)
    {
        $article = Article::where('is_published', true)->findOrFail($id);
        
        // Fetch related articles (excluding the current one)
        $relatedArticles = Article::where('is_published', true)
            ->where('id', '!=', $id)
            ->latest()
            ->take(3)
            ->get();
            
        return view('user.articles.show', compact('article', 'relatedArticles'));
    }
}
