<?php

namespace App\Services;

use App\Models\CityGridCell;
use Illuminate\Support\Collection;

class QolScoreService
{
    private const SENSITIVE_FUNCTIONS = [
        'park',
        'school',
        'hospital',
    ];

    private const POLLUTERS = [
        'road',
        'store',
        'gas station',
    ];

    // Keys are slugs used in HTML/JS/API responses; values are the database column names.
    public const CATEGORIES = [
        'safety'              => 'Safety',
        'recreation'          => 'Recreation',
        'environment_quality' => 'Environment Quality',
        'facilities'          => 'Facilities',
        'mobility'            => 'Mobility',
    ];

    public function calculate(): array
    {
        // Load the grid with its related function once so the score can be built from the current state.
        $cells = CityGridCell::with('cityFunction')->get();

        $totals = array_fill_keys(array_keys(self::CATEGORIES), 0);
        $totalScore = 0;
        $breakdown = [];

        foreach ($cells as $cell) {
            if (! $cell->cityFunction) {
                continue;
            }

            $fn = $cell->cityFunction;
            $adjustments = $this->calculateCellAdjustments($cells, $cell);

            foreach (self::CATEGORIES as $slug => $col) {
                $categoryScore = (int) $fn->{$col} + $adjustments[$slug];
                $totals[$slug] += $categoryScore;
                $totalScore += $categoryScore;
            }

            // Keep a per-cell breakdown so the UI can explain how the score was composed.
            $breakdown[] = [
                'row'                  => $cell->row_index,
                'column'               => $cell->column_index,
                'function'             => $fn->name,
                'safety'               => (int) $fn->{'Safety'} + $adjustments['safety'],
                'recreation'           => (int) $fn->{'Recreation'} + $adjustments['recreation'],
                'environment_quality'  => (int) $fn->{'Environment Quality'} + $adjustments['environment_quality'],
                'facilities'           => (int) $fn->{'Facilities'} + $adjustments['facilities'],
                'mobility'             => (int) $fn->{'Mobility'} + $adjustments['mobility'],
            ];
        }

        return [
            'total_score' => $totalScore,
            'categories'  => $totals,
            'breakdown'   => $breakdown,
        ];
    }

    private function calculateCellAdjustments(Collection $cells, CityGridCell $cell): array
    {
        $adjustments = array_fill_keys(array_keys(self::CATEGORIES), 0);
        $function = $cell->cityFunction;

        if (! $function) {
            return $adjustments;
        }

        $functionCategory = $this->categoryKey($function->category);

        $sameCategoryNeighbors = $this->orthogonalNeighbors($cells, $cell)
            ->filter(function (CityGridCell $neighbor) use ($function): bool {
                return $neighbor->cityFunction !== null
                    && $this->categoryKey($neighbor->cityFunction->category) === $this->categoryKey($function->category);
            })
            ->filter(function (CityGridCell $neighbor) use ($cell): bool {
                return $this->isLaterCell($cell, $neighbor);
            })
            ->count();

        if ($sameCategoryNeighbors > 0 && array_key_exists($functionCategory, $adjustments)) {
            $adjustments[$functionCategory] += $sameCategoryNeighbors * 2;
        }

        if (! $this->isSensitiveFunction($function->name)) {
            return $adjustments;
        }

        $pollutingNeighbors = $this->orthogonalNeighbors($cells, $cell)
            ->filter(function (CityGridCell $neighbor): bool {
                $neighborName = $this->normalizedFunctionName($neighbor->cityFunction?->name);

                if ($neighborName === '') {
                    return false;
                }

                return in_array($neighborName, self::POLLUTERS, true)
                    || collect(self::POLLUTERS)->contains(fn (string $polluter) => str_contains($neighborName, $polluter));
            })
            ->count();

        if ($pollutingNeighbors > 0 && array_key_exists($functionCategory, $adjustments)) {
            $adjustments[$functionCategory] -= $pollutingNeighbors * 2;
        }

        return $adjustments;
    }

    private function orthogonalNeighbors(Collection $cells, CityGridCell $cell): Collection
    {
        return $cells->filter(function (CityGridCell $candidate) use ($cell): bool {
            if ($candidate->row_index === $cell->row_index && abs($candidate->column_index - $cell->column_index) === 1) {
                return true;
            }

            if ($candidate->column_index === $cell->column_index && abs($candidate->row_index - $cell->row_index) === 1) {
                return true;
            }

            return false;
        });
    }

    private function categoryKey(?string $category): string
    {
        $normalized = $this->normalizedText($category);

        if ($normalized === 'environment quality') {
            return 'environment_quality';
        }

        return str_replace(' ', '_', $normalized);
    }

    private function isSensitiveFunction(?string $functionName): bool
    {
        return in_array($this->normalizedFunctionName($functionName), self::SENSITIVE_FUNCTIONS, true);
    }

    private function normalizedFunctionName(?string $functionName): string
    {
        return $this->normalizedText($functionName);
    }

    private function normalizedText(?string $value): string
    {
        return trim(mb_strtolower((string) $value));
    }

    private function isLaterCell(CityGridCell $currentCell, CityGridCell $neighborCell): bool
    {
        if ($neighborCell->row_index !== $currentCell->row_index) {
            return $neighborCell->row_index > $currentCell->row_index;
        }

        return $neighborCell->column_index > $currentCell->column_index;
    }
}
