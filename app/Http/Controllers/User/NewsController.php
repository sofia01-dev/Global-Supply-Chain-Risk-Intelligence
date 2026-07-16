<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\Api\NewsApiService;
use App\Services\AI\LexiconSentimentService;
use App\Services\Shipment\RecommendationService;
use App\Models\Country;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    protected $newsApiService;
    protected $lexiconSentimentService;
    protected $recommendationService;

    public function __construct(
        NewsApiService $newsApiService,
        LexiconSentimentService $lexiconSentimentService,
        RecommendationService $recommendationService
    ) {
        $this->newsApiService = $newsApiService;
        $this->lexiconSentimentService = $lexiconSentimentService;
        $this->recommendationService = $recommendationService;
    }

    public function index(Request $request)
    {
        $search = $request->query('search');
        $category = $request->query('category');
        $countryId = $request->query('country');

        $news = $this->newsApiService->getAllNews($search, $category, $countryId);
        $categoryStats = $this->newsApiService->getCategoryStatistics();
        
        $sentimentStats = $this->lexiconSentimentService->analyzeGlobalSentiment();
        $marketInsight = $this->recommendationService->generateNewsMarketInsight($sentimentStats);

        $countries = Country::whereHas('newsCaches')->get();

        if ($request->ajax()) {
            return response()->json([
                'news_html' => view('user.partials.news_list', compact('news'))->render(),
                'category_stats' => $categoryStats,
                'sentiment_stats' => $sentimentStats,
                'market_insight' => $marketInsight
            ]);
        }

        return view('user.news', compact('news', 'categoryStats', 'sentimentStats', 'marketInsight', 'countries', 'search', 'category', 'countryId'));
    }

    public function sync(Request $request)
    {
        $this->newsApiService->syncNews();
        
        // Re-fetch everything for the updated view
        $search = $request->query('search');
        $category = $request->query('category');
        $countryId = $request->query('country');

        $news = $this->newsApiService->getAllNews($search, $category, $countryId);
        $categoryStats = $this->newsApiService->getCategoryStatistics();
        
        $sentimentStats = $this->lexiconSentimentService->analyzeGlobalSentiment();
        $marketInsight = $this->recommendationService->generateNewsMarketInsight($sentimentStats);

        return response()->json([
            'success' => true,
            'news_html' => view('user.partials.news_list', compact('news'))->render(),
            'category_stats' => $categoryStats,
            'sentiment_stats' => $sentimentStats,
            'market_insight' => $marketInsight
        ]);
    }
}