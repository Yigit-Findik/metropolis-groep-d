<?php

use App\Models\CityGridCell;
use App\Http\Controllers\CityFunctionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CityGridCellController;

Route::get('/', function () {
    return view('welcome', [
        'gridCells' => CityGridCell::ensureGridExists(),
    ]);
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/grid', [CityGridCellController::class, 'index'])->middleware(['auth', 'verified'])->name('grid');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Grid Management Routes
// SIM.2 - Cell selection and function assignment
Route::post('/grid/select/{id}', [CityGridCellController::class, 'select']);
Route::post('/grid/{id}/assign', [CityGridCellController::class, 'assignFunction']);

// SIM.1.4 - QoL score calculation
Route::get('/grid/qol-score', [CityGridCellController::class, 'getQolScore']);

// SIM.3 - Subtask 4: Build Removal API Endpoint
// DELETE endpoint to remove a function from a cell (Subtask 5: Send Removal Request to Backend)
Route::delete('/grid/{id}/remove', [CityGridCellController::class, 'removeFunction']);

require __DIR__.'/auth.php';
