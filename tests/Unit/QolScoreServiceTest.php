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

test('service includes neighbor bonuses and penalties in the score', function () {
    $park = CityFunction::create([
        'name'                => 'Park',
        'category'            => 'Recreation',
        'image_path'          => 'test.jpg',
        'Safety'              => 0,
        'Recreation'          => 5,
        'Environment Quality' => 0,
        'Facilities'          => 0,
        'Mobility'            => 0,
    ]);

    $garden = CityFunction::create([
        'name'                => 'Garden',
        'category'            => 'Recreation',
        'image_path'          => 'test-2.jpg',
        'Safety'              => 0,
        'Recreation'          => 0,
        'Environment Quality' => 0,
        'Facilities'          => 0,
        'Mobility'            => 0,
    ]);

    $road = CityFunction::create([
        'name'                => 'Road',
        'category'            => 'Mobility',
        'image_path'          => 'test-3.jpg',
        'Safety'              => 0,
        'Recreation'          => 0,
        'Environment Quality' => 0,
        'Facilities'          => 0,
        'Mobility'            => 0,
    ]);

    CityGridCell::create([
        'row_index'    => 1,
        'column_index' => 1,
        'function_id'  => $park->id,
    ]);

    CityGridCell::create([
        'row_index'    => 1,
        'column_index' => 2,
        'function_id'  => $garden->id,
    ]);

    CityGridCell::create([
        'row_index'    => 2,
        'column_index' => 1,
        'function_id'  => $road->id,
    ]);

    $result = (new QolScoreService())->calculate();

    expect($result['total_score'])->toBe(5)
        ->and($result['categories']['recreation'])->toBe(5);
});

test('same-category neighbor bonus is counted once per pair', function () {
    $parkA = CityFunction::create([
        'name'                => 'Park A',
        'category'            => 'Recreation',
        'image_path'          => 'test-a.jpg',
        'Safety'              => 0,
        'Recreation'          => 0,
        'Environment Quality' => 0,
        'Facilities'          => 0,
        'Mobility'            => 0,
    ]);

    $parkB = CityFunction::create([
        'name'                => 'Park B',
        'category'            => 'Recreation',
        'image_path'          => 'test-b.jpg',
        'Safety'              => 0,
        'Recreation'          => 0,
        'Environment Quality' => 0,
        'Facilities'          => 0,
        'Mobility'            => 0,
    ]);

    CityGridCell::create([
        'row_index'    => 1,
        'column_index' => 1,
        'function_id'  => $parkA->id,
    ]);

    CityGridCell::create([
        'row_index'    => 1,
        'column_index' => 2,
        'function_id'  => $parkB->id,
    ]);

    $result = (new QolScoreService())->calculate();

    expect($result['total_score'])->toBe(2)
        ->and($result['categories']['recreation'])->toBe(2);
});
