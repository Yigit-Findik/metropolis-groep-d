<?php

namespace App\Services;

use App\Models\CityGridCell;

class QolScoreService
{
    public function calculate(): array
    {
        $cells = CityGridCell::with('cityFunction')->get();

        $totalScore = 0;
        $breakdown = [];

        foreach ($cells as $cell) {
            if ($cell->cityFunction) {
                $score = $cell->cityFunction->qol_score;
                $totalScore += $score;

                $breakdown[] = [
                    'row' => $cell->row_index,
                    'column' => $cell->column_index,
                    'function' => $cell->cityFunction->name,
                    'score' => $score,
                ];
            }
        }

        return [
            'total_score' => $totalScore,
            'breakdown' => $breakdown,
        ];
    }
}
