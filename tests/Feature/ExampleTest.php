<?php

use App\Models\CityGridCell;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns a successful response and builds the city grid', function () {
    $role = Role::firstOrCreate(['name' => 'Administrator'], [
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'role_id'           => $role->id,
    ]);

    $response = $this->withoutVite()->actingAs($user)->get('/grid');

    $response->assertStatus(200);
    $response->assertDontSee('3 x 4 city area grid');
    $response->assertDontSee('Selectable cells');
    $response->assertDontSee('Backend and frontend grid');
    $response->assertDontSee('No function assigned');

    expect(CityGridCell::query()->count())->toBe(12);
});
