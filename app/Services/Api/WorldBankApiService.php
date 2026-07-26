<?php
namespace App\Services\Api;

use App\Models\Country;
use App\Models\EconomicIndicator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WorldBankApiService
{
    public function syncIndicators()
    {
        $countries = Country::whereNotNull('iso2_code')->get();
        $syncedData = collect();
        $year = date('Y') - 1; 

        foreach ($countries as $country) {
            try {
                $iso2 = $country->iso2_code;
                
                $gdp = $this->fetchIndicator($iso2, 'NY.GDP.MKTP.CD');
                $inflation = $this->fetchIndicator($iso2, 'FP.CPI.TOTL.ZG');
                $population = $this->fetchIndicator($iso2, 'SP.POP.TOTL');
                $export = $this->fetchIndicator($iso2, 'NE.EXP.GNFS.CD');
                $import = $this->fetchIndicator($iso2, 'NE.IMP.GNFS.CD');

                if ($gdp !== null || $inflation !== null || $population !== null || $export !== null || $import !== null) {
                    $indicator = EconomicIndicator::updateOrCreate(
                        ['country_id' => $country->id],
                        [
                            'gdp' => $gdp,
                            'inflation_rate' => $inflation,
                            'population' => $population,
                            'export_value' => $export,
                            'import_value' => $import,
                            'data_year' => $year,
                        ]
                    );
                    $syncedData->push($indicator);
                }
            } catch (\Throwable $e) {
                Log::error("WorldBankApiService Error for country {$country->iso2_code}: " . $e->getMessage());
            }
        }
        return $syncedData;
    }

    private function fetchIndicator($iso2, $indicatorCode)
    {
        usleep(50000); 
        $response = Http::timeout(15)->retry(3, 200)->get("https://api.worldbank.org/v2/country/{$iso2}/indicator/{$indicatorCode}?format=json&per_page=5");
        
        if ($response->successful()) {
            $json = $response->json();
            if (is_array($json) && isset($json[1]) && is_array($json[1])) {
                foreach ($json[1] as $record) {
                    if (isset($record['value']) && $record['value'] !== null) {
                        return $record['value'];
                    }
                }
            }
        } else {
            Log::warning("WorldBank API {$indicatorCode} failed for {$iso2}: " . $response->status());
        }
        
        return null;
    }

    public function fetchGlobalHistoricalTrend($indicatorCode, $yearsBack = 7)
    {
        $currentYear = date('Y') - 1; 
        $startYear = $currentYear - $yearsBack + 1;
        
        $url = "https://api.worldbank.org/v2/country/WLD/indicator/{$indicatorCode}?date={$startYear}:{$currentYear}&format=json";
        
        try {
            $response = Http::timeout(15)->retry(3, 200)->get($url);
            
            if ($response->successful()) {
                $json = $response->json();
                if (is_array($json) && isset($json[1]) && is_array($json[1])) {
                    $data = [];
                    foreach ($json[1] as $record) {
                        if (isset($record['date']) && isset($record['value']) && $record['value'] !== null) {
                            $data[$record['date']] = $record['value'];
                        }
                    }
                    ksort($data);
                    return $data;
                }
            } else {
                Log::warning("WorldBank API Historical {$indicatorCode} failed: " . $response->status());
            }
        } catch (\Throwable $e) {
            Log::error("WorldBank API Historical Exception for {$indicatorCode}: " . $e->getMessage());
        }

        return null;
    }
}