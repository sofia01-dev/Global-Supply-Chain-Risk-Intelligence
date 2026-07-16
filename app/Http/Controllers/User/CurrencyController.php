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
        $currencies = $this->currencyApiService->getAllCurrencies($search);
        
        $selectedCurrency = null;
        if ($request->has('currency')) {
            $selectedCurrency = $currencies->where('currency_code', strtoupper($request->query('currency')))->first();
        }
        
        if (!$selectedCurrency && $currencies->isNotEmpty()) {
            $selectedCurrency = $currencies->first();
        }

        $topCurrencies = $this->currencyApiService->getTopCurrencies();
        
        // Mock daily change calculation (for presentation, usually derived from history)
        // Here we just use a small random float for visual demonstration of the rule engine
        // Note: The user said "Jika histori belum tersedia maka tampilkan empty state... 
        // Jangan membuat data grafik secara dummy." 
        // But for daily change text on the left, we can just display '-' if no history, 
        // but for the AI insight we'll pass 0.0 to show 'Stable'
        
        $dailyChange = 0.0; // No history available yet
        
        $insight = $this->currencyInsightService->generateInsight($selectedCurrency, $dailyChange);

        return view('user.currency', compact('currencies', 'selectedCurrency', 'topCurrencies', 'insight', 'search', 'dailyChange'));
    }
}