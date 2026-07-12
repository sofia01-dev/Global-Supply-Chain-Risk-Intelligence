<?php
namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;
use App\Services\Dashboard\VisualizationService;

class VisualizationController extends Controller
{
    protected $visualizationService;
    public function __construct(VisualizationService $visualizationService) {
        $this->visualizationService = $visualizationService;
    }
    public function index() {
        $data = [
            'gdp' => $this->visualizationService->getGdpTrend(),
            'inflation' => $this->visualizationService->getInflationTrend(),
            'currency' => $this->visualizationService->getCurrencyTrend(),
            'risk' => $this->visualizationService->getRiskTrend(),
        ];
        return view('user.visualization', compact('data'));
    }
}