<?php

use App\Models\User;
use App\Models\Role;
use App\Models\CityFunction;
use Illuminate\Support\Facades\DB;

function createAdminUser(): User
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

test('login page has labels properly associated with email and password fields', function () {
    $response = $this->get('/login');

    $response->assertOk();

    // Label must have for="email" and the input must have id="email"
    $response->assertSee('for="email"', false);
    $response->assertSee('id="email"', false);

    // Label must have for="password" and the input must have id="password"
    $response->assertSee('for="password"', false);
    $response->assertSee('id="password"', false);

    // Remember me checkbox must be associated with its label
    $response->assertSee('for="remember_me"', false);
    $response->assertSee('id="remember_me"', false);
});

test('register page has labels properly associated with all fields', function () {
    $response = $this->get('/register');

    $response->assertOk();

    $response->assertSee('for="name"', false);
    $response->assertSee('id="name"', false);

    $response->assertSee('for="email"', false);
    $response->assertSee('id="email"', false);

    $response->assertSee('for="password"', false);
    $response->assertSee('id="password"', false);

    $response->assertSee('for="password_confirmation"', false);
    $response->assertSee('id="password_confirmation"', false);
});

test('forgot password page has label associated with email field', function () {
    $response = $this->get('/forgot-password');

    $response->assertOk();

    $response->assertSee('for="email"', false);
    $response->assertSee('id="email"', false);
});

test('profile page has labels associated with name and email fields', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/profile');

    $response->assertOk();

    $response->assertSee('for="name"', false);
    $response->assertSee('id="name"', false);

    $response->assertSee('for="email"', false);
    $response->assertSee('id="email"', false);
});

test('profile password form has labels associated with all password fields', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/profile');

    $response->assertOk();

    $response->assertSee('for="update_password_current_password"', false);
    $response->assertSee('id="update_password_current_password"', false);

    $response->assertSee('for="update_password_password"', false);
    $response->assertSee('id="update_password_password"', false);

    $response->assertSee('for="update_password_password_confirmation"', false);
    $response->assertSee('id="update_password_password_confirmation"', false);
});

test('grid page has a label associated with the category filter select', function () {
    $user = createAdminUser();

    // The select only renders when there is at least one city function
    CityFunction::create([
        'name'       => 'Test Park',
        'category'   => 'Recreation',
        'image_path' => 'images/placeholder.png',
    ]);

    $response = $this->withoutVite()->actingAs($user)->get('/grid');

    $response->assertOk();

    $response->assertSee('for="category-filter"', false);
    $response->assertSee('id="category-filter"', false);
});

test('city functions page create modal has labels associated with all fields', function () {
    $user = createAdminUser();

    $response = $this->withoutVite()->actingAs($user)->get('/city_functions');

    $response->assertOk();

    $response->assertSee('for="create-image"', false);
    $response->assertSee('id="create-image"', false);

    $response->assertSee('for="create-name"', false);
    $response->assertSee('id="create-name"', false);

    $response->assertSee('for="create-category"', false);
    $response->assertSee('id="create-category"', false);

    $response->assertSee('for="create-description"', false);
    $response->assertSee('id="create-description"', false);
});

test('city functions page edit modal has labels associated with all fields', function () {
    $user = createAdminUser();

    $response = $this->withoutVite()->actingAs($user)->get('/city_functions');

    $response->assertOk();

    $response->assertSee('for="edit-image"', false);
    $response->assertSee('id="edit-image"', false);

    $response->assertSee('for="edit-name"', false);
    $response->assertSee('id="edit-name"', false);

    $response->assertSee('for="edit-category"', false);
    $response->assertSee('id="edit-category"', false);

    $response->assertSee('for="edit-description"', false);
    $response->assertSee('id="edit-description"', false);

    $response->assertSee('for="edit-safety"', false);
    $response->assertSee('id="edit-safety"', false);

    $response->assertSee('for="edit-recreation"', false);
    $response->assertSee('id="edit-recreation"', false);

    $response->assertSee('for="edit-environment-quality"', false);
    $response->assertSee('id="edit-environment-quality"', false);

    $response->assertSee('for="edit-facilities"', false);
    $response->assertSee('id="edit-facilities"', false);

    $response->assertSee('for="edit-mobility"', false);
    $response->assertSee('id="edit-mobility"', false);
});
