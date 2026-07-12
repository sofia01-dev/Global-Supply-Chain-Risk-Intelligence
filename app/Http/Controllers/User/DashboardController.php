<?php
namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardService;

class DashboardController extends Controller
{
    protected $dashboardService;
    public function __construct(DashboardService $dashboardService) {
        $this->dashboardService = $dashboardService;
    }
    public function index() {
        $summary = $this->dashboardService->getUserSummary();
        return view('user.dashboard', compact('summary'));
    }
}