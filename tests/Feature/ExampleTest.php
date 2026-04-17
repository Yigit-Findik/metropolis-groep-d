<?php

use App\Models\CityGridCell;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns a successful response and builds the city grid', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertDontSee('3 x 4 city area grid');
    $response->assertDontSee('Selectable cells');
    $response->assertDontSee('Backend and frontend grid');
    $response->assertDontSee('No function assigned');

    expect(CityGridCell::query()->count())->toBe(12);
});
