<?php

use App\Models\User;
use App\Models\Role;
use App\Models\CityFunction;

function createAdminForStructure(): User
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

// SCRUM-227: semantic elements nav and main exist in the layout
test('every page has a nav and main element', function () {
    $user = createAdminForStructure();

    foreach (['/dashboard', '/grid', '/city_functions', '/profile'] as $url) {
        $response = $this->withoutVite()->actingAs($user)->get($url);
        $response->assertOk();
        $response->assertSee('<nav', false);
        $response->assertSee('<main', false);
    }
});

// SCRUM-228: page title uses h1 (correct heading order)
test('grid page has an h1 as its page title', function () {
    $user = createAdminForStructure();

    $response = $this->withoutVite()->actingAs($user)->get('/grid');

    $response->assertOk();
    $response->assertSee('<h1', false);
});

test('city functions page has an h1 as its page title', function () {
    $user = createAdminForStructure();

    $response = $this->withoutVite()->actingAs($user)->get('/city_functions');

    $response->assertOk();
    $response->assertSee('<h1', false);
});

test('effects page has an h1 as its page title', function () {
    $user = createAdminForStructure();

    $response = $this->withoutVite()->actingAs($user)->get('/effects');

    $response->assertOk();
    $response->assertSee('<h1', false);
});

// SCRUM-226: clear section headings exist
test('grid page has headings for City Grid and Function Library sections', function () {
    $user = createAdminForStructure();

    $response = $this->withoutVite()->actingAs($user)->get('/grid');

    $response->assertOk();
    $response->assertSee('City Grid');
    $response->assertSee('Function Library');
});

// SCRUM-227: section elements wrap the main content areas on the grid page
test('grid page wraps City Grid and Function Library in section elements', function () {
    $user = createAdminForStructure();

    $response = $this->withoutVite()->actingAs($user)->get('/grid');

    $response->assertOk();
    $response->assertSee('aria-labelledby="city-grid-heading"', false);
    $response->assertSee('aria-labelledby="function-library-heading"', false);
});

// SCRUM-229: section headings have matching ids (reading order anchors)
test('grid section headings have ids that match aria-labelledby attributes', function () {
    $user = createAdminForStructure();

    $response = $this->withoutVite()->actingAs($user)->get('/grid');

    $response->assertOk();
    $response->assertSee('id="city-grid-heading"', false);
    $response->assertSee('id="function-library-heading"', false);
});
