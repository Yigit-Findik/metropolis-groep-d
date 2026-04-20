<?php

use App\Models\CityFunction;
use App\Models\CityGridCell;
use App\Services\QolScoreService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

// 1: check that service returns structure correctly
test('service returns correct structure', function () {
    $service = new QolScoreService();
    $result = $service->calculate();

    expect($result)->toHaveKeys(['total_score', 'categories', 'breakdown']);
});

// 2: check that scores have correct calculations
test('service calculates scores correctly', function () {
    // create city function with random scores
    CityFunction::create([
        'name' => 'Park',
        'qol_score' => 10,
        'category' => 'recreation',
        'image_path' => 'test.jpg',
        'livability' => 5,
        'safety' => 3,
        'economy' => 1,
        'environment' => 2,
        'welfare' => 4,
    ]);

    // add function to the grid
    CityGridCell::create([
        'row_index' => 1,
        'column_index' => 1,
        'function_id' => 1,
    ]);

    // calculate and verify total score (randomly set to 15)
    $result = (new QolScoreService())->calculate();
    expect($result['total_score'])->toBe(15);
});
