<?php
namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;
use App\Services\Api\CurrencyApiService;
use App\Services\Currency\CurrencyInsightService;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    protected $currencyApiService;
    protected $currencyInsightService;

    public function __construct(CurrencyApiService $currencyApiService, CurrencyInsightService $currencyInsightService) {
        $this->currencyApiService = $currencyApiService;
        $this->currencyInsightService = $currencyInsightService;
    }

    public function index(Request $request) {
        $search = $request->query('search');
        $currencies = $this->currencyApiService->getAllCurrencies($search)->reject(function($c) {
            return $c->currency_code === 'IDR';
        });
        
        $selectedCurrency = null;
        if ($request->has('currency')) {
            $selectedCurrency = $currencies->where('currency_code', strtoupper($request->query('currency')))->first();
        }
        
        if (!$selectedCurrency && $currencies->isNotEmpty()) {
            $selectedCurrency = $currencies->first();
        }

        $topCurrencies = $this->currencyApiService->getTopCurrencies();
        
        // Ambil kurs IDR untuk digunakan sebagai basis
        $idrRecord = \App\Models\CurrencyCache::where('currency_code', 'IDR')->orderBy('created_at', 'desc')->first();
        $idrRate = $idrRecord ? (float) $idrRecord->exchange_rate_usd : 16000.0;

        // Konversi mata uang utama ke kurs silang IDR
        foreach ($topCurrencies as $top) {
            $targetRate = (float) $top->exchange_rate_usd;
            $top->converted_rate = $targetRate > 0 ? ($idrRate / $targetRate) : 0;
        }

        // Ambil data historis untuk mata uang yang dipilih
        $history = \App\Models\CurrencyHistory::where('currency_code', $selectedCurrency->currency_code)
            ->orderBy('recorded_date', 'asc')
            ->get();

        $dailyChange = 0.0;
        $weeklyChange = 0.0;
        $monthlyChange = 0.0;
        $historicalLabels = [];
        $historicalData = [];

        if ($history->count() > 1) {
            $idrHistory = \App\Models\CurrencyHistory::where('currency_code', 'IDR')
                ->orderBy('recorded_date', 'asc')
                ->get()
                ->keyBy(function($item) { return \Carbon\Carbon::parse($item->recorded_date)->format('Y-m-d'); });

            $latestConverted = 0;
            $yesterdayConverted = 0;
            $lastWeekConverted = 0;

            foreach ($history as $record) {
                $dateKey = \Carbon\Carbon::parse($record->recorded_date)->format('Y-m-d');
                $histIdrRate = isset($idrHistory[$dateKey]) ? (float) $idrHistory[$dateKey]->exchange_rate_usd : $idrRate;
                
                $targetRate = (float) $record->exchange_rate_usd;
                $convertedRate = $targetRate > 0 ? ($histIdrRate / $targetRate) : 0;

                $historicalLabels[] = \Carbon\Carbon::parse($record->recorded_date)->format('M d');
                $historicalData[] = $convertedRate;
            }

            $latestConverted = end($historicalData);
            
            if (count($historicalData) >= 2) {
                $yesterdayConverted = $historicalData[count($historicalData) - 2];
                $dailyChange = (($latestConverted - $yesterdayConverted) / $yesterdayConverted) * 100;
            }

            if (count($historicalData) >= 7) {
                $lastWeekConverted = $historicalData[count($historicalData) - 7];
                $weeklyChange = (($latestConverted - $lastWeekConverted) / $lastWeekConverted) * 100;
            }
        }

        $insight = $this->currencyInsightService->generateInsight($selectedCurrency, $dailyChange);

        return view('user.currency', compact('currencies', 'selectedCurrency', 'topCurrencies', 'insight', 'search', 'dailyChange', 'weeklyChange', 'monthlyChange', 'historicalLabels', 'historicalData', 'idrRate'));
    }
}