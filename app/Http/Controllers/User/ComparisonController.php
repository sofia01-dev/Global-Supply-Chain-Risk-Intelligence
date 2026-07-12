<?php
namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;
use App\Services\Dashboard\ComparisonService;
use Illuminate\Http\Request;

class ComparisonController extends Controller
{
    protected $comparisonService;
    public function __construct(ComparisonService $comparisonService) {
        $this->comparisonService = $comparisonService;
    }
    public function index(Request $request) {
        $countryIds = $request->input('countries', []);
        $countries = $this->comparisonService->getComparisonData($countryIds);
        return view('user.comparison', compact('countries'));
    }
}