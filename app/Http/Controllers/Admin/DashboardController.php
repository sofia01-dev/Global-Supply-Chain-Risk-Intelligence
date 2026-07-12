<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $dashboardService;
    public function __construct(DashboardService $dashboardService) {
        $this->dashboardService = $dashboardService;
    }
    public function index() {
        $summary = $this->dashboardService->getAdminSummary();
        return view('admin.dashboard', compact('summary'));
    }
}