<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/countries', [\App\Http\Controllers\Api\CountryApiController::class, 'index']);
Route::get('/ports', [\App\Http\Controllers\Api\PortApiController::class, 'index']);
Route::get('/news', [\App\Http\Controllers\Api\NewsApiController::class, 'index']);
Route::get('/currency', [\App\Http\Controllers\Api\CurrencyApiController::class, 'index']);
Route::get('/risk', [\App\Http\Controllers\Api\RiskApiController::class, 'index']);
Route::get('/shipments', [\App\Http\Controllers\Api\ShipmentApiController::class, 'index']);
Route::get('/shipments/{id}', [\App\Http\Controllers\Api\ShipmentApiController::class, 'show']);
