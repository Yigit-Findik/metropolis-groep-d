<?php

use App\Models\CityGridCell;
use App\Http\Controllers\CityFunctionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CityGridController;

Route::get('/', function () {
    return view('welcome', [
        'gridCells' => CityGridCell::ensureGridExists(),
    ]);
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/library', [CityFunctionController::class, 'index'])->middleware(['auth', 'verified'])->name('library');
Route::get('/grid', [CityGridController::class, 'index'])->middleware(['auth', 'verified'])->name('grid');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

//gridcontroller
Route::post('/grid/select/{id}', [CityGridController::class, 'select']);
Route::post('/grid/{id}/assign', [CityGridController::class, 'assignFunction']);



require __DIR__.'/auth.php';
