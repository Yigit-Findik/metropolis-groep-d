<?php

use App\Models\CityFunction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

// 1: create a city function with all category scores
test('can create city function with all category scores', function () {
    $function = CityFunction::create([
        'name' => 'Police Station',
        'category' => 'Safety',
        'Safety' => 5,
        'Recreation' => 1,
        'Environment Quality' => 0,
        'Facilities' => 1,
        'Mobility' => 2,
        'image_path' => 'police_station.png',
    ]);

    expect($function->name)->toBe('Police Station');
    expect($function->Safety)->toBe(5);
});

// 2: verify function has all required fields stored correctly
test('city function has all required fields stored', function () {
    $function = CityFunction::create([
        'name' => 'Park',
        'category' => 'Recreation',
        'Safety' => -2,
        'Recreation' => 5,
        'Environment Quality' => 4,
        'Facilities' => 0,
        'Mobility' => 1,
        'image_path' => 'park.png',
        'description' => 'A public green space for recreation.',
    ]);

    expect($function)->toHaveKeys(['id', 'name', 'category', 'Safety', 'Recreation', 'Environment Quality', 'Facilities', 'Mobility']);
    expect($function->id)->toBeInt();
});
