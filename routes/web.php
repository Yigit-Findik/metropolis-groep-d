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
use App\Http\Controllers\GridCellSuggestionController;
use App\Http\Controllers\SimulationCommentController;

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

Route::middleware(['auth', 'verified', 'role:Administrator,City planner,Expert in effects,Policy maker'])->get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

// Grid view — accessible to city planners, administrators, and policy makers
Route::middleware(['auth', 'verified', 'role:Administrator,City planner,Policy maker'])->group(function () {
    Route::get('/grid', [CityGridCellController::class, 'index'])->name('grid');

    // SIM.1.4 - QoL score calculation
    Route::get('/grid/qol-score', [CityGridCellController::class, 'getQolScore']);

    // REV.2.2 - Live cell list for the improvement suggestions dropdown
    Route::get('/grid/cells', [CityGridCellController::class, 'getCells']);

    // REV.1 - Preview the PDF report in the browser before downloading
    Route::get('/grid/export-pdf', [CityGridCellController::class, 'previewPdf'])->name('grid.export-pdf');

    // REV.1 - Trigger the actual PDF download
    Route::get('/grid/download-pdf', [CityGridCellController::class, 'exportPdf'])->name('grid.download-pdf');
});

// City planner and administrator routes
Route::middleware(['auth', 'verified', 'role:Administrator,City planner'])->group(function () {
    Route::get('/events', [CityEventController::class, 'index'])->name('city_events.index');
    Route::get('/events/active', [CityEventController::class, 'activeEvents'])->name('city_events.active');
    Route::post('/events', [CityEventController::class, 'store'])->name('city_events.store');
    Route::put('/events/{id}', [CityEventController::class, 'update'])->name('city_events.update');
    Route::delete('/events/{id}', [CityEventController::class, 'destroy'])->name('city_events.destroy');

    // SIM.4.2 - Activate / deactivate an event, triggering temporary QoL effect adjustments.
    Route::post('/events/{id}/activate', [CityEventController::class, 'activate'])->name('city_events.activate');
    Route::post('/events/{id}/deactivate', [CityEventController::class, 'deactivate'])->name('city_events.deactivate');
    // Internal simulation cycle deactivation/reactivation — no audit log, does not remove the event from the simulation
    Route::post('/events/{id}/sim-deactivate', [CityEventController::class, 'simDeactivate'])->name('city_events.sim_deactivate');
    Route::post('/events/{id}/sim-reactivate', [CityEventController::class, 'simReactivate'])->name('city_events.sim_reactivate');

    // Day/Night Cycle specific routes
    Route::put('/events/{id}/day-night', [CityEventController::class, 'updateDayNight'])->name('city_events.update_day_night');
    Route::post('/events/{id}/switch-phase', [CityEventController::class, 'switchPhase'])->name('city_events.switch_phase');

    // SIM.2 - Cell selection and function assignment
    Route::post('/grid/select/{id}', [CityGridCellController::class, 'select']);
    Route::post('/grid/{id}/assign', [CityGridCellController::class, 'assignFunction']);

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

// REV.2.2 - Improvement suggestions
// All grid viewers may read; policy makers and administrators may create and delete their own
Route::middleware(['auth', 'verified', 'role:Administrator,City planner,Policy maker'])->group(function () {
    Route::get('/suggestions', [GridCellSuggestionController::class, 'index'])->name('suggestions.index');
});

Route::middleware(['auth', 'verified', 'role:Policy maker,Administrator'])->group(function () {
    Route::post('/suggestions', [GridCellSuggestionController::class, 'store'])->name('suggestions.store');
    Route::delete('/suggestions/{id}', [GridCellSuggestionController::class, 'destroy'])->name('suggestions.destroy');
});

// REV.2.2 - City planner and administrator can accept or reject suggestions
Route::middleware(['auth', 'verified', 'role:Administrator,City planner'])->group(function () {
    Route::patch('/suggestions/{id}/status', [GridCellSuggestionController::class, 'updateStatus'])->name('suggestions.status');
});

// BES.3 - Approval management — policy maker and administrator
Route::middleware(['auth', 'verified', 'role:Policy maker,Administrator'])->group(function () {
    Route::post('/grid/approve-all', [CityGridCellController::class, 'approveAllCells']);
    Route::post('/grid/revoke-all', [CityGridCellController::class, 'revokeAllCells']);
    Route::post('/grid/{id}/approve', [CityGridCellController::class, 'approveCell']);
    Route::delete('/grid/{id}/revoke', [CityGridCellController::class, 'revokeCell']);
});

// REV.2.1 - Simulation comments
// All grid viewers may read comments; only policy makers and administrators may write or delete
Route::middleware(['auth', 'verified', 'role:Administrator,City planner,Policy maker'])->group(function () {
    Route::get('/comments', [SimulationCommentController::class, 'index'])->name('comments.index');
});

Route::middleware(['auth', 'verified', 'role:Policy maker,Administrator'])->group(function () {
    Route::post('/comments', [SimulationCommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{id}', [SimulationCommentController::class, 'destroy'])->name('comments.destroy');
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
