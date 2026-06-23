<?php

use App\Events\NewFunctionAdded;
use App\Models\CityFunction;
use App\Models\CityGridCell;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

function e2eTestUser(string $roleName, string $email): User
{
    $role = Role::firstOrCreate(['name' => $roleName]);
    $user = User::factory()->create(['email' => $email]);
    $user->role()->associate($role)->save();
    return $user->refresh();
}

test('complete simulation flow: admin creates function, planner assigns it, policy maker approves, expert sets effects', function () {
    Event::fake([NewFunctionAdded::class]);

    $admin       = e2eTestUser('Administrator',    'e2e-admin@metropolis.test');
    $planner     = e2eTestUser('City planner',     'e2e-planner@metropolis.test');
    $policyMaker = e2eTestUser('Policy maker',     'e2e-policy@metropolis.test');
    $expert      = e2eTestUser('Expert in effects','e2e-expert@metropolis.test');

    // 1. Admin sees the dashboard
    $this->actingAs($admin)->get('/dashboard')->assertOk();

    // 2. Admin creates a new city function
    $this->actingAs($admin)->post('/city_functions', [
        'name'                => 'E2E Park',
        'category'            => 'Recreation',
        'safety'              => 1,
        'recreation'          => 3,
        'environment_quality' => 2,
        'facilities'          => 1,
        'mobility'            => 0,
    ])->assertRedirect();

    $function = CityFunction::where('name', 'E2E Park')->firstOrFail();
    expect($function->Recreation)->toBe(3);

    // 3. City planner views the grid
    CityGridCell::ensureGridExists();
    $this->actingAs($planner)->get('/grid')->assertOk();

    // 4. City planner assigns the function to a grid cell
    $cell = CityGridCell::first();

    $this->actingAs($planner)
        ->postJson("/grid/{$cell->id}/assign", ['function_id' => $function->id])
        ->assertOk()
        ->assertJsonPath('message', 'Function assigned');

    expect($cell->refresh()->function_id)->toBe($function->id);

    // 5. Policy maker approves the cell
    $this->actingAs($policyMaker)
        ->postJson("/grid/{$cell->id}/approve")
        ->assertOk()
        ->assertJsonPath('message', 'Cell approved');

    expect($cell->refresh()->is_approved)->toBeTrue();

    // 6. Approved cell cannot be modified by the planner
    $this->actingAs($planner)
        ->postJson("/grid/{$cell->id}/assign", ['function_id' => $function->id])
        ->assertStatus(422);

    // 7. Expert in effects updates the Recreation score
    $this->actingAs($expert)
        ->postJson(route('effects.update', $function->id), [
            'category' => 'Recreation',
            'value'    => 5,
        ])
        ->assertOk()
        ->assertJsonPath('message', 'Effect updated successfully');

    expect($function->refresh()->Recreation)->toBe(5);

    // 8. City planner can fetch the QoL score after the assignment
    $this->actingAs($planner)
        ->getJson('/grid/qol-score')
        ->assertOk();
});
