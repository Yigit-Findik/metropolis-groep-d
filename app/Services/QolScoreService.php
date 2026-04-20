<?php

namespace App\Services;

use App\Models\CityGridCell;

class QolScoreService
{
    public const CATEGORIES = ['livability', 'safety', 'economy', 'environment', 'welfare'];

    public function calculate(): array
    {
        $cells = CityGridCell::with('cityFunction')->get();

        $totals = array_fill_keys(self::CATEGORIES, 0);
        $totalScore = 0;
        $breakdown = [];

        foreach ($cells as $cell) {
            if ($cell->cityFunction) {
                $fn = $cell->cityFunction;
                foreach (self::CATEGORIES as $cat) {
                    $totals[$cat] += $fn->$cat;
                    $totalScore += $fn->$cat;
                }

                $breakdown[] = [
                    'row'         => $cell->row_index,
                    'column'      => $cell->column_index,
                    'function'    => $fn->name,
                    'livability'  => $fn->livability,
                    'safety'      => $fn->safety,
                    'economy'     => $fn->economy,
                    'environment' => $fn->environment,
                    'welfare'     => $fn->welfare,
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
