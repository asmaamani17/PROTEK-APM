<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SOSController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// SOS Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/sos/request', [SOSController::class, 'requestHelp'])->name('api.sos.request');
});

// SOS Endpoint
Route::middleware(['auth:sanctum', 'role:victim'])->group(function () {
    Route::post('/victim/sos', [\App\Http\Controllers\VictimController::class, 'sos'])->name('api.victim.sos');
});

// Rescue Operation Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('rescue')->group(function () {
        // Update rescue status (for rescuers and admins)
        Route::put('/{id}/status', [\App\Http\Controllers\API\RescueOperationController::class, 'updateStatus'])
            ->middleware('role:rescuer,admin')
            ->name('api.rescue.updateStatus');
            
        // Get rescue status (for all authenticated users)
        Route::get('/{id}/status', [\App\Http\Controllers\API\RescueOperationController::class, 'getStatus'])
            ->name('api.rescue.status');
    });
});

// Admin Endpoints
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    Route::match(['put', 'post'], '/cases/{caseId}/status', [\App\Http\Controllers\AdminController::class, 'updateCaseStatus'])
        ->name('api.admin.cases.status.update');
});

// Rescuer Endpoints
Route::middleware(['auth:sanctum', 'role:rescuer'])->prefix('rescuer')->group(function () {
    Route::put('/cases/{caseId}/status', [\App\Http\Controllers\Rescuer\CaseController::class, 'updateStatus'])
        ->name('api.rescuer.cases.status.update');
});

// Botpress Webhook
Route::post('/botpress-webhook', [\App\Http\Controllers\AdminController::class, 'handleBotpressWebhook'])->name('api.botpress.webhook');

// Rescuer Endpoints
Route::middleware(['auth:sanctum', 'role:rescuer'])->prefix('rescuer')->group(function () {
    Route::put('/cases/{caseId}/status', [\App\Http\Controllers\RescuerController::class, 'updateCaseStatus'])
        ->name('api.rescuer.cases.status.update');
});
