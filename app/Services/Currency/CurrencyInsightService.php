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
            // Target currency strengthens significantly against IDR
            return [
                'status' => 'Bearish for IDR',
                'summary' => __('The :code has strengthened significantly against the Indonesian Rupiah (IDR) today. This will increase import costs and inflate supply chain expenses from this region.', ['code' => $code]),
                'impacts' => [
                    __('Import costs will spike'),
                    __('Cross-border logistics costs will rise'),
                    __('Supplier pricing from this region will be more expensive')
                ],
                'recommendation' => __('Monitor supplier contracts and consider hedging strategies or delaying purchases if possible.')
            ];
        } elseif ($dailyChangePercentage < -0.5) {
            // Target currency weakens significantly against IDR
            return [
                'status' => 'Bullish for IDR',
                'summary' => __('The :code has weakened significantly against the Indonesian Rupiah (IDR). This provides a highly favorable window to pay off logistics debts or secure cheap imports from this region.', ['code' => $code]),
                'impacts' => [
                    __('Highly favorable for imports'),
                    __('Profit margins on procurement will widen'),
                    __('Excellent opportunity for early procurement')
                ],
                'recommendation' => __('Review purchase planning and strongly consider securing inventory or paying invoices today.')
            ];
        } elseif ($dailyChangePercentage > 0) {
            // Slight increase
            return [
                'status' => 'Slight Risk',
                'summary' => __('The :code shows a slight strengthening against the IDR. Minimal immediate impact, but upward trend should be watched.', ['code' => $code]),
                'impacts' => [
                    __('Slight upward pressure on import costs'),
                    __('Logistics costs remain manageable'),
                    __('Supplier pricing generally stable')
                ],
                'recommendation' => __('Maintain current operations while monitoring currency trends closely for further spikes.')
            ];
        } elseif ($dailyChangePercentage < 0) {
            // Slight decrease
            return [
                'status' => 'Favorable',
                'summary' => __('The :code shows a slight dip against the IDR. Supply chain costs from this region remain relatively stable and slightly cheaper.', ['code' => $code]),
                'impacts' => [
                    __('Marginal benefit for imports'),
                    __('Logistics costs stable'),
                    __('Favorable environment for standard procurement')
                ],
                'recommendation' => __('No immediate drastic action required. Safe to proceed with regular financial activities.')
            ];
        } else {
            // No change or no data
            return [
                'status' => 'Stable',
                'summary' => __('Exchange rate for :code vs IDR remains perfectly stable or historical data is pending. No significant supply chain disruptions expected from currency fluctuations.', ['code' => $code]),
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
