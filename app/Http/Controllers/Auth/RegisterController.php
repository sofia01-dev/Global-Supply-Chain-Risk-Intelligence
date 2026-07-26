<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\AuthService;
use App\Models\Country;

class RegisterController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function showRegistrationForm()
    {
        $countries = Country::orderBy('name')->get();

        return view('auth.register', compact('countries'));
    }

    public function register(RegisterRequest $request)
    {
        $user = $this->authService->register($request->validated());

        return redirect('/login')->with('success', 'Registrasi berhasil! Silakan login.');
    }
}