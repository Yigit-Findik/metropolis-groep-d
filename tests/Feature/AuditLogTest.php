<?php

use App\Models\ActionHistory;
use App\Models\CityFunction;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createAuditUserWithRole(string $roleName, string $email): User
{
    $role = Role::create(['name' => $roleName]);

    $user = User::factory()->create([
        'email' => $email,
    ]);

    $user->role()->associate($role);
    $user->save();

    return $user->refresh();
}

test('audit log keeps deleted city functions visible and stores a concise snapshot', function () {
    $admin = createAuditUserWithRole('Administrator', 'audit-admin@metropolis.test');

    $this->actingAs($admin);

    $function = CityFunction::create([
        'name' => 'Audit Function',
        'category' => 'Facilities',
        'description' => 'Short audit description',
        'Safety' => 3,
        'Recreation' => 2,
        'Environment Quality' => 4,
        'Facilities' => 5,
        'Mobility' => 1,
        'image_path' => 'images/audit-function.png',
    ]);

    $function->delete();

    $createHistory = ActionHistory::query()->where('action', 'create')->firstOrFail();
    $deleteHistory = ActionHistory::query()->where('action', 'delete')->firstOrFail();

    expect(array_keys($createHistory->details['new']))->toEqualCanonicalizing([
        'name',
        'category',
        'description',
        'Safety',
        'Recreation',
        'Environment Quality',
        'Facilities',
        'Mobility',
    ]);

    expect(array_keys($deleteHistory->details['original']))->toEqualCanonicalizing([
        'name',
        'category',
        'description',
        'Safety',
        'Recreation',
        'Environment Quality',
        'Facilities',
        'Mobility',
    ]);

    $this->get('/audit-log')
        ->assertOk()
        ->assertSee('Created city function')
        ->assertSee('Deleted city function')
        ->assertSee('Audit Function')
        ->assertSee('bg-green-100')
        ->assertSee('bg-red-100');
});