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
        $countries = \App\Models\Country::orderBy('name')->get();
        return view('user.comparison', compact('countries'));
    }

    public function compareAjax(Request $request) {
        $countryAId = $request->input('country_a');
        $countryBId = $request->input('country_b');

        if (!$countryAId || !$countryBId) {
            return response()->json(['error' => 'Please select both countries'], 400);
        }

        $data = $this->comparisonService->getComparisonData($countryAId, $countryBId);
        return response()->json($data);
    }
}