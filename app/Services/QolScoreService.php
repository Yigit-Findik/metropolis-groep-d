<?php

namespace App\Services;

use App\Models\CityGridCell;

class QolScoreService
{
    // Map display categories to database column names
    public const CATEGORIES = [
        'Safety' => 'Safety',
        'Recreation' => 'Recreation',
        'Environment Quality' => 'Environment Quality',
        'Facilities' => 'Facilities',
        'Mobility' => 'Mobility',
    ];

    public function calculate(): array
    {
        $cells = CityGridCell::with('cityFunction')->get();

        $totals = [
            'Safety' => 0,
            'Recreation' => 0,
            'Environment Quality' => 0,
            'Facilities' => 0,
            'Mobility' => 0,
        ];
        $totalScore = 0;
        $breakdown = [];

        foreach ($cells as $cell) {
            if ($cell->cityFunction) {
                $fn = $cell->cityFunction;
                foreach (self::CATEGORIES as $displayName => $columnName) {
                    $value = $fn->{$columnName} ?? 0;
                    $totals[$displayName] += $value;
                    $totalScore += $value;
                }

                $breakdown[] = [
                    'row'         => $cell->row_index,
                    'column'      => $cell->column_index,
                    'function'    => $fn->name,
                    'Safety'      => $fn->Safety ?? 0,
                    'Recreation'  => $fn->Recreation ?? 0,
                    'Environment Quality' => $fn->{'Environment Quality'} ?? 0,
                    'Facilities'  => $fn->Facilities ?? 0,
                    'Mobility'    => $fn->Mobility ?? 0,
                ];
            }
        }

        return [
            'total_score' => $totalScore,
            'categories'  => $totals,
            'breakdown'   => $breakdown,
        ];
    }
}
