<?php

use App\Models\User;
use App\Models\Role;
use App\Models\CityFunction;

function createAdminForButtons(): User
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

test('login page has a descriptive submit button', function () {
    $response = $this->get('/login');

    $response->assertOk();
    $response->assertSee('Log in');
});

test('register page has a descriptive submit button', function () {
    $response = $this->get('/register');

    $response->assertOk();
    $response->assertSee('Register');
});

test('forgot password page has a descriptive submit button', function () {
    $response = $this->get('/forgot-password');

    $response->assertOk();
    $response->assertSee('Email Password Reset Link');
});

test('profile page has descriptive save buttons instead of generic save', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/profile');

    $response->assertOk();

    // Each Save button must be specific
    $response->assertSee('Save profile');
    $response->assertSee('Update password');

    // Generic "Save" alone should no longer exist
    $response->assertDontSee('>Save<', false);
});

test('city functions edit buttons have aria-label with function name', function () {
    $user = createAdminForButtons();

    $fn = CityFunction::create([
        'name'       => 'Central Park',
        'category'   => 'Recreation',
        'image_path' => 'images/placeholder.png',
    ]);

    $response = $this->withoutVite()->actingAs($user)->get('/city_functions');

    $response->assertOk();
    $response->assertSee('aria-label="Edit Central Park"', false);
});

test('city functions delete buttons have aria-label with function name', function () {
    $user = createAdminForButtons();

    $fn = CityFunction::create([
        'name'       => 'Central Park',
        'category'   => 'Recreation',
        'image_path' => 'images/placeholder.png',
    ]);

    $response = $this->withoutVite()->actingAs($user)->get('/city_functions');

    $response->assertOk();
    $response->assertSee('aria-label="Delete Central Park"', false);
});

test('grid page undo button has descriptive text', function () {
    $user = createAdminForButtons();

    $response = $this->withoutVite()->actingAs($user)->get('/grid');

    $response->assertOk();
    $response->assertSee('Undo Last Action');
});

test('grid function library cards have descriptive aria-labels', function () {
    $user = createAdminForButtons();

    CityFunction::create([
        'name'       => 'Fire Station',
        'category'   => 'Safety',
        'image_path' => 'images/placeholder.png',
    ]);

    $response = $this->withoutVite()->actingAs($user)->get('/grid');

    $response->assertOk();
    $response->assertSee('aria-label="Drag Fire Station onto the grid"', false);
});
