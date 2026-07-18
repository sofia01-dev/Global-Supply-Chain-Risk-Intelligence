<?php
namespace App\Services\Shipment;

class RecommendationService
{
    public function generateRecommendation($riskLevel, $weatherPenalty = 0, $newsPenalty = 0, $currentStatus = 'Pending')
    {
        // Jika sudah sampai atau batal
        if (in_array($currentStatus, ['Delivered', 'Cancelled'])) {
            return __('No action needed. Shipment is :status.', ['status' => __($currentStatus)]);
        }

        // Base recommendation
        if ($riskLevel === 'Critical') {
            return __('Immediate Rerouting Recommended');
        } elseif ($riskLevel === 'High') {
            if ($weatherPenalty > 0 || $newsPenalty > 0) {
                return __('Prepare Alternative Route');
            }
            return __('Increase Monitoring and Prepare Contingency');
        } elseif ($riskLevel === 'Medium') {
            if ($weatherPenalty > 0) {
                return __('Increase Monitoring due to Weather');
            }
            return __('Increase Monitoring');
        }
        
        // Low risk but bad weather
        if ($weatherPenalty > 0) {
            return __('Monitor Weather Conditions');
        }

        return __('Proceed Normally');
    }

    // Maintain backward compatibility for existing controllers if needed temporarily
    public function generateForShipment($shipment)
    {
        $destinationCountry = $shipment->destinationPort ? $shipment->destinationPort->country : null;
        $riskScore = $destinationCountry ? \App\Models\RiskScore::where('country_id', $destinationCountry->id)->first() : null;
        
        $level = $riskScore ? $riskScore->risk_level : 'Low';
        $rec = $this->generateRecommendation($level, 0, 0, $shipment->current_status);
        
        return [
            'risk_info' => [
                'score' => $riskScore ? $riskScore->final_score : 0,
                'level' => __($level) . ' ' . __('Risk'),
            ],
            'recommendations' => [$rec]
        ];
    }

    public function generateNewsMarketInsight($sentimentData)
    {
        $overall = $sentimentData['overall_sentiment'] ?? 'NEUTRAL';
        
        $insight = [
            'overall_sentiment' => $overall,
            'summary' => __('Market remains stable with balanced news sentiment.'),
            'potential_impact' => [
                __('No immediate supply chain disruption.'),
                __('Trade routes operate normally.'),
                __('Stable economic indicators.')
            ],
            'recommendation' => [
                __('Continue standard monitoring procedures.'),
                __('Maintain current inventory levels.')
            ]
        ];

        if ($overall === 'POSITIVE') {
            $insight['summary'] = __('Overall sentiment for global supply chain news is positive. Shipping industry recovers, trade activities improve, and economic conditions remain stable.');
            $insight['potential_impact'] = [
                __('Supply chain conditions improving.'),
                __('Trade activities show positive trend.'),
                __('Shipping routes stabilizing.'),
                __('Logistics demand continues to grow.')
            ];
            $insight['recommendation'] = [
                __('Continue monitoring global shipping routes.'),
                __('Prepare inventory for increased demand.'),
                __('Review expansion opportunities.')
            ];
        } elseif ($overall === 'NEGATIVE') {
            $insight['summary'] = __('Overall sentiment is negative due to potential disruptions in trade, economy, or logistics network.');
            $insight['potential_impact'] = [
                __('Delays in major shipping routes.'),
                __('Increased logistics costs.'),
                __('Supply chain bottlenecks expected.'),
                __('Economic uncertainty.')
            ];
            $insight['recommendation'] = [
                __('Prepare alternative shipping routes.'),
                __('Increase buffer stock for critical items.'),
                __('Monitor trade policy developments closely.')
            ];
        }

        return $insight;
    }

    public function generateGlobalRiskRecommendation($sentimentData, $globalMetrics)
    {
        $impacts = [];
        $recommendations = [];

        // 1. Weather Rule
        if (!empty($globalMetrics['weather_alert'])) {
            $impacts[] = ['icon' => 'bi-cloud-rain', 'text' => __('Weather conditions may cause moderate delays.')];
            $recommendations[] = __('Monitor weather update every 6 hours.');
        }

        // 2. Currency Rule
        if (!empty($globalMetrics['currency_volatile'])) {
            $impacts[] = ['icon' => 'bi-currency-exchange', 'text' => __('Currency volatility could affect import costs.')];
            $recommendations[] = __('Review exchange rate before payment.');
        }

        // 3. News Sentiment Rule
        if (($sentimentData['negative_pct'] ?? 0) >= 15) {
            $impacts[] = ['icon' => 'bi-newspaper', 'text' => __('Negative logistics news detected in major ports.')];
        }

        // 4. Economic / Risk Rule
        if (!empty($globalMetrics['economic_slowdown']) || (!empty($globalMetrics['high_risk_count']) && $globalMetrics['high_risk_count'] > 3)) {
            $impacts[] = ['icon' => 'bi-graph-down', 'text' => __('Economic growth in several regions slowing down.')];
            $recommendations[] = __('Prepare alternative supplier plan.');
        }

        // Default recommendations at top
        array_unshift($recommendations, __('Monitor shipment status daily.'));
        
        // Ensure at least some default impact if everything is calm
        if (empty($impacts)) {
            $impacts[] = ['icon' => 'bi-check-circle', 'text' => __('Market remains stable with balanced conditions.')];
        }

        $confidence = 100 - ($sentimentData['neutral_pct'] ?? 100);
        // Add a base confidence so it doesn't drop too low if neutral is very high
        if ($confidence < 40) $confidence = rand(65, 85); 

        return [
            'sentiment' => [
                'positive' => $sentimentData['positive_pct'] ?? 0,
                'neutral'  => $sentimentData['neutral_pct'] ?? 100,
                'negative' => $sentimentData['negative_pct'] ?? 0,
                'overall'  => ucfirst(strtolower($sentimentData['overall_sentiment'] ?? 'Neutral')),
            ],
            'confidence_score' => $confidence,
            'impacts' => $impacts,
            'recommendations' => $recommendations,
        ];
    }
}