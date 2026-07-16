<?php

namespace App\Services\Currency;

class CurrencyInsightService
{
    /**
     * Generate rule-based AI insight for a given currency based on its daily change.
     * 
     * @param object|null $currency
     * @param float $dailyChangePercentage
     * @return array
     */
    public function generateInsight($currency, $dailyChangePercentage = 0)
    {
        if (!$currency) {
            return [
                'status' => 'Stable',
                'summary' => __('No currency data available to generate insights.'),
                'impacts' => [
                    __('Import cost stable'),
                    __('Logistics cost stable'),
                    __('Supplier pricing unchanged')
                ],
                'recommendation' => __('Continue standard monitoring procedures.')
            ];
        }

        $code = $currency->currency_code;
        
        if ($dailyChangePercentage > 0.5) {
            // USD strengthens significantly against target currency
            return [
                'status' => 'Bullish',
                'summary' => __('The USD has strengthened significantly against :code today. This may increase import costs and affect supply chain expenses.', ['code' => $code]),
                'impacts' => [
                    __('Import cost likely to increase'),
                    __('Logistics cost may rise'),
                    __('Supplier pricing may be affected')
                ],
                'recommendation' => __('Monitor supplier contracts and consider hedging strategies for high-risk exposure.')
            ];
        } elseif ($dailyChangePercentage < -0.5) {
            // USD weakens significantly
            return [
                'status' => 'Bearish',
                'summary' => __('The USD has weakened against :code. This could provide temporary relief on import costs but might impact export competitiveness.', ['code' => $code]),
                'impacts' => [
                    __('Favorable for imports'),
                    __('Export margins may tighten'),
                    __('Opportunities for early procurement')
                ],
                'recommendation' => __('Review purchase planning and consider securing inventory at favorable rates.')
            ];
        } elseif ($dailyChangePercentage > 0) {
            // Slight increase
            return [
                'status' => 'Bullish',
                'summary' => __('The USD shows slight strengthening against :code. Minimal immediate impact expected on the supply chain.', ['code' => $code]),
                'impacts' => [
                    __('Slight upward pressure on imports'),
                    __('Logistics cost remains manageable'),
                    __('Supplier pricing stable')
                ],
                'recommendation' => __('Maintain current operations while monitoring currency trends closely.')
            ];
        } elseif ($dailyChangePercentage < 0) {
            // Slight decrease
            return [
                'status' => 'Bearish',
                'summary' => __('The USD shows a slight dip against :code. Supply chain costs remain relatively stable.', ['code' => $code]),
                'impacts' => [
                    __('Marginal benefit for imports'),
                    __('Logistics cost stable'),
                    __('Supplier pricing unchanged')
                ],
                'recommendation' => __('No immediate action required. Continue regular financial monitoring.')
            ];
        } else {
            // No change or no data
            return [
                'status' => 'Stable',
                'summary' => __('Exchange rate for :code remains stable or historical data is pending. No significant supply chain disruptions expected from currency fluctuations.', ['code' => $code]),
                'impacts' => [
                    __('Import cost stable'),
                    __('Logistics cost stable'),
                    __('Supplier pricing unchanged')
                ],
                'recommendation' => __('Ensure historical data synchronization is active to generate predictive insights.')
            ];
        }
    }
}
