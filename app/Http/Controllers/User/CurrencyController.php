<?php
namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;
use App\Services\Api\CurrencyApiService;

class CurrencyController extends Controller
{
    protected $currencyApiService;
    public function __construct(CurrencyApiService $currencyApiService) {
        $this->currencyApiService = $currencyApiService;
    }
    public function index() {
        $currencies = $this->currencyApiService->getAllCurrencies();
        return view('user.currency', compact('currencies'));
    }
}