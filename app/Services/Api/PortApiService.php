<?php
namespace App\Services\Api;

use App\Models\Country;
use App\Models\Port;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PortApiService
{
    /**
     * Integrates World Port Index Dataset to populate ports table
     */
    public function syncPorts()
    {
        $url = 'https://raw.githubusercontent.com/tayljordan/ports/master/ports.json';
        
        Log::info("Starting World Port Index synchronization from {$url}");
        
        $response = Http::timeout(60)->retry(3, 1000)->get($url);
        
        if (!$response->successful()) {
            Log::error("Failed to fetch World Port Index Dataset. HTTP Status: " . $response->status());
            return [
                'success' => false,
                'message' => 'Failed to fetch dataset from source.'
            ];
        }

        $data = $response->json();
        // tayljordan/ports dataset has a 'ports' array inside the JSON response
        $portsList = isset($data['ports']) ? $data['ports'] : $data;

        $stats = [
            'total_processed' => 0,
            'countries_mapped' => 0,
            'ports_saved' => 0,
            'failed_mapped' => 0,
            'reasons' => []
        ];

        // Cache countries for performance to avoid N queries
        $countries = Country::all()->keyBy(function ($item) {
            return strtolower($item->name);
        });
        
        $isoCountries = Country::all()->keyBy(function ($item) {
            return strtolower($item->iso2_code);
        });

        // Some mapping rules for country names that differ
        $countryNameMapping = [
            'united states' => 'united states of america',
            'russia' => 'russian federation',
            'congo, d.r.' => 'congo, the democratic republic of the',
            'congo' => 'congo',
            'iran' => 'iran, islamic republic of',
            'syria' => 'syrian arab republic',
            'vietnam' => 'viet nam',
            'venezuela' => 'venezuela, bolivarian republic of',
            'u.a.e.' => 'united arab emirates',
            'u.k.' => 'united kingdom',
            'south korea' => 'korea, republic of',
            'north korea' => 'korea, democratic people\'s republic of',
            'taiwan' => 'taiwan, province of china',
        ];

        $mappedCountriesTracker = [];

        foreach ($portsList as $portData) {
            $stats['total_processed']++;

            $wpiPortId = $portData['wpi_port_id'] ?? null;
            $portName = $portData['wpi_port_name'] ?: ($portData['point_of_interest'] ?? null);
            $countryName = $portData['country'] ?? null;
            $latitude = $portData['latitude'] ?? null;
            $longitude = $portData['longitude'] ?? null;

            if (empty($wpiPortId) || empty($portName) || empty($countryName) || $latitude === null || $longitude === null) {
                $stats['failed_mapped']++;
                $stats['reasons']['missing_required_fields'] = ($stats['reasons']['missing_required_fields'] ?? 0) + 1;
                continue;
            }

            // Generate UNLOCODE fallback using wpi_port_id (padded to 5 chars to fit unique constraint)
            $unlocode = str_pad((string)$wpiPortId, 5, '0', STR_PAD_LEFT);

            $countryNameLower = strtolower($countryName);
            if (isset($countryNameMapping[$countryNameLower])) {
                $countryNameLower = $countryNameMapping[$countryNameLower];
            }

            $countryId = null;
            
            // Try by exact name
            if (isset($countries[$countryNameLower])) {
                $countryId = $countries[$countryNameLower]->id;
            } 
            // Try by iso2
            elseif (isset($isoCountries[$countryNameLower])) {
                $countryId = $isoCountries[$countryNameLower]->id;
            }
            // Try partial match
            if (!$countryId) {
                $country = Country::where('name', 'LIKE', '%' . $countryName . '%')->first();
                if ($country) {
                    $countryId = $country->id;
                }
            }

            if (!$countryId) {
                $stats['failed_mapped']++;
                $reasonKey = "unmatched_country: " . $countryName;
                $stats['reasons'][$reasonKey] = ($stats['reasons'][$reasonKey] ?? 0) + 1;
                continue;
            }

            $mappedCountriesTracker[$countryId] = true;

            try {
                Port::updateOrCreate(
                    ['unlocode' => $unlocode],
                    [
                        'name' => $portName,
                        'country_id' => $countryId,
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                    ]
                );
                $stats['ports_saved']++;
            } catch (\Exception $e) {
                Log::error("Failed to save port {$portName}: " . $e->getMessage());
                $stats['failed_mapped']++;
                $stats['reasons']['db_error'] = ($stats['reasons']['db_error'] ?? 0) + 1;
            }
        }

        $stats['countries_mapped'] = count($mappedCountriesTracker);

        return [
            'success' => true,
            'stats' => $stats
        ];
    }
}
