<?php

use App\Models\CityGridCell;
use App\Http\Controllers\CityFunctionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CityGridCellController;
use App\Http\Controllers\AccessRoadController;
use App\Http\Controllers\EventRouteController;
use App\Http\Controllers\EffectController;
use App\Http\Controllers\PendingActionController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\CityEventController;

/*
|--------------------------------------------------------------------------
| Routes — structure and conventions
|--------------------------------------------------------------------------
|
| Keep routes organized by responsibility and access level. Use the
| following conventions when adding new routes:
|
| - Group routes by middleware (e.g. `auth`, `verified`, `role:`) and by
|   purpose (admin, planner, effects expert, public, etc.).
| - Add a short comment header for each group describing its intent.
| - Use named routes when the route is referenced from views/controllers
|   (->name('...')).
| - Prefer controller methods for complex behavior. Use RESTful
|   conventions where appropriate (index, show, store, update, destroy).
| - Keep one-line comments for specific feature references (e.g. SIM.2,
|   EFF.1) so feature traceability is easy.
| - Put public or unauthenticated routes near the top, then role-based
|   groups, then `require __DIR__.'/auth.php'` at the end for auth helpers.
|
*/

// Public landing page
Route::get('/', function () {
    return view('welcome', [
        'gridCells' => CityGridCell::ensureGridExists(),
    ]);
});

Route::middleware(['auth', 'verified', 'role:Administrator,City planner,Expert in effects'])->get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

// City planner and administrator routes
Route::middleware(['auth', 'verified', 'role:Administrator,City planner'])->group(function () {
    Route::get('/grid', [CityGridCellController::class, 'index'])->name('grid');
    Route::get('/events', [CityEventController::class, 'index'])->name('city_events.index');
    Route::get('/events/active', [CityEventController::class, 'activeEvents'])->name('city_events.active');
    Route::post('/events', [CityEventController::class, 'store'])->name('city_events.store');
    Route::put('/events/{id}', [CityEventController::class, 'update'])->name('city_events.update');
    Route::delete('/events/{id}', [CityEventController::class, 'destroy'])->name('city_events.destroy');

    // SIM.4.2 - Activate / deactivate an event, triggering temporary QoL effect adjustments.
    Route::post('/events/{id}/activate', [CityEventController::class, 'activate'])->name('city_events.activate');
    Route::post('/events/{id}/deactivate', [CityEventController::class, 'deactivate'])->name('city_events.deactivate');

    // SIM.2 - Cell selection and function assignment
    Route::post('/grid/select/{id}', [CityGridCellController::class, 'select']);
    Route::post('/grid/{id}/assign', [CityGridCellController::class, 'assignFunction']);

    // SIM.1.4 - QoL score calculation
    Route::get('/grid/qol-score', [CityGridCellController::class, 'getQolScore']);

    // Get valid/invalid cells for adjacency rules
    Route::get('/grid/valid-cells', [CityGridCellController::class, 'getValidCells']);

    // SIM.3 - Remove a function from a cell
    Route::delete('/grid/{id}/remove', [CityGridCellController::class, 'removeFunction']);

    // SIM.5 - Undo a function from a cell
    Route::post('/grid/undo', [CityGridCellController::class, 'undo']);

    // SIM.12 - Main access road placement and removal
    Route::get('/access-roads', [AccessRoadController::class, 'index']);
    Route::post('/access-roads', [AccessRoadController::class, 'store']);
    Route::delete('/access-roads/{id}', [AccessRoadController::class, 'destroy']);

    // SIM.12.1 - Activate / deactivate an access road
    Route::patch('/access-roads/{id}/toggle', [AccessRoadController::class, 'toggle']);

    // SIM.12.2 - Event routes: create routes from access roads to event locations
    Route::get('/event-routes', [EventRouteController::class, 'index']);
    Route::post('/event-routes', [EventRouteController::class, 'store']);
    Route::delete('/event-routes/{id}', [EventRouteController::class, 'destroy']);
    Route::get('/event-cells', [EventRouteController::class, 'eventCells']);

});

// EFF.1 - Effect management table — accessible to city planners, effects experts, and administrators
Route::middleware(['auth', 'verified', 'role:Administrator,City planner,Expert in effects'])->group(function () {
    Route::get('/effects', [EffectController::class, 'index'])->name('effects.index');
    Route::post('/effects/{functionId}', [EffectController::class, 'update'])->name('effects.update');
});

// Pending actions dashboard — effects experts and administrators only
Route::middleware(['auth', 'verified', 'role:Administrator,Expert in effects'])->group(function () {
    Route::get('/effects/pending-actions', [PendingActionController::class, 'index'])->name('effects.pending-actions');
});

// BES.2 - City functions management
Route::middleware(['auth', 'verified', 'role:Administrator'])->group(function () {
    Route::get('/city_functions', [CityFunctionController::class, 'index'])->name('city_functions');
    Route::post('/city_functions', [CityFunctionController::class, 'store']);
    Route::put('/city_functions/{id}', [CityFunctionController::class, 'update']);
    Route::delete('/city_functions/{id}', [CityFunctionController::class, 'destroy']);
    Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit_log');
});

// Profile management — auth only, no role restriction so all users can manage their own account
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
