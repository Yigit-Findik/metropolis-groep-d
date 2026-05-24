<?php

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $role = Role::firstOrCreate(['name' => 'City planner']);
    $this->user = User::factory()->create(['role_id' => $role->id]);
});

it('grid displays without relying on color alone', function () {
    $response = $this->actingAs($this->user)->get('/grid');

    $response->assertStatus(200);
    $response->assertSee('id="qol-score-label"', false);
    $response->assertSee('border-2 border-dashed', false);
    $response->assertSee('aria-label="Row', false);
    $response->assertSee('available', false);
});

