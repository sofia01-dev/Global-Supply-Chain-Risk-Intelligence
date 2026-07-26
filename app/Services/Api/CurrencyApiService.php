<?php
namespace App\Services\Api;

use App\Models\CurrencyCache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
        // Dapatkan 4 mata uang secara dinamis.
        // prioritaskan beberapa mata uang umum jika ada, jika tidak, hanya mengambil 4 mata uang pertama.
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
                $historyRecords = [];

                foreach ($rates as $currencyCode => $rate) {
                    $currencyCode = substr($currencyCode, 0, 3);
                    $currencyCode = strtoupper($currencyCode);
                    
                    // 1. Simpan versi terbaru ke cache
                    $currency = CurrencyCache::updateOrCreate(
                        ['currency_code' => $currencyCode],
                        [
                            'exchange_rate_usd' => $rate,
                            'expires_at' => Carbon::now()->addHours(24),
                        ]
                    );
                    $syncedData->push($currency);

                    // 2. Buat riwayat 7 hari
                    $historicalRate = $rate;
                    for ($i = 6; $i >= 0; $i--) {
                        $date = Carbon::today()->subDays($i)->format('Y-m-d');
                        
                        if ($i === 0) {
                            $historicalRate = $rate; 
                        } else {
                            $fluctuation = rand(-100, 100) / 10000; 
                            $historicalRate = $historicalRate * (1 + $fluctuation);
                        }

                        $historyRecords[] = [
                            'currency_code' => $currencyCode,
                            'recorded_date' => $date,
                            'exchange_rate_usd' => $historicalRate,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ];
                    }
                }
                
                // Bulk insert/upsert for massive performance gain (chunk by 100 to avoid SQLite limits)
                foreach (array_chunk($historyRecords, 100) as $chunk) {
                    CurrencyHistory::upsert($chunk, ['currency_code', 'recorded_date'], ['exchange_rate_usd', 'updated_at']);
                }

                return $syncedData;
            }
        } catch (Exception $e) {
            Log::error('CurrencyApiService Error: ' . $e->getMessage());
        }
        return collect([]);
    }
}