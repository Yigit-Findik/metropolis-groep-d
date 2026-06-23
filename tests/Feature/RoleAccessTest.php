<?php

use App\Models\CityGridCell;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function roleTestUser(string $roleName, string $email): User
{
    $role = Role::firstOrCreate(['name' => $roleName]);
    $user = User::factory()->create(['email' => $email]);
    $user->role()->associate($role)->save();
    return $user->refresh();
}

// ── Dashboard ────────────────────────────────────────────────────────────────

test('all roles can access the dashboard', function (string $roleName) {
    $user = roleTestUser($roleName, 'test@metropolis.test');
    $this->actingAs($user)->get('/dashboard')->assertOk();
})->with(['Administrator', 'City planner', 'Expert in effects', 'Policy maker']);

test('unauthenticated user is redirected away from dashboard', function () {
    $this->get('/dashboard')->assertRedirect();
});

// ── Grid ─────────────────────────────────────────────────────────────────────

test('administrator city planner and policy maker can access the grid', function (string $roleName) {
    $user = roleTestUser($roleName, 'test@metropolis.test');
    $this->actingAs($user)->get('/grid')->assertOk();
})->with(['Administrator', 'City planner', 'Policy maker']);

test('effects expert cannot access the grid', function () {
    $user = roleTestUser('Expert in effects', 'test@metropolis.test');
    $this->actingAs($user)->get('/grid')->assertForbidden();
});

// ── City functions management (Administrator only) ────────────────────────────

test('administrator can access city functions management', function () {
    $user = roleTestUser('Administrator', 'test@metropolis.test');
    $this->actingAs($user)->get('/city_functions')->assertOk();
});

test('non-administrators cannot access city functions management', function (string $roleName) {
    $user = roleTestUser($roleName, 'test@metropolis.test');
    $this->actingAs($user)->get('/city_functions')->assertForbidden();
})->with(['City planner', 'Expert in effects', 'Policy maker']);

// ── Effects table ─────────────────────────────────────────────────────────────

test('administrator city planner and expert can access the effects page', function (string $roleName) {
    $user = roleTestUser($roleName, 'test@metropolis.test');
    $this->actingAs($user)->get('/effects')->assertOk();
})->with(['Administrator', 'City planner', 'Expert in effects']);

test('policy maker cannot access the effects page', function () {
    $user = roleTestUser('Policy maker', 'test@metropolis.test');
    $this->actingAs($user)->get('/effects')->assertForbidden();
});

// ── Pending actions (Administrator + Expert only) ─────────────────────────────

test('administrator and expert can access the pending actions dashboard', function (string $roleName) {
    $user = roleTestUser($roleName, 'test@metropolis.test');
    $this->actingAs($user)->get('/effects/pending-actions')->assertOk();
})->with(['Administrator', 'Expert in effects']);

test('city planner and policy maker cannot access pending actions', function (string $roleName) {
    $user = roleTestUser($roleName, 'test@metropolis.test');
    $this->actingAs($user)->get('/effects/pending-actions')->assertForbidden();
})->with(['City planner', 'Policy maker']);

// ── Audit log (Administrator only) ───────────────────────────────────────────

test('administrator can access the audit log', function () {
    $user = roleTestUser('Administrator', 'test@metropolis.test');
    $this->actingAs($user)->get('/audit-log')->assertOk();
});

test('non-administrators cannot access the audit log', function (string $roleName) {
    $user = roleTestUser($roleName, 'test@metropolis.test');
    $this->actingAs($user)->get('/audit-log')->assertForbidden();
})->with(['City planner', 'Expert in effects', 'Policy maker']);

// ── Cell approval (Policy maker + Administrator only) ─────────────────────────

test('policy maker and administrator can approve a grid cell', function (string $roleName) {
    CityGridCell::ensureGridExists();
    $cell = CityGridCell::first();
    $user = roleTestUser($roleName, 'test@metropolis.test');
    $this->actingAs($user)->postJson("/grid/{$cell->id}/approve")->assertOk();
})->with(['Administrator', 'Policy maker']);

test('city planner and expert cannot approve grid cells', function (string $roleName) {
    CityGridCell::ensureGridExists();
    $cell = CityGridCell::first();
    $user = roleTestUser($roleName, 'test@metropolis.test');
    $this->actingAs($user)->postJson("/grid/{$cell->id}/approve")->assertForbidden();
})->with(['City planner', 'Expert in effects']);

// ── Suggestions (read: Admin, Planner, Policy maker | write: Admin, Policy maker) ──

test('administrator city planner and policy maker can read suggestions', function (string $roleName) {
    $user = roleTestUser($roleName, 'test@metropolis.test');
    $this->actingAs($user)->get('/suggestions')->assertOk();
})->with(['Administrator', 'City planner', 'Policy maker']);

test('expert cannot read suggestions', function () {
    $user = roleTestUser('Expert in effects', 'test@metropolis.test');
    $this->actingAs($user)->get('/suggestions')->assertForbidden();
});

test('city planner cannot create suggestions', function () {
    $user = roleTestUser('City planner', 'test@metropolis.test');
    $this->actingAs($user)->postJson('/suggestions', ['cell_id' => 1, 'suggestion' => 'test'])->assertForbidden();
});
