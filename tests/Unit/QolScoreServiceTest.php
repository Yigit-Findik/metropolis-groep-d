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
    CityFunction::create([
        'name'                => 'Park',
        'category'            => 'Recreation',
        'image_path'          => 'test.jpg',
        'Safety'              => 3,
        'Recreation'          => 5,
        'Environment Quality' => 1,
        'Facilities'          => 2,
        'Mobility'            => 4,
    ]);

    CityGridCell::create([
        'row_index'    => 1,
        'column_index' => 1,
        'function_id'  => 1,
    ]);

    $result = (new QolScoreService())->calculate();
    expect($result['total_score'])->toBe(15);
});
