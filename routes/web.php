<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\{
    VictimController,
    RescuerController,
    AdminController,
    HomeController
};

Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication routes
Auth::routes();

Route::middleware(['auth', 'role:victim'])->group(function () {
    Route::get('/victim', [VictimController::class, 'index'])->name('victim.dashboard');
    Route::post('/victim/sos', [VictimController::class, 'sos'])->name('victim.sos');
 });

// Rescuer routes
Route::middleware(['auth', 'role:rescuer'])->group(function () {
    Route::get('/rescuer', [RescuerController::class, 'index'])->name('rescuer.dashboard');
    Route::post('/rescuer/accept/{id}', [RescuerController::class, 'accept'])->name('rescuer.accept');
    Route::post('/rescuer/complete/{id}', [RescuerController::class, 'complete'])->name('rescuer.complete');
    Route::post('/rescuer/status/{id}/{status}', [RescuerController::class, 'updateStatus'])->name('rescuer.updateStatus');
});

// Admin routes
use App\Http\Controllers\VictimsListController;

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/victims', [VictimsListController::class, 'index'])->name('admin.victims');
});

// General home route
Route::get('/home', [HomeController::class, 'index'])->name('home');
