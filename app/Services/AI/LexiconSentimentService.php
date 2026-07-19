<?php
namespace App\Services\AI;

use App\Models\NewsCache;

class LexiconSentimentService
{
    protected $positiveWords = [];
    protected $negativeWords = [];

    public function __construct()
    {
        $this->positiveWords = \App\Models\PositiveWord::pluck('word')->toArray();
        $this->negativeWords = \App\Models\NegativeWord::pluck('word')->toArray();
    }

    public function analyzeGlobalSentiment()
    {
        $newsItems = NewsCache::all();

        if ($newsItems->isEmpty()) {
            return [
                'positive_pct' => 0,
                'neutral_pct' => 100,
                'negative_pct' => 0,
                'overall_sentiment' => 'NEUTRAL',
                'description' => 'No news data available for analysis.',
            ];
        }

        $totalPositive = 0;
        $totalNegative = 0;
        $totalNeutral = 0;

        foreach ($newsItems as $news) {
            $sentiment = $this->analyzeText($news->title . ' ' . $news->summary);
            
            if ($sentiment === 'Positive') {
                $totalPositive++;
            } elseif ($sentiment === 'Negative') {
                $totalNegative++;
            } else {
                $totalNeutral++;
            }
        }

        $totalNews = $newsItems->count();
        $posPct = round(($totalPositive / $totalNews) * 100);
        $negPct = round(($totalNegative / $totalNews) * 100);
        $neuPct = 100 - ($posPct + $negPct); // Ensure it adds up to 100%

        $overall = 'NEUTRAL';

        if ($posPct > $negPct && $posPct > 40) {
            $overall = 'POSITIVE';
        } elseif ($negPct > $posPct && $negPct > 30) {
            $overall = 'NEGATIVE';
        }

        return [
            'positive_pct' => $posPct,
            'neutral_pct' => $neuPct,
            'negative_pct' => $negPct,
            'overall_sentiment' => $overall,
        ];
    }

    public function analyzeCountrySentiment($countryId)
    {
        $newsItems = NewsCache::where('country_id', $countryId)->get();

        if ($newsItems->isEmpty()) {
            return [
                'positive_pct' => 0,
                'neutral_pct' => 100,
                'negative_pct' => 0,
                'overall_sentiment' => 'NEUTRAL',
            ];
        }

        $totalPositive = 0;
        $totalNegative = 0;
        $totalNeutral = 0;

        foreach ($newsItems as $news) {
            $sentiment = $this->analyzeText($news->title . ' ' . $news->summary);
            if ($sentiment === 'Positive') {
                $totalPositive++;
            } elseif ($sentiment === 'Negative') {
                $totalNegative++;
            } else {
                $totalNeutral++;
            }
        }

        $totalNews = $newsItems->count();
        $posPct = round(($totalPositive / $totalNews) * 100);
        $negPct = round(($totalNegative / $totalNews) * 100);
        $neuPct = 100 - ($posPct + $negPct);

        $overall = 'NEUTRAL';
        if ($posPct > $negPct && $posPct > 40) {
            $overall = 'POSITIVE';
        } elseif ($negPct > $posPct && $negPct > 30) {
            $overall = 'NEGATIVE';
        }

        return [
            'positive_pct' => $posPct,
            'neutral_pct' => $neuPct,
            'negative_pct' => $negPct,
            'overall_sentiment' => $overall,
        ];
    }

    public function analyzeText($text)
    {
        // Lowercase
        $text = strtolower($text);
        
        // Remove punctuation
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
        
        // Split into words
        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        
        $posCount = 0;
        $negCount = 0;

        foreach ($words as $word) {
            if (in_array($word, $this->positiveWords)) {
                $posCount++;
            } elseif (in_array($word, $this->negativeWords)) {
                $negCount++;
            }
        }

        if ($posCount > $negCount) {
            return 'Positive';
        } elseif ($negCount > $posCount) {
            return 'Negative';
        }

        return 'Neutral';
    }
}
