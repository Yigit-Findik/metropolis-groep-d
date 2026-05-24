<?php

use App\Models\User;
use App\Models\Role;

function createAdminForErrors(): User
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

// --- REGISTER FORM ---

test('register shows actionable error when email is already taken', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->post('/register', [
        'name' => 'Test User',
        'email' => 'taken@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertSessionHasErrors([
        'email' => 'This email is already registered. Try logging in instead.',
    ]);
});

test('register shows actionable error when passwords do not match', function () {
    $this->post('/register', [
        'name' => 'Test User',
        'email' => 'new@example.com',
        'password' => 'password123',
        'password_confirmation' => 'different456',
    ])->assertSessionHasErrors([
        'password' => 'The passwords do not match. Please make sure both password fields are identical.',
    ]);
});

test('register shows actionable error when email format is invalid', function () {
    $this->post('/register', [
        'name' => 'Test User',
        'email' => 'not-an-email',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertSessionHasErrors([
        'email' => 'Please enter a valid email address (e.g. name@example.com).',
    ]);
});

// --- CITY FUNCTIONS FORM ---

test('city functions create shows actionable error when name is missing', function () {
    $user = createAdminForErrors();

    $this->actingAs($user)->post('/city_functions', [
        'name' => '',
        'category' => 'Recreation',
    ])->assertSessionHasErrors([
        'name' => 'Please enter a name for this city function.',
    ]);
});

test('city functions create shows actionable error when category is missing', function () {
    $user = createAdminForErrors();

    $this->actingAs($user)->post('/city_functions', [
        'name' => 'Test Park',
        'category' => '',
    ])->assertSessionHasErrors([
        'category' => 'Please select a category for this city function.',
    ]);
});

// --- PROFILE FORM ---

test('profile update shows actionable error when email is already in use', function () {
    User::factory()->create(['email' => 'other@example.com']);
    $user = User::factory()->create();

    $this->actingAs($user)->patch('/profile', [
        'name' => 'Test User',
        'email' => 'other@example.com',
    ])->assertSessionHasErrors([
        'email' => 'This email is already in use. Please enter a different email address.',
    ]);
});

test('profile update shows actionable error when email format is invalid', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->patch('/profile', [
        'name' => 'Test User',
        'email' => 'not-an-email',
    ])->assertSessionHasErrors([
        'email' => 'Please enter a valid email address (e.g. name@example.com).',
    ]);
});

// --- PASSWORD UPDATE FORM ---

test('password update shows actionable error when current password is wrong', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->from('/profile')->put('/password', [
        'current_password' => 'wrong-password',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ])->assertSessionHasErrorsIn('updatePassword', [
        'current_password' => 'The current password is incorrect. Please try again.',
    ]);
});

test('password update shows actionable error when new passwords do not match', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->from('/profile')->put('/password', [
        'current_password' => 'password',
        'password' => 'newpassword123',
        'password_confirmation' => 'different456',
    ])->assertSessionHasErrorsIn('updatePassword', [
        'password' => 'The passwords do not match. Please make sure both password fields are identical.',
    ]);
});
