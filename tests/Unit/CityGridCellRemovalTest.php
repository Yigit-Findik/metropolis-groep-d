<?php

use App\Models\CityGridCell;
use App\Models\CityFunction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

// Test 1: Remove function from a grid cell
test('can remove function from grid cell', function () {
    $function = CityFunction::create([
        'name' => 'Park',
        'category' => 'recreation',
        'qol_score' => 10,
        'livability' => 5,
        'safety' => 1,
        'economy' => 1,
        'environment' => 2,
        'welfare' => 1,
        'image_path' => 'park.jpg',
    ]);

    $cell = CityGridCell::create([
        'row_index' => 1,
        'column_index' => 1,
        'function_id' => $function->id,
    ]);

    $cell->update(['function_id' => null]);
    $cell->refresh();

    expect($cell->function_id)->toBeNull();
});

// Test 2: Removing function from one cell does not affect other cells
test('removing function does not affect other cells', function () {
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

    $cell1 = CityGridCell::create([
        'row_index' => 1,
        'column_index' => 1,
        'function_id' => $function->id,
    ]);

    $cell2 = CityGridCell::create([
        'row_index' => 1,
        'column_index' => 2,
        'function_id' => $function->id,
    ]);

    // Remove function from first cell
    $cell1->update(['function_id' => null]);
    $cell2->refresh();

    // First cell should be empty, second cell should still have the function
    expect($cell1->function_id)->toBeNull();
    expect($cell2->function_id)->toBe($function->id);
});
