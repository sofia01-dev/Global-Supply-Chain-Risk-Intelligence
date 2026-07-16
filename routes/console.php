<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Schedule;
use App\Services\Api\CountryApiService;
use App\Services\Api\WorldBankApiService;
use App\Services\Api\WeatherApiService;
use App\Services\Api\CurrencyApiService;
use App\Services\Api\NewsApiService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Schedule Daily Supply Chain Synchronization
Schedule::command('supply-chain:sync')
    ->dailyAt('00:30')
    ->withoutOverlapping()
    ->onOneServer();
