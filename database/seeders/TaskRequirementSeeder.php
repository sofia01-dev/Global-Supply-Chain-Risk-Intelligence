<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaskRequirementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Seed Positive Words
        $positiveWords = [
            "growth", "stable", "normal", "improve", "increase", "recover", 
            "safe", "efficient", "success", "positive", "good", "great", 
            "resolved", "steady", "profit"
        ];
        
        DB::table('positive_words')->truncate();
        foreach ($positiveWords as $word) {
            DB::table('positive_words')->insert([
                'word' => $word,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // 2. Seed Negative Words
        $negativeWords = [
            "war", "storm", "delay", "strike", "blocked", "conflict", 
            "crisis", "inflation", "accident", "risk", "bad", "loss", 
            "decline", "crash", "danger", "decrease"
        ];
        
        DB::table('negative_words')->truncate();
        foreach ($negativeWords as $word) {
            DB::table('negative_words')->insert([
                'word' => $word,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // 3. Seed Risk Factors
        // From user spec: Weather 30%, Inflation 20%, Political News 40%, Currency 10%
        DB::table('risk_factors')->truncate();
        $factors = [
            ['factor' => 'Weather', 'weight' => 30],
            ['factor' => 'Inflation', 'weight' => 20],
            ['factor' => 'Political News', 'weight' => 40],
            ['factor' => 'Currency', 'weight' => 10],
        ];
        
        foreach ($factors as $factor) {
            DB::table('risk_factors')->insert([
                'factor' => $factor['factor'],
                'weight' => $factor['weight'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
