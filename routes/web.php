<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LuckyDrawController;

/*
|--------------------------------------------------------------------------
| Web Routes - Lucky Draw Application
|--------------------------------------------------------------------------
*/

Route::get('/', [LuckyDrawController::class, 'index'])->name('home');
Route::get('/spinwheel', [LuckyDrawController::class, 'spinwheel'])->name('spinwheel');
Route::get('/dice', [LuckyDrawController::class, 'dice'])->name('dice');
Route::get('/balancer', [LuckyDrawController::class, 'balancer'])->name('balancer');
Route::get('/unbalancer', [LuckyDrawController::class, 'unbalancer'])->name('unbalancer');

// API routes for dynamic interactions
Route::post('/api/balancer/draw', [LuckyDrawController::class, 'apiBalancerDraw'])->name('api.balancer.draw');
Route::post('/api/unbalancer/draw', [LuckyDrawController::class, 'apiUnbalancerDraw'])->name('api.unbalancer.draw');
