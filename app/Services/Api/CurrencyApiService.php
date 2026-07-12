<?php
namespace App\Services\Api;
use App\Models\CurrencyCache;

class CurrencyApiService
{
    public function getAllCurrencies()
    {
        return CurrencyCache::all();
    }
}