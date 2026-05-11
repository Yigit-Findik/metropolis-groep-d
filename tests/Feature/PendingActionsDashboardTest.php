<?php

use App\Models\CityFunction;
use App\Models\PendingAction;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createUserWithRole(string $roleName, string $email): User
{
    $role = Role::create(['name' => $roleName]);

    $user = User::factory()->create([
        'email' => $email,
    ]);

    $user->role()->associate($role);
    $user->save();

    return $user->refresh();
}

test('effects expert can open the pending actions dashboard', function () {
    $expert = createUserWithRole('Expert in effects', 'expert-dashboard@metropolis.test');

    $this->actingAs($expert)
        ->get('/effects/pending-actions')
        ->assertOk()
        ->assertSee('Pending Actions');
});

test('expert can open the dashboard page', function () {
    $expert = createUserWithRole('Expert in effects', 'expert-home@metropolis.test');

    $this->actingAs($expert)
        ->get('/dashboard')
    ->assertOk()
    ->assertSee('Dashboard');
});

test('planner cannot open the pending actions dashboard', function () {
    $planner = createUserWithRole('City planner', 'planner@metropolis.test');

    $this->actingAs($planner)
        ->get('/effects/pending-actions')
        ->assertForbidden();
});

test('creating a city function with complete effects does not create a pending action', function () {
    $admin = createUserWithRole('Administrator', 'admin-trigger@metropolis.test');

    $this->actingAs($admin);

    $function = CityFunction::create([
        'name' => 'Test Function',
        'category' => 'Safety',
        'Safety' => 1,
        'Recreation' => 1,
        'Environment Quality' => 1,
        'Facilities' => 1,
        'Mobility' => 1,
        'image_path' => 'images/test.png',
    ]);

    expect(PendingAction::query()->count())->toBe(0);

    $function->update(['name' => 'Test Function Updated']);

    expect(PendingAction::query()->count())->toBe(0);

    $function->update(['name' => 'Test Function Updated Again']);

    expect(PendingAction::query()->count())->toBe(0);

    $function->delete();

    expect(PendingAction::query()->count())->toBe(0);
});

test('creating a city function with missing effects creates a pending action', function () {
    $admin = createUserWithRole('Administrator', 'admin-created-missing@metropolis.test');

    $this->actingAs($admin);

    $function = CityFunction::create([
        'name' => 'Missing Effects Function',
        'category' => 'Safety',
        'Safety' => 1,
        'Recreation' => null,
        'Environment Quality' => 1,
        'Facilities' => 1,
        'Mobility' => 1,
        'image_path' => 'images/test.png',
    ]);

    expect($function->exists)->toBeTrue();
    expect(PendingAction::query()->count())->toBe(1);
    expect(PendingAction::query()->first()->trigger_type)->toBe(PendingAction::TRIGGER_CREATED);
});

test('non administrators do not generate pending actions for city function changes', function () {
    $planner = createUserWithRole('City planner', 'planner-trigger@metropolis.test');

    $this->actingAs($planner);

    CityFunction::create([
        'name' => 'Planner Function',
        'category' => 'Safety',
        'Safety' => 1,
        'Recreation' => 1,
        'Environment Quality' => 1,
        'Facilities' => 1,
        'Mobility' => 1,
        'image_path' => 'images/test.png',
    ]);

    expect(PendingAction::query()->count())->toBe(0);
});

test('pending actions complete automatically after effect values are saved', function () {
    $admin = createUserWithRole('Administrator', 'admin-complete@metropolis.test');
    $expert = createUserWithRole('Expert in effects', 'expert-complete@metropolis.test');

    $this->actingAs($admin);

    $function = CityFunction::create([
        'name' => 'Completion Function',
        'category' => 'Safety',
        'Safety' => 0,
        'Recreation' => 0,
        'Environment Quality' => 0,
        'Facilities' => 0,
        'Mobility' => 0,
        'image_path' => 'images/test.png',
    ]);

    $pendingAction = PendingAction::query()->where('city_function_id', $function->id)->where('status', PendingAction::STATUS_PENDING)->first();

    expect($pendingAction)->not->toBeNull();

    $this->actingAs($expert)
        ->post(route('effects.update', $function->id), [
            'category' => 'Safety',
            'value' => 4,
        ])
        ->assertOk();

    $pendingAction->refresh();

    expect($pendingAction->status)->toBe(PendingAction::STATUS_COMPLETED);
    expect($pendingAction->completed_at)->not->toBeNull();
});

test('incomplete effect updates create a pending action for the effects expert', function () {
    $admin = createUserWithRole('Administrator', 'admin-incomplete@metropolis.test');
    $expert = createUserWithRole('Expert in effects', 'expert-incomplete@metropolis.test');

    $this->actingAs($admin);

    $function = CityFunction::create([
        'name' => 'Incomplete Function',
        'category' => 'Safety',
        'Safety' => 0,
        'Recreation' => 0,
        'Environment Quality' => 0,
        'Facilities' => 0,
        'Mobility' => 0,
        'image_path' => 'images/test.png',
    ]);

    $this->actingAs($expert);

    $function->forceFill([
        'Safety' => 15,
    ])->save();

    $pendingAction = PendingAction::query()
        ->where('city_function_id', $function->id)
        ->where('trigger_type', PendingAction::TRIGGER_EFFECT_VALUES)
        ->where('status', PendingAction::STATUS_PENDING)
        ->first();

    expect($pendingAction)->not->toBeNull();

    $this->actingAs($expert)
        ->post(route('effects.update', $function->id), [
            'category' => 'Safety',
            'value' => 5,
        ])
        ->assertOk();

    $pendingAction->refresh();

    expect($pendingAction->status)->toBe(PendingAction::STATUS_COMPLETED);
    expect($pendingAction->completed_at)->not->toBeNull();
});