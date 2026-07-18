<?php
namespace App\Services\Api;

use App\Models\NewsCache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;
use Carbon\Carbon;

class NewsApiService
{
    public function syncNews()
    {
        try {
            $apiKey = env('GNEWS_API_KEY', 'demo'); 
            $query = 'logistics OR shipping OR trade OR economy';
            
            $response = Http::timeout(15)->retry(2, 100)->get('https://gnews.io/api/v4/search', [
                'q' => $query,
                'lang' => 'en',
                'max' => 30,
                'apikey' => $apiKey
            ]);

            if ($response->successful()) {
                $json = $response->json();
                if (is_array($json) && isset($json['articles'])) {
                    $articles = $json['articles'];
                    $syncedData = collect();
                    
                    // Retrieve Lexicon Sentiment Service
                    $sentimentService = app(\App\Services\AI\LexiconSentimentService::class);
                    // Remove random country mapping for global sync
                    
                    foreach ($articles as $article) {
                        $title = $article['title'] ?? '';
                        $desc = $article['description'] ?? '';
                        $content = strtolower($title . ' ' . $desc);
                        
                        $category = 'Global';
                        if (strpos($content, 'shipping') !== false) {
                            $category = 'Shipping';
                        } elseif (strpos($content, 'logistics') !== false) {
                            $category = 'Logistics';
                        } elseif (strpos($content, 'trade') !== false) {
                            $category = 'Trade';
                        } elseif (strpos($content, 'economy') !== false || strpos($content, 'inflation') !== false) {
                            $category = 'Economy';
                        }
                        
                        $sentimentLabel = $sentimentService->analyzeText($content);
                        
                        $countryId = null; // Global news remains global

                        $news = NewsCache::updateOrCreate(
                            ['url' => substr($article['url'], 0, 500)],
                            [
                                'title' => substr($title, 0, 500),
                                'image_url' => $article['image'] ?? null,
                                'category' => $category,
                                'country_id' => $countryId,
                                'sentiment_label' => $sentimentLabel,
                                'published_at' => isset($article['publishedAt']) ? Carbon::parse($article['publishedAt'])->format('Y-m-d H:i:s') : now(),
                            ]
                        );
                        $syncedData->push($news);
                    }
                    return $syncedData;
                }
            } else {
                Log::warning('News API failed: ' . $response->status() . ' - ' . $response->body());
            }
        } catch (\Throwable $e) {
            Log::error('NewsApiService Error: ' . $e->getMessage());
        }
        return collect([]);
    }

    public function syncNewsForCountry(\App\Models\Country $country)
    {
        try {
            $apiKey = env('GNEWS_API_KEY', 'demo'); 
            // Broaden search: any of these keywords + country name
            $query = '("logistics" OR "supply chain" OR "economy" OR "trade") AND "' . $country->name . '"';
            
            $response = Http::timeout(15)->retry(2, 100)->get('https://gnews.io/api/v4/search', [
                'q' => $query,
                'lang' => 'en',
                'max' => 5,
                'apikey' => $apiKey
            ]);

            if ($response->successful()) {
                $json = $response->json();
                if (is_array($json) && isset($json['articles'])) {
                    $articles = $json['articles'];
                    $syncedData = collect();
                    
                    $sentimentService = app(\App\Services\AI\LexiconSentimentService::class);

                    foreach ($articles as $article) {
                        $title = $article['title'] ?? '';
                        $desc = $article['description'] ?? '';
                        $content = strtolower($title . ' ' . $desc);
                        
                        $category = 'Global';
                        if (strpos($content, 'shipping') !== false) {
                            $category = 'Shipping';
                        } elseif (strpos($content, 'logistics') !== false || strpos($content, 'supply chain') !== false) {
                            $category = 'Logistics';
                        } elseif (strpos($content, 'trade') !== false) {
                            $category = 'Trade';
                        } else {
                            $category = 'Economy';
                        }
                        
                        $sentimentLabel = $sentimentService->analyzeText($content);

                        $news = NewsCache::updateOrCreate(
                            ['url' => substr($article['url'], 0, 500)],
                            [
                                'title' => substr($title, 0, 500),
                                'image_url' => $article['image'] ?? null,
                                'category' => $category,
                                'country_id' => $country->id, // Specifically assign to this country
                                'sentiment_label' => $sentimentLabel,
                                'published_at' => isset($article['publishedAt']) ? Carbon::parse($article['publishedAt'])->format('Y-m-d H:i:s') : now(),
                            ]
                        );
                        $syncedData->push($news);
                    }
                    return $syncedData;
                }
            } else {
                Log::warning('News API (Country) failed: ' . $response->status() . ' - ' . $response->body());
            }
        } catch (\Throwable $e) {
            Log::error('NewsApiService Country Sync Error: ' . $e->getMessage());
        }
        return collect([]);
    }

    public function getAllNews($search = null, $category = null, $countryId = null)
    {
        $query = NewsCache::with('country')->orderBy('published_at', 'desc');

        if (!empty($search)) {
            $query->where('title', 'like', '%' . $search . '%');
        }

        if (!empty($category) && $category !== 'All Categories') {
            $query->where('category', $category);
        }

        if (!empty($countryId) && $countryId !== 'All Countries') {
            $query->where('country_id', $countryId);
        }

        return $query->paginate(5)->withQueryString();
    }

    public function getCategoryStatistics()
    {
        $totalNews = NewsCache::count();
        $categories = ['Shipping', 'Trade', 'Economy', 'Logistics'];
        $stats = [];
        
        foreach ($categories as $cat) {
            $count = NewsCache::where('category', $cat)->count();
            $percentage = $totalNews > 0 ? round(($count / $totalNews) * 100, 1) : 0;
            $stats[$cat] = [
                'count' => $count,
                'percentage' => $percentage
            ];
        }
        
        return [
            'total' => $totalNews,
            'categories' => $stats
        ];
    }
}