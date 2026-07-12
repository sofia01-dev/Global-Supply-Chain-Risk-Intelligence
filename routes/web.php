<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [\App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'login']);
    
    Route::get('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'register']);
    
    Route::get('/forgot-password', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    
    Route::get('/reset-password/{token}', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'reset'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');
    
    Route::get('/account-settings', [\App\Http\Controllers\Auth\AccountController::class, 'showSettingsForm'])->name('account.settings');
    Route::post('/account-settings', [\App\Http\Controllers\Auth\AccountController::class, 'update']);
    
    Route::middleware('admin')->group(function () {
        Route::get('/admin/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');
    });
    
    Route::middleware('user')->group(function () {
        Route::get('/user/dashboard', [\App\Http\Controllers\User\DashboardController::class, 'index'])->name('user.dashboard');
        Route::get('/user/country/{id}', [\App\Http\Controllers\User\CountryController::class, 'show'])->name('user.country');
        Route::get('/user/weather', [\App\Http\Controllers\User\WeatherController::class, 'index'])->name('user.weather');
        Route::get('/user/currency', [\App\Http\Controllers\User\CurrencyController::class, 'index'])->name('user.currency');
        Route::get('/user/news', [\App\Http\Controllers\User\NewsController::class, 'index'])->name('user.news');
        Route::get('/user/visualization', [\App\Http\Controllers\User\VisualizationController::class, 'index'])->name('user.visualization');
        Route::get('/user/comparison', [\App\Http\Controllers\User\ComparisonController::class, 'index'])->name('user.comparison');
    });
});
