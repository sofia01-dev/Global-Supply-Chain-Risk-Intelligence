<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'id'])) {
        session()->put('locale', $locale);
    }
    return redirect()->back();
})->name('lang.switch');

Route::middleware('guest')->group(function () {
    Route::get('/login', [\App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'login']);
    
    Route::get('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');
    Route::middleware('admin')->group(function () {
        Route::get('/admin/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');
        
        Route::get('/admin/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.users.index');
        Route::get('/admin/users/create', [\App\Http\Controllers\Admin\UserController::class, 'create'])->name('admin.users.create');
        Route::post('/admin/users', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('admin.users.store');
        Route::get('/admin/users/{id}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('admin.users.show');
        Route::get('/admin/users/{user}/edit', [\App\Http\Controllers\Admin\UserController::class, 'edit'])->name('admin.users.edit');
        Route::put('/admin/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('admin.users.update');
        Route::delete('/admin/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('admin.users.destroy');
        
        Route::get('/admin/ports', [\App\Http\Controllers\Admin\PortController::class, 'index'])->name('admin.ports.index');
        Route::get('/admin/ports/create', [\App\Http\Controllers\Admin\PortController::class, 'create'])->name('admin.ports.create');
        Route::post('/admin/ports', [\App\Http\Controllers\Admin\PortController::class, 'store'])->name('admin.ports.store');
        Route::get('/admin/ports/{id}', [\App\Http\Controllers\Admin\PortController::class, 'show'])->name('admin.ports.show');
        Route::get('/admin/ports/{port}/edit', [\App\Http\Controllers\Admin\PortController::class, 'edit'])->name('admin.ports.edit');
        Route::put('/admin/ports/{port}', [\App\Http\Controllers\Admin\PortController::class, 'update'])->name('admin.ports.update');
        Route::delete('/admin/ports/{port}', [\App\Http\Controllers\Admin\PortController::class, 'destroy'])->name('admin.ports.destroy');
        
        Route::get('/admin/articles', [\App\Http\Controllers\Admin\ArticleController::class, 'index'])->name('admin.articles.index');
        Route::get('/admin/articles/create', [\App\Http\Controllers\Admin\ArticleController::class, 'create'])->name('admin.articles.create');
        Route::post('/admin/articles', [\App\Http\Controllers\Admin\ArticleController::class, 'store'])->name('admin.articles.store');
        Route::get('/admin/articles/{id}', [\App\Http\Controllers\Admin\ArticleController::class, 'show'])->name('admin.articles.show');
        Route::get('/admin/articles/{article}/edit', [\App\Http\Controllers\Admin\ArticleController::class, 'edit'])->name('admin.articles.edit');
        Route::put('/admin/articles/{article}', [\App\Http\Controllers\Admin\ArticleController::class, 'update'])->name('admin.articles.update');
        Route::delete('/admin/articles/{article}', [\App\Http\Controllers\Admin\ArticleController::class, 'destroy'])->name('admin.articles.destroy');
    });
    
    Route::middleware('user')->group(function () {
        Route::get('/user/dashboard', [\App\Http\Controllers\User\DashboardController::class, 'index'])->name('user.dashboard');
        Route::get('/user/dashboard/sync', [\App\Http\Controllers\User\DashboardController::class, 'syncData'])->name('user.dashboard.sync');
        
        // Shipments
        Route::get('/user/shipments', [\App\Http\Controllers\User\ShipmentController::class, 'index'])->name('user.shipments.index');
        Route::get('/user/shipments/create', [\App\Http\Controllers\User\ShipmentController::class, 'create'])->name('user.shipments.create');
        Route::post('/user/shipments', [\App\Http\Controllers\User\ShipmentController::class, 'store'])->name('user.shipments.store');
        Route::get('/user/shipments/{id}/edit', [\App\Http\Controllers\User\ShipmentController::class, 'edit'])->name('user.shipments.edit');
        Route::put('/user/shipments/{id}', [\App\Http\Controllers\User\ShipmentController::class, 'update'])->name('user.shipments.update');
        Route::delete('/user/shipments/{id}', [\App\Http\Controllers\User\ShipmentController::class, 'destroy'])->name('user.shipments.destroy');
        Route::get('/user/shipments/{id}', [\App\Http\Controllers\User\ShipmentController::class, 'show'])->name('user.shipments.show');
        Route::get('/user/shipments/api/ports/{country_id}', [\App\Http\Controllers\User\ShipmentController::class, 'getPortsByCountry'])->name('user.shipments.ports');
        // Other Dashboards
        Route::get('/user/watchlist', [\App\Http\Controllers\User\WatchlistController::class, 'index'])->name('user.watchlist.index');
        Route::post('/user/watchlist/toggle', [\App\Http\Controllers\User\WatchlistController::class, 'toggle'])->name('user.watchlist.toggle');
        
        Route::get('/user/countries', [\App\Http\Controllers\User\CountryController::class, 'index'])->name('user.countries.index');
        Route::get('/user/country/{id}', [\App\Http\Controllers\User\CountryController::class, 'show'])->name('user.country');
        Route::get('/user/weather', [\App\Http\Controllers\User\WeatherController::class, 'index'])->name('user.weather');
        Route::get('/user/currency', [\App\Http\Controllers\User\CurrencyController::class, 'index'])->name('user.currency');
        Route::get('/user/news', [\App\Http\Controllers\User\NewsController::class, 'index'])->name('user.news');
        Route::post('/user/news/sync', [\App\Http\Controllers\User\NewsController::class, 'sync'])->name('user.news.sync');
        Route::get('/user/comparison', [\App\Http\Controllers\User\ComparisonController::class, 'index'])->name('user.comparison');
        Route::get('/user/comparison/ajax', [\App\Http\Controllers\User\ComparisonController::class, 'compareAjax'])->name('user.comparison.ajax');
        
        // Ports
        Route::get('/user/ports', [\App\Http\Controllers\User\PortController::class, 'index'])->name('user.ports.index');
        Route::get('/user/ports/{id}', [\App\Http\Controllers\User\PortController::class, 'show'])->name('user.ports.show');
    });
});
