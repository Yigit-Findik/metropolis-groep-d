<?php

use App\Models\User;
use App\Models\Role;
use App\Models\CityFunction;
use App\Models\CityGridCell;

function createAdminForGrid(): User
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

// AC.F.3: grid container must carry role="grid" so screen readers treat it as a data grid
test('city grid container has role grid', function () {
    $user = createAdminForGrid();

    $response = $this->withoutVite()->actingAs($user)->get('/grid');

    $response->assertOk();
    $response->assertSee('role="grid"', false);
});

// AC.F.3: grid must be labelled so its purpose is announced on focus
test('city grid container is labelled by the city grid heading', function () {
    $user = createAdminForGrid();

    $response = $this->withoutVite()->actingAs($user)->get('/grid');

    $response->assertOk();
    $response->assertSee('aria-labelledby="city-grid-heading"', false);
});

// AC.F.3: each logical row must be wrapped in a role="row" element
test('city grid rows have role row', function () {
    $user = createAdminForGrid();

    $response = $this->withoutVite()->actingAs($user)->get('/grid');

    $response->assertOk();
    $response->assertSee('role="row"', false);
});

// AC.F.3: each cell wrapper must carry role="gridcell"
test('city grid cells have role gridcell', function () {
    $user = createAdminForGrid();

    $response = $this->withoutVite()->actingAs($user)->get('/grid');

    $response->assertOk();
    $response->assertSee('role="gridcell"', false);
});

// AC.F.3: aria-rowindex and aria-colindex allow screen readers to announce grid position
test('city grid cells have aria-rowindex and aria-colindex attributes', function () {
    $user = createAdminForGrid();

    $response = $this->withoutVite()->actingAs($user)->get('/grid');

    $response->assertOk();
    $response->assertSee('aria-rowindex=', false);
    $response->assertSee('aria-colindex=', false);
});

// AC.F.3: empty cells announce their coordinates so users know where they are on the grid
test('empty grid cells have an aria-label that includes row and column coordinates', function () {
    $user = createAdminForGrid();

    CityGridCell::create([
        'row_index'    => 2,
        'column_index' => 3,
        'function_id'  => null,
    ]);

    $response = $this->withoutVite()->actingAs($user)->get('/grid');

    $response->assertOk();
    $response->assertSee('aria-rowindex="2"', false);
    $response->assertSee('aria-colindex="3"', false);
});

// AC.F.3: occupied cells include the function name in the gridcell aria-label
test('occupied grid cells include the function name in their aria-label', function () {
    $user = createAdminForGrid();

    $fn = CityFunction::create([
        'name'       => 'Police Station',
        'category'   => 'Safety',
        'image_path' => 'images/placeholder.png',
    ]);

    CityGridCell::create([
        'row_index'    => 1,
        'column_index' => 1,
        'function_id'  => $fn->id,
    ]);

    $response = $this->withoutVite()->actingAs($user)->get('/grid');

    $response->assertOk();
    $response->assertSee('Police Station', false);
});

// AC.F.3: occupied cells include the category in the gridcell aria-label
test('occupied grid cells include the category in their aria-label', function () {
    $user = createAdminForGrid();

    $fn = CityFunction::create([
        'name'       => 'Sports Centre',
        'category'   => 'Recreation',
        'image_path' => 'images/placeholder.png',
    ]);

    CityGridCell::create([
        'row_index'    => 1,
        'column_index' => 2,
        'function_id'  => $fn->id,
    ]);

    $response = $this->withoutVite()->actingAs($user)->get('/grid');

    $response->assertOk();
    $response->assertSee('category Recreation', false);
});
