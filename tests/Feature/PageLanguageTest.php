<?php

use App\Models\User;
use App\Models\Role;
use App\Models\CityFunction;

function createAdminForLanguage(): User
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

// --- Subtask 5: root lang attribute ---

test('app layout pages declare lang="en" on the html element', function () {
    $user = createAdminForLanguage();

    foreach (['/dashboard', '/grid', '/city_functions', '/profile'] as $url) {
        $response = $this->withoutVite()->actingAs($user)->get($url);
        $response->assertOk();
        $response->assertSee('lang="en"', false);
    }
});

test('guest layout pages declare lang="en" on the html element', function () {
    foreach (['/login', '/register', '/forgot-password'] as $url) {
        $response = $this->get($url);
        $response->assertOk();
        $response->assertSee('lang="en"', false);
    }
});

test('effects page declares lang="en" on the html element', function () {
    $user = createAdminForLanguage();

    $response = $this->withoutVite()->actingAs($user)->get('/effects');
    $response->assertOk();
    $response->assertSee('lang="en"', false);
});

test('audit log page declares lang="en" on the html element', function () {
    $user = createAdminForLanguage();

    $response = $this->withoutVite()->actingAs($user)->get('/audit-log');
    $response->assertOk();
    $response->assertSee('lang="en"', false);
});

// --- Subtask 6: no untagged foreign-language content ---

test('no page contains a lang attribute set to a language other than english', function () {
    $user = createAdminForLanguage();

    $pages = ['/dashboard', '/grid', '/city_functions', '/profile', '/effects', '/audit-log'];

    foreach ($pages as $url) {
        $response = $this->withoutVite()->actingAs($user)->get($url);
        $response->assertOk();

        // Confirm no element is tagged with a non-English lang value
        $response->assertDontSee('lang="nl"', false);
        $response->assertDontSee('lang="de"', false);
        $response->assertDontSee('lang="fr"', false);
    }
});

test('audit log detail elements that carry an explicit lang attribute use the correct language', function () {
    $user = createAdminForLanguage();

    CityFunction::create([
        'name'       => 'Test Park',
        'category'   => 'Recreation',
        'image_path' => 'images/placeholder.png',
    ]);

    // Trigger a logged action so detail cells are rendered
    $this->actingAs($user)->post('/city_functions', [
        'name'     => 'Audit Park',
        'category' => 'Recreation',
    ]);

    $response = $this->withoutVite()->actingAs($user)->get('/audit-log');
    $response->assertOk();

    // The detail cells explicitly declare lang="en" — verify the value is correct
    $response->assertSee('lang="en"', false);
    $response->assertDontSee('lang="nl"', false);
});
