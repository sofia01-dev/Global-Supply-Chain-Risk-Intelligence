<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Watchlist;
use App\Services\Dashboard\CountryDashboardService;

class WatchlistController extends Controller
{
    protected $countryDashboardService;

    public function __construct(CountryDashboardService $countryDashboardService) {
        $this->countryDashboardService = $countryDashboardService;
    }

    public function index() {
        $user = auth()->user();
        $watchlists = $user->watchlists()->with('country')->get();
        
        $countries = collect();
        foreach ($watchlists as $watchlist) {
            if ($watchlist->country) {
                $detail = $this->countryDashboardService->getCountryDetail($watchlist->country_id);
                if ($detail) {
                    $countries->push($detail);
                }
            }
        }

        return view('user.watchlist.index', compact('countries'));
    }

    public function toggle(Request $request) {
        $request->validate([
            'country_id' => 'required|exists:countries,id'
        ]);

        $user = auth()->user();
        $countryId = $request->country_id;

        $existing = $user->watchlists()->where('country_id', $countryId)->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['status' => 'removed', 'message' => __('Removed from favorites')]);
        } else {
            $user->watchlists()->create(['country_id' => $countryId]);
            return response()->json(['status' => 'added', 'message' => __('Added to favorites')]);
        }
    }
}
