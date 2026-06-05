<?php

use App\Models\User;
use App\Models\Role;

function createAdminForSim(): User
{
    $role = Role::firstOrCreate(['name' => 'Administrator'], [
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return User::factory()->create([
        'email_verified_at' => now(),
        'role_id'           => $role->id,
    ]);
}

// SCRUM-273: visible play and pause controls exist on the grid page
test('grid page has a play/pause button', function () {
    $user = createAdminForSim();

    $response = $this->withoutVite()->actingAs($user)->get('/grid');

    $response->assertOk();
    $response->assertSee('id="simulation-play-pause"', false);
});

// SCRUM-275: at least 3 speed levels (1x, 2x, 5x) are available
test('grid page has speed buttons for 1x, 2x, and 5x', function () {
    $user = createAdminForSim();

    $response = $this->withoutVite()->actingAs($user)->get('/grid');

    $response->assertOk();
    $response->assertSee('data-speed="1"', false);
    $response->assertSee('data-speed="2"', false);
    $response->assertSee('data-speed="5"', false);
});

// SCRUM-276: the current speed is clearly displayed
test('grid page shows the current speed display', function () {
    $user = createAdminForSim();

    $response = $this->withoutVite()->actingAs($user)->get('/grid');

    $response->assertOk();
    $response->assertSee('id="simulation-current-speed"', false);
});

// SCRUM-274: simulation starts paused (the status text says "paused" by default)
test('grid page shows simulation as paused by default', function () {
    $user = createAdminForSim();

    $response = $this->withoutVite()->actingAs($user)->get('/grid');

    $response->assertOk();
    $response->assertSee('id="simulation-status"', false);
    $response->assertSee('(paused)');
});

// SCRUM-277: the simulation panel uses Alpine so pause/play can happen at any moment
test('grid page simulation controls use the simulationControls Alpine component', function () {
    $user = createAdminForSim();

    $response = $this->withoutVite()->actingAs($user)->get('/grid');

    $response->assertOk();
    $response->assertSee('x-data="simulationControls"', false);
});
