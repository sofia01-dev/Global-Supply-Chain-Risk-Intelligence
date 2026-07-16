<?php
namespace App\Services\Api;

use App\Models\CurrencyCache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
        return $query->orderBy('currency_code')->get();
    }

    public function getTopCurrencies()
    {
        // Dynamically get 4 currencies. 
        // We will prioritize some common ones if they exist, otherwise just take the first 4.
        $topCodes = ['EUR', 'GBP', 'JPY', 'CNY', 'AUD', 'CAD', 'CHF', 'IDR'];
        $currencies = CurrencyCache::whereIn('currency_code', $topCodes)->take(4)->get();
        
        if ($currencies->count() < 4) {
            $more = CurrencyCache::whereNotIn('currency_code', $currencies->pluck('currency_code')->toArray())->take(4 - $currencies->count())->get();
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
                    // Restrict to max 3 chars as per DB schema
                    $currencyCode = substr($currencyCode, 0, 3);
                    $currency = CurrencyCache::updateOrCreate(
                        ['currency_code' => $currencyCode],
                        [
                            'exchange_rate_usd' => $rate,
                            'expires_at' => Carbon::now()->addHours(24),
                        ]
                    );

                    $syncedData->push($currency);
                }
                return $syncedData;
            }
        } catch (Exception $e) {
            Log::error('CurrencyApiService Error: ' . $e->getMessage());
        }
        return collect([]);
    }
}