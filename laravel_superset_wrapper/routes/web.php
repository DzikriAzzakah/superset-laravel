<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SupersetController;

Route::get('/', function () {
    return view('welcome');
});

// Superset Dashboard Routes
Route::prefix('superset')->group(function () {
    Route::get('/dashboard', [SupersetController::class, 'showDashboard'])->name('superset.dashboard');
    Route::post('/guest-token', [SupersetController::class, 'fetchGuestToken'])->name('superset.guest-token');
});
