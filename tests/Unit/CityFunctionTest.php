<?php

use App\Models\CityFunction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

// 1: create a city function with all qol scores
test('can create city function with all QoL scores', function () {
    $function = CityFunction::create([
        'name'                => 'Hospital',
        'category'            => 'Facilities',
        'Safety'              => 10,
        'Recreation'          => 5,
        'Environment Quality' => 3,
        'Facilities'          => 4,
        'Mobility'            => 8,
        'image_path'          => 'hospital.jpg',
    ]);

    expect($function->name)->toBe('Hospital');
    expect($function->{'Safety'})->toBe(10);
});

// 2: verify function has all required fields stored correctly
test('city function has all required fields stored', function () {
    $function = CityFunction::create([
        'name'                => 'School',
        'category'            => 'Facilities',
        'Safety'              => 2,
        'Recreation'          => 4,
        'Environment Quality' => 5,
        'Facilities'          => 3,
        'Mobility'            => 6,
        'image_path'          => 'school.jpg',
        'description'         => 'Primary school',
    ]);

    expect($function)->toHaveKeys(['id', 'name', 'category', 'Safety', 'Recreation', 'Environment Quality', 'Facilities', 'Mobility']);
    expect($function->id)->toBeInt();
});
