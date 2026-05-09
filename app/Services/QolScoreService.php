<?php

namespace App\Services;

use App\Models\CityGridCell;

class QolScoreService
{
    // Keys are slugs used in HTML/JS/API responses, values are the actual DB column names
    public const CATEGORIES = [
        'safety'              => 'Safety',
        'recreation'          => 'Recreation',
        'environment_quality' => 'Environment Quality',
        'facilities'          => 'Facilities',
        'mobility'            => 'Mobility',
    ];

    public function calculate(): array
    {
        $cells = CityGridCell::with('cityFunction')->get();

        $totals = array_fill_keys(array_keys(self::CATEGORIES), 0);
        $totalScore = 0;
        $breakdown = [];

        foreach ($cells as $cell) {
            if ($cell->cityFunction) {
                $fn = $cell->cityFunction;
                foreach (self::CATEGORIES as $slug => $col) {
                    $totals[$slug] += $fn->{$col};
                    $totalScore += $fn->{$col};
                }

                $breakdown[] = [
                    'row'                  => $cell->row_index,
                    'column'               => $cell->column_index,
                    'function'             => $fn->name,
                    'safety'               => $fn->{'Safety'},
                    'recreation'           => $fn->{'Recreation'},
                    'environment_quality'  => $fn->{'Environment Quality'},
                    'facilities'           => $fn->{'Facilities'},
                    'mobility'             => $fn->{'Mobility'},
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
