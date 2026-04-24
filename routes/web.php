<?php

use App\Models\CityGridCell;
use App\Http\Controllers\CityFunctionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CityGridCellController;

// Public landing page
Route::get('/', function () {
    return view('welcome', [
        'gridCells' => CityGridCell::ensureGridExists(),
    ]);
});

// All application routes — restricted to Administrator and City planner
Route::middleware(['auth', 'verified', 'role:Administrator,City planner'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/grid', [CityGridCellController::class, 'index'])->name('grid');

    // SIM.2 - Cell selection and function assignment
    Route::post('/grid/select/{id}', [CityGridCellController::class, 'select']);
    Route::post('/grid/{id}/assign', [CityGridCellController::class, 'assignFunction']);

    // SIM.1.4 - QoL score calculation
    Route::get('/grid/qol-score', [CityGridCellController::class, 'getQolScore']);

    // SIM.3 - Remove a function from a cell
    Route::delete('/grid/{id}/remove', [CityGridCellController::class, 'removeFunction']);
});

// Profile management — auth only, no role restriction so all users can manage their own account
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
