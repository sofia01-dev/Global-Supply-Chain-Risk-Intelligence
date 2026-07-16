<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Services\Api\CountryApiService;
use App\Services\Api\WorldBankApiService;
use App\Services\Api\WeatherApiService;
use App\Services\Api\CurrencyApiService;
use App\Services\Api\NewsApiService;
use App\Services\Api\PortApiService;
use App\Services\Risk\RiskEngineService;

class SupplyChainSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'supply-chain:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Orchestrates the daily synchronization of all APIs and calculates Risk Engine scores.';

    /**
     * Execute the console command.
     */
    public function handle(
        CountryApiService $countryService,
        WorldBankApiService $worldBankService,
        WeatherApiService $weatherService,
        CurrencyApiService $currencyService,
        NewsApiService $newsService,
        PortApiService $portService,
        RiskEngineService $riskEngineService
    ) {
        $this->info("Starting Daily Synchronization...");
        Log::info("Starting Daily Synchronization...");

        // 1. Country API
        try {
            $countryService->syncCountries();
            $count = \App\Models\Country::count();
            $this->info("Countries synced: {$count} records");
            Log::info("Countries synced: {$count} records");
        } catch (\Exception $e) {
            $this->error("CountryApiService failed: " . $e->getMessage());
            Log::error("CountryApiService failed: " . $e->getMessage());
        }

        // 2. World Bank API
        try {
            $worldBankService->syncIndicators();
            $count = \App\Models\EconomicIndicator::count();
            $this->info("Economic Indicators synced: {$count} records");
            Log::info("Economic Indicators synced: {$count} records");
        } catch (\Exception $e) {
            $this->error("WorldBankApiService failed: " . $e->getMessage());
            Log::error("WorldBankApiService failed: " . $e->getMessage());
        }

        // 3. Weather API
        try {
            $weatherService->syncWeather();
            $count = \App\Models\WeatherCache::count();
            $this->info("Weather synced: {$count} records");
            Log::info("Weather synced: {$count} records");
        } catch (\Exception $e) {
            $this->error("WeatherApiService failed: " . $e->getMessage());
            Log::error("WeatherApiService failed: " . $e->getMessage());
        }

        // 4. Currency API
        try {
            $currencyService->syncCurrencies();
            $count = \App\Models\CurrencyCache::count();
            $this->info("Currency synced: {$count} records");
            Log::info("Currency synced: {$count} records");
        } catch (\Exception $e) {
            $this->error("CurrencyApiService failed: " . $e->getMessage());
            Log::error("CurrencyApiService failed: " . $e->getMessage());
        }

        // 5. News API
        try {
            $newsService->syncNews();
            $count = \App\Models\NewsCache::count();
            $this->info("News synced: {$count} records");
            Log::info("News synced: {$count} records");
        } catch (\Exception $e) {
            $this->error("NewsApiService failed: " . $e->getMessage());
            Log::error("NewsApiService failed: " . $e->getMessage());
        }

        // 6. Port Dataset
        try {
            $portService->syncPorts();
            $count = \App\Models\Port::count();
            $this->info("Ports synced: {$count} records");
            Log::info("Ports synced: {$count} records");
        } catch (\Exception $e) {
            $this->error("PortApiService failed: " . $e->getMessage());
            Log::error("PortApiService failed: " . $e->getMessage());
        }

        // 7. Risk Engine (Must be last)
        try {
            $riskEngineService->syncRiskScores();
            $count = \App\Models\Country::count();
            $this->info("Risk Engine calculated: {$count} countries");
            $this->info("Risk History saved");
            $this->info("Snapshot updated");
            Log::info("Risk Engine calculated: {$count} countries");
        } catch (\Exception $e) {
            $this->error("RiskEngineService failed: " . $e->getMessage());
            Log::error("RiskEngineService failed: " . $e->getMessage());
        }

        $this->info("Synchronization completed successfully.");
        Log::info("Synchronization completed successfully.");
    }
}
