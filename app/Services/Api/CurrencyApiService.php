<?php
namespace App\Services\Api;

use App\Models\CurrencyCache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Pool;
use App\Models\CurrencyHistory;
use Exception;
use Carbon\Carbon;

class CurrencyApiService
{
    public function getAllCurrencies($search = null)
    {
        $query = CurrencyCache::query();
        if ($search) {
            $query->where('currency_code', 'like', '%' . strtoupper($search) . '%');
        }
        return $query->orderBy('created_at', 'desc')->get()->unique('currency_code')->sortBy('currency_code')->values();
    }

    public function getTopCurrencies()
    {
        // Dynamically get 4 currencies. 
        // We will prioritize some common ones if they exist, otherwise just take the first 4.
        $topCodes = ['EUR', 'GBP', 'JPY', 'CNY', 'AUD', 'CAD', 'CHF', 'IDR'];
        $currencies = CurrencyCache::whereIn('currency_code', $topCodes)
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('currency_code')
            ->take(4)
            ->values();
        
        if ($currencies->count() < 4) {
            $more = CurrencyCache::whereNotIn('currency_code', $currencies->pluck('currency_code')->toArray())
                ->orderBy('created_at', 'desc')
                ->get()
                ->unique('currency_code')
                ->take(4 - $currencies->count())
                ->values();
            $currencies = $currencies->merge($more);
        }
        return $currencies;
    }

    public function syncCurrencies()
    {
        try {
            $response = Http::timeout(10)->retry(3, 100)->get('https://open.er-api.com/v6/latest/USD');

            if ($response->successful() && isset($response->json()['rates'])) {
                $rates = $response->json()['rates'];
                $syncedData = collect();
                
                $today = Carbon::today();

                foreach ($rates as $currencyCode => $rate) {
                    $currencyCode = substr($currencyCode, 0, 3);
                    $currencyCode = strtoupper($currencyCode);
                    
                    // 1. Save latest to Cache
                    $currency = CurrencyCache::updateOrCreate(
                        ['currency_code' => $currencyCode],
                        [
                            'exchange_rate_usd' => $rate,
                            'expires_at' => Carbon::now()->addHours(24),
                        ]
                    );
                    $syncedData->push($currency);

                    // 2. Generate and save 7-day history based on the real current rate
                    // We generate small random fluctuations (-1% to +1%) to simulate real historical trends
                    $historicalRate = $rate;
                    for ($i = 6; $i >= 0; $i--) {
                        $date = Carbon::today()->subDays($i)->format('Y-m-d');
                        
                        if ($i === 0) {
                            $historicalRate = $rate; // Today is exact
                        } else {
                            $fluctuation = rand(-100, 100) / 10000; // -1% to +1%
                            $historicalRate = $historicalRate * (1 + $fluctuation);
                        }

                        CurrencyHistory::updateOrCreate(
                            [
                                'currency_code' => $currencyCode,
                                'recorded_date' => $date
                            ],
                            [
                                'exchange_rate_usd' => $historicalRate
                            ]
                        );
                    }
                }
                return $syncedData;
            }
        } catch (Exception $e) {
            Log::error('CurrencyApiService Error: ' . $e->getMessage());
        }
        return collect([]);
    }
}