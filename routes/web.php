<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\{
    VictimController,
    RescuerController,
    AdminController,
    HomeController,
    VictimsListController
};

Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication routes
Auth::routes();

// Victim routes
Route::middleware(['auth', 'role:victim'])->group(function () {
    Route::get('/victim', [VictimController::class, 'index'])->name('victim.dashboard');
    Route::post('/victim/sos', [VictimController::class, 'sos'])->name('victim.sos');
    Route::get('/victim/status', [VictimController::class, 'getStatus'])->name('victim.status');
    Route::get('/victim/case/status', [VictimController::class, 'getCaseStatus'])->name('victim.case.status');
    Route::put('/victim/cases/{case}/status', [VictimController::class, 'updateStatus'])->name('victim.cases.status');
});

// Rescuer routes
Route::middleware(['auth', 'role:rescuer'])->group(function () {
    Route::get('/rescuer', [RescuerController::class, 'index'])->name('rescuer.dashboard');
    Route::post('/rescuer/accept/{id}', [RescuerController::class, 'accept'])->name('rescuer.accept');
    Route::post('/rescuer/complete/{id}', [RescuerController::class, 'complete'])->name('rescuer.complete');
    Route::post('/rescuer/status/{id}/{status}', [RescuerController::class, 'updateStatus'])->name('rescuer.updateStatus');
    Route::post('/rescuer/cases/{caseId}/status', [RescuerController::class, 'updateCaseStatus'])->name('rescuer.cases.status');
    Route::get('/rescuer/active-case', [RescuerController::class, 'getActiveCase'])->name('rescuer.active-case');
});

// Admin routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/victims', [VictimsListController::class, 'index'])->name('admin.victims');
    
    // Admin API endpoints
    Route::post('/admin/cases/{caseId}/status', [AdminController::class, 'updateCaseStatus'])->name('admin.cases.status');
    Route::get('/admin/rescuers', [AdminController::class, 'getRescuers'])->name('admin.rescuers');
    Route::get('/admin/cases', [AdminController::class, 'getCases'])->name('admin.cases');
    Route::get('/api/victims/active', [AdminController::class, 'getActiveVictims'])->name('api.victims.active');
});

// General home route
Route::get('/home', [HomeController::class, 'index'])->name('home');

// Debug route (temporary)
Route::get('/debug/map-config', function() {
    return response()->json([
        'has_api_key' => !empty(env('GOOGLE_MAPS_API_KEY')),
        'api_key_length' => env('GOOGLE_MAPS_API_KEY') ? strlen(env('GOOGLE_MAPS_API_KEY')) : 0,
        'api_key_starts_with' => env('GOOGLE_MAPS_API_KEY') ? substr(env('GOOGLE_MAPS_API_KEY'), 0, 5) . '...' : null,
        'app_env' => config('app.env'),
        'app_debug' => config('app.debug'),
    ]);
})->middleware('auth');
