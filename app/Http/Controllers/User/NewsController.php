<?php
namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;
use App\Services\Api\NewsApiService;

class NewsController extends Controller
{
    protected $newsApiService;
    public function __construct(NewsApiService $newsApiService) {
        $this->newsApiService = $newsApiService;
    }
    public function index() {
        $news = $this->newsApiService->getAllNews();
        return view('user.news', compact('news'));
    }
}