<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RiskScoreHistory;
use App\Models\CurrencyHistory;
use App\Models\Country;
use Carbon\Carbon;

class DashboardHistoricalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Clear old historical data to prevent duplicates when re-seeding
        RiskScoreHistory::truncate();
        CurrencyHistory::truncate();

        $today = Carbon::today();
        
        // 2. Generate 14 days of Historical Risk Scores (Global average simulation)
        $countries = Country::take(5)->get();
        if ($countries->isEmpty()) {
            $this->command->warn('No countries found in DB. RiskScoreHistory will not be seeded.');
        } else {
            $riskData = [];
            for ($i = 14; $i >= 0; $i--) {
                $date = $today->copy()->subDays($i);
                
                foreach ($countries as $country) {
                    $baseScore = 40 + (sin($i) * 20) + rand(-10, 10);
                    $baseScore = max(0, min(100, $baseScore));
                    
                    $level = 'Low';
                    if ($baseScore > 75) $level = 'Critical';
                    elseif ($baseScore > 50) $level = 'High';
                    elseif ($baseScore > 25) $level = 'Medium';

                    $riskData[] = [
                        'country_id' => $country->id,
                        'final_score' => $baseScore,
                        'risk_level' => $level,
                        'calculated_at' => $date->copy()->addHours(rand(8, 18)),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            RiskScoreHistory::insert($riskData);
        }

        // 3. Generate 14 days of Historical Currency Rates
        $currencies = [
            'IDR' => 16000,
            'EUR' => 0.92,
            'CNY' => 7.23
        ];

        $currencyData = [];
        for ($i = 14; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);

            foreach ($currencies as $code => $baseRate) {
                $fluctuation = 1 + (rand(-100, 100) / 10000); 
                $rate = $baseRate * $fluctuation;

                $currencyData[] = [
                    'currency_code' => $code,
                    'exchange_rate_usd' => $rate,
                    'recorded_date' => $date,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        CurrencyHistory::insert($currencyData);
        
        $this->command->info('Dashboard Historical Data generated successfully for the last 14 days!');
    }
}
