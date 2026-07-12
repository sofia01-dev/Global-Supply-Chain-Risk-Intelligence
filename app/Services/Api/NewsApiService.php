<?php
namespace App\Services\Api;
use App\Models\NewsCache;

class NewsApiService
{
    public function getAllNews()
    {
        return NewsCache::latest('published_at')->get();
    }
}