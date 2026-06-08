<?php

namespace App\Services;

use App\Models\CityEvent;
use App\Models\CityGridCell;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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

        // Build a per-function modifier map from all currently active events.
        $eventModifiers = $this->buildEventModifierMap();

        $totals = array_fill_keys(array_keys(self::CATEGORIES), 0);
        $bonusTotals = array_fill_keys(array_keys(self::CATEGORIES), 0);
        $penaltyTotals = array_fill_keys(array_keys(self::CATEGORIES), 0);
        $eventTotals = array_fill_keys(array_keys(self::CATEGORIES), 0);
        $totalScore = 0;
        $breakdown = [];

        foreach ($cells as $cell) {
            if (! $cell->cityFunction) {
                continue;
            }

            $fn = $cell->cityFunction;
            $adjustmentData = $this->calculateCellAdjustmentBreakdown($cells, $cell);
            $adjustments = $adjustmentData['adjustments'];
            $eventMods = $eventModifiers[$fn->id] ?? array_fill_keys(array_keys(self::CATEGORIES), 0);

            foreach (self::CATEGORIES as $slug => $col) {
                $categoryScore = (int) $fn->{$col} + $adjustments[$slug] + $eventMods[$slug];
                $totals[$slug] += $categoryScore;
                $bonusTotals[$slug] += $adjustmentData['bonus'][$slug];
                $penaltyTotals[$slug] += $adjustmentData['penalty'][$slug];
                $eventTotals[$slug] += $eventMods[$slug];
                $totalScore += $categoryScore;
            }

            // Keep a per-cell breakdown so the UI can explain how the score was composed.
            $breakdown[] = [
                'row'                  => $cell->row_index,
                'column'               => $cell->column_index,
                'function'             => $fn->name,
                'safety'               => (int) $fn->{'Safety'} + $adjustments['safety'] + $eventMods['safety'],
                'recreation'           => (int) $fn->{'Recreation'} + $adjustments['recreation'] + $eventMods['recreation'],
                'environment_quality'  => (int) $fn->{'Environment Quality'} + $adjustments['environment_quality'] + $eventMods['environment_quality'],
                'facilities'           => (int) $fn->{'Facilities'} + $adjustments['facilities'] + $eventMods['facilities'],
                'mobility'             => (int) $fn->{'Mobility'} + $adjustments['mobility'] + $eventMods['mobility'],
            ];
        }

        $totalBonus = array_sum($bonusTotals);
        $totalPenalty = array_sum($penaltyTotals);
        $totalEvent = array_sum($eventTotals);

        return [
            'total_score'        => $totalScore,
            'categories'         => $totals,
            'bonus_categories'   => $bonusTotals,
            'penalty_categories' => $penaltyTotals,
            'event_categories'   => $eventTotals,
            'total_event'        => $totalEvent,
            'total_bonus' => $totalBonus,
            'total_penalty' => $totalPenalty,
            'breakdown'   => $breakdown,
        ];
    }

    private function buildEventModifierMap(): array
    {
        $map = [];

        $activeEvents = CityEvent::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->with('cityFunctions')
            ->get();

        foreach ($activeEvents as $event) {
            if ($event->is_day_night_cycle) {
                $phase = $event->current_phase;
                if (! $phase) {
                    continue;
                }

                $phaseFunctions = DB::table('city_event_day_night_functions')
                    ->where('city_event_id', $event->id)
                    ->where('phase', $phase)
                    ->get();

                foreach ($phaseFunctions as $fn) {
                    $map[$fn->city_function_id] ??= array_fill_keys(array_keys(self::CATEGORIES), 0);
                    $map[$fn->city_function_id]['safety']              += (int) ($fn->safety_modifier ?? 0);
                    $map[$fn->city_function_id]['recreation']          += (int) ($fn->recreation_modifier ?? 0);
                    $map[$fn->city_function_id]['environment_quality'] += (int) ($fn->environment_quality_modifier ?? 0);
                    $map[$fn->city_function_id]['facilities']          += (int) ($fn->facilities_modifier ?? 0);
                    $map[$fn->city_function_id]['mobility']            += (int) ($fn->mobility_modifier ?? 0);
                }
            } else {
                foreach ($event->cityFunctions as $fn) {
                    $map[$fn->id] ??= array_fill_keys(array_keys(self::CATEGORIES), 0);
                    $map[$fn->id]['safety']               += (int) ($fn->pivot->safety_modifier ?? 0);
                    $map[$fn->id]['recreation']           += (int) ($fn->pivot->recreation_modifier ?? 0);
                    $map[$fn->id]['environment_quality']  += (int) ($fn->pivot->environment_quality_modifier ?? 0);
                    $map[$fn->id]['facilities']           += (int) ($fn->pivot->facilities_modifier ?? 0);
                    $map[$fn->id]['mobility']             += (int) ($fn->pivot->mobility_modifier ?? 0);
                }
            }
        }

        return $map;
    }

    private function calculateCellAdjustmentBreakdown(Collection $cells, CityGridCell $cell): array
    {
        $adjustments = array_fill_keys(array_keys(self::CATEGORIES), 0);
        $bonus = array_fill_keys(array_keys(self::CATEGORIES), 0);
        $penalty = array_fill_keys(array_keys(self::CATEGORIES), 0);
        $function = $cell->cityFunction;

        if (! $function) {
            return [
                'adjustments' => $adjustments,
                'bonus' => $bonus,
                'penalty' => $penalty,
            ];
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
            $value = $sameCategoryNeighbors * 2;
            $adjustments[$functionCategory] += $value;
            $bonus[$functionCategory] += $value;
        }

        if (! $this->isSensitiveFunction($function->name)) {
            return [
                'adjustments' => $adjustments,
                'bonus' => $bonus,
                'penalty' => $penalty,
            ];
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
            $value = $pollutingNeighbors * 2;
            $adjustments[$functionCategory] -= $value;
            $penalty[$functionCategory] -= $value;
        }

        return [
            'adjustments' => $adjustments,
            'bonus' => $bonus,
            'penalty' => $penalty,
        ];
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
