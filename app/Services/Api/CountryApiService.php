<?php
namespace App\Services\Api;

use App\Models\Country;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class CountryApiService
{
    public function syncCountries()
    {
        try {
            $response = Http::timeout(120)->retry(3, 100)->get('https://raw.githubusercontent.com/mledoze/countries/master/countries.json');

            if ($response->successful()) {
                $countries = $response->json();
                
                // Handle deprecated API response or errors wrapper
                if (isset($countries['errors']) || (isset($countries['success']) && $countries['success'] === false)) {
                    $errorMsg = json_encode($countries['errors'] ?? 'Unknown API Error');
                    Log::error("REST Countries API Error: " . $errorMsg);
                    return collect([]);
                }

                $countriesData = $countries['data'] ?? $countries;
                
                Log::info('CountryApiService: Received ' . count($countriesData) . ' countries');
                
                $successCount = 0;
                foreach ($countriesData as $countryData) {
                    try {
                        $iso2 = $countryData['cca2'] ?? null;
                        if (!$iso2) {
                            continue;
                        }

                        $currencies = $countryData['currencies'] ?? [];
                        $currencyCode = !empty($currencies) ? array_key_first($currencies) : 'UNK';

                        $capital = isset($countryData['capital']) && is_array($countryData['capital']) && count($countryData['capital']) > 0 
                                    ? $countryData['capital'][0] 
                                    : null;
                                    
                        $lat = isset($countryData['latlng'][0]) ? $countryData['latlng'][0] : null;
                        $lng = isset($countryData['latlng'][1]) ? $countryData['latlng'][1] : null;
                        $flag = isset($countryData['flags']['png']) ? $countryData['flags']['png'] : null;

                        Country::updateOrCreate(
                            ['iso2_code' => $iso2],
                            [
                                'name' => $countryData['name']['common'] ?? 'Unknown',
                                'capital' => $capital,
                                'region' => $countryData['region'] ?? null,
                                'latitude' => $lat,
                                'longitude' => $lng,
                                'flag_url' => $flag,
                                'currency_code' => substr($currencyCode, 0, 3), // max 3 chars
                            ]
                        );
                        $successCount++;
                    } catch (Exception $e) {
                        Log::error('CountryApiService Error processing country ' . ($iso2 ?? 'unknown') . ': ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
                    }
                }
                
                Log::info('CountryApiService: Successfully synced ' . $successCount . ' countries');
                return collect($countriesData);
            }
        } catch (Exception $e) {
            Log::error('CountryApiService Critical Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        }
        return collect([]);
    }
}