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
                'max' => 10,
                'apikey' => $apiKey
            ]);

            if ($response->successful()) {
                $json = $response->json();
                if (is_array($json) && isset($json['articles'])) {
                    $articles = $json['articles'];
                    $syncedData = collect();
                    
                    // Retrieve Lexicon Sentiment Service
                    $sentimentService = app(\App\Services\AI\LexiconSentimentService::class);
                    // Retrieve a random country id for demo mapping if no direct country mapping is available from gnews
                    $countries = \App\Models\Country::pluck('id')->toArray();

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
                        
                        $countryId = null;
                        if (!empty($countries)) {
                            $countryId = $countries[array_rand($countries)];
                        }

                        $news = NewsCache::updateOrCreate(
                            ['url' => substr($article['url'], 0, 500)],
                            [
                                'title' => substr($title, 0, 500),
                                'category' => $category,
                                'country_id' => $countryId,
                                'sentiment_label' => $sentimentLabel,
                                'published_at' => isset($article['publishedAt']) ? Carbon::parse($article['publishedAt'])->format('Y-m-d H:i:s') : now(),
                                // Assuming we store image url in the 'url' or another column if we have one. 
                                // Wait, the existing NewsCache doesn't have an image_url column. 
                                // Let's check NewsCache fillable. It has title, url, category, country_id, sentiment_label, published_at. 
                                // No image_url. We'll skip image from db, or we can just add it if we modify migration, but user said "Jangan mengubah struktur database jika tidak diperlukan." 
                                // We can't save thumbnail without column. 
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