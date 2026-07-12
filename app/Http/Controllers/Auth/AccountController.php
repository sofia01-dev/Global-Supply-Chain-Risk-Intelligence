<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AccountSettingsRequest;
use App\Services\Auth\AccountService;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    protected $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    public function showSettingsForm()
    {
        return view('auth.account-settings', ['user' => Auth::user()]);
    }

    public function update(AccountSettingsRequest $request)
    {
        $this->accountService->updateProfile(Auth::user(), $request->validated());
        
        return back()->with('status', 'Profile updated successfully.');
    }
}