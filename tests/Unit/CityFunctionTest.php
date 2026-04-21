<?php

use App\Models\CityFunction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

// 1: create a city function with all qol scores
test('can create city function with all QoL scores', function () {
    $function = CityFunction::create([
        'name' => 'Hospital',
        'category' => 'healthcare',
        'qol_score' => 30,
        'livability' => 5,
        'safety' => 10,
        'economy' => 3,
        'environment' => 4,
        'welfare' => 8,
        'image_path' => 'hospital.jpg',
    ]);

    expect($function->name)->toBe('Hospital');
    expect($function->safety)->toBe(10);
});

// 2: verify function has all required fields stored correctly
test('city function has all required fields stored', function () {
    $function = CityFunction::create([
        'name' => 'School',
        'category' => 'education',
        'qol_score' => 20,
        'livability' => 4,
        'safety' => 2,
        'economy' => 5,
        'environment' => 3,
        'welfare' => 6,
        'image_path' => 'school.jpg',
        'description' => 'Primary school',
    ]);

    expect($function)->toHaveKeys(['id', 'name', 'category', 'livability', 'safety', 'economy', 'environment', 'welfare']);
    expect($function->id)->toBeInt();
});
