<?php

use App\Models\CityFunction;
use App\Services\CityFunctionEffectValueService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('effect columns are derived from the city function schema', function () {
    $service = new CityFunctionEffectValueService();

    expect($service->effectColumns())
        ->toContain('Safety')
        ->and($service->effectColumns())
        ->not->toContain('name')
        ->and($service->effectColumns())
        ->not->toContain('category');
});

test('hasCompleteValues rejects invalid effect states', function () {
    $service = new CityFunctionEffectValueService();

    $function = CityFunction::create([
        'name' => 'Service Test Function',
        'category' => 'Safety',
        'Safety' => 1,
        'Recreation' => 1,
        'Environment Quality' => 1,
        'Facilities' => 1,
        'Mobility' => 1,
        'image_path' => 'images/test.png',
    ]);

    expect($service->hasCompleteValues($function))->toBeTrue();

    $function->update(['Safety' => 15]);
    $function->refresh();

    expect($service->hasCompleteValues($function))->toBeFalse();
});

test('missingEffectColumns detects empty and partial values from persisted data', function () {
    $service = new CityFunctionEffectValueService();

    $function = CityFunction::create([
        'name' => 'Partial Function',
        'category' => 'Safety',
        'Safety' => 0,
        'Recreation' => 0,
        'Environment Quality' => 0,
        'Facilities' => 0,
        'Mobility' => 0,
        'image_path' => 'images/test.png',
    ]);

    expect($service->missingEffectColumns($function))->toBe([]);
    expect($service->allRequiredEffectValuesAreFilledAndSaved($function))->toBeTrue();

    $function->update(['Safety' => 7]);
    $function->refresh();

    expect($service->missingEffectColumns($function))->toBe([]);
    expect($service->allRequiredEffectValuesAreFilledAndSaved($function))->toBeTrue();
});