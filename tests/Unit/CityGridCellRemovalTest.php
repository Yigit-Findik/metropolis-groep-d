<?php

use App\Models\CityGridCell;
use App\Models\CityFunction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

// 1: remove function from a grid cell
test('can remove function from grid cell', function () {
    $function = CityFunction::create([
        'name'                => 'Park',
        'category'            => 'Recreation',
        'Safety'              => 1,
        'Recreation'          => 5,
        'Environment Quality' => 2,
        'Facilities'          => 1,
        'Mobility'            => 1,
        'image_path'          => 'park.jpg',
    ]);

    $cell = CityGridCell::create([
        'row_index'    => 1,
        'column_index' => 1,
        'function_id'  => $function->id,
    ]);

    $cell->update(['function_id' => null]);
    $cell->refresh();

    expect($cell->function_id)->toBeNull();
});

// 2: removing a function from one cell does not affect other cells
test('removing function does not affect other cells', function () {
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

    $cell1 = CityGridCell::create([
        'row_index'    => 1,
        'column_index' => 1,
        'function_id'  => $function->id,
    ]);

    $cell2 = CityGridCell::create([
        'row_index'    => 1,
        'column_index' => 2,
        'function_id'  => $function->id,
    ]);

    $cell1->update(['function_id' => null]);
    $cell2->refresh();

    expect($cell1->function_id)->toBeNull();
    expect($cell2->function_id)->toBe($function->id);
});
